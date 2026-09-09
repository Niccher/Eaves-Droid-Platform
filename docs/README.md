# ML Eaves Droid — Engineering Documentation

Welcome to the engineering documentation for **ML Eaves Droid**. This repository contains the Python/FastAPI anomaly detection service for the Eaves Droid ecosystem, executing 13 CPU-only detector algorithms against the shared MySQL database.

---

## Navigation

| I want to… | Go here |
|------------|---------|
| Run the service without coding | [User README](../README.md) |
| Detailed user setup and run guide | [docs/user/setup-and-run.md](user/setup-and-run.md) |
| Environment configuration reference | [docs/user/configuration.md](user/configuration.md) |
| Operator troubleshooting | [docs/user/troubleshooting.md](user/troubleshooting.md) |
| Understand service architecture & DB model | [docs/architecture/overview.md](architecture/overview.md) |
| Communication protocols with WebApp | [docs/architecture/communication.md](architecture/communication.md) |
| API Request/Response schema contracts | [docs/api/contract.md](api/contract.md) |
| FastAPI application topology | [docs/services/fastapi.md](services/fastapi.md) |
| Machine learning detectors catalog (13 algorithms) | [docs/services/ml.md](services/ml.md) |
| Native local Python development (uvicorn) | [docs/engineering/local-development.md](engineering/local-development.md) |
| **How to add a new detector** | [docs/engineering/making-changes.md](engineering/making-changes.md) |
| Database schema (`ml_jobs`, `ml_results`) | [docs/engineering/database.md](engineering/database.md) |
| Pytest automated test execution | [docs/engineering/testing.md](engineering/testing.md) |

---

## Ecosystem Repositories

* **[Eaves Droid WebApp](https://github.com/Niccher/Eaves-Droid-WebApp)**: CodeIgniter 4 web application and client portal that schedules analysis jobs.
* **[Eaves Droid App](https://github.com/Niccher/Eaves-Droid-App)**: Android mobile client that collects source forensic telemetry.
