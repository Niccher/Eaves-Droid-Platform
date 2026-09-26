# API Contract Reference

This document defines the REST API endpoints provided by Eaves Droid WebApp for native mobile clients, background collectors, and web controllers.

---

## 1. Authentication & Headers

| Header | Description | Required | Example |
|---|---|---|---|
| `X-Auth-Token` | Device ingestion authentication token | Ingestion endpoints | `eav_tok_94a7bc81f0` |
| `X-Device-Id` | Hardware UUID of client device | Ingestion endpoints | `android-9876543210` |
| `X-Internal-Token` | Security token for inter-service ML calls | ML / Internal | `eaves_internal_sec_token` |
| `Content-Type` | Payload serialization format | POST requests | `application/json` or `multipart/form-data` |

---

## 2. Endpoints

### 1. Forensic File Upload
* **Route:** `POST /api/v1/files/upload`
* **Auth:** Required (`X-Auth-Token`)
* **Content-Type:** `multipart/form-data`
* **Parameters:**
  * `token`: API Token string
  * `device_info`: Encrypted JSON metadata
  * `loot_file`: Binary encrypted file or archive
* **Success Response (HTTP 200):**
  ```json
  {
    "status": "success",
    "message": "Payload decrypted and parsed successfully",
    "records_inserted": 42
  }
  ```

### 2. Service Health Check
* **Route:** `GET /api/health`
* **Auth:** None
* **Success Response (HTTP 200):**
  ```json
  {
    "status": "healthy",
    "database": "connected",
    "timestamp": "2026-09-09T21:30:00Z"
  }
  ```

### 3. ML Live Telemetry Heartbeat
* **Route:** `GET /admin/ml/heartbeat`
* **Auth:** Admin / SuperAdmin Session
* **Success Response (HTTP 200):**
  ```json
  {
    "online": true,
    "version": "2.5.0",
    "latency_ms": 42,
    "memory": {"used": 195, "total": 1024},
    "cpu_percent": 3.4,
    "models_count": 7,
    "database_status": "connected",
    "database_tables_verified": 10,
    "database_total_tables": 10
  }
  ```

### 4. Pesapal Payment IPN Webhook
* **Route:** `POST /billing/pesapal/ipn`
* **Auth:** Server-to-Server IPN Verification
* **Parameters:** `OrderTrackingId`, `OrderMerchantReference`
* **Success Response (HTTP 200):**
  ```json
  {
    "status": "success",
    "message": "IPN processed and subscription activated"
  }
  ```
