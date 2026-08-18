"""
One-Class SVM System-State Profiler — models normal operational bounds
(CPU, RAM, battery temperature, active radios) and flags abnormal states.

Queries ``tbl_device_profiles`` through the user's device checksums.
"""

import numpy as np
from sklearn.svm import OneClassSVM
from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult


class DeviceOneClassDetector(BaseDetector):
    algorithm_id = "dev_oneclass"
    algorithm_name = "One-Class SVM System-State Profiler"
    category = "device_info"

    async def detect(self, user_id: int, scope: str = "full",
                     incremental_since: str | None = None,
                     params: dict | None = None) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine

        # Resolve device checksums for this user (tokens + uploads)
        sql = text("""
            SELECT DISTINCT device_checksum FROM tbl_user_api_tokens
            WHERE owner_id = :uid AND device_checksum IS NOT NULL AND device_checksum != ''
            UNION
            SELECT DISTINCT device_checksum FROM tbl_uploaded_files
            WHERE token_owner_id = :uid AND device_checksum IS NOT NULL AND device_checksum != ''
        """)
        with get_engine().connect() as conn:
            checksum_rows = conn.execute(sql, {"uid": user_id}).fetchall()

        checksums = [r[0] for r in checksum_rows if r[0]]
        if not checksums:
            return []

        # Fetch the latest device profile for each checksum
        import json
        placeholders = ",".join(f":chk{i}" for i in range(len(checksums)))
        params = {f"chk{i}": c for i, c in enumerate(checksums)}
        where_extra = ""
        if scope == "incremental" and incremental_since:
            where_extra = " AND extraction_timestamp > :since"
            params["since"] = incremental_since

        sql = text(f"""
            SELECT system_load, memory_available_mb, battery_temperature_c, battery_charging,
                   extraction_timestamp
            FROM tbl_device_profiles
            WHERE device_id IN ({placeholders}){where_extra}
            ORDER BY extraction_timestamp DESC
            LIMIT 500
        """)
        with get_engine().connect() as conn:
            rows = conn.execute(sql, params).mappings().fetchall()

        if len(rows) < 5:
            return []

        features = []
        timestamps = []
        for row in rows:
            cpu = float(row["system_load"] or 0)
            ram = float(row["memory_available_mb"] or 0)
            batt = float(row["battery_temperature_c"] or 0)
            charging = 1 if row["battery_charging"] else 0
            features.append([cpu, ram, batt, charging])
            ts = row["extraction_timestamp"]
            if isinstance(ts, (int, float)) and ts > 0:
                import datetime
                ts = datetime.datetime.fromtimestamp(ts / 1000.0).strftime("%Y-%m-%d %H:%M:%S")
            timestamps.append(str(ts or ""))

        X = np.array(features)
        X = np.nan_to_num(X)

        nu = float(params.get('ml_python_oneclass_nu', 0.05)) if params else 0.05
        gamma = float(params.get('ml_python_oneclass_gamma', 0.01)) if params else 0.01
        model = OneClassSVM(nu=nu, gamma=gamma, kernel="rbf")
        preds = model.fit_predict(X)
        scores = model.score_samples(X)

        anomaly_indices = np.where(preds == -1)[0]
        score_mean = np.mean(scores)
        score_std = np.std(scores) or 1.0

        results = []
        for idx in anomaly_indices[:10]:
            z = (scores[idx] - score_mean) / score_std
            cpu, ram, batt, charging = features[idx]
            results.append(AnomalyResult(
                algorithm=self.algorithm_name,
                algorithm_id=self.algorithm_id,
                category=self.category,
                severity="High" if z < -2 else "Medium",
                anomaly=(
                    f"Abnormal system state: CPU={cpu:.0f}%, RAM available={ram:.0f}MB, "
                    f"battery={batt:.0f}°C, charging={'yes' if charging else 'no'}"
                ),
                score=round(float(scores[idx]), 4),
                event_timestamp=timestamps[idx] if idx < len(timestamps) else "",
                details={
                    "system_load": round(cpu, 1),
                    "memory_available_mb": round(ram, 1),
                    "battery_temperature_c": round(batt, 1),
                    "charging": bool(charging),
                    "anomaly_score": float(scores[idx]),
                },
            ))
        return results
