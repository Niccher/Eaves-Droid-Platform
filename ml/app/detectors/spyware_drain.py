from __future__ import annotations

import logging
from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult
from sqlalchemy import text
from app.utils.db import get_engine

logger = logging.getLogger(__name__)

class SpywareDrainDetector(BaseDetector):
    algorithm_id   = "spyware_drain"
    algorithm_name = "Covert Surveillance Suspected (Drain)"
    category       = "device_info"

    async def detect(
        self,
        user_id: int,
        scope: str = "full",
        incremental_since: str | None = None,
        params: dict | None = None,
    ) -> list[AnomalyResult]:
        
        # We need recent health checks (battery, screen status) and locations
        with get_engine().connect() as conn:
            device_rows = conn.execute(text("SELECT device_id FROM tbl_device_profiles WHERE owner_id = :uid"), {"uid": user_id}).fetchall()
            
            results = []
            for (did,) in device_rows:
                # Fetch recent health checks for this device (last 12 hours)
                health_rows = conn.execute(text("""
                    SELECT battery_level, is_screen_on, created_at 
                    FROM tbl_device_health_checks 
                    WHERE device_id = :did 
                    ORDER BY created_at DESC 
                    LIMIT 20
                """), {"did": did}).fetchall()
                
                # Fetch recent locations
                loc_rows = conn.execute(text("""
                    SELECT latitude, longitude, created_at 
                    FROM tbl_extracted_locations 
                    WHERE token_owner_id = :uid 
                    ORDER BY created_at DESC 
                    LIMIT 20
                """), {"uid": user_id}).fetchall()
                
                if len(health_rows) < 5 or len(loc_rows) < 2:
                    continue
                    
                # Basic heuristic check:
                # Is the screen off continuously?
                screen_mostly_off = all(r.is_screen_on == 0 for r in health_rows[:5])
                
                # Is battery dropping fast? (e.g. dropped by > 10% in recent checks)
                latest_batt = health_rows[0].battery_level
                older_batt = health_rows[-1].battery_level
                fast_drain = (older_batt - latest_batt) > 10
                
                # Is location completely static?
                l1 = loc_rows[0]
                l2 = loc_rows[1]
                static_loc = abs(l1.latitude - l2.latitude) < 0.0001 and abs(l1.longitude - l2.longitude) < 0.0001
                
                if screen_mostly_off and fast_drain and static_loc:
                    results.append(AnomalyResult(
                        algorithm=self.algorithm_name,
                        algorithm_id=self.algorithm_id,
                        category=self.category,
                        severity="High",
                        anomaly=f"Rapid battery drain ({older_batt}% -> {latest_batt}%) while stationary and screen off. Potential covert streaming.",
                        score=0.92,
                        event_timestamp=health_rows[0].created_at.isoformat() if hasattr(health_rows[0].created_at, 'isoformat') else str(health_rows[0].created_at),
                        details={
                            "battery_drop": float(older_batt - latest_batt),
                            "screen_state": "off",
                            "location_state": "static"
                        }
                    ))
            
            return results
