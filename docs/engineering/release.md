# Engineering: Release Train & Ecosystem Compatibility

This document outlines the version matrix, API deprecation policy, and release cadence across the Eaves Droid polyrepo ecosystem.

---

## 1. Version Compatibility Matrix

| WebApp Version (`Eaves-Droid-WebApp`) | Android Client (`Eaves-Droid-App`) | ML Microservice (`ML-Eaves-Droid`) | Database Migration Epoch | Status |
|---|---|---|---|---|
| **v2.5.0** (Current) | `v2.4.0+` (code `24`) | `v2.5.0+` | Batch 2026-09 | **Active / Production** |
| **v2.0.0** | `v2.0.0` (code `20`) | `v2.0.0` | Batch 2026-06 | Deprecated |
| **v1.0.0** | `v1.0.0` (code `10`) | *(PHP-ML only)* | Batch 2026-01 | Unsupported |

---

## 2. Cross-Stack Release Protocols

When making changes that impact cross-repo contracts:
1. **Additive Schema Updates:** New fields added to `POST /api/v1/files/upload` must be optional in the backend until all mobile clients update.
2. **ML Payload Evolution:** When introducing new detector algorithms in `ML Eaves Droid`, register detector metadata in `AnomaliesModel` before deploying the microservice update.
3. **Database Migrations:** Migrations must never drop deprecated columns until the minimum supported Android client version is incremented.
