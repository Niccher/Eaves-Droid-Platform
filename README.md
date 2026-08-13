<div align="center">

# ML Eaves Droid

**Python anomaly-detection backend for the Eaves Droid platform.**

[![Python](https://img.shields.io/badge/Python-3.12-3776AB?style=for-the-badge&logo=python)](https://python.org)
[![FastAPI](https://img.shields.io/badge/FastAPI-0.115-009688?style=for-the-badge&logo=fastapi)](https://fastapi.tiangolo.com)
[![scikit-learn](https://img.shields.io/badge/scikit--learn-1.6-F7931E?style=for-the-badge&logo=scikit-learn)](https://scikit-learn.org)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker)](https://docker.com)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge)](LICENSE)

</div>

---

## Overview

ML Eaves Droid is the **third component** of the Eaves Droid ecosystem, alongside the [Android client](https://github.com/Niccher/Eaves-Droid-App) and the [PHP/CodeIgniter 4 webapp](https://github.com/Niccher/Eaves-Droid-WebApp).

It is a lightweight **FastAPI service** that performs anomaly detection against the data the webapp has already stored in MySQL. The PHP webapp creates a job row in `ml_jobs`, POSTs a tiny `{job_id, user_id, algorithms, scope}` payload to `/api/analyze`, and this backend:

1. Updates the job status to `running`.
2. Checks the model cache — if a valid cache entry exists for the algorithm set and data version, it returns cached results immediately.
3. Runs each requested detector, which queries the **shared MySQL database directly** (no HTTP data blobs).
4. Persists every finding into `ml_results` so the PHP side can render them.
5. Updates `ml_analysis_tracking` for incremental-run bookkeeping.
6. Marks the job `completed` and returns a light status response.

Data never leaves your infrastructure — detectors read from the same MySQL the webapp writes to.

> **Design note:** all detectors are deliberately **CPU-only** implementations built on scikit-learn and NetworkX. No GPU, TensorFlow, or PyTorch is required, which keeps the container small and deployable on any Docker host.

---

## Detectors

Seven detectors are registered (the "Deep" tier in the webapp's Platinum plan). They are named for what they actually do — no claims of BERT/GCN/LSTM where none exist:

| ID | Display name | Category | Table | Method |
|----|--------------|----------|-------|--------|
| `sms_bert` | SMS Phishing Keyword Heuristic | SMS | `tbl_sms` | Keyword/phrase scan for phishing indicators |
| `contacts_graph` | Contact Graph Outlier Model | Contacts | `tbl_contacts` | NetworkX graph; flags orphaned / low-connectivity contacts |
| `calls_isolation` | Isolation Forest Outlier Detection | Call Logs | `tbl_logs` | scikit-learn Isolation Forest on call features |
| `apps_autoencoder` | App Manifest Anomaly Scanner (PCA) | Apps | `tbl_apps` | PCA reconstruction error over manifest-style features |
| `files_entropy` | Suspicious File Metadata Scanner | Files | `tbl_device_files` | Metadata rules (extension, depth, hidden names) |
| `act_lstm` | Activity Sequence Predictor (MLP) | Activity | `tbl_app_usage` | sklearn MLP on app-usage timestamps |
| `dev_oneclass` | One-Class SVM System-State Profiler | Device Info | `tbl_device_profile` | One-Class SVM on system telemetry |

> The `algorithm_id`s above are kept as stable machine identifiers for compatibility with existing `ml_results` rows and webapp configuration — e.g. `sms_bert` is the historical id, not an indication that BERT is used.

### Category → table mapping

| Category | Table | PK |
|----------|-------|----|
| `sms` | `tbl_sms` | `id` |
| `contacts` | `tbl_contacts` | `id` |
| `call_logs` | `tbl_logs` | `id` |
| `locations` | `tbl_location` | `id` |
| `apps` | `tbl_apps` | `id` |
| `files` | `tbl_device_files` | `id` |
| `activity` | `tbl_app_usage` | `id` |
| `device_info` | `tbl_device_profile` | `id` (keyed by device checksum, resolved via `tbl_tokens` / `uploaded_files`) |

---

## API

| Method | Path | Description |
|--------|------|-------------|
| GET | `/` | Service landing page (discovery) |
| GET | `/api/health` | Status, loaded models, DB connectivity, cache stats |
| GET | `/api/models` | List registered algorithms + parameters |
| POST | `/api/analyze` | Run a detection job (`{job_id, user_id, algorithms, scope}`) |
| GET | `/metrics` | Prometheus metrics |

### `/api/analyze` request

```json
{
  "job_id": 123,
  "user_id": 7,
  "algorithms": ["calls_isolation", "sms_bert"],
  "scope": "full"
}
```

Incremental runs add `"scope": "incremental"` and `"incremental_since": "2026-08-01 00:00:00"`.

Findings are written to `ml_results`; the response is a lightweight status with a result count and timing:

```json
{
  "status": "completed",
  "job_id": 123,
  "results_count": 12,
  "timing_ms": 314.2
}
```

---

## Configuration

Environment variables (also in `.env`):

| Variable | Default | Description |
|----------|---------|-------------|
| `LOG_LEVEL` | `info` | Logging verbosity |
| `CUDA_VISIBLE_DEVICES` | `-1` | GPU device id (`-1` = CPU only) |
| `API_HOST` | `0.0.0.0` | FastAPI bind host |
| `API_PORT` | `9070` | FastAPI port (internal) |
| `METRICS_PORT` | `9073` | Prometheus metrics port |
| `TF_SERVING_PORT` | `9072` | Reserved (unused by current detectors) |
| `MODELS_CACHE` | `/app/models_cache` | Joblib cache directory |
| `DB_HOST` | `mysql` | Shared MySQL host |
| `DB_PORT` | `3306` | MySQL port |
| `DB_NAME` | `db_eaves_droid` | Database name (shared with webapp) |
| `DB_USER` | `root` | MySQL user |
| `DB_PASSWORD` | `root_password` | MySQL password |

---

## Database setup

Run the migration against the shared MySQL once:

```bash
docker exec -i shared-mysql mysql -uroot -proot_password db_eaves_droid < migrations/001_ml_jobs_tables.sql
```

This creates `ml_jobs`, `ml_results`, and `ml_analysis_tracking`.

---

## Deployment

### Standalone docker-compose (this repo)

```bash
cp .env.example .env    # adjust credentials/ports
docker compose up --build -d
```

- FastAPI: `http://localhost:9071` (mapped from internal `9070`)
- Health: `http://localhost:9071/api/health`

### With the full platform

The ML service is defined in the central `docker-compose.yml` alongside the webapp and MySQL. From the parent directory containing all repos:

```bash
docker compose up --build -d ml-eaves-droid eaves-droid mysql
```

The webapp is configured (`.env` → `PYTHON_BACKEND_HOST=ml-eaves-droid`, `PYTHON_BACKEND_PORT=9070`) to reach the backend by its Docker network name.

---

## Development

```bash
pip install -r requirements.txt
cp .env.example .env
uvicorn app.main:app --reload --port 9070
```

### Adding a detector

1. Create `app/detectors/<name>.py` implementing `BaseDetector` (`detect()` returns a list of `AnomalyResult`).
2. Register it in `app/models/registry.py` (metadata shown by `/api/models`).
3. Import it in `app/routers/analyze.py::load_detectors()`.
4. Add it to `app/routers/health.py::_DETECTOR_MODULES` and the webapp's `Mod_Anomalies::getAlgorithmCategories()` / `getAlgorithmTiers()`.

1. Create `app/detectors/<name>.py` implementing `BaseDetector` (`detect()` returns a list of `AnomalyResult`).
2. Register it in `app/models/registry.py` (metadata shown by `/api/models`).
3. Import it in `app/routers/analyze.py::load_detectors()`.
4. Add it to `app/routers/health.py::_DETECTOR_MODULES` and the webapp's `Mod_Anomalies::getAlgorithmCategories()` / `getAlgorithmTiers()`.

---

## Project structure

```
├── app/
│   ├── __init__.py
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

## License

MIT — see [LICENSE](LICENSE).

---

## See also

- [Eaves Droid App](https://github.com/Niccher/Eaves-Droid-App) — Android client
- [Eaves Droid WebApp](https://github.com/Niccher/Eaves-Droid-WebApp) — PHP/CodeIgniter 4 backend + AdminLTE dashboard
