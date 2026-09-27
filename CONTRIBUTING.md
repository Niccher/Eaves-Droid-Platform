# Contributing to Eaves Droid Platform

Thank you for your interest in contributing to **Eaves Droid Platform**! We welcome bug fixes, documentation improvements, new forensic telemetry extractors, and machine learning anomaly detectors from the open-source community.

---

## 🧭 Code of Conduct

All contributors are expected to uphold our standards of conduct. Please review our [Code of Conduct](CODE_OF_CONDUCT.md) before participating.

---

## 🛠 Getting Started

### 1. Fork & Clone
```bash
git clone https://github.com/Niccher/Eaves-Droid-Platform.git
cd Eaves-Droid-Platform
```

### 2. Branching Model
Create a dedicated feature branch from `main`:
- `feat/sms-transformer-detector` (for new features or ML models)
- `fix/upload-stream-oom` (for bug fixes)
- `docs/gcp-compute-engine-guide` (for documentation updates)
- `perf/redis-socket-probe` (for performance improvements)

```bash
git checkout -b feat/your-feature-name
```

---

## 🧪 Development & Coding Standards

### 1. WebApp (PHP 8.3 / CodeIgniter 4)
- **Style:** Follow **PSR-12** formatting and CodeIgniter 4 conventions.
- **Linting:** Run `php -l` on modified files to ensure zero syntax errors.
- **Testing:** Add PHPUnit tests in `web/tests/`:
  ```bash
  docker compose exec web ./vendor/bin/phpunit
  ```

### 2. ML Inference Backend (Python 3.12 / FastAPI)
- **Style:** Adhere to **PEP 8** and modern type hinting.
- **Linting & Formatting:** Format code via `ruff` or `flake8`:
  ```bash
  ruff check ml/app
  ruff format ml/app
  ```
- **Testing:** Add unit tests in `ml/tests/`:
  ```bash
  docker compose exec ml pytest
  ```

### 3. Docker Compose & Environment
- Validate Docker Compose syntax whenever editing `docker-compose.yml`:
  ```bash
  docker compose config --quiet
  ```

---

## 🚀 One-Click Deployment Pipeline

Before submitting a Pull Request, verify that the automated deployment pipeline passes locally:
```bash
bash scripts/deploy.sh
```

---

## 📥 Submitting a Pull Request (PR)

1. Ensure all code adheres to style guidelines and test suites pass.
2. Commit with concise, descriptive messages using [Conventional Commits](https://www.conventionalcommits.org/):
   - `feat(ml): add SIM swap anomaly detector`
   - `fix(web): resolve Redis fallback session race condition`
   - `docs(api): document Android upload envelope format`
3. Push your branch to GitHub and open a Pull Request against `main`.
4. Provide a clear description of the problem solved, testing steps taken, and any breaking changes.
