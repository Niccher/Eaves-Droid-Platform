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

Eaves Droid is a two‑repo ecosystem with an **Android client** (`Eaves_Droid_App`) that extracts 17+ categories of device data (SMS, call logs, contacts, location, apps, files, network, Bluetooth, sensor profiles, app usage, notifications, calendar, accounts, security audit logs, device context, SIM configs, and live location) and a **PHP/CodeIgniter 4 backend** (`Eaves Droid WebApp`) that receives, decrypts, parses, stores, and visualises the data on an AdminLTE dashboard.

The platform is designed for device owners who want inspect their own digital footprint on a self‑hosted server. It distinguishes itself by running **on‑device ML‑inspired heuristics** (anomaly detection, sentiment analysis, communication scoring, geo‑clustering) and **server‑side PHP‑ML algorithms** (K‑Means sender clustering, DBSCAN trajectory analysis, isolation forest, time‑series frequency monitors) without sending raw data to third‑party services.

---

## Architecture

```
┌──────────────────────────────────────┐          HTTPS/JSON          ┌──────────────────────────────────────┐
│        Android Device (Client)       │  ──────────────────────────►  │        Web Backend (Server)          │
│                                      │  POST /api/v1/files/upload   │                                      │
│  ┌────────────────────────────┐      │  POST /api/v1/device/print   │  ┌──────────────────────────────┐     │
│  │  ExtractDataActivity       │      │  POST /api/v1/token/verify   │  │  Receive Controller          │     │
│  │  (17 DataExtractors)       │──────┼──────────────────────────────►│  │  (decrypt, route by category)│     │
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
│  │  SimChangeReceiver         │──────┼──────────────────────────────►│  └──────────────────────────────┘     │
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
```

**Dependency chain:**

1. The **Android app** extracts device data through `DataExtractor` implementations, encrypts it with AES (user‑provided key), and uploads via the API endpoint `POST /api/v1/files/upload`.
2. The **Receive controller** decrypts the payload and routes to the correct parser (`Mod_Parse_Loot` for legacy categories, `Mod_Parse_Advanced` for advanced ones) based on the `category` field.
3. Parsed records are inserted into MySQL tables (one per data type).
4. The **client web dashboard** (CodeIgniter controllers + AdminLTE views) queries the database and renders sortable, searchable, paginated tables with per‑row delete and export features.
5. **Mod_Anomalies** runs detection engines (K‑Means, DBSCAN, frequency monitors) on stored data and persists findings in `tbl_anomaly_results`.
6. The Android app also runs periodic **DataSyncWorker** and **LiveLocationWorker** via WorkManager, and auto‑triggers on SIM change via **SimChangeReceiver**.

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

| Algorithm | Location | Library / Method | How It Works |
|-----------|----------|------------------|--------------|
| **K‑Means Clustering** | `Mod_Anomalies::detectSmsCluster()` | `PhpMl\Clustering\KMeans` (k=3, min-max scaled) | Groups SMS senders by frequency/night‑ratio/unique‑receivers; small clusters flagged as outliers |
| **DBSCAN Clustering** | `Mod_Anomalies::detectLocationDBSCAN()` | `PhpMl\Clustering\DBSCAN` (ε=0.01, minPts=2) | Clusters lat/lng coordinates; points outside dense zones flagged as trajectory anomalies |
| **Isolation Forest** | `Mod_Anomalies::detectCallIsolationForest()` | `PhpMl\AnomalyDetection\IsolationForest` (trees=100, sample=256) | Isolates short‑call bursts and unusual call patterns by random partitioning |
| **Rule‑based Anomaly Heuristic** | `AnomalyDetector.java` | On‑device (Java) | Adds weighted scores for late‑night extractions, low‑battery + active, unusual running hours, no‑connectivity states |
| **Location Clustering** | `LocationClusterer.java` | On‑device (Java) | Centroids calculated via moving‑average Haversine distance (100 m threshold); clusters tagged as "hotspots" |
| **Lexicon Sentiment** | `SentimentAnalyzer.java` | On‑device (Java) | Positive/negative word dictionary; scores SMS body text as a net sentiment value |
| **Contact Quality Score** | `CommunicationMetrics.java` | On‑device (Java) | Weighted formula: frequency (40 pts) + recency (30 pts) + call duration (30 pts) |

