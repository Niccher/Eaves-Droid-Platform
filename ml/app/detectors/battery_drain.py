"""
Battery Drain Outlier Model — flags periods of abnormal battery depletion
while screen is off and device is not charging, indicating hidden spyware/miners.
"""

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

class BatteryDrainDetector(BaseDetector):
    algorithm_id = "battery_drain"
    algorithm_name = "Battery Drain Outlier Model"
    category = "device_info"

    async def detect(self, user_id: int, scope: str = "full",
                      incremental_since: str | None = None,
                      params: dict | None = None) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine
        import time

        # 1. Fetch battery stats
        battery_sql = text("""
            SELECT level_percent, is_charging, created_at
            FROM tbl_telemetry_battery_stats
            WHERE owner_id = :uid
            ORDER BY created_at ASC
            LIMIT 5000
        """)

        # 2. Fetch screen state events
        screen_sql = text("""
            SELECT event_type, timestamp
            FROM tbl_screen_state
            WHERE owner_id = :uid
            ORDER BY timestamp ASC
        """)

        with get_engine().connect() as conn:
            battery_stats = conn.execute(battery_sql, {"uid": user_id}).mappings().fetchall()
            screen_events = conn.execute(screen_sql, {"uid": user_id}).mappings().fetchall()

        if len(battery_stats) < 2:
            return []

        # Build screen-off intervals
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

        def is_screen_off(dt_obj) -> bool:
            if not off_intervals:
                return True # Fallback if no screen logs
            ts = int(dt_obj.timestamp() * 1000)
            for start, end in off_intervals:
                if start <= ts <= end:
                    return True
            return False

        results = []
        for i in range(len(battery_stats) - 1):
            curr = battery_stats[i]
            nxt = battery_stats[i+1]

            # Only analyze if not charging and screen was off
            if curr["is_charging"] == 1 or nxt["is_charging"] == 1:
                continue

            c_time = curr["created_at"]
            n_time = nxt["created_at"]
            if not is_screen_off(c_time) or not is_screen_off(n_time):
                continue

            diff_sec = (n_time - c_time).total_seconds()
            # We want interval between 2 minutes and 2 hours
            if diff_sec < 120 or diff_sec > 7200:
                continue

            diff_level = curr["level_percent"] - nxt["level_percent"]
            if diff_level <= 0:
                continue

            rate_per_minute = diff_level / (diff_sec / 60.0)

            # Threshold: 0.35% per minute (~21% per hour) during screen-off is highly anomalous
            if rate_per_minute >= 0.35:
                rate_per_hour = rate_per_minute * 60
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="High" if rate_per_hour >= 30 else "Medium",
                    anomaly=(
                        f"Critical stealth battery drain detected: depletion rate of "
                        f"{rate_per_hour:.1f}% per hour while screen is off and not charging."
                    ),
                    score=min(0.50 + (rate_per_hour / 100.0) * 0.5, 0.99),
                    event_timestamp=n_time.strftime("%Y-%m-%d %H:%M:%S"),
                    details={
                        "start_level": curr["level_percent"],
                        "end_level": nxt["level_percent"],
                        "duration_minutes": round(diff_sec / 60.0, 1),
                        "drain_rate_percent_per_hour": round(rate_per_hour, 2)
                    }
                ))

        results.sort(key=lambda r: -r.score)
        return results[:10]
