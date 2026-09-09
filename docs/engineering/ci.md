# Engineering: CI & Continuous Delivery

This guide details automated continuous integration pipelines, quality linters, and deployment automations for Eaves Droid WebApp.

---

## 1. Automated GitHub Actions Workflows

* **Documentation Quality Gate (`.github/workflows/docs-verify.yml`):**
  Executes `scripts/lint-docs.py` to enforce README length limits, eliminate broken links, validate Mermaid diagrams, and prevent secret leakage on every push and pull request.
* **PHP Static Analysis & Syntax Verification:**
  Validates PHP syntax across controllers, models, and views with `php -l` and runs automated test suites with PHPUnit.

---

## 2. Railway Auto-Deployment Lifecycle

When code is pushed to the `main` branch of `Niccher/Eaves-Droid-WebApp`:
1. Railway detects commit and clones repository.
2. Builds multi-stage Docker image using `Dockerfile`.
3. Runs database migrations via entrypoint script.
4. Performs health check against `GET /api/health`.
5. Directs traffic to the new container instance with zero-downtime rolling restart.

---

## 3. Pre-Commit Checklist

Before opening a PR or pushing to `main`:
```bash
# 1. Run documentation linter
python3 scripts/lint-docs.py .

# 2. Run PHP syntax validation
find app -name "*.php" -exec php -l {} \;

# 3. Run PHPUnit test suite
vendor/bin/phpunit
```
