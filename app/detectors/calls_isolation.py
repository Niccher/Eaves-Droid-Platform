import numpy as np
from app.detectors.base import BaseDetector
from app.detectors.shared.isolation_forest import run_iforest
from app.models.schemas import AnomalyResult


class CallsIsolationDetector(BaseDetector):
    algorithm_id = "calls_isolation"
    algorithm_name = "Isolation Forest Outlier Detection"
    category = "call_logs"

    async def detect(self, data: list[dict], user_id: int | None = None) -> list[AnomalyResult]:
        if len(data) < 10:
            return []

        features = []
        timestamps = []
        for row in data:
            duration = float(row.get("duration", 0))
            direction = 1 if row.get("direction", "").lower() in ("outgoing", "dialled", "dialed") else 0
            hour = 12
            try:
                ts = row.get("timestamp", "")
                if ts:
                    hour = __import__("datetime").datetime.fromisoformat(ts).hour
            except Exception:
                pass
            network = 1 if row.get("network_type") or row.get("network") else 0
            features.append([duration, direction, hour, network, duration * direction])
            timestamps.append(row.get("timestamp", ""))

        X = np.array(features)
        X = np.nan_to_num(X)
        if X.shape[0] < 10 or np.all(X == 0):
            return []

        preds, scores = run_iforest(X, n_estimators=200, contamination=0.05)
        anomaly_indices = np.where(preds == -1)[0]

        score_mean = np.mean(scores)
        score_std = np.std(scores) or 1.0

        results = []
        for idx in anomaly_indices[:10]:
            z = (scores[idx] - score_mean) / score_std
            results.append(AnomalyResult(
                algorithm=self.algorithm_name,
                algorithm_id=self.algorithm_id,
                category=self.category,
                severity="High" if z < -2 else "Medium",
                anomaly=f"Anomalous call: duration={features[idx][0]:.0f}s, direction={'outgoing' if features[idx][1] else 'incoming'} at hour {features[idx][2]:.0f}",
                score=round(float(scores[idx]), 4),
                timestamp=timestamps[idx] if idx < len(timestamps) else "",
                details={"call_features": features[idx], "anomaly_score": float(scores[idx])},
            ))
        return results
