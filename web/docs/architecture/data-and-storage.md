# Architecture: Data & Storage Topology

This document details the database schema, relational models, forensic storage directory layout, and data retention policies for Eaves Droid WebApp.

---

## 1. Database Schema & Entity Relationship Diagram

```mermaid
erDiagram
  USERS ||--o{ USER_GROUPS : has
  USERS ||--o{ TOKENS : owns
  USERS ||--o{ SUBSCRIPTIONS : billed
  USERS ||--o{ ML_JOBS : runs
  USERS ||--o{ AUDIT_LOGS : generates

  TOKENS ||--o{ SMS_RECORDS : ingests
  TOKENS ||--o{ CALL_LOGS : ingests
  TOKENS ||--o{ LOCATIONS : ingests
  TOKENS ||--o{ APP_INVENTORY : ingests
  TOKENS ||--o{ FILE_LISTS : ingests

  ML_JOBS ||--o{ ML_RESULTS : produces

  USERS {
    int id PK
    string username
    string email
    string password_hash
    string status
    datetime created_at
  }

  TOKENS {
    int id PK
    int user_id FK
    string token_hash
    string device_name
    string device_id
    datetime last_used_at
  }

  SUBSCRIPTIONS {
    int id PK
    int user_id FK
    string plan_id
    string status
    datetime expires_at
  }

  SMS_RECORDS {
    int id PK
    int token_id FK
    string address
    string body
    int type
    datetime date
  }

  CALL_LOGS {
    int id PK
    int token_id FK
    string number
    int duration
    int type
    datetime date
  }

  LOCATIONS {
    int id PK
    int token_id FK
    float latitude
    float longitude
    float speed
    datetime timestamp
  }

  ML_JOBS {
    int id PK
    int user_id FK
    string engine
    string status
    json algorithms
    datetime created_at
  }

  ML_RESULTS {
    int id PK
    int job_id FK
    string detector
    float anomaly_score
    json metadata
    datetime detected_at
  }
```

---

## 2. Multi-Tenant Isolation

1. **Tenant Scoping:** All forensic records (`tbl_sms`, `tbl_logs`, `tbl_location`, `tbl_installed_apps`, `tbl_files`) contain foreign keys referencing `token_id` and the user's account ID.
2. **Query Scoping:** CodeIgniter models automatically scope queries with `where('user_id', $userId)` preventing Cross-Tenant IDOR.
3. **Shield Group Protections:** Non-superadmin users are restricted from viewing data belonging to other user accounts.

---

## 3. File Storage & Directory Layout

Forensic binary uploads, chat attachments, and system caches are persisted in the `writable/` directory:

```
writable/
├── cache/                  # CodeIgniter transient query & view cache
├── exports/                # Generated forensic PDF, CSV, and JSON exports
├── logs/                   # System error logs & debug traces
├── loot/                   # Encrypted and raw extracted mobile dumps
│   └── {user_id}/
│       └── {device_id}/
├── session/                # PHP secure file session storage
├── temp/                   # Transient decompression & staging files
└── uploads/                # User avatar uploads & chat attachments
```
