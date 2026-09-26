# Architecture: Inter-Service Communication

This document outlines the protocols, authentication mechanisms, sequence flows, and emulator routing connecting the Eaves Droid Platform components.

---

## 1. Communication Topology

| From | To | Protocol | Auth | Monorepo Address | Notes |
|------|----|----------|------|-------------------|-------|
| **Android Client** | WebApp Ingest | HTTPS / JSON | Token Header + AES-256 Body | `http://10.0.2.2:9007` | Emulator loopback or LAN routing |
| **Web Browser** | Web Dashboard | HTTPS / HTML | CodeIgniter Shield Session | `http://localhost:9007` | CSRF & Session protected |
| **WebApp (Heartbeat)** | ML Engine | HTTP / JSON | `X-Internal-Token` Header | `http://ml:9070` | Internal Docker DNS healthcheck |
| **WebApp (Jobs)** | ML Engine | HTTP / JSON | `X-Internal-Token` Header | `http://ml:9070` | Anomaly job execution dispatch |
| **ML Engine** | MySQL | TCP / MySQL | DB Credentials | `mysql:3306` | Direct query pool (SQLAlchemy) |
| **WebApp / ML** | Redis | TCP / RESP | None / Auth | `redis:6379` | Worker queues & response caching |
| **Pesapal v3** | WebApp | HTTPS POST | Server-to-Server IPN | `/billing/pesapal/ipn` | Payment webhook verification |

---

## 2. Sequence Diagrams

### Sequence 1: Android Forensic Data Ingestion

```mermaid
sequenceDiagram
  autonumber
  participant App as Android Client (Kotlin)
  participant Ingest as WebApp ReceiveController
  participant Parser as Mod_Parse_Loot Engine
  participant DB as MySQL (db_eaves_droid)
  participant Redis as Redis Queue

  App->>Ingest: POST /api/v1/files/upload (Token, Device Info, AES-256 Payload)
  Ingest->>DB: Validate Token & Device Checksum
  Ingest->>Ingest: Decrypt Payload in-memory using AES Key
  Ingest->>Parser: Route records by category (sms, call_logs, location, etc.)
  Parser->>DB: Batch insert normalized telemetry
  Ingest->>Redis: Enqueue upload post-processing task
  Ingest-->>App: HTTP 200 {status: "success", batch_id: N}
```

### Sequence 2: ML Anomaly Job Dispatch with Self-Healing Failover

```mermaid
sequenceDiagram
  autonumber
  participant User as Web Dashboard
  participant Web as Mod_Anomalies (WebApp)
  participant DB as MySQL Database
  participant ML as FastAPI Engine (ml:9070)
  participant PHPML as PHP-ML Fallback Engine

  User->>Web: Request Anomaly Analysis
  Web->>DB: Insert row into ml_jobs (status: "pending")
  alt Python ML Backend Online (Docker DNS: http://ml:9070)
    Web->>ML: POST /api/v1/analysis-jobs (X-Internal-Token)
    ML->>DB: Read raw logs (tbl_extracted_sms, tbl_extracted_call_logs, etc.)
    ML->>ML: Run 13+ CPU models (Isolation Forest, Autoencoders, BERT, Graph)
    ML->>DB: Insert findings into ml_results & update ml_jobs (completed)
    ML-->>Web: HTTP 200 {status: "completed", results_count: M}
  else Python Backend Offline (Self-Healing Failover)
    Web->>PHPML: Execute synchronous PHP-ML statistical detectors
    PHPML->>DB: Query tenant logs & fit K-Means / DBSCAN / IF
    PHPML->>DB: Insert findings into ml_results & update ml_jobs (completed)
  end
  Web-->>User: Render anomaly findings & interactive visualizations
```

### Sequence 3: Automated Container Startup, Migrations & Seeding

```mermaid
sequenceDiagram
  autonumber
  participant Docker as Docker Compose / Deploy
  participant Entry as web/entrypoint.sh
  participant MySQL as MySQL 8.4 Container
  participant Spark as CodeIgniter Spark CLI
  participant ML as FastAPI Container

  Docker->>MySQL: Boot MySQL 8.4 container
  Docker->>Entry: Boot eaves-web container
  Entry->>MySQL: Wait for TCP ping at mysql:3306
  MySQL-->>Entry: MySQL Healthy & Accepting Connections
  Entry->>Spark: php spark migrate --all
  Spark->>MySQL: Execute 147 migrations (schema, forensic tables, ml_jobs, ml_results)
  Entry->>Spark: php spark db:seed DatabaseSeeder
  Spark->>MySQL: Seed plans, superadmin, & default ML settings (ml_python_url: http://ml:9070)
  Entry->>Entry: Start PHP-FPM & Apache foreground
  Docker->>ML: Boot eaves-ml container
  ML->>MySQL: Connect to db_eaves_droid (tables already fully prepared)
```

---

## 3. Mobile Device Networking & Routing

```mermaid
flowchart TB
  subgraph DockerNetwork["Internal Docker Platform Network (eaves-network)"]
    W["WebApp Container (:80)"]
    M["ML Engine Container (:9070)"]
    DB[("MySQL (:3306)")]
    R[("Redis (:6379)")]
    W <--> M
    W <--> DB
    M <--> DB
    W <--> R
  end

  subgraph MobileClients["Android Client Devices"]
    E["Android Emulator (AVD)"]
    P["Physical Android Device (USB)"]
    WLAN["Physical Device (Wi-Fi LAN)"]
  end

  E -->|"http://10.0.2.2:9007"| W
  P -->|"adb reverse tcp:9007 tcp:9007"| W
  WLAN -->|"http://192.168.x.x:9007"| W
```
