# Eaves Droid — Web Application

> The analytical brain of a full-stack Device Intelligence & Insights System. Ingesting data from Android collector apps (SMS, call logs, contacts, apps, GPS, notifications, app usage, network info, Bluetooth, and more) and processing them into rich dashboards with **machine-learning-powered anomaly detection**.

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](#)
[![CodeIgniter 4](https://img.shields.io/badge/CodeIgniter-4.x-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white)](#)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](#)
[![phpMyAdmin](https://img.shields.io/badge/phpMyAdmin-Latest-F3971D?style=for-the-badge&logo=phpmyadmin&logoColor=white)](#)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](#)
[![PHP-ML](https://img.shields.io/badge/PHP--ML-2.x-4B8BBE?style=for-the-badge&logo=php&logoColor=white)](#)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](#)

---

## Table of Contents

1. [About the Project](#about-the-project)
2. [Machine Learning & Anomaly Detection](#machine-learning--anomaly-detection)
3. [Features](#features)
4. [Tech Stack](#tech-stack)
5. [Prerequisites](#prerequisites)
6. [Installation & Setup](#installation--setup)
7. [Project Structure](#project-structure)
8. [Contributing](#contributing)
9. [License](#license)

---

## About the Project

**Eaves Droid** is a professional, containerized CodeIgniter 4 web application that serves as the backend analytics platform for Android device data. It receives encrypted payloads from the companion Android app, stores them in MySQL, and provides a rich browser-based interface for:

- **Visual dashboards** for every data category (SMS, calls, contacts, apps, locations, device activity, network, Bluetooth, calendar, files, security audit, and more)
- **Cross-data correlation** and timeline reconstruction
- **Machine-learning anomaly detection** that automatically surfaces suspicious behavioural patterns
- **PDF export** with html2pdf.js for portable reporting

Every component is fully Dockerized — database migrations run automatically on boot, and persistent storage volumes keep your data safe across container lifecycles.

---

## Machine Learning & Anomaly Detection

The platform includes a **built-in anomaly detection pipeline** that analyses device datasets using both **statistical heuristics** and **PHP-ML library algorithms**. Results are surfaced through a three-step wizard at `/analysis/anomalies`, with interactive drill-downs, severity colouring, and algorithm explainers.

### Detection Engines

| Engine | Runtime | Status |
|--------|---------|--------|
| **PHP Engine** | In-process PHP-ML | Fully operational — all statistical and clustering algorithms run live |
| **Python Engine** | Separate Docker container (scikit-learn, PyOD) | Planned — architecture ready, algorithms listed in catalogue |

### Algorithms & How They Work

#### SMS Analysis
| Algorithm | Method | How It Works |
|-----------|--------|--------------|
| **Frequency Spike Detector** | Z-Score / Std Dev | Buckets SMS into 15-minute windows; computes mean + 2.5σ threshold; flags windows exceeding it. Escalates to High severity if count > 1.5× threshold |
| **Time-Pattern Analyser** | Rule-based | Flags messages sent during night hours (23:00–05:00) |
| **Sender K-Means** | PHP-ML KMeans clustering | Feature-engineers each sender into a 3D vector `[message_count, night_activity_ratio, avg_body_length]`; normalises to [0,1]; runs K-Means with k=3; flags singleton clusters or clusters with <15% of senders |

#### Contacts Analysis
| Algorithm | Method | How It Works |
|-----------|--------|--------------|
| **New-Contact Frequency Monitor** | Statistical threshold | Flags days where new contacts exceed 3× the daily mean count |
| **Duplicate Detector** | Hash-map comparison | Strips non-digits from phone numbers; detects collisions where the same number has different saved names |

#### Call Log Analysis
| Algorithm | Method | How It Works |
|-----------|--------|--------------|
| **Short-Call Burst Detector** | Sliding window | Flags 30-minute windows with ≥3 calls under 10 seconds. High severity if ≥6 calls |
| **Night-Activity Monitor** | Rule-based | Flags calls placed during night hours (23:00–05:00) |

#### Location Analysis
| Algorithm | Method | How It Works |
|-----------|--------|--------------|
| **Geo-Fence Violation Detector** | Haversine + Std Dev | Computes centroid of all location points; home zone radius = mean distance + 1σ; flags points > 1.5× radius |
| **Travel Speed Anomaly** | Haversine distance / Δt | Flags point-to-point speeds exceeding 900 km/h (commercial aircraft threshold) |
| **DBSCAN Trajectory Clustering** | PHP-ML DBSCAN | Passes `[lat, lng]` pairs to DBSCAN with ε=0.01 (≈1 km) and minSamples=2; flags noise points not belonging to any cluster |

#### Installed Apps Analysis
| Algorithm | Method | How It Works |
|-----------|--------|--------------|
| **Package Reputation Scanner** | Keyword pattern matching | Checks package names against 11 suspicious keywords (spy, track, stealth, ghost, hidden, etc.) |
| **Permission Anomaly Detector** | Z-Score / Std Dev | Counts sensitive permissions per app from 13 dangerous permissions; flags apps with counts exceeding mean + 2σ |

#### Files Analysis
| Algorithm | Method | How It Works |
|-----------|--------|--------------|
| **File Creation Spike Detector** | Statistical threshold | Flags days where file creation count exceeds 3× the daily mean |

#### Device Activity Analysis
| Algorithm | Method | How It Works |
|-----------|--------|--------------|
| **Screen-Time Anomaly Detector** | Z-Score / Std Dev | Flags days where screen-on minutes have |Z-score| > 2; High severity if Z > 3 |
| **App-Switch Rate Monitor** | Sliding window | Buckets app-usage events by hour; flags hours with >60 app transitions; High severity if >100 |

#### Device Info Analysis
| Algorithm | Method | How It Works |
|-----------|--------|--------------|
| **Hardware Change Detector** | Identifier comparison | Compares IMEI, serial, fingerprint, Android ID, MAC address across device profile snapshots |
| **Network Profile Monitor** | SSID comparison | Flags unrecognised Wi-Fi SSIDs and VPN connections against a known-safe list |

### Wizard Flow

```
Step 1  →  Step 2  →  Step 3
 Info /    Algorithm    Results with
 Engine    Selection    severity-sorted
 Pick      (multi-      findings,
           select)      explainers,
                        PDF export
```

The system auto-configures with sensible defaults, so one click takes you straight to results. The algorithm selection page lets you hand-pick which detectors to run per data category.

### Results View

Each detection finding includes: category icon, human-readable anomaly description, severity badge (High/Medium/Low with colouring), algorithm name, technical engine notes (threshold values, Z-scores), and timestamp. Findings are grouped by algorithm with collapsible explanation cards describing "How It Works" and "What the Results Mean". Results can be exported to PDF.

### Data Sources

All detection runs against live database tables populated by the Android collector app: `tbl_sms`, `tbl_contacts`, `tbl_logs`, `tbl_location`, `tbl_apps`, `tbl_device_files`, `tbl_app_usage`, `tbl_device_profile`, `tbl_network_info`. When a table is empty, the system falls back to pre-written demo data so the interface is never blank.

---

## Features

- **ML-Powered Anomaly Detection** — 16+ algorithms across 8 data categories using PHP-ML and statistical methods
- **Dual-Engine Architecture** — PHP engine (live, in-process) and Python engine (Docker-based, coming soon)
- **Rich Interactive Dashboards** — Per-category views with DataTables search/filter, colour-coded badges, and responsive layouts
- **Cross-Data Correlation** — Unified intelligence dashboard linking SMS, calls, locations, and timeline
- **Live Location Tracking** — Configurable periodic real-time location batching and dashboard tracking
- **SIM Configurations** — Real-time tracking of SIM states and auto-extraction on changes
- **PDF Export** — One-click PDF generation via html2pdf.js with SweetAlert2 progress feedback
- **Full Containerization** — Docker Compose with auto-migrations, persistent volumes, and phpMyAdmin
- **Standardised Timestamps** — All dates rendered in consistent format with calendar/clock icons via a shared helper
- **Responsive UI** — Bootstrap 4 + AdminLTE 3 with dark sidebar, card-based layouts, and mobile-friendly tables

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | CodeIgniter 4 |
| **Language** | PHP 8.3 |
| **Machine Learning** | PHP-ML 2.x (KMeans, DBSCAN), custom statistical engine (Z-Score, Haversine, sliding windows) |
| **Database** | MySQL 8.4 |
| **Database Manager** | phpMyAdmin |
| **Containerization** | Docker & Docker Compose |
| **PDF Generation** | html2pdf.js (browser-side) |
| **UI Framework** | AdminLTE 3 (Bootstrap 4), FontAwesome 5, DataTables |
| **Dependency Manager** | Composer |

---

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) & [Docker Compose](https://docs.docker.com/compose/install/)
- [Git](https://git-scm.com/)

*(Without Docker: PHP 8.3+, Composer, MySQL 8.4)*

---

## Installation & Setup

### Option 1: Using Docker (Preferred)

```bash
git clone https://github.com/yourusername/eaves-droid-webapp.git
cd "Eaves Droid WebApp"
cp .env.example .env
docker compose up --build -d
```

Access:
- **App**: [http://localhost:9007](http://localhost:9007)
- **phpMyAdmin**: [http://localhost:9000](http://localhost:9000)

Useful commands:
```bash
docker compose logs -f eaves-droid
docker compose down
docker compose restart eaves-droid
```

### Option 2: Local Development

```bash
composer install
cp .env.example .env
# Configure database credentials in .env
php spark migrate --all
php spark serve
```

---

## Project Structure

```text
.
├── app/
│   ├── Config/            # App configuration (routes, helpers, database)
│   ├── Controllers/
│   │   ├── api/v1/        # REST API endpoints for Android app
│   │   └── clients/       # Web UI controllers (dashboards, analysis)
│   ├── Helpers/           # Shared functions (time, logs, security)
│   ├── Models/            # Data access and ML anomaly detection
│   └── Views/             # PHP view templates (AdminLTE UI)
├── public/                # Document root
├── writable/              # Cache, logs, sessions, uploads
├── docker-compose.yml
├── Dockerfile
└── entrypoint.sh          # Auto-migration startup script
```

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes
4. Push: `git push origin feature/your-feature`
5. Open a Pull Request

---

## License

Distributed under the MIT License. See `LICENSE` for more information.
