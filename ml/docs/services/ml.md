# Service Guide: Machine Learning Detectors

Catalog and mathematical methodology for the 13 CPU-only anomaly detection engines in ML Eaves Droid.

---

## 1. Registered Detectors Catalog

| Algorithm ID | Display Name | Category | Database Table | Method |
|--------------|--------------|----------|----------------|--------|
| `sms_bert` | SMS Phishing Classifier | SMS | `tbl_sms` | TF-IDF vectorizer + Naive Bayes social engineering heuristics |
| `contacts_graph` | Contact Graph Outlier | Contacts | `tbl_contacts` | NetworkX weighted community partitioning & modularity |
| `calls_isolation` | Isolation Forest Outlier | Call Logs | `tbl_logs` | scikit-learn IsolationForest multidimensional call analysis |
| `apps_autoencoder`| App Manifest PCA Scan | Apps | `tbl_apps` | PCA reconstruction error over permission matrices |
| `files_entropy` | File Metadata Scanner | Files | `tbl_device_files` | Rule-based entropy and suspicious extension checks |
| `act_lstm` | App Transition Markov | Activity | `tbl_app_usage` | Dynamic transition matrix transition likelihood |
| `dev_oneclass` | One-Class SVM Profiler | Device Info | `tbl_device_profile` | One-Class SVM system telemetry boundary |
| `notification_hijack`| Interception Guard | Apps | `tbl_apps` | Sensitive overlay permission auditing |
| `background_exfiltration`| Data Exfiltration | Device Info | `tbl_device_profile` | Z-score anomaly on outbound network payloads |
| `accessibility_abuse`| Accessibility Auditor | Apps | `tbl_apps` | Audits background accessibility scraping services |
| `sleep_disturbance` | Sleep Disturbance | Activity | `tbl_app_usage` | Nighttime activity spike detection |
| `battery_drain` | Battery Drain Model | Device Info | `tbl_device_profile` | Flags abnormal screen-off power depletion |
| `communication_spikes`| Contact Frequency | Contacts | `tbl_contacts` | Rolling Z-score anomaly on interaction bursts |

---

## 2. Methodology Details

### Isolation Forest (`calls_isolation`)
* Uses `sklearn.ensemble.IsolationForest` configured with `n_estimators=200` and `contamination=0.05`.
* Analyzes multidimensional feature vectors per call: `duration_seconds`, `hour_of_day`, `call_type_direction`, `is_weekend`, `roaming_flag`.

### App Manifest PCA (`apps_autoencoder`)
* Extracts binary permission vectors for all installed packages.
* Computes principal components via `sklearn.decomposition.PCA`.
* Packages whose reconstruction error exceeds the 95th percentile are flagged for elevated risk.

### Contact Graph Partitioning (`contacts_graph`)
* Builds an undirected graph using `NetworkX` where nodes are contacts and edge weights represent co-occurrence in SMS and call records.
* Disconnected or low-clustering coefficient nodes are highlighted as potential anomalous contacts.
