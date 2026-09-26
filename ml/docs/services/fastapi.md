# Service Guide: FastAPI Application

This guide covers the internal topology, routing modules, and configuration structures of the FastAPI backend.

---

## 1. Codebase Structure

```
app/
├── main.py             # FastAPI entrypoint, lifespan startup/shutdown, CORS middleware
├── config.py           # Pydantic Settings reading environment variables
├── detectors/          # Algorithm implementations
│   ├── base.py         # BaseDetector abstract base class
│   └── shared/         # Feature engineering & shared math utilities
├── models/
│   ├── cache.py        # Joblib disk serialization manager
│   ├── registry.py     # Central ALGORITHM_REGISTRY metadata dictionary
│   └── schemas.py      # Pydantic request and response schemas
├── routers/
│   ├── analyze.py      # /api/v1/analysis-jobs execution orchestrator
│   ├── health.py       # /api/health connectivity probes
│   └── models_info.py  # /api/models listing registered algorithms
├── services/
│   ├── persistence.py  # SQL result bulk persistence helpers
│   └── cache_manager.py# Joblib cache hit/miss evaluation
└── utils/
    └── db.py           # SQLAlchemy engine & session generators
```

---

## 2. Lifespan and Startup

In `app/main.py`:
* Initializes database connection pool.
* Pre-loads the 13 detector instances into memory.
* Verifies or creates the `models_cache/` directory.
* Registers prometheus instrumentation if `METRICS_PORT` is enabled.