### Heuristic vs. ML rationale

The server‑side anomaly system uses PHP‑ML (K‑Means, DBSCAN, Isolation Forest) instead of simple thresholds because behavioural patterns vary per device. Rules like ">100 messages = anomalous" would miss a heavy texter's normal pattern or falsely flag a power user. Clustering adapts to each device's unique baseline.

On‑device heuristics (`AnomalyDetector`, `LocationClusterer`) are deliberately kept lightweight (no external model) to avoid battery drain and preserve privacy — they run every time location is extracted and the result is uploaded as metadata, not as a separate event.

---

## Features

### Data Extraction

| Category | Extractor | On‑Device | Scheduled | Server Parser | DB Table |
|----------|-----------|-----------|-----------|---------------|----------|
| SMS | `SMSExtractor` | ✅ | ✅ | `Mod_Parse_Loot::get_sms` | `tbl_sms` |
| Call logs | `CallLogExtractor` | ✅ | ✅ | `Mod_Parse_Loot::get_logs` | `tbl_call_logs` |
| Contacts | `ContactsExtractor` | ✅ | ✅ | `Mod_Parse_Loot::get_contacts` | `tbl_contacts` |
| Installed apps | `AppsExtractor` | ✅ | ✅ | `Mod_Parse_Loot::get_apps` | `tbl_apps` |
| Files | `FileExtractor` | ✅ | ✅ | `Mod_Parse_Loot::get_files` | `tbl_device_files` |
| Location + activity | `LocationActivityExtractor` | ✅ | ✅ | `Mod_Parse_Loot::get_location` | `tbl_location`, `tbl_activity` |
| Live GPS batch | `LiveLocationWorker` | ✅ | Every 15 min | `Mod_Parse_Loot::parse_live_locations` | `tbl_location` |
| App usage | `AppUsageExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_app_usage` | `tbl_app_usage` |
| Notifications | `NotificationExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_notifications` | `tbl_notifications` |
| Calendar | `CalendarExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_calendar` | `tbl_calendar_events` |
| Accounts | `AccountsExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_accounts` | `tbl_accounts` |
| Network info | `NetworkInfoExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_network_info` | `tbl_network_info` |
| Bluetooth | `BluetoothExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_bluetooth` | `tbl_bluetooth` |
| Sensor profile | `SensorProfileExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_sensors` | `tbl_sensor_profile` |
| Device context | `DeviceContextExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_device_context` | `tbl_device_context` |
| Security audit | `SecurityAuditExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_security_audit` | `tbl_security_audit` |
| Device info | `DeviceInfoExtractor` | ✅ | ✅ | `Mod_Parse_Advanced::parse_device_info` | (nested in device context) |
| SIM configs | `SimConfigExtractor` | ✅ | ✅ | `Mod_Parse_Loot::parse_sim_configs` | `tbl_sim_configs` |
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

### Dashboard

- **24 data views** — SMS (inbox/sent/all), call logs, contacts, apps (all/system/user), files (all/media/docs/audio/archives/others), location, activity, plus 14 advanced extractor pages.
- **Anomaly detection wizard** — step‑by‑step analysis with engine catalogue and highlighted risk findings.
- **Timeline** — chronological view of all extractor events merged into a single stream.
- **Correlation analysis** — cross‑reference data (e.g., contact ID → call log → SMS → location) with interactive charts.
- **Remote device control** — trigger extraction commands on the device remotely (pending implementation).

---

## Tech Stack

### Backend (`Eaves Droid WebApp`)

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.3 |
| Framework | CodeIgniter 4.5 (`codeigniter4/framework ^4.0`) |
| Auth | CodeIgniter Shield 1.0 (beta) |
| Database | MySQL 8.4 |
| ML Library | `php-ai/php-ml` 0.10.0 |
| PDF Export | `dompdf/dompdf` ^3.1 |
| Frontend | AdminLTE 3 (Bootstrap 4, Font Awesome, jQuery) |
| Container | Docker / docker-compose (PHP 8.3‑apache, MySQL 8.4, phpMyAdmin) |
| Web Server | Apache 2 (mod_rewrite) |

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

