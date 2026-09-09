# Architecture: Communication Protocol

This document details how the CodeIgniter 4 WebApp and the ML Eaves Droid service communicate.

---

## 1. Inter-Service Endpoints

| Method | Path | Source | Destination | Description |
|--------|------|--------|-------------|-------------|
| GET | `/` | User / Proxy | ML Engine | Landing page & service identification |
| GET | `/api/health` | WebApp / Healthchecker | ML Engine | Reports engine readiness, loaded models, DB connectivity |
| GET | `/api/models` | WebApp Admin | ML Engine | Returns list of registered algorithms and default hyperparameters |
| POST | `/api/v1/analysis-jobs` | WebApp `Mod_Anomalies` | ML Engine | Dispatches analysis job execution |
| GET | `/metrics` | Prometheus | ML Engine | Prometheus metrics exporter |

---

## 2. Analysis Job Lifecycle

```mermaid
sequenceDiagram
  autonumber
  participant Web as WebApp (PHP)
  participant DB as MySQL Database
  participant ML as ML FastAPI Engine

  Web->>DB: INSERT INTO ml_jobs (id, user_id, status='pending')
  Web->>ML: POST /api/v1/analysis-jobs {job_id: N, user_id: U, algorithms: [...]}
  ML->>DB: UPDATE ml_jobs SET status='running'
  loop For each requested detector
    ML->>DB: SELECT data FROM target_table WHERE user_id=U
    ML->>ML: Compute anomalies (Isolation Forest / PCA / Graph)
    ML->>DB: INSERT INTO ml_results (job_id, algorithm_id, score, details)
  end
  ML->>DB: UPDATE ml_jobs SET status='completed'
  ML-->>Web: HTTP 200 {status: 'completed', results_count: R, timing_ms: T}
```
