"""
Accessibility Abuse Detector — flags non-system applications that request and hold
active Accessibility Service permissions with high-risk capabilities like window
content retrieval, overlay generation, or tap simulation.
"""

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

class AccessibilityAbuseDetector(BaseDetector):
    algorithm_id = "accessibility_abuse"
    algorithm_name = "Accessibility Abuse Detector"
    category = "apps"

    async def detect(self, user_id: int, scope: str = "full",
                      incremental_since: str | None = None,
                      params: dict | None = None) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine

        # Query active accessibility services
        services_where = "owner_id = :uid"
        params_dict = {"uid": user_id}
        if scope == "incremental" and incremental_since:
            services_where += " AND created_at > :since"
            params_dict["since"] = incremental_since

        sql = text(f"""
            SELECT service_id, package_name, description, capabilities, can_retrieve_window_content, created_at
            FROM tbl_system_accessibility_services
            WHERE {services_where}
            ORDER BY created_at DESC
        """)

        with get_engine().connect() as conn:
            rows = conn.execute(sql, params_dict).mappings().fetchall()

        if not rows:
            return []

        results = []
        for row in rows:
            pkg = row["package_name"] or ""
            service_id = row["service_id"] or ""
            caps = (row["capabilities"] or "").lower()
            desc = (row["description"] or "").lower()
            can_retrieve = row["can_retrieve_window_content"] or 0

            # Skip verified safe/system app packages
            if pkg in ["com.google.android.marvin.talkback", "com.android.talkback", "android"]:
                continue
            if any(pkg.startswith(prefix) for prefix in ["com.android.providers", "com.google.android.apps.accessibility"]):
                continue

            score = 0.50
            reasons = []

            # Check for window content scraping
            if can_retrieve == 1:
                score = max(score, 0.88)
                reasons.append("can retrieve screen/window content (potential spyware/credential harvester)")

            # Check for overlay or keystroke capabilities in description/caps
            for kw in ["keystroke", "keylogger", "overlay", "gesture", "tap", "click", "simulate", "input"]:
                if kw in caps or kw in desc:
                    score = max(score, 0.75 if score < 0.75 else score + 0.05)
                    reasons.append(f"capability indicators mention '{kw}'")

            # If it's a completely unknown 3rd-party utility app holding accessibility
            reason_str = "; ".join(reasons) if reasons else "holds active accessibility service"
            results.append(AnomalyResult(
                algorithm=self.algorithm_name,
                algorithm_id=self.algorithm_id,
                category=self.category,
                severity="High" if score >= 0.85 else "Medium",
                anomaly=(
                    f"Application '{pkg}' holds active accessibility service with elevated risk capabilities: {reason_str}."
                ),
                score=round(score, 2),
                event_timestamp=row["created_at"].strftime("%Y-%m-%d %H:%M:%S") if row["created_at"] else "",
                details={
                    "package_name": pkg,
                    "service_id": service_id,
                    "can_retrieve_window_content": bool(can_retrieve),
                    "capabilities": row["capabilities"] or "",
                    "description": row["description"] or ""
                }
            ))

        results.sort(key=lambda r: -r.score)
        return results
