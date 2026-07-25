import numpy as np
from sklearn.svm import OneClassSVM
from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult


class DeviceOneClassDetector(BaseDetector):
    algorithm_id = "dev_oneclass"
    algorithm_name = "One-Class SVM System-State Profiler"
    category = "device_info"

    async def detect(self, data: list[dict], user_id: int | None = None) -> list[AnomalyResult]:
        if len(data) < 5:
            return []

        features = []
        timestamps = []
        for row in data:
            cpu = float(row.get("cpu_usage", row.get("cpu", 0)))
            ram = float(row.get("ram_usage", row.get("ram", 0)))
            batt_temp = float(row.get("battery_temperature", row.get("battery_temp", 0)))
            radios = 1 if row.get("active_radios") or row.get("radio_active") else 0
            features.append([cpu, ram, batt_temp, radios])
            timestamps.append(row.get("timestamp", ""))

        X = np.array(features)
        X = np.nan_to_num(X)
        if X.shape[0] < 5:
            return []

        model = OneClassSVM(nu=0.05, gamma=0.01, kernel="rbf")
        preds = model.fit_predict(X)
        scores = model.score_samples(X)

        anomaly_indices = np.where(preds == -1)[0]
        score_mean = np.mean(scores)
        score_std = np.std(scores) or 1.0

        results = []
        for idx in anomaly_indices[:10]:
            z = (scores[idx] - score_mean) / score_std
            cpu, ram, batt_temp, radios = features[idx]
            results.append(AnomalyResult(
                algorithm=self.algorithm_name,
                algorithm_id=self.algorithm_id,
                category=self.category,
                severity="High" if z < -2 else "Medium",
                anomaly=f"Abnormal system state: CPU={cpu}%, RAM={ram}%, battery={batt_temp}°C, radios={'active' if radios else 'inactive'}",
                score=round(float(scores[idx]), 4),
                timestamp=timestamps[idx] if idx < len(timestamps) else "",
                details={"system_state": features[idx], "anomaly_score": float(scores[idx])},
            ))
        return results
