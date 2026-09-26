"""
Communication Spikes Detector — flags contacts with statistically abnormal
spikes in communication frequency (Z > 3.0σ) compared to their historical average.
"""

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

class CommunicationSpikesDetector(BaseDetector):
    algorithm_id = "communication_spikes"
    algorithm_name = "Communication Spikes Detector"
    category = "contacts"

    async def detect(self, user_id: int, scope: str = "full",
                      incremental_since: str | None = None,
                      params: dict | None = None) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine
        import numpy as np

        # Query daily SMS counts per contact
        sms_sql = text("""
            SELECT address, DATE(FROM_UNIXTIME(sms_date/1000)) as date_day, COUNT(*) as count
            FROM tbl_extracted_sms
            WHERE owner_id = :uid AND sms_date > 0
            GROUP BY address, date_day
        """)

        # Query daily Call counts per contact
        call_sql = text("""
            SELECT phone_number as address, DATE(FROM_UNIXTIME(call_date/1000)) as date_day, COUNT(*) as count
            FROM tbl_extracted_call_logs
            WHERE owner_id = :uid AND call_date > 0
            GROUP BY phone_number, date_day
        """)

        with get_engine().connect() as conn:
            sms_days = conn.execute(sms_sql, {"uid": user_id}).mappings().fetchall()
            call_days = conn.execute(call_sql, {"uid": user_id}).mappings().fetchall()

        # Combine SMS and Call counts per contact per day
        contact_history = {}
        for row in sms_days:
            addr = row["address"] or ""
            day = str(row["date_day"])
            cnt = row["count"] or 0
            contact_history.setdefault(addr, {}).setdefault(day, 0)
            contact_history[addr][day] += cnt

        for row in call_days:
            addr = row["address"] or ""
            day = str(row["date_day"])
            cnt = row["count"] or 0
            contact_history.setdefault(addr, {}).setdefault(day, 0)
            contact_history[addr][day] += cnt

        if not contact_history:
            return []

        results = []
        for contact, day_counts in contact_history.items():
            counts = list(day_counts.values())
            if len(counts) < 3: # Need at least 3 days to establish standard deviation
                continue

            mean = np.mean(counts)
            std = np.std(counts)
            std = max(std, 1.0) # avoid division by zero

            for day, count in day_counts.items():
                # Enforce minimum threshold of 15 messages/calls per day to avoid triggering on tiny counts
                if count < 15:
                    continue

                z = (count - mean) / std
                if z > 3.0:
                    results.append(AnomalyResult(
                        algorithm=self.algorithm_name,
                        algorithm_id=self.algorithm_id,
                        category=self.category,
                        severity="High" if z > 4.5 else "Medium",
                        anomaly=(
                            f"Statistical communication spike with '{contact}': "
                            f"{count} interaction(s) on {day} (Z-Score: {z:.2f}σ, average: {mean:.1f})."
                        ),
                        score=min(0.50 + z * 0.10, 0.99),
                        event_timestamp=day + " 00:00:00",
                        details={
                            "contact": contact,
                            "date": day,
                            "interactions_count": count,
                            "historical_mean": round(mean, 2),
                            "z_score": round(z, 2)
                        }
                    ))

        results.sort(key=lambda r: -r.score)
        return results[:10]
