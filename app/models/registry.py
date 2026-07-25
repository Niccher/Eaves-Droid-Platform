ALGORITHM_REGISTRY: dict[str, dict] = {
    "sms_bert": {
        "id": "sms_bert",
        "name": "BERT Semantic Phishing Classifier",
        "category": "sms",
        "description": "Uses deep learning NLP (Transformer model) to analyze SMS message content for phishing semantics and intent.",
        "engine": "python",
        "parameters": {
            "model_name": "bert-base-uncased",
            "max_length": 128,
            "batch_size": 32,
            "threshold": 0.85,
        },
    },
    "contacts_graph": {
        "id": "contacts_graph",
        "name": "Graph Relation Outlier Model (GCN)",
        "category": "contacts",
        "description": "Maps contact relationships into a graph network database to identify structural anomalies and orphaned contacts.",
        "engine": "python",
        "parameters": {
            "embedding_dim": 64,
            "min_community_size": 3,
            "similarity_threshold": 0.85,
        },
    },
    "calls_isolation": {
        "id": "calls_isolation",
        "name": "Isolation Forest Outlier Detection",
        "category": "call_logs",
        "description": "Applies Isolation Forest algorithm to multidimensional call attributes (duration, timestamp, direction, network).",
        "engine": "python",
        "parameters": {
            "n_estimators": 200,
            "max_samples": 512,
            "contamination": 0.05,
        },
    },
    "apps_autoencoder": {
        "id": "apps_autoencoder",
        "name": "Neural Autoencoder App Classifier",
        "category": "apps",
        "description": "Trains an Autoencoder network on APK manifest components. Reconstructs features to find apps with abnormal configuration.",
        "engine": "python",
        "parameters": {
            "encoding_dim": 16,
            "epochs": 50,
            "batch_size": 64,
            "learning_rate": 0.001,
            "anomaly_threshold_sigma": 3.0,
        },
    },
    "files_entropy": {
        "id": "files_entropy",
        "name": "File Entropy & Encryption Scanner",
        "category": "files",
        "description": "Calculates shannon entropy of file bytes to find encrypted archives or payload assets hidden in assets/ directories.",
        "engine": "python",
        "parameters": {
            "entropy_threshold": 7.2,
            "chunk_size": 4096,
        },
    },
    "act_lstm": {
        "id": "act_lstm",
        "name": "LSTM Sequence Pattern Predictor",
        "category": "activity",
        "description": "Deep LSTM model predicting subsequent user interaction events. Reports high prediction error as abnormal.",
        "engine": "python",
        "parameters": {
            "sequence_length": 20,
            "lstm_units": 64,
            "epochs": 30,
            "batch_size": 32,
        },
    },
    "dev_oneclass": {
        "id": "dev_oneclass",
        "name": "One-Class SVM System-State Profiler",
        "category": "device_info",
        "description": "Models normal operational bounds of CPU, RAM, battery temperature, and active radios to find abnormal system states.",
        "engine": "python",
        "parameters": {
            "nu": 0.05,
            "gamma": 0.01,
            "kernel": "rbf",
        },
    },
}


def get_algorithm_info(alg_id: str) -> dict | None:
    return ALGORITHM_REGISTRY.get(alg_id)


def list_available_algorithms() -> list[dict]:
    return list(ALGORITHM_REGISTRY.values())
