# Service Guide: Machine Learning & Anomaly Detection

This guide covers the dual-engine machine learning architecture powering anomaly detection, behavioral baselining, and heuristic classification across the Eaves Droid platform.

---

## 1. Dual-Engine Architecture

Eaves Droid employs a hybrid, self-healing analytics architecture:

```
┌────────────────────────────────────────────────────────┐
│               Eaves Droid WebApp (PHP)                 │
│         /admin/ml & /users/correlation/*               │
└───────────┬────────────────────────────────┬───────────┘
            │                                │
 (Primary Job Dispatch)             (Failover Fallback)
            │                                │
            ▼                                ▼
┌───────────────────────┐        ┌───────────────────────┐
│  Python FastAPI ML    │        │  In-Process PHP-ML    │
│  (PyOD / Scikit-Learn)│        │  (Clustering/Trees)   │
│                       │        │                       │
│ • Isolation Forest    │        │ • K-Means (K=3)       │
│ • One-Class SVM       │        │ • DBSCAN (eps=0.01)   │
│ • Local Outlier Factor│        │ • PHP Isolation Forest│
│ • PCA Reconstruction  │        │ • Dynamic Z-Score     │
│ • Activity MLP        │        └───────────────────────┘
│ • Deep Autoencoders   │
└───────────────────────┘
```

---

## 2. Supported Algorithms & Models

### Python Detectors (`ML Eaves Droid`)
* **Isolation Forest (`sklearn.ensemble.IsolationForest`):** Random partitioning trees isolating multi-attribute outliers across SMS, call, and location records.
* **One-Class SVM (`sklearn.svm.OneClassSVM`):** RBF-kernel decision boundary enclosing normal system states (CPU, RAM, battery temp, active radios).
* **Local Outlier Factor (`sklearn.neighbors.LocalOutlierFactor`):** Measures local density deviations compared to k-nearest neighbors; effective for non-uniform spatial clusters.
* **PCA Reconstruction Error:** Projects app manifest features into latent dimensions; high reconstruction error signals anomalous permissions.
* **Activity MLP Classifier:** Neural network predicting expected device activity transitions based on timestamp rhythms.
* **Deep Neural Autoencoder:** Neural encoder-decoder architecture flagging complex nonlinear behavioral anomalies.

### Local PHP-ML Detectors
* **K-Means Clustering:** Partitions SMS recipients into behavioural clusters to detect sudden mass-broadcast anomalies.
* **DBSCAN Density Clustering:** Identifies spatial outliers and impossible GPS transit velocities.
* **Isolation Forest (PHP-ML):** Pure-PHP implementation used when Python microservice is offline.

---

## 3. Real-Time Telemetry & Live Heartbeat

The WebApp monitors backend status via `GET /admin/ml/heartbeat`:
* **Process-Level CPU:** Reported via `psutil.Process().cpu_percent()` for accurate container telemetry.
* **Container RAM:** Current memory consumption and heap limits.
* **Core Table Health:** Case-insensitive verification across 10 core MySQL forensic tables.
* **Failover Mode:** Instantly toggles between Python Microservice and PHP-ML without dropping user requests.
