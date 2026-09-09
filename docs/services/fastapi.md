# Service Guide: FastAPI ML Microservice

This guide details the Python FastAPI microservice (`Niccher/ML-Eaves-Droid`) that powers deep anomaly detection, graph analytics, and neural models for the Eaves Droid ecosystem.

---

## 1. Responsibilities & Architecture

* **Repository:** [Niccher/ML-Eaves-Droid](https://github.com/Niccher/ML-Eaves-Droid)
* **Framework:** Python 3.12, FastAPI, SQLAlchemy 2.0, scikit-learn, PyOD, NetworkX, PyTorch.
* **Port:** `9070` (internal Docker / host).
* **Database Access:** Direct shared MySQL connection (`MYSQL_URL` / `DATABASE_URL`) with automatic connection pooling (`pool_pre_ping=True`).

---

## 2. Key Routers & Endpoints

| Route | Method | Purpose | Auth |
|---|---|---|---|
| `/health` | `GET` | Process-level CPU, RAM, active models & MySQL core table verification | `X-Internal-Token` / None |
| `/api/v1/analysis-jobs` | `POST` | Dispatches multi-detector anomaly scan across forensic tables | `X-Internal-Token` / Bearer |
| `/api/v1/jobs/{job_id}` | `GET` | Queries progress and status of a running analysis job | `X-Internal-Token` / Bearer |
| `/api/v1/detectors` | `GET` | Lists all 15 registered detector algorithms and capabilities | `X-Internal-Token` / Bearer |

---

## 3. Local Development & Start Command

```bash
cd "/home/niccher/Music/hosts/ML Eaves Droid"

# Create virtual environment and install dependencies
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt

# Run FastAPI development server with hot reload
uvicorn app.main:app --host 0.0.0.0 --port 9070 --reload
```
