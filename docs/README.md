# Eaves Droid Platform — Engineering Documentation

Welcome to the engineering documentation for the **Eaves Droid Platform** monorepo. This repository unifies the PHP 8.3/CodeIgniter 4.5 web control plane and forensic ingestion server (`web/`), the Python 3.12/FastAPI anomaly detection engine (`ml/`), and documents integration with the mobile sensory client ([`Niccher/Eaves-Droid-App`](https://github.com/Niccher/Eaves-Droid-App)).

---

## Documentation Index

| Topic | Description | Link |
|---|---|---|
| **Platform Quickstart** | High-level overview and one-command run instructions | [README.md](../README.md) |
| **Android Dependency** | Mobile client sensory architecture, 60+ extractors & AES protocol | [ecosystem/android-dependency.md](ecosystem/android-dependency.md) |
| **Android API Contract** | REST endpoints (`/api/v1/files/upload`, `/api/v1/device/pair`) | [ecosystem/android-api-contract.md](ecosystem/android-api-contract.md) |
| **System Overview** | C4 container architecture and topology | [architecture/overview.md](architecture/overview.md) |
| **Inter-Service Communication** | Protocols, sequence flows, and Docker network DNS | [architecture/communication.md](architecture/communication.md) |
| **Data & Storage** | Database schema, models, filesystem & ERD | [architecture/data-and-storage.md](architecture/data-and-storage.md) |
| **Deployment Topology** | Docker Compose, Railway, Apache & PHP-FPM / Uvicorn | [architecture/deployment.md](architecture/deployment.md) |
| **Threat Model** | STRIDE security analysis, IDOR & mobile upload defenses | [architecture/threat-model.md](architecture/threat-model.md) |
| **CodeIgniter 4 Service** | Web ingestion, controllers, models & PHP-ML failover | [services/codeigniter.md](services/codeigniter.md) |
| **FastAPI Microservice** | Anomaly detection service, routers & job execution | [services/fastapi.md](services/fastapi.md) |
| **Machine Learning Models** | Python & PHP-ML 13+ anomaly detection algorithms | [services/ml.md](services/ml.md) |
| **Android Client Service** | Kotlin native client architecture & build commands | [services/android.md](services/android.md) |
| **Database & Migrations** | Single authoritative CodeIgniter migrations & automated seeders | [engineering/database.md](engineering/database.md) |
| **Local Development** | Native environment setup and testing workflows | [engineering/local-development.md](engineering/local-development.md) |
| **Making Changes** | Developer tasks, Definition of Done, adding new detectors | [engineering/making-changes.md](engineering/making-changes.md) |
| **Testing Suite** | Automated PHPUnit and Python PyTest execution | [engineering/testing.md](engineering/testing.md) |
| **CI & Automation** | GitHub Actions workflows and path filtering | [engineering/ci.md](engineering/ci.md) |
| **Contributing** | Monorepo branching conventions, PRs & commit rules | [engineering/contributing.md](engineering/contributing.md) |
| **Security & Access Control** | Shield RBAC, CSRF, device tokens & payment safety | [engineering/security.md](engineering/security.md) |
| **Release Train** | Platform versioning (`VERSION.json`) & mobile compatibility | [engineering/release.md](engineering/release.md) |
| **Container Restart Runbook** | Clean rebuilds, deadlock recovery & reset | [runbooks/restart.md](runbooks/restart.md) |
| **Backup & Restore Runbook** | Database snapshots & loot archive restoration | [runbooks/backup-and-restore.md](runbooks/backup-and-restore.md) |
| **ADR 0001** | Hybrid QR & Numeric Pairing | [adr/0001-hybrid-qr-numeric-pairing.md](adr/0001-hybrid-qr-numeric-pairing.md) |
| **ADR 0002** | Unified Feed Aggregation | [adr/0002-unified-feed-aggregation.md](adr/0002-unified-feed-aggregation.md) |
| **ADR 0003** | On-Device OCR via ML Kit | [adr/0003-on-device-ocr-via-ml-kit.md](adr/0003-on-device-ocr-via-ml-kit.md) |
| **ADR 0004** | Python FastAPI ML Failover | [adr/0004-python-fastapi-microservice-failover.md](adr/0004-python-fastapi-microservice-failover.md) |

---

## Ecosystem Repositories

| Component | Responsibility | Repository URL | Documentation |
|---|---|---|---|
| **Eaves Droid Platform** | Monorepo: Web Control Plane & ML Anomaly Service | [GitHub Repo](https://github.com/Niccher/Eaves-Droid-Platform) | [docs/](README.md) |
| **Android Client** | Kotlin Native App, 60+ Forensic Extractors & AES Upload | [GitHub Repo](https://github.com/Niccher/Eaves-Droid-App) | [docs/ecosystem/android-dependency.md](ecosystem/android-dependency.md) |
