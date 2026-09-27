# Eaves Droid Platform

Unified forensic data ingestion, analytics control plane, and machine learning anomaly detection platform. Ingests encrypted on-device forensic telemetry from mobile devices, manages multi-tenant investigations, and executes 13+ statistical & neural anomaly detection models directly against incoming device events.

**Stack:** CodeIgniter 4.5 (PHP 8.3), FastAPI 0.115 (Python 3.12), scikit-learn 1.6, Redis 7, MySQL 8.4, Docker Compose.

**If you only need to run the system, this page is enough.**  
Software engineers: [docs/README.md](docs/README.md).

---

## Ecosystem Architecture

| Component | Responsibility | Repository / Path | Documentation |
|---|---|---|---|
| **Eaves Droid Platform (This Repo)** | Monorepo containing Web Control Plane & ML Anomaly Service | [GitHub Repo](https://github.com/Niccher/Eaves-Droid-Platform) | [docs/](docs/README.md) |
| ├── **Web App (`web/`)** | Forensic Ingestion, Shield Auth, Billing, Dashboard & Migrations | `./web` | [docs/services/codeigniter.md](docs/services/codeigniter.md) |
| └── **ML Engine (`ml/`)** | FastAPI, scikit-learn, ONNX, PyOD & Autoencoders | `./ml` | [docs/services/fastapi.md](docs/services/fastapi.md) |
| **Android Client** | Kotlin Native App, 60+ Forensic Extractors & AES-256 Upload | [Niccher/Eaves-Droid-App](https://github.com/Niccher/Eaves-Droid-App) | [docs/ecosystem/android-dependency.md](docs/ecosystem/android-dependency.md) |

---

## What “Running” Looks Like

| Interface | URL / Endpoint | Purpose / Notes |
|-----------|----------------|-----------------|
| **Web Dashboard** | http://localhost:9007 | Management UI & Forensic Analytics (Admin/SuperAdmin) |
| **Data Ingestion API** | POST http://localhost:9007/api/v1/files/upload | Ingestion endpoint for encrypted Android telemetry |
| **Web Health & Resilience** | GET http://localhost:9007/health | Live dual-engine telemetry (Redis probe, MySQL fallback status) |
| **ML Engine Health Check** | GET http://localhost:9071/api/health | ML Engine status & loaded detector count |
| **ML Interactive Swagger** | http://localhost:9071/docs | OpenAPI documentation for anomaly job endpoints |

---

## Prerequisites

* Docker Engine 24.0+ and Docker Compose v2
* Git

---

## Setup and Run

1. Clone the repository and enter the directory:
   ```bash
   git clone https://github.com/Niccher/Eaves-Droid-Platform.git
   cd Eaves-Droid-Platform
   ```

2. Copy the environment configuration:
   ```bash
   cp .env.example .env
   ```

3. Launch the full platform stack:
   ```bash
   docker compose up --build -d
   ```

4. **Automated Migrations & Seeding:**  
   During container startup, CodeIgniter's entrypoint automatically waits for MySQL, runs all 147+ database migrations (`php spark migrate --all`), and provisions all default seeders (`DatabaseSeeder`), including plan tiers, superadmin account, and ML internal communication settings. No manual SQL imports are required!

5. Open [http://localhost:9007](http://localhost:9007) in your browser. Default superadmin credentials:
   * **Email:** `superadmin@eavesdroid.local`
   * **Password:** `Admin@123456`

6. Stop the platform when finished:
   ```bash
   docker compose down
   ```

*Android Emulator connection note:* In the mobile application settings, set the Server URL to `http://10.0.2.2:9007`.

---

## Configuration Reference

| Variable | Default | Purpose |
|----------|---------|---------|
| `WEB_PORT` | `9007` | Host port for the CodeIgniter Web Dashboard |
| `ML_PORT` | `9071` | Host port for the FastAPI Python ML service |
| `DB_PORT_FORWARD` | `9306` | Published host port for the MySQL database |
| `DB_PASS` | `root_password` | Root password for MySQL |
| `ML_INTERNAL_TOKEN` | `default_secure_token...` | Shared secret header between WebApp and ML |
| `RUN_MIGRATIONS` | `true` | When true, runs migrations & seeders automatically on boot |

Full configuration reference: [docs/user/configuration.md](docs/user/configuration.md).

---

## Software Engineers

* **Engineering Portal:** [docs/README.md](docs/README.md)
* **Architecture & Inter-Service Communication:** [docs/architecture/communication.md](docs/architecture/communication.md)
* **Android Client Dependency & Ingestion Protocol:** [docs/ecosystem/android-dependency.md](docs/ecosystem/android-dependency.md)
* **ML Model Catalog & Methodology:** [docs/services/ml.md](docs/services/ml.md)
* **Database & Migration Management:** [docs/engineering/database.md](docs/engineering/database.md)
