# API Specification: Android Client Contract

This document defines the REST API endpoints consumed by the native Android client application (`Niccher/Eaves-Droid-App`) when communicating with the CodeIgniter WebApp ingestion layer.

---

## 1. Authentication & Security Headers

All requests made by the Android client must supply the following headers:

| Header | Format | Description |
|---|---|---|
| `Authorization` | `Bearer <mobile_api_token>` | 64-character hex device token generated during pairing |
| `X-Device-Checksum` | `<sha256_hash>` | Hardware fingerprint checksum of the target device |
| `Content-Type` | `application/json` or `multipart/form-data` | Depending on upload payload type |

---

## 2. Ingestion Endpoints

### `POST /api/v1/files/upload`
Primary ingestion receiver for encrypted telemetry packets and binary evidence.

#### Request Body (JSON Encrypted Packet)
```json
{
  "device_id": "8f39a1c2-d3e4-4a5b-9c8d-1e2f3a4b5c6d",
  "device_checksum": "9f8e7d6c5b4a3...",
  "category": "sms",
  "encrypted_payload": "a1b2c3d4e5f6...",
  "iv": "7g8h9i0j...",
  "tag": "k1l2m3n4...",
  "records_count": 25,
  "client_timestamp": 1788300000000
}
```

#### Success Response (`HTTP 200 OK`)
```json
{
  "status": "success",
  "message": "Telemetry received and queued for ingestion",
  "batch_id": 4092,
  "processed_count": 25
}
```

#### Error Response (`HTTP 401 Unauthorized`)
```json
{
  "status": "error",
  "message": "Invalid device API token or device pairing revoked"
}
```

---

### `POST /api/v1/device/pair`
Initial pairing handshake between mobile device and user dashboard.

#### Request Body
```json
{
  "pairing_code": "489210",
  "device_model": "Pixel 8 Pro",
  "android_version": "14.0",
  "app_version": "2.8.1",
  "hardware_id": "f8a91b2c3d4e5f"
}
```

#### Success Response (`HTTP 200 OK`)
```json
{
  "status": "paired",
  "device_id": "8f39a1c2-d3e4-4a5b-9c8d-1e2f3a4b5c6d",
  "api_token": "a1b2c3d4e5f6g7h8...",
  "aes_key": "k9j8h7g6f5e4d3c2...",
  "sync_interval_seconds": 900
}
```
