# Eaves Droid WebApp — Engineering Documentation

Welcome to the engineering documentation for the **Eaves Droid WebApp** backend. This repository hosts the PHP/CodeIgniter 4 web application, administrative portals, subscription gating, forensic loot ingestion, and analytics coordination.

---

## Navigation

| I want to… | Go here |
|------------|---------|
| Run the system without coding | [User README](../README.md) |
| Detailed user setup and run guide | [docs/user/setup-and-run.md](user/setup-and-run.md) |
| Environment configuration reference | [docs/user/configuration.md](user/configuration.md) |
| User and operator troubleshooting | [docs/user/troubleshooting.md](user/troubleshooting.md) |
| Understand system architecture & containers | [docs/architecture/overview.md](architecture/overview.md) |
| Understand service communication & sequences | [docs/architecture/communication.md](architecture/communication.md) |
| Inspect database topology & data retention | [docs/architecture/data-and-storage.md](architecture/data-and-storage.md) |
| Review deployment & production setups | [docs/architecture/deployment.md](architecture/deployment.md) |
| Work on CodeIgniter 4 (controllers, parsers, PHP-ML) | [docs/services/codeigniter.md](services/codeigniter.md) |
| Set up a native local development environment | [docs/engineering/local-development.md](engineering/local-development.md) |
| Safely make changes and add features | [docs/engineering/making-changes.md](engineering/making-changes.md) |
| Run migrations and seed data | [docs/engineering/database.md](engineering/database.md) |
| Access control, Shield RBAC & Pesapal security | [docs/engineering/security.md](engineering/security.md) |
| Run test suites (PHPUnit) | [docs/engineering/testing.md](engineering/testing.md) |

---

## Ecosystem Repositories

* **[Eaves Droid App (Android Client)](https://github.com/Niccher/Eaves-Droid-App)**: Native Android client capturing 60+ forensic categories and encrypting with AES-GCM before upload.
* **[ML Eaves Droid (Python Engine)](https://github.com/Niccher/Eaves-Droid-ML)**: FastAPI service executing CPU-only anomaly detection against shared MySQL.
