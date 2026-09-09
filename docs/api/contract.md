# API Contract Reference

Comprehensive specification of HTTP payloads for ML Eaves Droid.

---

## 1. POST `/api/v1/analysis-jobs`

Dispatches an anomaly detection job across specified detectors.

### Request Body (JSON)

```json
{
  "job_id": 123,
  "user_id": 7,
  "algorithms": [
    "calls_isolation",
    "sms_bert",
    "apps_autoencoder"
  ],
  "scope": "full",
  "incremental_since": null,
  "params": {}
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `job_id` | integer | Yes | Pre-allocated row ID from `ml_jobs` in MySQL |
| `user_id` | integer | Yes | ID of the device owner whose forensic logs are analyzed |
| `algorithms` | list[string] | Yes | List of algorithm IDs to execute |
| `scope` | string | No | Either `"full"` (default) or `"incremental"` |
| `incremental_since`| string | No | ISO datetime string if `scope="incremental"` |
| `params` | object | No | Optional detector parameter overrides |

### Successful Response (HTTP 200)

```json
{
  "status": "completed",
  "job_id": 123,
  "results_count": 14,
  "timing_ms": 286.4,
  "errors": {}
}
```

### Partial Failure Response (HTTP 200 with warnings)

If an individual detector fails due to insufficient data or malformed rows:
```json
{
  "status": "completed",
  "job_id": 123,
  "results_count": 8,
  "timing_ms": 194.1,
  "errors": {
    "act_lstm": "Insufficient rows in tbl_app_usage for user 7 (minimum required: 10)"
  }
}
```

---

## 2. GET `/api/health`

### Response (HTTP 200)
```json
{
  "status": "ok",
  "version": "2.8.0",
  "database": "connected",
  "detectors_loaded": 13,
  "cache_entries": 42
}
```
