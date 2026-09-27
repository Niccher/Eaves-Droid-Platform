# ML Eaves Droid

Python anomaly detection service for the Eaves Droid platform. Executes 13 CPU-only detector algorithms directly against the shared MySQL database to surface suspicious patterns without transferring raw data blobs over HTTP.

**Stack:** Python 3.12, FastAPI 0.115, scikit-learn 1.6, MySQL 8.4, Docker Compose.

**If you only need to run the system, this page is enough.**  
Software engineers: [docs/README.md](docs/README.md).

---

## What “Running” Looks Like

| Interface | URL / Endpoint | Purpose |
|-----------|----------------|---------|
| Interactive API Docs | http://localhost:9071/docs | OpenAPI Swagger interface |
| Health Check | GET http://localhost:9071/api/health | Engine status & loaded detector count |
| Job Dispatch | POST http://localhost:9071/api/v1/analysis-jobs | Internal job execution endpoint |
| Metrics | GET http://localhost:9071/metrics | Prometheus metrics |

---

## Prerequisites

* Docker Engine 24.0+ and Docker Compose v2
* The external shared bridge network `hosts-shared-network`
* Running MySQL instance (`shared-mysql`) with `db_eaves_droid`

---

## Setup and Run

1. Clone the repository and enter the directory:
   ```bash
   git clone https://github.com/Niccher/Eaves-Droid-ML.git
   cd Eaves-Droid-ML
   ```
2. Ensure the shared Docker network exists:
   ```bash
   docker network inspect hosts-shared-network >/dev/null 2>&1 || docker network create hosts-shared-network
   ```
3. Copy the environment configuration:
   ```bash
   cp .env.example .env
   ```
4. Start the platform services:
   ```bash
   docker compose up --build -d
   ```
5. Database migrations are handled automatically by CodeIgniter on container boot:
   ```bash
   # CodeIgniter runs migrations and seeds on startup.
   # To run manually from the platform monorepo root:
   docker compose exec web php spark migrate --all
   ```
6. Open [http://localhost:9071/api/health](http://localhost:9071/api/health) to confirm the engine is healthy and all detectors are loaded.
7. Stop the service when needed:
   ```bash
   docker compose down
   ```

---

## Configuration Users May Change

| Variable | Default | Purpose |
|----------|---------|---------|
| `ML_EAVES_DROID_API_PORT` | `9071` | Published host port for the FastAPI service |
| `LOG_LEVEL` | `info` | Logging verbosity (`debug`, `info`, `warning`) |
| `DB_HOST` | `mysql` | MySQL container hostname on the shared network |
| `DB_NAME` | `db_eaves_droid` | Target database name |

Full configuration reference: [docs/user/configuration.md](docs/user/configuration.md).

---

## Something Went Wrong?

* **Missing network `hosts-shared-network`:** Run `docker network create hosts-shared-network` before launching.
* **Database connection timeout:** Ensure `shared-mysql` is running and has completed initial table creation.
* **Port 9071 occupied:** Change `ML_EAVES_DROID_API_PORT` in `.env` to an open port (e.g. `9075`).

Detailed troubleshooting steps: [docs/user/troubleshooting.md](docs/user/troubleshooting.md).

---

## Software Engineers

* **Architecture & Database Integration:** [docs/architecture/overview.md](docs/architecture/overview.md)
* **Inter-Service Communication:** [docs/architecture/communication.md](docs/architecture/communication.md)
* **API Schema Contracts:** [docs/api/contract.md](docs/api/contract.md)
* **FastAPI Service Structure:** [docs/services/fastapi.md](docs/services/fastapi.md)
* **Detector Catalog & Methodology:** [docs/services/ml.md](docs/services/ml.md)
* **Adding a New Detector:** [docs/engineering/making-changes.md](docs/engineering/making-changes.md)
* **Local Development & Testing:** [docs/engineering/local-development.md](docs/engineering/local-development.md)
