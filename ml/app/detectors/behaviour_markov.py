from __future__ import annotations

import logging
import os
import joblib
import numpy as np
from datetime import datetime

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult
from sqlalchemy import text

logger = logging.getLogger(__name__)

MODEL_PATH = "models/behaviour_markov.pkl"

class BehaviourMarkovDetector(BaseDetector):
    algorithm_id   = "behaviour_markov"
    algorithm_name = "Markov Chain Behaviour Modelling"
    category       = "activity"

    async def detect(
        self,
        user_id: int,
        scope: str = "full",
        incremental_since: str | None = None,
        params: dict | None = None,
    ) -> list[AnomalyResult]:
        from app.utils.db import get_engine
        
        # Simplified heuristic to generate a sequence of states for the user
        # In a real app, this would query tbl_extracted_locations and use clustering
        # to determine home, work, transit, unknown.
        # For this skeleton, we'll build a synthetic recent sequence based on health check times
        
        with get_engine().connect() as conn:
            # Try to fetch some real location/health data to base the sequence on
            health_rows = conn.execute(
                text("""
                    SELECT created_at
                    FROM tbl_device_health_checks
                    WHERE device_id IN (
                        SELECT device_id FROM tbl_device_profiles WHERE owner_id = :uid
                    )
                    ORDER BY created_at DESC LIMIT 24
                """),
                {"uid": user_id}
            ).fetchall()
            
            # If no data, return early
            if not health_rows:
                return []
                
            # Build a mock sequence representing the last 24 hours (0=home, 1=work, 2=transit, 3=unknown, 4=offline)
            # In a full implementation, you would map GPS coords to these states.
            recent_sequence = [np.random.choice([0, 1, 2, 3, 4], p=[0.5, 0.3, 0.1, 0.05, 0.05]) for _ in range(len(health_rows))]
            
            # Train if model doesn't exist
            os.makedirs("models", exist_ok=True)
            
            try:
                from hmmlearn import hmm
            except ImportError:
                logger.error("hmmlearn not installed! Cannot run Markov Behaviour detector.")
                return []
                
            if not os.path.exists(MODEL_PATH):
                # Generate 30 days of "normal" baseline sequences
                training_seqs = []
                lengths = []
                for _ in range(30):
                    # Normal pattern: lots of 0 and 1
                    seq = [np.random.choice([0, 1, 2], p=[0.6, 0.3, 0.1]) for _ in range(24)]
                    training_seqs.append(seq)
                    lengths.append(len(seq))
                
                X = np.concatenate([np.array(seq).reshape(-1, 1) for seq in training_seqs])
                
                model = hmm.CategoricalHMM(n_components=5, n_iter=100)
                model.fit(X, lengths)
                joblib.dump(model, MODEL_PATH)
            
            model = joblib.load(MODEL_PATH)
            
            # Score recent sequence
            X_new = np.array(recent_sequence).reshape(-1, 1)
            try:
                log_prob = model.score(X_new)
            except Exception as e:
                logger.error(f"HMM scoring failed: {e}")
                return []
                
            # If log probability is very low, it's anomalous
            # For 24 steps, a log_prob < -40 might be unusual
            threshold = -35.0
            
            results = []
            if log_prob < threshold:
                score = min(1.0, abs(log_prob) / 100.0)
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="High" if score > 0.8 else "Medium",
                    anomaly=f"Unusual routine/behavior sequence detected (Log Likelihood: {log_prob:.2f})",
                    score=score,
                    event_timestamp=datetime.now().isoformat(),
                    details={
                        "log_likelihood": round(log_prob, 2),
                        "sequence_length": len(recent_sequence),
                        "reason": "improbable_state_sequence"
                    }
                ))
                
            return results
