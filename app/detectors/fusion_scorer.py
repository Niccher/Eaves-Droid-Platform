from __future__ import annotations

import logging
import os
import joblib
import numpy as np
from datetime import datetime, timedelta

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult
from sqlalchemy import text
from sklearn.ensemble import IsolationForest

logger = logging.getLogger(__name__)

MODEL_PATH = "models/fusion_iforest.pkl"

class FusionScorerDetector(BaseDetector):
    algorithm_id   = "fusion_scorer"
    algorithm_name = "Sensor Fusion Multi-Modal Score"
    category       = "system"

    async def detect(
        self,
        user_id: int,
        scope: str = "full",
        incremental_since: str | None = None,
        params: dict | None = None,
    ) -> list[AnomalyResult]:
        from app.utils.db import get_engine
        
        # In a full implementation, we'd extract the actual complex features:
        # battery delta, location jump, app install delta, call frequency spike,
        # sms odd hours, new contact ratio, wifi ssid changes, root flag.
        
        # Here we do a simplified heuristic + Isolation Forest extraction
        with get_engine().connect() as conn:
            # 1. Fetch recent health checks for battery/wifi
            health_rows = conn.execute(
                text("""
                    SELECT battery_level, wifi_ssid, created_at
                    FROM tbl_device_health_checks
                    WHERE device_id IN (
                        SELECT device_id FROM tbl_device_profiles WHERE owner_id = :uid
                    )
                    ORDER BY created_at DESC LIMIT 50
                """),
                {"uid": user_id}
            ).mappings().fetchall()

            # 2. Fetch calls in last 7 days vs previous 7 days
            calls_current = conn.execute(
                text("""
                    SELECT COUNT(*) as c FROM tbl_extracted_call_logs
                    WHERE owner_id = :uid AND timestamp > :recent
                """),
                {"uid": user_id, "recent": int((datetime.now() - timedelta(days=7)).timestamp() * 1000)}
            ).scalar() or 0

            calls_prev = conn.execute(
                text("""
                    SELECT COUNT(*) as c FROM tbl_extracted_call_logs
                    WHERE owner_id = :uid AND timestamp > :older AND timestamp <= :recent
                """),
                {
                    "uid": user_id, 
                    "older": int((datetime.now() - timedelta(days=14)).timestamp() * 1000),
                    "recent": int((datetime.now() - timedelta(days=7)).timestamp() * 1000)
                }
            ).scalar() or 0
            
            # 3. SMS at odd hours (1am - 5am)
            sms_odd = conn.execute(
                text("""
                    SELECT COUNT(*) as c FROM tbl_extracted_sms
                    WHERE owner_id = :uid AND HOUR(FROM_UNIXTIME(date / 1000)) BETWEEN 1 AND 5
                """),
                {"uid": user_id}
            ).scalar() or 0
            
            # Compute heuristic features
            battery_drops = 0
            wifi_changes = 0
            if len(health_rows) > 1:
                for i in range(len(health_rows) - 1):
                    if health_rows[i+1]['battery_level'] - health_rows[i]['battery_level'] > 15:
                        battery_drops += 1
                    if health_rows[i]['wifi_ssid'] != health_rows[i+1]['wifi_ssid']:
                        wifi_changes += 1
                        
            call_freq_spike = max(0, calls_current - calls_prev) / (calls_prev + 1)
            
            features = {
                "battery_delta": battery_drops,
                "location_jump": 0, # Placeholder
                "app_install_delta": 0, # Placeholder
                "call_freq_spike": float(call_freq_spike),
                "sms_odd_hours": int(sms_odd),
                "new_contact_ratio": 0.0, # Placeholder
                "wifi_ssid_changes": wifi_changes,
                "root_flag_changed": 0 # Placeholder
            }
            
            # Vectorize
            vec = np.array([[
                features["battery_delta"],
                features["location_jump"],
                features["app_install_delta"],
                features["call_freq_spike"],
                features["sms_odd_hours"],
                features["new_contact_ratio"],
                features["wifi_ssid_changes"],
                features["root_flag_changed"],
            ]])
            
            # Ensure models dir exists
            os.makedirs("models", exist_ok=True)
            
            # Train model if not exists (dummy logic for local test)
            if not os.path.exists(MODEL_PATH):
                # Make dummy training data
                dummy_X = np.zeros((100, 8))
                dummy_X[:, 4] = np.random.randint(0, 2, 100) # sms odd hours
                dummy_X[:, 3] = np.random.uniform(0, 0.5, 100) # call spike
                dummy_X = np.vstack([dummy_X, vec]) # Include current
                
                model = IsolationForest(contamination=0.05, random_state=42)
                model.fit(dummy_X)
                joblib.dump(model, MODEL_PATH)
            
            model = joblib.load(MODEL_PATH)
            
            # Score
            raw = model.score_samples(vec)[0]
            # Normalize to 0.0 (normal) to 1.0 (anomalous)
            score = float(np.clip(1 - (raw + 0.5) / 1.0, 0.0, 1.0))
            
            results = []
            
            if score > 0.6:
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="High" if score > 0.8 else "Medium",
                    anomaly=f"Sensor Fusion identified multi-modal anomalous behavior (Risk Score: {score:.2f})",
                    score=score,
                    event_timestamp=datetime.now().isoformat(),
                    details=features
                ))
                
            return results
