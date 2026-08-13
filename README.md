<div align="center">

# Eaves Droid

**Privacy‑focused Android data extraction platform with real‑time analysis, anomaly detection, and geospatial clustering.**

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.5-EF4223?style=for-the-badge&logo=codeigniter)](https://codeigniter.com)
[![Android](https://img.shields.io/badge/Android-API_25--35-3DDC84?style=for-the-badge&logo=android)](https://developer.android.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker)](https://docker.com)
[![PHP‑ML](https://img.shields.io/badge/PHP--ML-0.10.0-FF6F00?style=for-the-badge)](#machine-learning--algorithms)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge)](LICENSE)

</div>

---

## About the Project

Eaves Droid is a **three‑repo ecosystem**:

1. **Android client** ([`Eaves_Droid_App`](https://github.com/Niccher/Eaves-Droid-App)) — extracts 60+ types of device data, encrypts it with AES, and uploads it to the backend.
2. **Web backend** (this repo, `Eaves Droid WebApp`) — a PHP/CodeIgniter 4 app that receives, decrypts, parses, stores, and visualises the data on an AdminLTE dashboard.
3. **ML Engine** ([`ML Eaves Droid`](https://github.com/Niccher/Eaves-Droid-ML)) — a Python/FastAPI service running the advanced anomaly‑detection detectors (the "Deep" plan tier) against the shared MySQL database.

The platform is designed for device owners who want to inspect their own digital footprint on a self‑hosted server. It distinguishes itself by running **on‑device ML‑inspired heuristics** (anomaly detection, sentiment analysis, communication scoring, geo‑clustering) and **server‑side analysis** (PHP‑ML algorithms for K‑Means / DBSCAN clustering, plus the Python ML engine for Isolation Forest, One‑Class SVM, PCA anomaly scanning, contact‑graph outliers, activity MLP, phishing heuristics, and suspicious‑file scanning) without sending raw data to third‑party services.

---

## Architecture

```
┌──────────────────────────────────────┐          HTTPS/JSON          ┌──────────────────────────────────────┐
│        Android Device (Client)       │  ──────────────────────────►  │        Web Backend (Server)          │
│                                      │  POST /api/v1/files/upload   │                                      │
│  ┌────────────────────────────┐      │  POST /api/v1/device/print   │  ┌──────────────────────────────┐     │
│  │  ExtractDataActivity       │      │  POST /api/v1/token/verify   │  │  Receive Controller          │     │
│  │  (60+ DataExtractors)      │──────┼──────────────────────────────►│  │  (decrypt, route by category)│     │
│  └────────────────────────────┘      │                              │  └──────────┬───────────────────┘     │
│  ┌────────────────────────────┐      │                              │             │                          │
│  │  DataSyncWorker            │      │                              │  ┌──────────▼───────────────────┐     │
│  │  (Periodic background sync)│──────┼──────────────────────────────►│  │  Mod_Parse_Loot /              │     │
│  └────────────────────────────┘      │                              │  │  Mod_Parse_Advanced (parsers) │     │
│  ┌────────────────────────────┐      │                              │  └──────────┬───────────────────┘     │
│  │  LiveLocationWorker        │──────┼──────────────────────────────►│             │                          │
│  │  (GPS batch every 15 min)  │      │                              │  ┌──────────▼───────────────────┐     │
│  └────────────────────────────┘      │                              │  │  MySQL Database              │     │
│  ┌────────────────────────────┐      │                              │  │  (30+ tables)                │     │
│  │  SimChangeReceiver         │──────┼──────────────────────────────►│  └──────────┬───────────────────┘     │
│  │  (auto‑upload on SIM swap) │      │                              │             │                          │
│  └────────────────────────────┘      │                              │  ┌──────────▼───────────────────┐     │
│  ┌────────────────────────────┐      │                              │  │  Client Controllers          │     │
│  │  UploadService +           │      │                              │  │  (dashboard, filters, export)│     │
│  │  DataProcessingService     │──────┼──────────────────────────────►│  └──────────┬───────────────────┘     │
│  └────────────────────────────┘      │                              │             │                          │
│  ┌────────────────────────────┐      │                              │  ┌──────────▼───────────────────┐     │
│  │  OfflinePayload (Room DB)  │      │                              │  │  AdminLTE Views             │     │
│  │  QueueSyncWorker           │──────┼──────────────────────────────►│  │  (20+ data pages)            │     │
│  └────────────────────────────┘      │                              │  └──────────────────────────────┘     │
└──────────────────────────────────────┘                              └──────────────────────────────────────┘
        ┌───────────────────────────┐
        │   ML Eaves Droid (Python) │  ◄──────────  HTTP /api/analyze (job dispatch)
        │   FastAPI + scikit-learn  │  ──────────►  reads/writes shared MySQL
        │   7 anomaly detectors     │               (ml_jobs, ml_results)
        └───────────────────────────┘
```

**Dependency chain:**

1. The **Android app** extracts device data through `DataExtractor` implementations, encrypts it with AES (user‑provided key), and uploads via the API endpoint `POST /api/v1/files/upload`.
2. The **Receive controller** decrypts the payload and routes to the correct parser (`Mod_Parse_Loot` for legacy categories, `Mod_Parse_Advanced` for advanced ones) based on the `category` field.
3. Parsed records are inserted into MySQL tables (one per data type).
4. The **client web dashboard** (CodeIgniter controllers + AdminLTE views) queries the database and renders sortable, searchable, paginated tables with per‑row delete and export features.
5. **Mod_Anomalies** runs detection engines on stored data. PHP‑ML algorithms (K‑Means, DBSCAN, Z‑Score heuristics) run in‑process; the seven advanced detectors are dispatched asynchronously to the **ML Eaves Droid** Python service via `ml_jobs` / `ml_results`.
6. The Android app also runs periodic **DataSyncWorker** and **LiveLocationWorker** via WorkManager, and auto‑triggers on SIM change via **SimChangeReceiver**.

---

## Access Control & Subscription System

Eaves Droid implements a **dual‑layer access control** architecture combining Role‑Based Access Control (RBAC) with subscription‑based feature gating.

### Roles & Permissions (RBAC)

Managed via [CodeIgniter Shield](https://github.com/codeigniter4/shield) with 5 auth groups and 17 granular permissions.

| Group | Title | Description |
|-------|-------|-------------|
| `superadmin` | Super Admin | Complete control — all permissions, fleet management, plan management, impersonation, forensic export, audit, maintenance |
| `admin` | Admin | Day‑to‑day operations — user management (CRUD), settings, logs, ML configuration, remote device commands |
| `developer` | Developer | Site programmers — admin access, settings, user creation |
| `user` | User | General users — data dashboard, analysis, billing (default group for new registrations) |
| `beta` | Beta User | Early access to beta‑level features |

**Permission matrix:**

| Permission | superadmin | admin | developer | user | beta |
|------------|:----------:|:-----:|:---------:|:----:|:----:|
| `admin.access` | ✅ | ✅ | ✅ | — | — |
| `admin.settings` | ✅ | — | ✅ | — | — |
| `users.manage-admins` | ✅ | — | — | — | — |
| `users.manage-roles` | ✅ | — | — | — | — |
| `users.create` | ✅ | ✅ | ✅ | — | — |
| `users.edit` | ✅ | ✅ | ✅ | — | — |
| `users.delete` | ✅ | ✅ | — | — | — |
| `security.audit` | ✅ | — | — | — | — |
| `system.migrate` | ✅ | — | — | — | — |
| `system.seed` | ✅ | — | — | — | — |
| `system.env` | ✅ | — | — | — | — |
| `beta.access` | ✅ | ✅ | ✅ | — | ✅ |
| `intelligence.search` | ✅ | — | — | — | — |
| `intelligence.impersonate` | ✅ | — | — | — | — |
| `forensics.export` | ✅ | — | — | — | — |
| `system.maintenance` | ✅ | — | — | — | — |
| `data.retention` | ✅ | — | — | — | — |

### Route‑Level Enforcement

The global `RoleFilter` enforces access on every request:

- `/admin/**` — requires `admin` or `superadmin` group
- `/superadmin/**` — requires `superadmin` group only (or impersonation session)
- `/home`, `/apps`, `/call_logs`, `/sms`, `/files`, `/location`, `/contacts`, `/analysis`, `/account` — requires any authenticated `user`

The `ImpersonateFilter` allows superadmins to temporarily assume another user's session for troubleshooting.

### Subscription Plans & Feature Gating

Three subscription tiers control feature access at the service and route level.

| Feature | Free ($0) | Gold ($4.99/mo) | Platinum ($9.99/mo) |
|---------|:---------:|:---------------:|:-------------------:|
| **Max Devices** | 1 | 3 | 10 |
| **Data History** | 10 days | 60 days | 180 days |
| **Core ML Algorithms** (8) | ✅ | ✅ | ✅ |
| **Advanced ML Algorithms** (10) | — | ✅ | ✅ |
| **ML‑Engine Algorithms** (7) | — | — | ✅ |
| **Risk Scoring** | — | ✅ | ✅ |
| **Geofencing & Location Safety** | — | ✅ | ✅ |
| **Push Notifications** | — | — | ✅ |
| **Forensic Export** | ✅ | ✅ | ✅ |
| **Smart Timeline** | ✅ | ✅ | ✅ |
| **Digital Wellbeing** | Off | 7‑day summary | 365‑day full |
| **Correlation Engine** | — | — | ✅ |
| **Care Plans** | — | — | ✅ |
| **Support Tier** | Standard | Standard | Priority 24/7 |
| **Yearly Price** | $0 | $49.90 | $99.90 |

> **Note:** Admin & Superadmin accounts are unaffected by subscription plans.

**Enforcement layers:**

1. **Route‑level** — The `PlanGate` filter blocks access to gated routes (e.g., `/location`, `/analysis/correlation-engine`) and renders an upgrade page with plan comparison and simulated checkout.
2. **Service‑level** — `PlanGate::hasFeature()` is called before executing geofencing, risk scoring, correlation, and care plan logic.
3. **Algorithm‑level** — `PlanGate::filterAlgorithms()` restricts ML algorithms by tier: `core` (free), `core+advanced` (gold), `core+advanced+deep` (platinum).
4. **Device‑level** — `PlanGate::canAddDevice()` enforces device limits per plan.

### Plan Versioning

Plans are versioned with full diff history. Superadmins can edit plan features, pricing, limits, and algorithm tiers — each update creates a new version with a diff preview and rollback capability.

---

## Superadmin Panel

Full administrative control accessible at `/superadmin/**`.

| Feature | Route | Description |
|---------|-------|-------------|
| **Dashboard** | `/superadmin/home` | System overview with key metrics |
| **Fleet Management** | `/superadmin/fleet` | Device fleet timeline, patches, alerts, geo‑tracking, device detail |
| **Plan Management** | `/superadmin/plans` | Edit plan features, pricing, limits, algorithm tiers; version history with diffs |
| **Subscription Management** | `/superadmin/subscriptions` | View all user subscriptions, manually assign plans, payment history |
| **Payment History** | `/superadmin/payments` | Cross‑user payment ledger |
| **Audit Trail** | `/superadmin/audit` | Security audit log for all administrative actions |
| **Omni Search** | `/superadmin/omni-search` | Cross‑user forensic search across all stored data |
| **User Impersonation** | `/superadmin/impersonate` | Assume another user's session for troubleshooting |
| **Role Matrix** | `/superadmin/users` | Manage user roles and permission assignments |
| **Forensic Export** | `/superadmin/forensic-export` | Export user data packages for legal/forensic handover |

---

## Admin Panel

Day‑to‑day administration accessible at `/admin/**`.

| Feature | Route | Description |
|---------|-------|-------------|
| **Dashboard** | `/admin/dashboard` | Admin overview with system health |
| **User Management** | `/admin/users` | CRUD operations, suspend/activate accounts |
| **Settings** | `/admin/settings` | Site configuration, API keys, security, notifications, maintenance mode |
| **Logs** | `/admin/logs` | Access, error, API, maintenance, and engine logs |
| **ML Configuration** | `/admin/ml` | Machine learning engine settings and algorithm management |
| **Anomaly Settings** | `/admin/anomalies` | Anomaly detection configuration |
| **Remote Device** | `/admin/remote-device` | Send remote extraction commands to registered devices |

---

## Machine Learning / Algorithms

### Detection categories

| Category | Data Analyzed | What's Detected |
|----------|--------------|-----------------|
| SMS | Sender frequency, time of day, message volume | Outlier sender clusters (K‑Means), frequency spikes, high night‑time activity |
| Contacts | New contact creation rate | Unusual bulk contact additions |
| Call Logs | Call duration, time of day, frequency | Short‑call bursts, night‑time activity, isolation forest outliers |
| Locations | GPS coordinates, speed, geo‑fences | Geo‑fence violations, improbable travel speeds, DBSCAN trajectory outliers |
| Installed Apps | Package names, permissions | Package reputation mismatch, permission anomaly score |
| Files | Creation timestamps, file extensions | File creation spikes, extension‑mismatch scanner |
| Device Activity | Screen on/off, app switches, battery | Screen‑time anomaly, app‑switch rate deviation |
| Device Info | Hardware identifiers, network profile | Hardware change detection, network profile drift |

### Algorithms

**PHP‑ML (in‑process):**

| Algorithm | Location | Library / Method | How It Works |
|-----------|----------|------------------|--------------|
| **K‑Means Clustering** | `Mod_Anomalies::detectSmsCluster()` | `PhpMl\Clustering\KMeans` (k=3, min-max scaled) | Groups SMS senders by frequency/night‑ratio/unique‑receivers; small clusters flagged as outliers |
| **DBSCAN Clustering** | `Mod_Anomalies::detectLocationDBSCAN()` | `PhpMl\Clustering\DBSCAN` (ε=0.01, minPts=2) | Clusters lat/lng coordinates; points outside dense zones flagged as trajectory anomalies |
| **Frequency / Z‑Score monitors** | `Mod_Anomalies` | Statistical heuristics | Flags message/call frequency spikes and night‑time activity against rolling baselines |

**ML Engine (Python, "Deep" tier — see [ML Eaves Droid](https://github.com/Niccher/Eaves-Droid-ML)):**

| Algorithm | Category | Library / Method | How It Works |
|-----------|----------|------------------|--------------|
| **Isolation Forest Outlier Detection** | Call logs | `scikit-learn IsolationForest` (trees=200, contamination=0.05) | Flags multidimensional call anomalies (duration, hour, direction, network) |
| **One‑Class SVM System‑State Profiler** | Device info | `sklearn.svm.OneClassSVM` (nu=0.05, rbf) | Models normal CPU/RAM/battery/radio bounds; flags abnormal system states |
| **SMS Phishing Keyword Heuristic** | SMS | Keyword scan | Flags messages with 2+ phishing/social‑engineering indicators |
| **Contact Graph Outlier Model** | Contacts | `networkx` graph | Flags orphaned / low‑connectivity contacts from number & name similarity edges |
| **App Manifest Anomaly Scanner (PCA)** | Apps | `sklearn.decomposition.PCA` | PCA reconstruction error over package/permission features |
| **Suspicious File Metadata Scanner** | Files | Metadata rules | Flags hidden / high‑risk files in Android data dirs |
| **Activity Sequence Predictor (MLP)** | Activity | `sklearn.neural_network.MLPRegressor` | Predicts app‑usage timing; flags large prediction errors |

**On‑device (Java):**

| Algorithm | Location | Method | How It Works |
|-----------|----------|--------|--------------|
| **Rule‑based Anomaly Heuristic** | `AnomalyDetector.java` | On‑device (Java) | Adds weighted scores for late‑night extractions, low‑battery + active, unusual running hours, no‑connectivity states |
| **Location Clustering** | `LocationClusterer.java` | On‑device (Java) | Centroids calculated via moving‑average Haversine distance (100 m threshold); clusters tagged as "hotspots" |
| **Lexicon Sentiment** | `SentimentAnalyzer.java` | On‑device (Java) | Positive/negative word dictionary; scores SMS body text as a net sentiment value |
| **Contact Quality Score** | `CommunicationMetrics.java` | On‑device (Java) | Weighted formula: frequency (40 pts) + recency (30 pts) + call duration (30 pts) |

### Algorithm Tiers

| Tier | Plans | Algorithms |
|------|-------|------------|
| **Core** | Free, Gold, Platinum | 8 statistical detection algorithms — baseline anomaly identification |
| **Advanced** | Gold, Platinum | 10 pattern‑analysis algorithms — K‑Means, DBSCAN, Z‑Score monitors |
| **Deep** | Platinum only | 7 ML‑engine detectors — Isolation Forest, One‑Class SVM, PCA, graph, MLP, phishing & file heuristics |

> **Honesty note:** the "Deep" tier algorithms are CPU‑only scikit‑learn / NetworkX models and heuristics running in the Python ML engine. They are named for what they actually do — there is no BERT, GCN, or LSTM in the stack, and no GPU is required.

### Heuristic vs. ML rationale

The server‑side anomaly system uses clustering‑based approaches (PHP‑ML K‑Means / DBSCAN, plus the Python Isolation Forest) instead of simple thresholds because behavioural patterns vary per device. Rules like ">100 messages = anomalous" would miss a heavy texter's normal pattern or falsely flag a power user. Clustering adapts to each device's unique baseline.

On‑device heuristics (`AnomalyDetector`, `LocationClusterer`) are deliberately kept lightweight (no external model) to avoid battery drain and preserve privacy — they run every time location is extracted and the result is uploaded as metadata, not as a separate event.

---

## Services

| Service | Description |
|---------|-------------|
| **PlanGate** | Central plan enforcement — device limits, history days, feature checks, algorithm filtering |
| **GeoIntelligenceService** | DBSCAN geo‑clustering, spatial hot‑spot detection, geo‑fence breach analysis |
| **GeoTransitionDetector** | Travel velocity computation, geo‑fence violation detection, improbable movement alerts |
| **RiskScoreService** | Composite 0–100 device risk scoring based on anomalies, geo‑fencing, and device context |
| **CarePlanService** | Prioritised care plan generation from anomaly and wellbeing data |
| **CorrelationService** | Cross‑category data correlation (contact → call → SMS → location) |
| **ForensicExportService** | Forensic data export packages for legal/forensic handover |
| **RetentionService** | Automated data retention enforcement and purges |

---

## Data Extraction

### Android Extractors (60+ categories)

The app ships over 60 `DataExtractor` implementations. The table below lists the primary modules (those wired into the periodic `DataSyncWorker`); many additional extractors (browser history, clipboard, keyboard, email, health data, USB, NFC, accessibility, thermal, etc.) are available on demand.

| Category | Extractor | On‑Device | Scheduled | Server Parser | DB Table |
|----------|-----------|:---------:|:---------:|---------------|----------|
| SMS | `SMSExtractor` | ✅ | ✅ | `Mod_Parse_Loot::get_sms` | `tbl_sms` |
| Call logs | `CallLogExtractor` | ✅ | ✅ | `Mod_Parse_Loot::get_logs` | `tbl_logs` |
| Contacts | `ContactsExtractor` | ✅ | ✅ | `Mod_Parse_Loot::get_contacts` | `tbl_contacts` |
| Installed apps | `AppsExtractor` | ✅ | ✅ | `Mod_Parse_Loot::get_apps` | `tbl_apps` |
| Files | `FileExtractor` | ✅ | ❌ (manual only — too heavy for periodic sync) | `Mod_Parse_Loot::get_files` | `tbl_device_files` |
| Location + activity | `LocationActivityExtractor` | ✅ | ✅ | `Mod_Parse_Loot::get_location` | `tbl_location`, `tbl_activity` |
| Live GPS batch | `LiveLocationWorker` | ✅ | Every 15 min | `Mod_Parse_Loot::parse_live_locations` | `tbl_location` |
| App usage | `AppUsageExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_app_usage` | `tbl_app_usage` |
| Notifications | `NotificationExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_notifications` | `tbl_notifications` |
| Calendar | `CalendarExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_calendar` | `tbl_calendar_events` |
| Accounts | `AccountsExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_accounts` | `tbl_accounts` |
| Network info | `NetworkInfoExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_network_info` | `tbl_network_info` |
| Bluetooth | `BluetoothExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_bluetooth` | `tbl_bluetooth` |
| Sensor profile | `SensorProfileExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_sensors` | `tbl_sensor_profile` |
| Device context | `DeviceContextExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_device_context` | `tbl_device_profile` |
| Security audit | `SecurityAuditExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_security_audit` | `tbl_security_audit` |
| SIM configs | `SimConfigExtractor` | ✅ | ✅ | `Mod_Parse_Loot::parse_sim_configs` | `tbl_sim_configs` |
| Hardware graphics / network | `HardwareGraphicsExtractor`, `HardwareNetworkExtractor` | ✅ | ✅ | `Mod_Parse_Advanced` | `tbl_device_profile` |
| App security / network security | `AppSecurityExtractor`, `NetworkSecurityExtractor` | ✅ | ✅ | `Mod_Parse_Advanced` | `tbl_security_audit` |
| Telephony / system locale / misc | `TelephonyNetworkExtractor`, `SystemLocaleExtractor`, `MiscSoftwareComposite`, `MiscHardwareComposite` | ✅ | ✅ | `Mod_Parse_Advanced` | `tbl_network_info` / `tbl_device_profile` |
| Remote media | `CameraCaptureUtil` / `AudioRecorderUtil` | ✅ | On demand | `Mod_Parse_Advanced::parse_captured_media` | `tbl_captured_media` |

### Security & Privacy

- **AES encryption** — all extracted payloads encrypted with a user‑supplied key before leaving the device.
- **Biometric auth** — app lock via `BiometricAuthActivity` using Android Biometric API.
- **Stealth mode** — hides the app icon, suppresses notifications (`StealthModeUtils`).
- **Self‑hosted backend** — no third‑party data processing; data stays on infrastructure the user controls.
- **Firebase Crashlytics & Performance** — optional monitoring for crash reports only.

### Sync & Offline

- **WorkManager periodic sync** — `DataSyncWorker` runs every N hours (configurable) and extracts/queues/uploads all enabled modules.
- **Offline queue** — `QueueSyncWorker` retries failed uploads from Room DB (`OfflinePayload`).
- **SIM change detection** — `SimChangeReceiver` triggers a full SIM config upload when a card is swapped.
- **Live location** — `LiveLocationWorker` caches GPS fixes and batch‑uploads when 5+ points accumulate.

---

## Dashboard

### Client Dashboard (authenticated users)

| View | Route | Description |
|------|-------|-------------|
| Home | `/home` | Data overview cards + recent activity |
| Apps | `/apps` | All / system / user applications |
| Call Logs | `/call_logs` | All / incoming / outgoing / missed / rejected / blocked |
| Contacts | `/contacts` | Contact list with quality scores |
| SMS | `/sms` | Inbox / sent / all |
| Files | `/files` | All / media / documents / audio / archives / others |
| Location | `/location` | Location history with coordinate filter and activity overlay |
| Advanced | `/advanced/{type}` | 14 advanced views: device, network, accounts, calendar, app‑usage, notifications, bluetooth, sensors, security, media, and more |
| SIM Configs | `/sim-configs` | SIM configuration history with device filter |
| Global Search | `/globalsearch` | Cross‑category search within user's data |
| Analysis | `/analysis` | ML‑powered analysis dashboard |
| Anomalies | `/analysis/anomalies` | 3‑step anomaly detection wizard with engine catalogue |
| Smart Timeline | `/analysis/timeline` | Chronological event feed across all extractors |
| Digital Wellbeing | `/analysis/wellbeing` | Usage trends and screen‑time insights *(plan‑gated)* |
| Correlation Engine | `/analysis/correlation-engine` | Cross‑event correlation with interactive charts *(platinum)* |
| Care Plans | `/analysis/care-plan` | Prioritised action plans from anomaly data *(platinum)* |
| Remote Device | `/remote-device` | Trigger extraction commands on the device remotely |
| Billing | `/billing` | Standalone upgrade page with plan comparison and simulated checkout (Card / M‑Pesa / PayPal / Stripe) |

### Admin Dashboard

| View | Route | Description |
|------|-------|-------------|
| Overview | `/admin/dashboard` | System health and key metrics |
| User Management | `/admin/users` | CRUD, suspend, activate user accounts |
| Settings | `/admin/settings` | Site, API, security, notification, and maintenance configuration |
| Logs | `/admin/logs` | Access, error, API, maintenance, and engine logs |
| ML Engine | `/admin/ml` | Algorithm configuration and management |

### Superadmin Dashboard

| View | Route | Description |
|------|-------|-------------|
| Overview | `/superadmin/home` | System‑wide metrics and health |
| Fleet | `/superadmin/fleet` | Device fleet timeline, patches, alerts, geo‑tracking |
| Plans | `/superadmin/plans` | Plan version management with diff history |
| Subscriptions | `/superadmin/subscriptions` | User subscription management and manual plan assignment |
| Payments | `/superadmin/payments` | Cross‑user payment history |
| Audit Log | `/superadmin/audit` | Security audit trail |
| Omni Search | `/superadmin/omni-search` | Cross‑user forensic search |
| Impersonate | `/superadmin/impersonate` | User session impersonation for troubleshooting |
| Role Matrix | `/superadmin/users` | Role and permission management |
| Forensic Export | `/superadmin/forensic-export` | Data export jobs for legal/forensic purposes |

---

## Tech Stack

### Backend (`Eaves Droid WebApp`)

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.3 |
| Framework | CodeIgniter 4.5 (`codeigniter4/framework ^4.0`) |
| Auth | CodeIgniter Shield 1.0 (beta) |
| Database | MySQL 8.4 |
| ML Library | `php-ai/php-ml` 0.10.0 (in‑process KMeans / DBSCAN) |
| ML Engine | Python FastAPI + scikit-learn / networkx (external service, CPU-only) |
| PDF Export | `dompdf/dompdf` ^3.1 |
| Frontend | AdminLTE 3 (Bootstrap 4, Font Awesome, jQuery) |
| Container | Docker / docker-compose (PHP 8.3‑apache, MySQL 8.4, phpMyAdmin, ML engine) |
| Web Server | Apache 2 (mod_rewrite) |
| Testing | PHPUnit ^9.1, Faker |

### Android Client (`Eaves_Droid_App`)

| Layer | Technology |
|-------|-----------|
| Language | Java 17 (minSdk 25, targetSdk 35, compileSdk 35) |
| Networking | Retrofit 2.11.0 + OkHttp 4.9.3 |
| Background Work | AndroidX WorkManager |
| Database (offline) | Room (via `AppDatabase`) |
| Image Loading | Picasso 2.71828 |
| UI | Material Design (com.google.android.material:1.12.0), ViewBinding, ConstraintLayout |
| Biometrics | AndroidX Biometric 1.1.0 |
| Firebase | Crashlytics, Performance Monitoring |
| Animations | Lottie 6.5.0 |
| Security | AndroidX Security‑Crypto 1.1.0‑alpha06 |

### ML Engine (`ML Eaves Droid`)

| Layer | Technology |
|-------|-----------|
| Language | Python 3.12 |
| Framework | FastAPI 0.115 + uvicorn |
| ML | scikit-learn 1.6, networkx 3.4, numpy, pandas |
| Database | Shared MySQL 8.4 (via SQLAlchemy + pymysql) |
| Cache | joblib model cache (`models_cache/`) |
| Observability | Prometheus (`/metrics`) |
| Container | Docker / docker-compose (python:3.12-slim) |

---

## Prerequisites

- **PHP** 8.3+ (with `intl`, `mysqli`, `pdo_mysql`, `zip`, `gd`, `xml`, `dom` extensions)
- **Composer** 2.x
- **MySQL** 8.4
- **Docker** & **docker-compose** (recommended) or **Apache** 2.4 + **mod_rewrite**
- **Android Studio** Hedgehog (2023.1+) or later
- **JDK** 17
- **Android SDK** API 25–35
- **Python** 3.12 (only if running the ML engine without Docker)

---

## Installation & Setup

### Docker (recommended)

```bash
# 1. Clone all three repos side-by-side
git clone <webapp-repo-url> "Eaves Droid WebApp"
git clone <android-repo-url> Eaves_Droid_App
git clone <ml-repo-url> "ML Eaves Droid"

# 2. Central docker-compose (webapp + MySQL + phpMyAdmin + ML engine)
cd hosts/                      # directory containing the three repos
docker compose up --build -d

# 3. Run database migrations
docker compose exec eaves-droid php spark migrate --all

# 4. Seed default plans, ML tables and superadmin account
docker compose exec eaves-droid php spark db:seed PlanSeeder
docker compose exec eaves-droid php spark db:seed SuperAdminSeeder
docker exec -i shared-mysql mysql -uroot -proot_password db_eaves_droid < "ML Eaves Droid/migrations/001_ml_jobs_tables.sql"

# 5. Configure the ML engine connection (defaults already set in .env):
#    PYTHON_BACKEND_HOST=ml-eaves-droid
#    PYTHON_BACKEND_PORT=9070

# Services:
# - Web app:     http://localhost:9007
# - MySQL:       localhost:9306
# - phpMyAdmin:  http://localhost:9000
# - ML engine:   http://localhost:9071/api/health
```

### Local / Manual

```bash
# Backend
composer install
cp .env.example .env
# Edit .env with your database credentials
php spark key:generate
php spark migrate --all
php spark db:seed PlanSeeder
php spark db:seed SuperAdminSeeder
php spark serve --host 0.0.0.0 --port 8080

# ML engine (optional, for the Deep tier)
cd "ML Eaves Droid"
pip install -r requirements.txt
cp .env.example .env
uvicorn app.main:app --reload --port 9070
# Point the webapp at it via PYTHON_BACKEND_HOST / PYTHON_BACKEND_PORT

# Android client
# Open Eaves_Droid_App/ in Android Studio
# Update the server URL in app/src/main/java/.../konstants/Konstants.java
#   (use 10.0.2.2 for emulator, or LAN IP for physical device)
# Build & run on device/emulator: ./gradlew installDebug
```

---

## Database Configuration

All tables are created by CodeIgniter migrations under `app/Database/Migrations/` (111 migration files). Key tables:

### Core

| Table | Purpose |
|-------|---------|
| `tbl_users` | User accounts (managed by Shield) |
| `tbl_devices` | Registered device fingerprints |
| `tbl_uploaded_files` | Upload manifest (original filename, size, category, hash) |
| `tbl_tokens` | Per‑device API tokens + device checksums |
| `tbl_blocklist` | Category‑based block rules |
| `tbl_sim_configs` | SIM card configurations and change history |
| `ml_jobs` | Anomaly detection job queue (created by the ML engine migration) |
| `ml_results` | Persisted anomaly detection findings |
| `ml_analysis_tracking` | Per‑user/category analysis progress for incremental runs |

### Billing & Subscriptions

| Table | Purpose |
|-------|---------|
| `plans` | Plan definitions (slug, name, active status) |
| `plan_versions` | Versioned plan configurations (pricing, features, limits, algorithms) |
| `user_subscriptions` | Active user subscriptions (plan, status, billing cycle) |
| `user_payments` | Payment history records |

### Extracted Data

| Table | Key Columns |
|-------|-------------|
| `tbl_logs` | `phone_number`, `duration_seconds`, `call_type`, `call_date` |
| `tbl_sms` | `address`, `body`, `type` (inbox/sent), `sms_date` |
| `tbl_contacts` | `display_name`, `phone_numbers` (JSON), `last_time_contacted` |
| `tbl_apps` | `app_name`, `package_name`, `is_system` |
| `tbl_device_files` | `name`, `file_path`, `file_size`, `file_category` |
| `tbl_location` | `latitude`, `longitude`, `accuracy`, `provider`, `location_time` |
| `tbl_activity` | `activity_type`, `confidence`, `battery_level`, `screen_on` |
| `tbl_network_info` | `connection_type`, `is_roaming`, `sim_operator_name`, `wifi_*` |
| `tbl_app_usage` | `package_name`, `total_time_in_foreground`, `last_time_used` |
| `tbl_notifications` | `package_name`, `title`, `text`, `post_time` |
| `tbl_calendar_events` | `title`, `description`, `event_start`, `event_end` |
| `tbl_accounts` | `account_type`, `account_name` |
| `tbl_bluetooth` | `adapter_name`, `is_enabled`, `paired_devices` (JSON) |
| `tbl_sensor_profile` | `sensor_name`, `vendor`, `type`, `max_range` |
| `tbl_device_profile` | `device_manufacturer`, `model`, `os_version`, `cpu_usage`, `ram_usage`, `battery_temperature` |
| `tbl_security_audit` | `audit_type`, `finding`, `severity` |
| `tbl_captured_media` | `media_type`, `file_path`, `captured_at` |

---

## Routes

### API (mobile client)

| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/v1/token/verify` | Verify API token |
| POST | `/api/v1/device/print` | Register device fingerprint |
| GET | `/api/v1/device/status` | Check device registration status |
| POST | `/api/v1/files/upload` | Upload encrypted extracted data |
| POST | `/api/v1/data/sms` | Upload SMS (legacy direct endpoint) |
| POST | `/api/v1/data/calls` | Upload call logs (legacy direct endpoint) |
| POST | `/api/v1/data/contacts` | Upload contacts (legacy direct endpoint) |
| POST | `/api/v1/data/apps` | Upload apps (legacy direct endpoint) |
| GET | `/api/v1/account/info` | Fetch user account info |
| GET | `/api/v1/config` | Fetch remote configuration |
| POST | `/api/v1/command/send` | Issue remote command to device |

### Client Dashboard

| Method | Path | Description |
|--------|------|-------------|
| GET | `/` | Landing page |
| GET | `/home` | Dashboard with data overview |
| GET | `/apps` | Installed apps |
| GET | `/call_logs` | Call logs |
| GET | `/contacts` | Contacts list |
| GET | `/sms` | SMS overview |
| GET | `/files` | Device files |
| GET | `/location` | Location history |
| GET | `/activities` | Device activity events |
| GET | `/advanced/{type}` | Advanced extraction views |
| GET | `/sim-configs` | SIM config history |
| GET | `/analysis` | Analysis dashboard |
| GET | `/analysis/anomalies` | Anomaly detection wizard |
| GET | `/analysis/timeline` | Smart timeline |
| GET | `/analysis/wellbeing` | Digital wellbeing *(plan‑gated)* |
| GET | `/analysis/correlation-engine` | Correlation engine *(platinum)* |
| GET | `/analysis/care-plan` | Care plans *(platinum)* |
| GET | `/globalsearch` | Cross‑category search |
| GET | `/remote-device` | Remote device control |
| GET | `/billing` | Standalone billing / upgrade page (simulated checkout) |
| GET | `/billing/subscription` | Current subscription details |
| POST | `/billing/simulate` | Simulated plan upgrade (marks payment paid + emails account holder) |
| POST | `/{resource}/delete/{id}` | Delete a record (AJAX) |

### Admin

| Method | Path | Description |
|--------|------|-------------|
| GET | `/admin/dashboard` | Admin overview |
| GET | `/admin/users` | User management |
| GET | `/admin/settings` | Site settings |
| GET | `/admin/logs` | System logs |
| GET | `/admin/ml` | ML engine config |
| GET | `/admin/anomalies` | Anomaly settings |
| GET | `/admin/remote-device` | Remote device commands |

### Superadmin

| Method | Path | Description |
|--------|------|-------------|
| GET | `/superadmin/home` | Superadmin dashboard |
| GET | `/superadmin/fleet` | Fleet management |
| GET | `/superadmin/plans` | Plan management |
| GET | `/superadmin/subscriptions` | Subscription management |
| GET | `/superadmin/payments` | Payment history |
| GET | `/superadmin/audit` | Audit trail |
| GET | `/superadmin/omni-search` | Cross‑user forensic search |
| GET | `/superadmin/impersonate` | User impersonation |
| GET | `/superadmin/users` | Role matrix |
| GET | `/superadmin/forensic-export` | Forensic data export |

---

## Project Structure

### Backend (`Eaves Droid WebApp`)

```
├── app/
│   ├── Commands/
│   ├── Config/
│   │   ├── AuthGroups.php          # 5 groups, 17 permissions, permission matrix
│   │   ├── Filters.php             # Global filter registration
│   │   └── Routes.php              # 2000+ route definitions
│   ├── Controllers/
│   │   ├── auth/                   # Login, Register, Forgot Password
│   │   ├── api/v1/                 # Receive, DatatableAPI, DeviceConfig, FCM
│   │   ├── clients/                # 18 dashboard controllers
│   │   │   ├── Client.php          # Home dashboard
│   │   │   ├── Billing.php         # Subscription management
│   │   │   ├── Anomalies.php       # Anomaly detection wizard
│   │   │   ├── Advanced.php        # 14 advanced extraction views
│   │   │   ├── Location.php        # Location + activity
│   │   │   ├── Correlation.php     # Correlation + wellbeing + care plans
│   │   │   └── ...
│   │   ├── admin/                  # 10 admin controllers
│   │   │   ├── Dashboard.php
│   │   │   ├── Users.php
│   │   │   ├── Settings.php
│   │   │   ├── Logs.php
│   │   │   ├── Ml.php
│   │   │   └── ...
│   │   └── superadmin/             # 11 superadmin controllers
│   │       ├── Dashboard.php
│   │       ├── FleetController.php
│   │       ├── Plans.php
│   │       ├── Subscriptions.php
│   │       ├── AuditLog.php
│   │       ├── OmniSearch.php
│   │       ├── Impersonate.php
│   │       ├── RoleMatrix.php
│   │       ├── ForensicExport.php
│   │       └── ...
│   ├── Database/
│   │   ├── Migrations/             # 111 migration files
│   │   └── Seeds/                  # PlanSeeder, SuperAdminSeeder, etc.
│   ├── Filters/
│   │   ├── PlanGate.php            # Subscription‑based feature gating
│   │   ├── RoleFilter.php          # Route‑level RBAC
│   │   ├── ImpersonateFilter.php   # Superadmin impersonation
│   │   ├── ThrottleFilter.php      # Rate limiting
│   │   └── MaintenanceFilter.php   # System maintenance mode
│   ├── Helpers/
│   ├── Libraries/
│   ├── Models/
│   │   ├── Mod_Anomalies.php       # ~3200‑line anomaly detection engine
│   │   ├── Mod_Finder.php          # ~7400‑line data query model
│   │   ├── Mod_Parse_Loot.php      # Legacy data parsers
│   │   ├── Mod_Parse_Advanced.php  # Advanced data parsers
│   │   ├── Mod_ML_Analyzer.php     # ML analysis (KMeans, NaiveBayes, TF-IDF, DBSCAN)
│   │   ├── Mod_Receive.php         # Device registration + PlanGate
│   │   ├── SubscriptionModel.php   # Subscriptions & plan limits
│   │   ├── PaymentModel.php        # Payment history & revenue
│   │   ├── PlanModel.php           # Plan definitions
│   │   ├── PlanVersionModel.php    # Plan versioning with diffs
│   │   └── ...
│   ├── Services/
│   │   ├── PlanGate.php            # Central plan enforcement
│   │   ├── GeoIntelligenceService.php
│   │   ├── GeoTransitionDetector.php
│   │   ├── RiskScoreService.php
│   │   ├── CarePlanService.php
│   │   ├── CorrelationService.php
│   │   ├── ForensicExportService.php
│   │   └── RetentionService.php
│   └── Views/
│       ├── headers_footers/        # Head, sidebar, footer partials
│       ├── landing/                # Public pages (pricing, FAQ, about, terms)
│       ├── auth/                   # Login, register, forgot/reset password
│       ├── users/                  # Data view pages (30+ files)
│       ├── admin/                  # Admin panel views
│       ├── superadmin/             # Superadmin panel views
│       ├── analysis/               # ML analysis, results, progress
│       └── email/                  # Admin and user email templates
├── public/                         # Web root (index.php, assets)
├── writable/                       # Uploads, exports, cache, logs
├── docker-compose.yml
├── Dockerfile
├── composer.json
└── phpunit.xml.dist
```

### Android Client (`Eaves_Droid_App`)

```
├── app/src/main/java/com/niccher/eaves_droid_app/
│   ├── activities/                 # 14 Activities
│   │   ├── ExtractDataActivity.java
│   │   ├── HomeActivity.java
│   │   └── ...
│   ├── interfaces/
│   │   └── DeviceApi.java          # Retrofit API interface
│   ├── konstants/
│   │   └── Konstants.java          # SharedPref keys, URLs, worker names
│   ├── model/                      # Room entities + DAO
│   ├── receivers/
│   │   └── SimChangeReceiver.java  # SIM swap detection + auto‑upload
│   ├── service/                    # Background services
│   │   ├── BackgroundUploadService.java
│   │   ├── BulkExtractionService.java
│   │   ├── DataSyncWorker.java
│   │   ├── LiveLocationWorker.java
│   │   ├── QueueSyncWorker.java
│   │   └── UploadService.java
│   └── utils/                      # 90+ utility/extractor classes
│       ├── DataExtractor.java      # Extractor interface
│       ├── *_Extractor.java        # 60+ DataExtractor implementations
│       ├── *_Composite.java        # Multi‑signal extractor bundles
│       ├── AnomalyDetector.java    # On‑device anomaly heuristics
│       ├── LocationClusterer.java  # Geo‑clustering (Haversine)
│       ├── SentimentAnalyzer.java  # Lexicon‑based sentiment
│       └── CommunicationMetrics.java
```

### ML Engine (`ML Eaves Droid`)

```
├── app/
│   ├── main.py                 # FastAPI entry point
│   ├── config.py               # Pydantic settings (env-driven)
│   ├── detectors/              # 7 detector implementations
│   │   ├── base.py             # BaseDetector ABC
│   │   └── shared/             # feature_engineering, isolation_forest helpers
│   ├── models/
│   │   ├── cache.py            # joblib result cache
│   │   ├── registry.py         # ALGORITHM_REGISTRY
│   │   └── schemas.py          # Pydantic request/response models
│   ├── routers/
│   │   ├── analyze.py          # /api/analyze — job dispatch + persistence
│   │   ├── health.py           # /api/health
│   │   └── models_info.py      # /api/models
│   └── utils/
│       └── db.py               # shared SQLAlchemy engine + ml_jobs/ml_results helpers
├── migrations/
│   └── 001_ml_jobs_tables.sql  # ml_jobs, ml_results, ml_analysis_tracking
├── Dockerfile
├── docker-compose.yml
├── requirements.txt
└── .env
```

---

## Configuration / Environment Variables

| Variable | Example | Description |
|----------|---------|-------------|
| `CI_ENVIRONMENT` | `development` | CodeIgniter environment mode |
| `app.baseURL` | `http://localhost:9007/` | Application base URL |
| `app.forceGlobalSecureRequests` | `false` | Enforce HTTPS |
| `database.default.hostname` | `mysql` | Database host (Docker service name) |
| `database.default.database` | `db_eaves_droid` | Database name |
| `database.default.username` | `root` | Database user |
| `database.default.password` | `your_password` | Database password |
| `database.default.DBDriver` | `MySQLi` | Database driver |
| `database.default.port` | `3306` | Database port |
| `encryption.key` | *(hex string)* | AES key (generate with `php spark key:generate`) |
| `PYTHON_BACKEND_HOST` | `ml-eaves-droid` | ML engine host (Docker service name) |
| `PYTHON_BACKEND_PORT` | `9070` | ML engine API port (internal) |
| `PYTHON_BACKEND_ENDPOINT` | `/api/analyze` | ML engine analyze endpoint |

---

## Contributing

1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/my-feature`).
3. Commit your changes (`git commit -am 'Add my feature'`).
4. Push to the branch (`git push origin feature/my-feature`).
5. Open a Pull Request.

Please keep commits atomic and write meaningful messages. For major changes, open an issue first to discuss.

---

## License

Both repositories are licensed under the [MIT License](LICENSE).

Copyright (c) 2026 niccher / Eaves Droid App.

---

## Support / Contact

For issues, feature requests, or questions, please open a GitHub issue in the relevant repository.
