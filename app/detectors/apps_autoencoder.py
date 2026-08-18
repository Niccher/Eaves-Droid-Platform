"""
App Manifest Anomaly Scanner — uses PCA reconstruction error to flag
apps with abnormal manifest-style features (package name patterns,
permission counts, etc.).  PCA acts as a linear autoencoder: apps that
deviate from the low-dimensional normal structure have high
reconstruction error.

Note: this is a PCA-based anomaly scanner, not a neural autoencoder.
The ``algorithm_id`` (``apps_autoencoder``) is kept for backward
compatibility with existing ``ml_results`` rows and webapp configuration.

Queries ``tbl_extracted_installed_apps`` directly from the shared MySQL database.
"""

import json
import numpy as np
from sklearn.decomposition import PCA
from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

SUSPICIOUS_PACKAGE_PATTERNS = [
    "hidden", "spy", "monitor", "track", "stealth", "covert",
    "sneak", "camouflage", "ghost", "shadow", "secret",
]

SUSPICIOUS_PERMISSIONS = [
    "read_sms", "send_sms", "record_audio", "camera",
    "read_contacts", "read_call_log", "access_fine_location",
    "access_background_location", "read_external_storage",
    "write_external_storage", "system_alert_window",
    "request_install_packages", "bind_accessibility_service",
]


class AppManifestAnomalyDetector(BaseDetector):
    algorithm_id = "apps_autoencoder"
    algorithm_name = "App Manifest Anomaly Scanner (PCA)"
    category = "apps"

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
            SELECT app_name, package_name, permissions
            FROM tbl_extracted_installed_apps
            WHERE {where}
            LIMIT 1000
        """)
        with get_engine().connect() as conn:
            rows = conn.execute(sql, params).mappings().fetchall()

        if len(rows) < 3:
            return []

        def extract_features(row) -> np.ndarray:
            pkg = (row["package_name"] or "").lower()
            name = (row["app_name"] or "").lower()
            perms = row["permissions"]
            if isinstance(perms, str):
                try:
                    perms = json.loads(perms)
                except Exception:
                    perms = []
            if not isinstance(perms, list):
                perms = []

            sensitive_count = sum(
                1 for p in perms
                if any(sp in p.lower() for sp in SUSPICIOUS_PERMISSIONS)
            )
            pkg_sus = sum(1 for pat in SUSPICIOUS_PACKAGE_PATTERNS if pat in pkg)
            name_sus = sum(1 for pat in SUSPICIOUS_PACKAGE_PATTERNS if pat in name)
            return np.array([
                min(sensitive_count / 10.0, 1.0),
                min(pkg_sus / 3.0, 1.0),
                min(name_sus / 3.0, 1.0),
                min(len(pkg) / 100.0, 1.0),
                min(len(perms) / 50.0, 1.0),
            ], dtype=np.float32)

        features = np.array([extract_features(r) for r in rows])
        n_components_param = int(params.get('ml_python_autoencoder_latent', 2)) if params else 2
        n_components = min(n_components_param, features.shape[1] - 1)
        if n_components < 1:
            return []

        pca = PCA(n_components=n_components, random_state=42)
        transformed = pca.fit_transform(features)
        reconstructed = pca.inverse_transform(transformed)
        errors = np.mean((features - reconstructed) ** 2, axis=1)

        mean_err = float(np.mean(errors))
        std_err = float(np.std(errors)) or 1.0

        anomaly_threshold = float(params.get('ml_python_autoencoder_threshold', 2.0)) if params else 2.0

        results = []
        for i in range(len(errors)):
            z = (errors[i] - mean_err) / std_err
            if z > anomaly_threshold:
                name = rows[i]["app_name"] or rows[i]["package_name"] or f"app #{i}"
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="High" if z > 3.5 else "Medium",
                    anomaly=f"Abnormal app manifest: '{name}' (reconstruction error {z:.1f}σ)",
                    score=round(float(errors[i]), 4),
                    event_timestamp="",
                    details={
                        "app_name": name,
                        "package": rows[i]["package_name"],
                        "reconstruction_error": float(errors[i]),
                        "zscore": round(z, 2),
                    },
                ))
        return results[:10]
