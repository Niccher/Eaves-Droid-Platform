# Eaves Droid WebApp

Self-hosted forensic data ingestion server and analytical web dashboard for the Eaves Droid platform. Ingests encrypted on-device telemetry, parses multi-category forensic logs, and presents dashboards with real-time anomaly detection.

**Stack:** CodeIgniter 4.5 (PHP 8.3), MySQL 8.4, Docker Compose.

**If you only need to run the system, this page is enough.**  
Software engineers: [docs/README.md](docs/README.md).

---

## What “Running” Looks Like

| Interface | URL / Endpoint | Default Dev Credentials |
|-----------|----------------|-------------------------|
| Web Dashboard | http://localhost:9007 | Seeded via `SuperAdminSeeder` |
| Health Check | GET http://localhost:9007/api/health | *(None - returns HTTP 200)* |
| Data Ingestion Endpoint | POST http://localhost:9007/api/v1/files/upload | Requires mobile token |

---

## Prerequisites

### Option A — Docker Compose (Recommended)
* Git
* Docker Engine 24.0+ and Docker Compose v2

### Option B — Without Docker
* PHP 8.3 (with `curl`, `mysqlnd`, `mbstring`, `intl`, `xml`)
* Composer 2.7+
* MySQL 8.4 server

---

## Setup and Run

1. Clone the repository and navigate into the workspace:
   ```bash
   git clone https://github.com/Niccher/Eaves-Droid-WebApp.git
   cd Eaves-Droid-WebApp
   ```
2. Copy the environment template:
   ```bash
   cp env .env
   ```
3. Start the application and database containers:
   ```bash
   docker compose up --build -d
   ```
4. Run migrations and seed baseline plans and superadmin account:
   ```bash
   docker compose exec eaves-droid php spark migrate --all
   docker compose exec eaves-droid php spark db:seed PlanSeeder
   docker compose exec eaves-droid php spark db:seed SuperAdminSeeder
   ```
5. Open [http://localhost:9007](http://localhost:9007) in your web browser.
6. Stop the system when finished:
   ```bash
   docker compose down
   ```

*Android Emulator connection note:* In the mobile app, set server URL to `http://10.0.2.2:9007`.

---

## Configuration Users May Change

| Variable | Default | Purpose |
|----------|---------|---------|
| `CI_ENVIRONMENT` | `production` | Set to `development` for local debugging |
| `app.baseURL` | `http://localhost:9007/` | Web application base URL |
| `database.default.hostname` | `mysql` | MySQL hostname or container name |
| `database.default.database` | `db_eaves_droid` | Primary database name |

Full configuration reference: [docs/user/configuration.md](docs/user/configuration.md).

---

## Something Went Wrong?

* **Port 9007 in use:** Another process occupies port 9007. Change published host port in `docker-compose.yml`.
* **Database connection refused:** Wait for `shared-mysql` container health check to pass before accessing the web app.
* **Storage permission denied:** Run `chmod -R 777 writable/` to allow file uploads and session cache.

Detailed troubleshooting steps: [docs/user/troubleshooting.md](docs/user/troubleshooting.md).

---

## Software Engineers

* **Architecture Overview:** [docs/architecture/overview.md](docs/architecture/overview.md)
* **Inter-Service Communication:** [docs/architecture/communication.md](docs/architecture/communication.md)
* **Database & Storage Topology:** [docs/architecture/data-and-storage.md](docs/architecture/data-and-storage.md)
* **Deployment Guide:** [docs/architecture/deployment.md](docs/architecture/deployment.md)
* **CodeIgniter 4 Service Details:** [docs/services/codeigniter.md](docs/services/codeigniter.md)
* **Local Development & Recipes:** [docs/engineering/making-changes.md](docs/engineering/making-changes.md)
