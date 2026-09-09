# Architecture: Inter-Service Communication

This document outlines the protocols, authentication mechanisms, sequence flows, and emulator routing connecting the Eaves Droid ecosystem components.

---

## 1. Communication Topology

| From | To | Protocol | Auth | Dev Address | Notes |
|------|----|----------|------|-------------|-------|
| Android Client | WebApp | HTTPS / JSON | Token Header + AES Body | `http://10.0.2.2:9007` | Android emulator loopback routing |
| Web Browser | WebApp | HTTPS / HTML | Shield Session Cookie | `http://localhost:9007` | CSRF protected session |
| WebApp (Heartbeat) | ML Backend | HTTP / JSON | `X-Internal-Token` Header | `http://ml-eaves-droid:9070` | Microservice container health check |
| WebApp (Jobs) | ML Backend | HTTP / JSON | `X-Internal-Token` Header | `http://ml-eaves-droid:9070` | Analysis dispatch endpoint |
| ML Backend | MySQL | TCP / MySQL | DB Credentials | `mysql:3306` | SQLAlchemy shared connection pool |
| Pesapal | WebApp | HTTPS POST | Server-to-Server IPN | `/billing/pesapal/ipn` | Payment verification webhook |

---

## 2. Sequence Diagrams

### Sequence 1: Forensic Data Ingestion

```mermaid
sequenceDiagram
  autonumber
  participant App as Android Client
  participant Recv as WebApp Receive Controller
  participant Parser as Mod_Parse_Loot / Advanced
  participant DB as MySQL Database

  App->>Recv: POST /api/v1/files/upload (Token, Device Info, AES Payload)
  Recv->>DB: Validate Token & Device Checksum
  Recv->>Recv: Decrypt Payload using AES Key
  Recv->>Parser: Route by category (sms, call_logs, location, apps, files)
  Parser->>DB: Insert batch records
  Recv-->>App: HTTP 200 {status: "success", count: N}
```

### Sequence 2: ML Anomaly Job Dispatch with Self-Healing Failover

```mermaid
sequenceDiagram
  autonumber
  participant User as Web Dashboard
  participant Web as Mod_Anomalies (WebApp)
  participant DB as MySQL Database
  participant ML as ML Microservice (FastAPI)
  participant PHPML as PHP-ML Fallback Engine

  User->>Web: Request Anomaly Analysis (Job)
  Web->>DB: Insert row into ml_jobs (status: "pending")
  alt Python ML Backend Online
    Web->>ML: POST /api/v1/analysis-jobs (X-Internal-Token)
    ML->>DB: Read raw logs (tbl_sms, tbl_logs, tbl_location)
    ML->>ML: Run Isolation Forest / OCSVM / LOF / Autoencoder
    ML->>DB: Insert findings into ml_results & update ml_jobs (completed)
    ML-->>Web: HTTP 200 {status: "completed", results_count: M}
  else Python Backend Offline (Self-Healing Failover)
    Web->>PHPML: Execute synchronous PHP-ML statistical detectors
    PHPML->>DB: Query tenant logs & fit K-Means / DBSCAN / IF
    PHPML->>DB: Insert findings into ml_results & update ml_jobs (completed)
  end
  Web-->>User: Render anomaly findings & interactive visualizations
```

### Sequence 3: Pesapal Subscription IPN Verification

```mermaid
sequenceDiagram
  autonumber
  participant Pesa as Pesapal v3
  participant IPN as WebApp /billing/pesapal/ipn
  participant DB as MySQL Database
  participant Mail as Notification Engine

  Pesa->>IPN: POST IPN Webhook (OrderTrackingId)
  IPN->>Pesa: GET TransactionStatus (Direct Server-to-Server)
  Pesa-->>IPN: Verified Status (status_code: 1 = Completed)
  IPN->>DB: Update tbl_subscriptions & tbl_ipn_logs
  IPN->>Mail: Dispatch subscription_upgraded confirmation email
  IPN-->>Pesa: HTTP 200 Ack
```

---

## 3. Emulator & Device Networking

```mermaid
flowchart TB
  subgraph LocalHost["Host Machine (:9007 / :9070)"]
    W["WebApp (:9007)"]
    M["ML Backend (:9070)"]
  end

  subgraph MobileClients["Android Clients"]
    E["Android Emulator"]
    P["Physical Android Device"]
  end

  E -->|"http://10.0.2.2:9007"| W
  P -->|"http://192.168.x.x:9007 (LAN)"| W
  P -.->|"adb reverse tcp:9007 tcp:9007"| W
```