---

## Prerequisites

- **PHP** 8.3+ (with `intl`, `mysqli`, `pdo_mysql`, `zip`, `gd`, `xml`, `dom` extensions)
- **Composer** 2.x
- **MySQL** 8.4
- **Docker** & **docker-compose** (recommended) or **Apache** 2.4 + **mod_rewrite**
- **Android Studio** Hedgehog (2023.1+) or later
- **JDK** 17
- **Android SDK** API 25–35

---

## Installation & Setup

### Docker (recommended)

```bash
# Clone the backend
git clone <repo-url> eaves-droid-webapp
cd eaves-droid-webapp

# Copy environment config
cp .env.example .env
# Edit .env: set app.baseURL, database credentials, and generate encryption key

# Build and start
docker compose up --build -d

# Run database migrations
docker compose exec eaves-droid php spark migrate --all

# Services:
# - Web app:  http://localhost:9007
# - MySQL:    localhost:9306
# - phpMyAdmin: http://localhost:9000
```

### Local / Manual

```bash
# Backend
composer install
cp .env.example .env
# Edit .env with your database credentials
php spark key:generate
php spark migrate --all
php spark serve --host 0.0.0.0 --port 8080

# Android client
# Open Eaves_Droid_App/ in Android Studio
# Update the server URL in app/src/main/java/.../konstants/Konstants.java
#   (use 10.0.2.2 for emulator, or LAN IP for physical device)
# Build & run on device/emulator: ./gradlew installDebug
```

---

## Database Configuration

All tables are created by CodeIgniter migrations under `app/Database/Migrations/`. Key tables:

### Core

| Table | Purpose |
|-------|---------|
| `tbl_users` | User accounts (managed by Shield) |
| `tbl_devices` | Registered device fingerprints |
| `tbl_uploaded_files` | Upload manifest (original filename, size, category, hash) |
| `tbl_anomaly_results` | Persisted anomaly detection findings |
| `tbl_blocklist` | Category‑based block rules |
| `tbl_sim_configs` | SIM card configurations and change history |

### Extracted data

| Table | Key Columns |
|-------|-------------|
| `tbl_call_logs` | `phone_number`, `duration_secs`, `call_type`, `call_time` |
| `tbl_sms` | `address`, `body`, `type` (inbox/sent), `date` |
| `tbl_contacts` | `display_name`, `phone_numbers` (JSON), `last_time_contacted` |
| `tbl_apps` | `app_name`, `package_name`, `is_system` |
| `tbl_device_files` | `file_name`, `file_path`, `file_size`, `file_category` |
| `tbl_location` | `latitude`, `longitude`, `accuracy`, `provider`, `location_time` |
| `tbl_activity` | `activity_type`, `confidence`, `battery_level`, `screen_on` |
| `tbl_network_info` | `connection_type`, `is_roaming`, `sim_operator_name`, `wifi_*` |
| `tbl_app_usage` | `package_name`, `total_time_in_foreground`, `last_usage_time` |
| `tbl_notifications` | `package_name`, `title`, `text`, `post_time` |
| `tbl_calendar_events` | `title`, `description`, `event_start`, `event_end` |
| `tbl_accounts` | `account_type`, `account_name` |
| `tbl_bluetooth` | `adapter_name`, `is_enabled`, `paired_devices` (JSON) |
| `tbl_sensor_profile` | `sensor_name`, `vendor`, `type`, `max_range` |
| `tbl_device_context` | `device_manufacturer`, `model`, `os_version`, `root_status` |
| `tbl_security_audit` | `audit_type`, `finding`, `severity` |
| `tbl_captured_media` | `media_type`, `file_path`, `captured_at` |

---

## Usage / Routes

