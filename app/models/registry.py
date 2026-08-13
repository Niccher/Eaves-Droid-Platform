ALGORITHM_REGISTRY: dict[str, dict] = {
    "sms_bert": {
        "id": "sms_bert",
        "name": "SMS Phishing Keyword Heuristic",
        "category": "sms",
        "description": "Heuristic keyword scan of SMS message bodies for phishing / social-engineering indicators (urgency, impersonation, credential requests).",
        "engine": "python",
        "parameters": {},
    },
    "contacts_graph": {
        "id": "contacts_graph",
        "name": "Contact Graph Outlier Model",
        "category": "contacts",
        "description": "Builds a NetworkX graph of contacts from phone-prefix and name similarity, then flags orphaned and low-connectivity nodes.",
        "engine": "python",
        "parameters": {},
    },
    "calls_isolation": {
        "id": "calls_isolation",
        "name": "Isolation Forest Outlier Detection",
        "category": "call_logs",
        "description": "Applies scikit-learn Isolation Forest to multidimensional call attributes (duration, timestamp, direction, network).",
        "engine": "python",
        "parameters": {
            "ml_python_iforest_trees": 200,
            "ml_python_iforest_samples": 512,
            "ml_python_iforest_contamination": 0.05,
        },
    },
    "apps_autoencoder": {
        "id": "apps_autoencoder",
        "name": "App Manifest Anomaly Scanner (PCA)",
        "category": "apps",
        "description": "Uses PCA reconstruction error (a linear autoencoder) over package name, permission, and manifest-style features to find apps with abnormal configurations.",
        "engine": "python",
        "parameters": {
            "ml_python_autoencoder_latent": 2,
            "ml_python_autoencoder_threshold": 2.0,
        },
    },
    "files_entropy": {
        "id": "files_entropy",
        "name": "Suspicious File Metadata Scanner",
        "category": "files",
        "description": "Flags files whose metadata (extension, path depth, location in Android data directories, hidden names) suggests encrypted payloads, ransomware artefacts, or hidden executables.",
        "engine": "python",
        "parameters": {},
    },
    "act_lstm": {
        "id": "act_lstm",
        "name": "Activity Sequence Predictor (MLP)",
        "category": "activity",
        "description": "Trains a small scikit-learn MLP on app-usage timestamps to model normal activity rhythms, then flags windows where the actual activity time deviates significantly from the prediction.",
        "engine": "python",
        "parameters": {
            "ml_python_lstm_sequence": 20,
            "ml_python_lstm_units": 32,
        },
    },
    "dev_oneclass": {
        "id": "dev_oneclass",
        "name": "One-Class SVM System-State Profiler",
        "category": "device_info",
        "description": "Models normal operational bounds of CPU, RAM, battery temperature, and active radios with a one-class SVM to find abnormal system states.",
        "engine": "python",
        "parameters": {
            "ml_python_oneclass_nu": 0.05,
            "ml_python_oneclass_gamma": 0.01,
        },
    },
}


def get_algorithm_info(alg_id: str) -> dict | None:
    return ALGORITHM_REGISTRY.get(alg_id)


def list_available_algorithms() -> list[dict]:
    return list(ALGORITHM_REGISTRY.values())
