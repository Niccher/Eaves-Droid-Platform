# Architecture: Threat Model & Security Hardening

This document provides a STRIDE security threat analysis and outlines defense-in-depth mitigations implemented across the Eaves Droid platform.

---

## 1. STRIDE Threat Analysis Matrix

| Threat Category | Potential Vector | Platform Mitigation |
|---|---|---|
| **Spoofing** | Rogue device forging uploads | API token verification against `tbl_tokens` with cryptographic hash checking. |
| **Tampering** | Man-in-the-Middle payload modification | Client-side AES-GCM encryption on the Android client before HTTP transmission. |
| **Repudiation** | Unauthorized administrative actions | `tbl_security_audit` logs all critical mutations (role change, impersonation, suspension). |
| **Information Disclosure** | Cross-tenant data inspection (IDOR) | Model-level tenant scoping (`where('user_id', $userId)`) on all forensic queries. |
| **Denial of Service** | Unbounded forensic uploads | File upload size limits (100MB), request rate-limiting, and memory-safe stream parsers. |
| **Elevation of Privilege** | Privilege escalation via group manipulation | Strict Shield group gates; users cannot alter their own roles or bypass SuperAdmin checks. |

---

## 2. LAN & Wi-Fi Security Hardening

When deploying within local networks (LAN / Wi-Fi):
1. **Cleartext Traffic Block:** Android Network Security Config restricts cleartext traffic in production flavors.
2. **Ephemeral Device Pairing:** Mobile device pairing uses time-limited numeric pins and QR verification codes expiring after 10 minutes.
3. **Session Hardening:** PHP sessions use `SameSite=Lax`, `HttpOnly`, and secure cookie attributes.
4. **IPN Webhook Verification:** Pesapal IPNs require direct server-to-server TLS callback queries to verify payment legitimacy before activating subscriptions.

---

## 3. SuperAdmin Impersonation Audit Trail

When a SuperAdmin uses the 1-click Impersonate feature:
* An immutable record is created in `tbl_security_audit` with the operator's actual user ID, target account ID, client IP address, and timestamp.
* The session clearly marks `is_impersonating = true`, presenting an persistent exit banner.
* Impersonated sessions cannot demote or modify other SuperAdmin accounts.
