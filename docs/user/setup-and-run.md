# User Guide: Setup and Run

This guide walks operators through starting the **ML Eaves Droid** FastAPI service.

---

## 1. Prerequisites

* **Docker Engine** 24.0+ and **Docker Compose v2**
* The shared Docker network `hosts-shared-network` must exist (created by the WebApp or manually created via `docker network create hosts-shared-network`).
* Running MySQL instance (`shared-mysql`) containing `db_eaves_droid`.

---

## 2. Running via Docker Compose

### Step 1: Ensure Network and Environment
Ensure the shared network is active and copy configuration:
```bash
docker network inspect hosts-shared-network >/dev/null 2>&1 || docker network create hosts-shared-network
cp .env.example .env
```

### Step 2: Launch the ML Container
Start the container in detached mode:
```bash
docker compose up --build -d
```

### Step 3: Run Initial Database Migration
If not already initialized by the platform, run the SQL migration against the shared MySQL container:
```bash
docker exec -i shared-mysql mysql -uroot -proot_password db_eaves_droid < migrations/001_ml_jobs_tables.sql
```

### Step 4: Verify Service Health
* **Health Check:** `http://localhost:9071/api/health` should return status `ok` and list loaded detectors.
* **Interactive API Docs:** Browse Swagger UI at `http://localhost:9071/docs`.

### Step 5: Stopping the Service
```bash
docker compose down
```
