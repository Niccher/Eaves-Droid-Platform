# User Configuration Reference

This document catalogs the environment variables configured in `.env` (copied from `.env.example`).

---

## Service & Port Configuration

| Variable | Default | Purpose |
|----------|---------|---------|
| `API_HOST` | `0.0.0.0` | Bind interface for the FastAPI server |
| `API_PORT` | `9070` | Internal container listen port for HTTP API |
| `METRICS_PORT` | `9073` | Internal container port for Prometheus `/metrics` |
| `LOG_LEVEL` | `info` | Logging verbosity (`debug`, `info`, `warning`, `error`) |
| `CUDA_VISIBLE_DEVICES` | `-1` | Hardware acceleration setting (`-1` enforces CPU-only execution) |
| `MODELS_CACHE` | `/app/models_cache` | Filesystem path for Joblib serialized anomaly results |

---

## Database Connectivity (Shared MySQL)

| Variable | Default | Purpose |
|----------|---------|---------|
| `DB_HOST` | `mysql` | MySQL hostname (use `127.0.0.1` if running outside Docker) |
| `DB_PORT` | `3306` | MySQL port |
| `DB_NAME` | `db_eaves_droid` | Target database name shared with WebApp |
| `DB_USER` | `root` | Database username |
| `DB_PASSWORD` | `root_password` | Database user password |