### Client dashboard (authenticated web UI)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/` | Landing page |
| GET | `/home` | Dashboard with data overview (cards + recent activity) |
| GET | `/apps` | Installed apps (all / system / user) |
| GET | `/call_logs` | Call logs (all / incoming / outgoing / missed) |
| GET | `/contacts` | Contacts list with quality scores |
| GET | `/sms` | SMS overview (inbox / sent / all) |
| GET | `/files` | Device files (all / media / documents / audio / archives / others) |
| GET | `/location` | Location history with coordinate filter |
| GET | `/activities` | Device activity events |
| GET | `/advanced/{type}` | Advanced extraction views (device, network, accounts, calendar, app‑usage, notifications, bluetooth, sensors, security, media) |
| GET | `/sim-configs` | SIM config history with device filter |
| GET | `/analysis` | Correlation analysis dashboard |
| GET | `/timeline` | Merged chronological feed |
| GET | `/anomalies` | Anomaly detection wizard and results |
| POST | `/{resource}/delete/{id}` | Delete a single record (AJAX) |

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

---

## Project Structure

### Backend (`Eaves Droid WebApp`)

```
├── app/
│   ├── Config/
│   │   ├── Routes.php              # All 1400+ route definitions
│   │   └── ...
│   ├── Controllers/
│   │   ├── api/v1/Receive.php      # File upload, parsing, device mgmt
│   │   ├── clients/                # Dashboard controllers (20+ files)
│   │   │   ├── Advanced.php        # Advanced extraction views (14 types)
│   │   │   ├── Analysis.php        # Correlation analysis
│   │   │   ├── Client.php          # Home dashboard, profile
│   │   │   ├── Location.php        # Location + Activity views
│   │   │   ├── SimConfig.php       # SIM config view
│   │   │   └── ...
│   │   └── Errors.php              # Custom error pages
│   ├── Database/
│   │   └── Migrations/             # 36 migration files
│   ├── Models/
│   │   ├── Mod_Anomalies.php       # 1825‑line ML detection engine
│   │   ├── Mod_Finder.php          # 3700‑line data query model
│   │   ├── Mod_Parse_Loot.php      # Legacy data parsers
│   │   ├── Mod_Parse_Advanced.php  # Advanced data parsers
│   │   └── ...
│   └── Views/
│       ├── headers_footers/        # Head, sidebar, footer partials
│       └── users/                  # Data view pages (30+ files)
├── public/                         # Web root (index.php, assets)
├── writable/                       # Upload storage, logs, cache
├── docker-compose.yml              # MySQL 8.4 + phpMyAdmin + app
├── Dockerfile                      # php:8.3-apache
└── composer.json
```

### Android Client (`Eaves_Droid_App`)

```
├── app/src/main/java/com/niccher/eaves_droid_app/
│   ├── activities/                 # 14 Activities
│   │   ├── ExtractDataActivity.java# Manual extraction trigger UI
│   │   ├── HomeActivity.java       # Main dashboard
│   │   └── ...
│   ├── interfaces/
│   │   └── DeviceApi.java          # Retrofit API interface
│   ├── konstants/
│   │   └── Konstants.java          # SharedPref keys, URLs, worker names
│   ├── model/                      # Room entities + DAO
│   ├── receivers/
│   │   └── SimChangeReceiver.java  # SIM swap detection + auto‑upload
│   ├── service/                    # Background services
│   │   ├── BackgroundUploadService.java # Service wrapping extractors
│   │   ├── BulkExtractionService.java   # Batch extraction orchestrator
│   │   ├── DataSyncWorker.java     # Periodic WorkManager sync
│   │   ├── LiveLocationWorker.java # GPS batch collector
│   │   ├── QueueSyncWorker.java    # Offline retry worker
│   │   └── UploadService.java      # File upload with retry
│   └── utils/                      # 33 utility/extractor classes
│       ├── DataExtractor.java      # Extractor interface
│       ├── *_Extractor.java        # 17 DataExtractor implementations
│       ├── AnomalyDetector.java    # On‑device anomaly heuristics
│       ├── LocationClusterer.java  # Geo‑clustering (Haversine)
│       ├── SentimentAnalyzer.java  # Lexicon‑based sentiment
│       └── CommunicationMetrics.java# Contact quality scoring
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
| `encryption.key` | *(hex string)* | AES‑256 key for data encryption (generate with `php spark key:generate`) |

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
