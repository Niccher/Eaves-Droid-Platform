"""
File Entropy & Encryption Scanner — flags files whose metadata
(extension, path depth, location in Android data directories, etc.)
suggests they may be encrypted payloads, ransomware artefacts, or
hidden executables.

NOTE: Actual byte-level Shannon entropy scanning is not possible here
because the PHP side only sends file *metadata* (name, path, timestamp)
from tbl_device_files.  If raw file access is needed in the future, add
a volume mount and scan via ``os.path.getsize()`` / ``open()``.

Queries ``tbl_device_files`` directly from the shared MySQL database.
"""

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

HIGH_RISK_EXTENSIONS = {
    ".apk", ".dex", ".jar", ".zip", ".rar", ".7z", ".tar.gz",
    ".enc", ".encrypted", ".crypt", ".locked", ".rns", ".rrk",
    ".ezz", ".exe", ".bin", ".dat",
}

SUSPICIOUS_KEYWORDS = [
    "encrypt", "decrypt", "ransom", "locked", "secret",
    "payload", "backup", "cache", "temp", "hidden",
    "stolen", "leak", "export", "dump",
]


class FileEntropyDetector(BaseDetector):
    algorithm_id = "files_entropy"
    algorithm_name = "File Entropy & Encryption Scanner"
    category = "files"

    async def detect(self, user_id: int, scope: str = "full",
                     incremental_since: str | None = None,
                     params: dict | None = None) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine

        where = "owner_id = :uid"
        params: dict = {"uid": user_id}
        if scope == "incremental" and incremental_since:
            where += " AND created_at > :since"
            params["since"] = incremental_since

        sql = text(f"""
            SELECT name AS file_name, created_at
            FROM tbl_device_files
            WHERE {where}
            ORDER BY created_at DESC
            LIMIT 3000
        """)
        with get_engine().connect() as conn:
            rows = conn.execute(sql, params).mappings().fetchall()

        if not rows:
            return []

        results = []
        for row in rows:
            fname = (row["file_name"] or "").strip()
            if not fname:
                continue

            ext = ("." + fname.rsplit(".", 1)[1].lower()) if "." in fname else ""
            name_lower = fname.lower()
            parts = fname.replace("\\", "/").split("/")
            depth = len(parts)
            basename = parts[-1] if parts else fname
            in_android = "android/data" in name_lower or "android/obb" in name_lower
            is_hidden = basename.startswith(".")
            is_high = ext in HIGH_RISK_EXTENSIONS
            word_hits = sum(1 for w in SUSPICIOUS_KEYWORDS if w in name_lower)

            score = 0.0
            reasons = []

            if is_high and in_android:
                score = max(score, 0.85)
                reasons.append(f"'{ext}' in Android data dir")
            elif is_high:
                score = max(score, 0.60)
                reasons.append(f"high-risk extension '{ext}'")

            if word_hits >= 2:
                score = max(score, 0.4 + word_hits * 0.1)
                reasons.append(f"{word_hits} keyword hits")

            if is_hidden and is_high:
                score = max(score, 0.70)
                reasons.append("hidden + high-risk")

            if depth >= 4 and in_android and is_high:
                score = max(score, 0.70)
                reasons.append("deep in Android data")

            if depth >= 5 and is_hidden:
                score = max(score, 0.50)
                reasons.append("deep hidden file")

            score = min(score, 0.98)
            if score >= 0.50:
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="High" if score >= 0.75 else "Medium",
                    anomaly=f"Suspicious file '{fname[:80]}' — {'; '.join(reasons[:2])}",
                    score=round(score, 4),
                    event_timestamp=str(row["created_at"] or ""),
                    details={
                        "file_name": fname,
                        "extension": ext,
                        "depth": depth,
                        "in_android_data": in_android,
                        "is_hidden": is_hidden,
                        "reasons": reasons,
                    },
                ))

        return results[:15]
