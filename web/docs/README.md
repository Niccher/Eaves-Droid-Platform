# Eaves Droid WebApp — Engineering Documentation

Welcome to the engineering documentation for the **Eaves Droid WebApp** backend. This repository hosts the PHP 8.3/CodeIgniter 4.5 web application, administrative portals, subscription gating, forensic loot ingestion, and analytics coordination with the Python FastAPI microservice.

---

## Documentation Index

| Topic | Description | Link |
|---|---|---|
| **User Quickstart** | High-level overview and run commands | [README.md](../README.md) |
| **User Setup & Run** | Detailed container setup, migrations & seeders | [user/setup-and-run.md](user/setup-and-run.md) |
| **User Configuration** | Complete `.env` and runtime settings reference | [user/configuration.md](user/configuration.md) |
| **User Troubleshooting** | Resolving common startup and networking issues | [user/troubleshooting.md](user/troubleshooting.md) |
| **System Overview** | C4 container architecture and topology | [architecture/overview.md](architecture/overview.md) |
| **Inter-Service Communication** | Protocols, sequences, and emulator networking | [architecture/communication.md](architecture/communication.md) |
| **Data & Storage** | Database schema, models, filesystem & ERD | [architecture/data-and-storage.md](architecture/data-and-storage.md) |
| **Deployment** | Docker Compose, Railway, Nginx & PHP-FPM | [architecture/deployment.md](architecture/deployment.md) |
| **Threat Model** | STRIDE security analysis, IDOR & LAN defenses | [architecture/threat-model.md](architecture/threat-model.md) |
| **API Contract** | REST API endpoints, payloads and response format | [api/contract.md](api/contract.md) |
| **CodeIgniter 4 Service** | Architecture, controllers, models & PHP-ML | [services/codeigniter.md](services/codeigniter.md) |
| **FastAPI Microservice** | Microservice contract, routers & job handling | [services/fastapi.md](services/fastapi.md) |
| **Android Client** | Kotlin architecture, AES encryption & extractors | [services/android.md](services/android.md) |
| **Machine Learning Models** | Python & PHP-ML anomaly detection engines | [services/ml.md](services/ml.md) |
| **Local Development** | Native environment setup without Docker | [engineering/local-development.md](engineering/local-development.md) |
| **Making Changes** | Step-by-step developer tasks & Definition of Done | [engineering/making-changes.md](engineering/making-changes.md) |
| **Database & Migrations** | Schema versioning, seeders & tenant isolation | [engineering/database.md](engineering/database.md) |
| **Testing** | PHPUnit test suites and mocking | [engineering/testing.md](engineering/testing.md) |
| **CI & Automation** | GitHub Actions workflows and quality gates | [engineering/ci.md](engineering/ci.md) |
| **Contributing** | Branching conventions, PRs & commit rules | [engineering/contributing.md](engineering/contributing.md) |
| **Engineering Troubleshooting** | Build errors, Composer extensions & deadlocks | [engineering/troubleshooting.md](engineering/troubleshooting.md) |
| **Security & Access Control** | Shield RBAC, CSRF, tokens & payment safety | [engineering/security.md](engineering/security.md) |
| **Release Train** | Ecosystem compatibility matrix & versioning | [engineering/release.md](engineering/release.md) |
| **Container Restart Runbook** | Clean rebuilds, deadlock recovery & reset | [runbooks/restart.md](runbooks/restart.md) |
| **Backup & Restore Runbook** | Database snapshots & loot archive restoration | [runbooks/backup-and-restore.md](runbooks/backup-and-restore.md) |
| **ADR 0001** | Hybrid QR & Numeric Pairing | [adr/0001-hybrid-qr-numeric-pairing.md](adr/0001-hybrid-qr-numeric-pairing.md) |
| **ADR 0002** | Unified Feed Aggregation | [adr/0002-unified-feed-aggregation.md](adr/0002-unified-feed-aggregation.md) |
| **ADR 0003** | On-Device OCR via ML Kit | [adr/0003-on-device-ocr-via-ml-kit.md](adr/0003-on-device-ocr-via-ml-kit.md) |
| **ADR 0004** | Python FastAPI ML Failover | [adr/0004-python-fastapi-microservice-failover.md](adr/0004-python-fastapi-microservice-failover.md) |

---

## Sibling Ecosystem Repositories

| Component | Responsibility | Repository URL | Documentation |
|---|---|---|---|
| **Web App** | CodeIgniter 4 Web UI, Auth, Billing & Ingestion | [GitHub Repo](https://github.com/Niccher/Eaves-Droid-WebApp) | [docs/](README.md) |
| **ML Microservice** | FastAPI, scikit-learn, PyOD, PyTorch & Deep Autoencoders | [GitHub Repo](https://github.com/Niccher/ML-Eaves-Droid) | [docs/](https://github.com/Niccher/ML-Eaves-Droid/tree/main/docs) |
| **Android Client** | Kotlin Native App, 60+ Forensic Extractors & AES Upload | [GitHub Repo](https://github.com/Niccher/Eaves-Droid-App) | [docs/](https://github.com/Niccher/Eaves-Droid-App/tree/main/docs) |
