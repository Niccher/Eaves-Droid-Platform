# Architecture: Inter-Service Communication

This document outlines the protocols, authentication mechanisms, and sequence diagrams connecting the Eaves Droid ecosystem components.

---

## 1. Communication Topology

| From | To | Protocol | Auth | Dev Address | Notes |
|------|----|----------|------|-------------|-------|
| Android Client | WebApp | HTTPS / JSON | Token Header + AES Body | `http://10.0.2.2:9007` | Emulator routing |
| Web Browser | WebApp | HTTPS / HTML | Shield Session Cookie | `http://localhost:9007` | CSRF protected |
| WebApp | ML Backend | HTTP / JSON | Internal Network | `http://ml-eaves-droid:9070` | Docker internal DNS |
| ML Backend | MySQL | TCP / MySQL | DB Credentials | `mysql:3306` | Shared database |
| Pesapal | WebApp | HTTPS POST | Server-to-Server IPN | `/billing/pesapal/ipn` | PCI-DSS Webhook |

---

## 2. Sequence Diagrams

### Sequence 1: Forensic Data Upload

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
  Recv->>Parser: Route by category (sms, call_logs, location, etc.)
  Parser->>DB: Insert batch records
  Recv-->>App: HTTP 200 {status: "success", count: N}
```

### Sequence 2: ML Anomaly Job Dispatch

```mermaid
sequenceDiagram
  autonumber
  participant User as Web Dashboard
  participant Web as Mod_Anomalies (WebApp)
  participant DB as MySQL Database
  participant ML as ML Eaves Droid (FastAPI)

  User->>Web: Request Anomaly Analysis (Job)
  Web->>DB: Insert row into ml_jobs (status: "pending")
  Web->>ML: POST /api/v1/analysis-jobs {job_id, user_id, algorithms, scope}
  ML->>DB: Update ml_jobs (status: "running")
  ML->>DB: Query raw data tables (tbl_sms, tbl_logs, etc.)
  ML->>ML: Run detectors & model cache check
  ML->>DB: Insert findings into ml_results
  ML->>DB: Update ml_jobs (status: "completed")
  ML-->>Web: HTTP 200 {status: "completed", results_count: M}
  Web-->>User: Render anomaly charts & alerts
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
