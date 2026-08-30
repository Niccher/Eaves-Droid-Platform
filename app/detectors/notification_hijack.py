"""
Notification Interception Guard — flags user-installed apps running in the
foreground at the exact moment a sensitive notification (like OTP, M-Pesa PIN,
or banking confirmation) is delivered to the device.
"""

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

SENSITIVE_KEYWORDS = [
    "otp", "pin", "verification", "code", "confirm", "transaction",
    "mpesa", "m-pesa", "bank", "token", "password", "security", "credentials"
]

SAFE_PACKAGES = {
    "com.android.systemui", "com.android.launcher", "com.google.android.inputmethod.latin",
    "android", "com.sec.android.app.launcher", "com.huawei.android.launcher",
    "com.oppo.launcher", "com.miui.home", "com.google.android.apps.nexuslauncher"
}

class NotificationHijackDetector(BaseDetector):
    algorithm_id = "notification_hijack"
    algorithm_name = "Notification Interception Guard"
    category = "apps"

    async def detect(self, user_id: int, scope: str = "full",
                      incremental_since: str | None = None,
                      params: dict | None = None) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine
        from datetime import datetime

        # 1. Fetch recent notifications
        notif_where = "owner_id = :uid"
        notif_params = {"uid": user_id}
        if scope == "incremental" and incremental_since:
            notif_where += " AND created_at > :since"
            notif_params["since"] = incremental_since

        notif_sql = text(f"""
            SELECT package_name, app_name, title, text, notification_timestamp, created_at
            FROM tbl_extracted_notifications
            WHERE {notif_where}
            ORDER BY notification_timestamp DESC
            LIMIT 1000
        """)

        # 2. Fetch running process snapshots
        proc_sql = text("""
            SELECT pd.process_name, pd.importance, rp.extracted_at
            FROM tbl_system_running_process_details pd
            JOIN tbl_running_processes rp ON pd.running_processes_id = rp.id
            WHERE rp.owner_id = :uid AND pd.importance = 100
            ORDER BY rp.extracted_at DESC
            LIMIT 5000
        """)

        with get_engine().connect() as conn:
            notifications = conn.execute(notif_sql, notif_params).mappings().fetchall()
            processes = conn.execute(proc_sql, {"uid": user_id}).mappings().fetchall()

        if not notifications or not processes:
            return []

        # Find anomalies
        results = []
        already_flagged = set()

        for notif in notifications:
            title = (notif["title"] or "").lower()
            body = (notif["text"] or "").lower()
            notif_pkg = notif["package_name"] or ""
            notif_time = notif["notification_timestamp"] or 0

            # Check if notification contains sensitive keywords
            is_sensitive = any(w in title or w in body for w in SENSITIVE_KEYWORDS)
            if not is_sensitive or notif_time <= 0:
                continue

            # Look for active foreground processes around notification timestamp (+/- 10 seconds)
            for proc in processes:
                proc_name = proc["process_name"] or ""
                proc_time = proc["extracted_at"] or 0

                # Skip system apps, launchers, keyboards, and the app that posted the notification itself
                if proc_name == notif_pkg or proc_name in SAFE_PACKAGES or any(proc_name.startswith(p) for p in ["com.android.", "com.google.android."]):
                    continue

                # Proximity window: 10 seconds
                if abs(proc_time - notif_time) <= 10000:
                    key = (proc_name, notif_time)
                    if key in already_flagged:
                        continue
                    already_flagged.add(key)

                    results.append(AnomalyResult(
                        algorithm=self.algorithm_name,
                        algorithm_id=self.algorithm_id,
                        category=self.category,
                        severity="High",
                        anomaly=(
                            f"App '{proc_name}' was active in the foreground when a sensitive "
                            f"notification from '{notif['app_name'] or notif_pkg}' was delivered."
                        ),
                        score=0.92,
                        event_timestamp=notif["created_at"].strftime("%Y-%m-%d %H:%M:%S") if notif["created_at"] else "",
                        details={
                            "hijacker_app": proc_name,
                            "notification_source": notif_pkg,
                            "notification_title": notif["title"] or "",
                            "notification_body": notif["text"][:100] if notif["text"] else "",
                            "timestamp_diff_ms": abs(proc_time - notif_time)
                        }
                    ))

        # Cap results at 10 to avoid alert fatigue
        results.sort(key=lambda r: -r.score)
        return results[:10]
