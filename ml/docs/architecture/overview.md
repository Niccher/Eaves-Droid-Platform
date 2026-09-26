# Architecture: ML Engine Overview

ML Eaves Droid is a CPU-optimized Python service providing deep offline anomaly detection algorithms for forensic device telemetry.

---

## 1. System Topology & Shared Database

Unlike traditional microservices that exchange large serialized HTTP JSON bodies of raw forensic data, ML Eaves Droid uses a **direct database integration pattern**:

```mermaid
flowchart LR
  subgraph Web["Web Frontend"]
    CI["CodeIgniter 4 (Mod_Anomalies)"]
  end

  subgraph Engine["ML Engine (FastAPI)"]
    API["FastAPI (:9070)"]
    Reg["Algorithm Registry"]
    Cache["Joblib Result Cache"]
    D["13 Detectors (CPU-Only)"]
  end

  subgraph Database["Shared Datastore"]
    DB[("MySQL 8.4 (db_eaves_droid)")]
    Raw[("Raw Tables: tbl_sms, tbl_logs, tbl_apps...")]
    Jobs[("Job Coordination: ml_jobs, ml_results")]
  end

  CI -->|"POST /api/v1/analysis-jobs {job_id, user_id, algorithms}"| API
  API --> Reg
  Reg --> Cache
  Reg --> D
  D -->|"Direct SQL SELECT"| Raw
  D -->|"Direct SQL INSERT findings"| Jobs
  API -->>|"Lightweight Ack {status, count, timing_ms}"| CI
```

---

## 2. Key Architectural Tenets

1. **Zero Raw-Data Transfer over HTTP:**
   The WebApp sends only a compact job pointer (`{job_id: 123, user_id: 7, algorithms: [...]}`). The detectors query MySQL directly, avoiding memory overhead and JSON serialization latency.
2. **Deterministic CPU Execution:**
   All algorithms are implemented using `scikit-learn` (Isolation Forest, One-Class SVM, PCA), `NetworkX` (community graphs), and vectorized heuristics. No GPU, CUDA runtime, PyTorch, or TensorFlow libraries are required.
3. **Resilient Per-Algorithm Fault Isolation:**
   Detectors execute within individual try-except blocks. If one detector encounters irregular data formatting, it reports an algorithm-specific error without aborting remaining detectors or failing the entire job.
4. **Intelligent Caching:**
   Results are cached via Joblib on disk (`models_cache/`). Identical job requests against unchanged data versions return cached findings in sub-millisecond time.
