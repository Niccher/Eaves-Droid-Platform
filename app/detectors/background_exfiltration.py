"""
Background Data Exfiltration Profiler — flags apps that transmit an abnormally
large volume of network data (high uploads) while the device screen is off.
"""

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

class BackgroundExfiltrationDetector(BaseDetector):
    algorithm_id = "background_exfiltration"
    algorithm_name = "Background Data Exfiltration Profiler"
    category = "device_info"

    async def detect(self, user_id: int, scope: str = "full",
                      incremental_since: str | None = None,
                      params: dict | None = None) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine
        import numpy as np
        import time

        # 1. Fetch screen state events
        screen_sql = text("""
            SELECT event_type, timestamp
            FROM tbl_screen_state
            WHERE owner_id = :uid
            ORDER BY timestamp ASC
        """)

        # 2. Fetch data usage uploads
        usage_where = "owner_id = :uid"
        usage_params = {"uid": user_id}
        if scope == "incremental" and incremental_since:
            usage_where += " AND created_at > :since"
            usage_params["since"] = incremental_since

        usage_sql = text(f"""
            SELECT package_name, tx_bytes, extracted_at, created_at
            FROM tbl_data_usage
            WHERE {usage_where}
            ORDER BY extracted_at ASC
        """)

        with get_engine().connect() as conn:
            screen_events = conn.execute(screen_sql, {"uid": user_id}).mappings().fetchall()
            data_usages = conn.execute(usage_sql, usage_params).mappings().fetchall()

        if not screen_events or not data_usages:
            return []

        # -- Build screen-off intervals -------------------------
        off_intervals = []
        start_off = None

        for event in screen_events:
            etype = event["event_type"] or ""
            ts = event["timestamp"] or 0
            if "OFF" in etype.upper():
                start_off = ts
            elif ("ON" in etype.upper() or "PRESENT" in etype.upper()) and start_off is not None:
                off_intervals.append((start_off, ts))
                start_off = None

        if start_off is not None:
            off_intervals.append((start_off, int(time.time() * 1000)))

        if not off_intervals:
            return []

        # Helper to check if a timestamp falls inside any screen-off interval
        def is_screen_off(ts: int) -> bool:
            for start, end in off_intervals:
                if start <= ts <= end:
                    return True
            return False

        # -- Aggregate uploads during screen-off ----------------
        pkg_uploads = {}
        row_created_map = {}

        for row in data_usages:
            pkg = row["package_name"] or "unknown"
            tx = row["tx_bytes"] or 0
            ts = row["extracted_at"] or 0

            # Skip common system or known-safe packages
            if pkg in ["android", "com.android.providers.media", "com.google.android.gms"]:
                continue

            if is_screen_off(ts):
                pkg_uploads[pkg] = pkg_uploads.get(pkg, 0) + tx
                row_created_map[pkg] = row["created_at"]

        if not pkg_uploads:
            return []

        # -- Run Z-Score anomaly detection ----------------------
        values = list(pkg_uploads.values())
        mean = np.mean(values)
        std = np.std(values)
        std = max(std, 1.0) # avoid division by zero

        results = []
        for pkg, upload_bytes in pkg_uploads.items():
            # Only flag significant uploads (> 5 MB) to avoid noise
            if upload_bytes < 5 * 1024 * 1024:
                continue

            z = (upload_bytes - mean) / std
            if z > 2.0:
                mb = upload_bytes / (1024 * 1024)
                created_time = row_created_map.get(pkg)
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="High" if z > 3.0 else "Medium",
                    anomaly=(
                        f"App '{pkg}' exfiltrated {mb:.1f} MB of background upload data "
                        f"while screen was off (Z-Score: {z:.2f}σ)."
                    ),
                    score=min(0.40 + z * 0.15, 0.99),
                    event_timestamp=created_time.strftime("%Y-%m-%d %H:%M:%S") if created_time else "",
                    details={
                        "package_name": pkg,
                        "background_upload_bytes": upload_bytes,
                        "background_upload_mb": round(mb, 2),
                        "device_mean_background_mb": round(mean / (1024 * 1024), 2),
                        "z_score": round(z, 2)
                    }
                ))

        results.sort(key=lambda r: -r.score)
        return results[:10]
