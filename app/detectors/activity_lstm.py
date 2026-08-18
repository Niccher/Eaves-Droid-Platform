"""
Activity Sequence Predictor — trains a small scikit-learn MLP on
app-usage timestamps to model normal activity rhythms, then flags windows
where the actual activity time deviates significantly from the prediction.

Note: this uses sklearn's MLPRegressor, not a PyTorch LSTM.  The
``algorithm_id`` (``act_lstm``) is kept for backward compatibility with
existing ``ml_results`` rows and webapp configuration.

Queries ``tbl_system_app_usage`` directly from the shared MySQL database.
"""

import numpy as np
from sklearn.neural_network import MLPRegressor
from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult


class ActivitySequenceDetector(BaseDetector):
    algorithm_id = "act_lstm"
    algorithm_name = "Activity Sequence Predictor (MLP)"
    category = "activity"

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
            SELECT package_name,
                   DATE_FORMAT(FROM_UNIXTIME(last_time_used/1000),
                               '%Y-%m-%d %H:%i:%s') AS ts
            FROM tbl_system_app_usage
            WHERE {where}
            ORDER BY last_time_used ASC
            LIMIT 3000
        """)

        with get_engine().connect() as conn:
            rows = conn.execute(sql, params).mappings().fetchall()

        if len(rows) < 10:
            return []

        hours = []
        timestamps = []
        for row in rows:
            ts = str(row["ts"] or "")
            timestamps.append(ts)
            try:
                dt = __import__("datetime").datetime.fromisoformat(ts)
                hours.append(dt.hour + dt.minute / 60)
            except Exception:
                hours.append(12.0)

        hour_arr = np.array(hours, dtype=np.float32).reshape(-1, 1)
        hour_arr = (hour_arr - 12.0) / 12.0

        seq_len_param = int(params.get('ml_python_lstm_sequence', 20)) if params else 20
        mlp_units_param = int(params.get('ml_python_lstm_units', 32)) if params else 32
        seq_len = seq_len_param if len(hour_arr) > seq_len_param + 1 else max(3, len(hour_arr) // 2)
        X, y = [], []
        for i in range(len(hour_arr) - seq_len):
            X.append(hour_arr[i:i + seq_len].flatten())
            y.append(hour_arr[i + seq_len].item())
        if len(X) < 2 or len(set(y)) < 2:
            return []

        X_arr = np.array(X, dtype=np.float32)
        y_arr = np.array(y, dtype=np.float32)

        model = MLPRegressor(
            hidden_layer_sizes=(mlp_units_param,),
            activation="relu",
            solver="adam",
            max_iter=30,
            random_state=42,
        )
        model.fit(X_arr, y_arr)
        preds = model.predict(X_arr)
        errors = np.abs(preds - y_arr)

        mean_err = float(np.mean(errors))
        std_err = float(np.std(errors)) or 1.0

        results = []
        for i in range(len(errors)):
            z = (errors[i] - mean_err) / std_err
            if z > 2.0:
                idx = i + seq_len
                pred_hour = float(preds[i]) * 12.0 + 12.0
                actual_hour = hours[idx] if idx < len(hours) else 0
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="High" if z > 3.0 else "Medium",
                    anomaly=(
                        f"Activity pattern deviation at ~{actual_hour:.1f}h — "
                        f"expected {pred_hour:.1f}h, error {z:.1f}σ"
                    ),
                    score=round(float(errors[i]), 4),
                    event_timestamp=timestamps[idx] if idx < len(timestamps) else "",
                    details={
                        "sequence_position": idx,
                        "predicted_hour": round(pred_hour, 2),
                        "actual_hour": round(actual_hour, 2),
                        "error_zscore": round(z, 2),
                    },
                ))
        return results[:10]
