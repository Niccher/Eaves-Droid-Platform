"""
BERT Semantic Phishing Classifier — analyses SMS message bodies for
phishing indicators (urgent language, suspicious keywords, etc.).

Queries ``tbl_sms`` directly from the shared MySQL database.
"""

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

# Heuristic keywords that correlate with phishing / social-engineering SMS
URGENCY_KEYWORDS = [
    "urgent", "verify", "account", "login", "click here",
    "suspended", "password", "bank", "security", "alert",
    "confirm", "update", "restricted", "unusual activity",
]


class BERTPhishingDetector(BaseDetector):
    algorithm_id = "sms_bert"
    algorithm_name = "BERT Semantic Phishing Classifier"
    category = "sms"

    async def detect(self, user_id: int, scope: str = "full",
                     incremental_since: str | None = None) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine

        # Build the WHERE clause based on scope
        where = "owner_id = :uid"
        params: dict = {"uid": user_id}
        if scope == "incremental" and incremental_since:
            where += " AND created_at > :since"
            params["since"] = incremental_since

        sql = text(f"""
            SELECT body, address,
                   DATE_FORMAT(FROM_UNIXTIME(sms_date/1000), '%Y-%m-%d %H:%i:%s') AS ts
            FROM tbl_sms
            WHERE {where}
            ORDER BY sms_date DESC
            LIMIT 2000
        """)

        with get_engine().connect() as conn:
            rows = conn.execute(sql, params).mappings().fetchall()

        if not rows:
            return []

        results = []
        for row in rows:
            body = (row["body"] or "").strip()
            if not body:
                continue

            # Simple keyword-hit scoring (placeholder for a real BERT model)
            hits = sum(1 for kw in URGENCY_KEYWORDS if kw in body.lower())
            if hits < 2:
                continue

            score = min(0.50 + hits * 0.12, 0.95)
            results.append(AnomalyResult(
                algorithm=self.algorithm_name,
                algorithm_id=self.algorithm_id,
                category=self.category,
                severity="High" if score >= 0.85 else "Medium",
                anomaly=(
                    f"Suspicious message from '{row['address'] or 'unknown'}': "
                    f"{body[:100]}… ({hits} phishing indicators)"
                ),
                score=round(score, 4),
                event_timestamp=str(row["ts"] or ""),
                details={
                    "sender": row["address"],
                    "body_preview": body[:300],
                    "phishing_keyword_hits": hits,
                },
            ))

        return results[:15]
