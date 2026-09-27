# Security Policy

The **Eaves Droid Platform** team is committed to ensuring the safety, privacy, and cryptographic integrity of mobile telemetry ingestion, forensic data analytics, and machine learning anomaly scoring.

---

## 🔒 Security Commitments & Cryptographic Boundaries

1. **Zero-Knowledge On-Device Encryption:**
   - Raw forensic logs extracted by the Android client are encrypted directly on-device using **AES-256-GCM** before network transmission.
   - Decryption occurs strictly inside the WebApp ingestion pipeline using device-paired keys.
2. **Internal Network Isolation:**
   - Database (MySQL 8.4) and caching (Redis 7) services run on an isolated bridge network (`eaves-platform-network`) without exposed public host ports by default in production.
3. **Dual-Engine High-Availability Resilience:**
   - The platform incorporates a 50ms bounded socket probe that automatically degrades sessions to MySQL during Redis outages, preventing denial-of-service loops and unhandled exceptions.
4. **Inter-Service Token Enforcement:**
   - Communication between CodeIgniter 4 and the Python FastAPI ML microservice enforces shared `X-Internal-Token` authorization headers.

---

## 🛡 Supported Versions

| Version | Supported | Security Patch SLA |
| :--- | :--- | :--- |
| **3.0.x (Current)** | ✅ Yes | Within 24–48 hours |
| **< 3.0.0** | ❌ No | Please upgrade to the unified monorepo v3.0.0+ |

---

## 🚨 Reporting a Vulnerability

If you discover a security vulnerability within this repository, **do not open a public GitHub issue.** Public disclosure puts forensic telemetry and production deployments at risk.

Instead, report vulnerabilities privately by emailing:
📧 **domi777nicch@gmail.com**

Please include:
1. Description of the vulnerability and attack vector.
2. Steps to reproduce or proof-of-concept (PoC) code.
3. Potential impact on WebApp, ML Engine, Android Ingestion, or Database integrity.
4. Any suggested fixes or mitigations.

### Our Commitment:
* **Acknowledgement:** We will acknowledge receipt of your vulnerability report within **24 hours**.
* **Assessment & Fix:** We will assess severity and prepare a patched release within **48–72 hours**.
* **Credit:** We will publicly credit you in the release notes and advisory (unless you prefer anonymity).
