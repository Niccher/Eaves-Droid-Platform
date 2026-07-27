"""
Isolation Forest Outlier Detection — flags anomalous call records using
scikit-learn's Isolation Forest on multi-dimensional call features
(duration, direction, hour, network presence).

Queries ``tbl_logs`` directly from the shared MySQL database.
"""

import numpy as np
from app.detectors.base import BaseDetector
from app.detectors.shared.isolation_forest import run_iforest
from app.models.schemas import AnomalyResult


class CallsIsolationDetector(BaseDetector):
    algorithm_id = "calls_isolation"
    algorithm_name = "Isolation Forest Outlier Detection"
    category = "call_logs"

    async def detect(self, user_id: int, scope: str = "full",
                     incremental_since: str | None = None,
                     params: dict | None = None) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine

        where = "owner_id = :uid"
        params: dict = {"uid": user_id}
        if scope == "incremental" and incremental_since:
            where += " AND created_at > :since"
            params["since"] = incremental_since

        sql = text(f"""
            SELECT duration_seconds, call_type AS direction,
                   DATE_FORMAT(FROM_UNIXTIME(call_date/1000), '%Y-%m-%d %H:%i:%s') AS ts,
                   network_type
            FROM tbl_logs
            WHERE {where}
            ORDER BY call_date DESC
            LIMIT 3000
        """)

        with get_engine().connect() as conn:
            rows = conn.execute(sql, params).mappings().fetchall()

        if len(rows) < 10:
            return []

        features = []
        timestamps = []
        for row in rows:
            duration = float(row["duration_seconds"] or 0)
            direction = 1 if (row["direction"] or "").lower() in ("outgoing", "dialled", "dialed") else 0
            hour = 12
            ts = str(row["ts"] or "")
            if ts:
                try:
                    dt = __import__("datetime").datetime.fromisoformat(ts)
                    hour = dt.hour
                except Exception:
                    pass
            network = 1 if row["network_type"] else 0
            features.append([duration, direction, hour, network, duration * direction])
            timestamps.append(ts)

        X = np.array(features)
        X = np.nan_to_num(X)
        if X.shape[0] < 10 or np.all(X == 0):
            return []

        n_estimators = int(params.get('ml_python_iforest_trees', 200)) if params else 200
        max_samples_param = params.get('ml_python_iforest_samples', None) if params else None
        max_samples: int | str = 'auto'
        if max_samples_param is not None:
            try:
                max_samples = int(max_samples_param)
            except (ValueError, TypeError):
                max_samples = 'auto'
        contamination = float(params.get('ml_python_iforest_contamination', 0.05)) if params else 0.05
        preds, scores = run_iforest(X, n_estimators=n_estimators, max_samples=max_samples, contamination=contamination)
        anomaly_indices = np.where(preds == -1)[0]
        score_mean = np.mean(scores)
        score_std = np.std(scores) or 1.0

        results = []
        for idx in anomaly_indices[:10]:
            z = (scores[idx] - score_mean) / score_std
            dur, direc, hr, net, _ = features[idx]
            results.append(AnomalyResult(
                algorithm=self.algorithm_name,
                algorithm_id=self.algorithm_id,
                category=self.category,
                severity="High" if z < -2 else "Medium",
                anomaly=(
                    f"Anomalous call: duration={dur:.0f}s, "
                    f"{'outgoing' if direc else 'incoming'} at ~{hr:.0f}:00"
                ),
                score=round(float(scores[idx]), 4),
                event_timestamp=timestamps[idx] if idx < len(timestamps) else "",
                details={
                    "duration_seconds": round(dur, 1),
                    "direction": "outgoing" if direc else "incoming",
                    "hour": int(hr),
                    "has_network": bool(net),
                    "anomaly_score": float(scores[idx]),
                },
            ))
        return results
