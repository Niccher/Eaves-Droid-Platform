ALGORITHM_REGISTRY: dict[str, dict] = {
    "sms_bert": {
        "id": "sms_bert",
        "name": "SMS Phishing Classifier (TF-IDF / NB)",
        "category": "sms",
        "description": (
            "Classifies SMS messages using a pre-trained TF-IDF vectorizer + Naive Bayes model "
            "loaded from models_cache/. Falls back to urgency-keyword heuristics when model "
            "artefacts are absent. Reports phishing probability scores (0–1) with High ≥ 0.85."
        ),
        "engine": "python",
        "parameters": {},
    },
    "contacts_graph": {
        "id": "contacts_graph",
        "name": "Contact Graph Outlier Model (Interaction-Weighted)",
        "category": "contacts",
        "description": (
            "Builds a weighted NetworkX contact graph using real call duration and SMS counts as "
            "edge weights, then applies Louvain community detection to partition contacts into "
            "social clusters. Flags orphaned contacts (zero interaction) and high-interaction "
            "contacts that fall outside all detected communities."
        ),
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
        "name": "App Transition Markov Chain Detector",
        "category": "activity",
        "description": (
            "Models normal app-launch sequences as a first-order Markov Chain. Computes the "
            "log-likelihood of N-gram transition windows and flags sequences whose probability "
            "is statistically improbable (Z < -2σ). No external training data required — the "
            "transition matrix is built dynamically from the user's own app usage history."
        ),
        "engine": "python",
        "parameters": {
            "markov_ngram": 3,
            "markov_z_threshold": -2.0,
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
    "notification_hijack": {
        "id": "notification_hijack",
        "name": "Notification Interception Guard",
        "category": "apps",
        "description": "Flags 3rd-party apps running in the foreground during sensitive OTP/banking notification arrivals.",
        "engine": "python",
        "parameters": {},
    },
    "background_exfiltration": {
        "id": "background_exfiltration",
        "name": "Background Data Exfiltration Profiler",
        "category": "device_info",
        "description": "Flags apps executing statistically abnormal background network uploads (Z > 2.0σ) while screen is off.",
        "engine": "python",
        "parameters": {},
    },
    "accessibility_abuse": {
        "id": "accessibility_abuse",
        "name": "Accessibility Abuse Detector",
        "category": "apps",
        "description": "Audits active accessibility services for unverified 3rd-party apps with high-risk UI scraping or overlay capabilities.",
        "engine": "python",
        "parameters": {},
    },
    "sleep_disturbance": {
        "id": "sleep_disturbance",
        "name": "Sleep Disturbance & Stealth Tracker",
        "category": "activity",
        "description": "Identifies suspicious device usage or stealth background app activity during nighttime hours (11 PM - 5:30 AM).",
        "engine": "python",
        "parameters": {},
    },
    "battery_drain": {
        "id": "battery_drain",
        "name": "Battery Drain Outlier Model",
        "category": "device_info",
        "description": "Flags periods of abnormal battery depletion while screen is off and device is not charging, indicating hidden spyware/miners.",
        "engine": "python",
        "parameters": {},
    },
    "communication_spikes": {
        "id": "communication_spikes",
        "name": "Communication Spikes Detector",
        "category": "contacts",
        "description": "Flags contacts with statistically abnormal spikes in communication frequency (Z > 3.0σ) compared to their historical average.",
        "engine": "python",
        "parameters": {},
    },
}


def get_algorithm_info(alg_id: str) -> dict | None:
    return ALGORITHM_REGISTRY.get(alg_id)


def list_available_algorithms() -> list[dict]:
    return list(ALGORITHM_REGISTRY.values())
