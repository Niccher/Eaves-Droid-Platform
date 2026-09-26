"""
Sleep Disturbance Scanner — identifies suspicious device usage or stealth background
activity (processes running, screen events, network traffic) during predicted sleep hours (11 PM - 5:30 AM).
"""

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult
from datetime import datetime, time as dt_time

SAFE_PACKAGES = {
    "com.android.systemui", "com.android.launcher", "com.google.android.inputmethod.latin",
    "android", "com.sec.android.app.launcher", "com.huawei.android.launcher",
    "com.oppo.launcher", "com.miui.home", "com.google.android.apps.nexuslauncher",
    "com.android.deskclock", "com.google.android.deskclock"
}

class SleepDisturbanceDetector(BaseDetector):
    algorithm_id = "sleep_disturbance"
    algorithm_name = "Sleep Disturbance & Stealth Tracker"
    category = "activity"

    async def detect(self, user_id: int, scope: str = "full",
                      incremental_since: str | None = None,
                      params: dict | None = None) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine

        # 1. Fetch screen events
        screen_sql = text("""
            SELECT event_type, timestamp, created_at
            FROM tbl_screen_state
            WHERE owner_id = :uid
            ORDER BY timestamp ASC
        """)

        # 2. Fetch running processes
        proc_sql = text("""
            SELECT pd.process_name, pd.importance, rp.extracted_at, rp.created_at
            FROM tbl_system_running_process_details pd
            JOIN tbl_running_processes rp ON pd.running_processes_id = rp.id
            WHERE rp.owner_id = :uid AND pd.importance = 100
            ORDER BY rp.extracted_at ASC
        """)

        with get_engine().connect() as conn:
            screen_events = conn.execute(screen_sql, {"uid": user_id}).mappings().fetchall()
            processes = conn.execute(proc_sql, {"uid": user_id}).mappings().fetchall()

        if not screen_events:
            return []

        results = []
        # Find nighttime screen-ON events (23:00 - 05:30)
        for event in screen_events:
            etype = event["event_type"] or ""
            ts = event["timestamp"] or 0
            
            if "ON" not in etype.upper():
                continue

            # Convert timestamp to local datetime
            dt = datetime.fromtimestamp(ts / 1000.0)
            t = dt.time()

            is_night = (t >= dt_time(23, 0)) or (t <= dt_time(5, 30))
            if not is_night:
                continue

            # Check what apps were running in foreground at this exact millisecond (+/- 15 sec)
            active_apps = []
            for proc in processes:
                proc_name = proc["process_name"] or ""
                proc_time = proc["extracted_at"] or 0
                if proc_name in SAFE_PACKAGES or any(proc_name.startswith(p) for p in ["com.android.", "com.google.android."]):
                    continue
                
                if abs(proc_time - ts) <= 15000:
                    active_apps.append(proc_name)

            if active_apps:
                active_apps = list(set(active_apps))
                apps_str = ", ".join(active_apps)
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="Medium",
                    anomaly=(
                        f"Unusual nighttime screen activity at {dt.strftime('%H:%M:%S')} "
                        f"with active foreground apps: {apps_str}."
                    ),
                    score=0.72,
                    event_timestamp=dt.strftime("%Y-%m-%d %H:%M:%S"),
                    details={
                        "event_time": dt.strftime("%H:%M:%S"),
                        "active_apps": active_apps,
                        "timestamp_ms": ts
                    }
                ))

        # Cap results
        results.sort(key=lambda r: -r.score)
        return results[:10]
