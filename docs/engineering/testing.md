# Engineering: Testing Guide

Instructions for running automated unit and integration tests for ML Eaves Droid.

---

## 1. Running Tests with Pytest

The test suite runs with **pytest**:

```bash
# Run all tests
pytest

# Run with verbose output and coverage
pytest -v --cov=app tests/

# Run a specific detector test
pytest tests/test_detectors.py -k "test_calls_isolation"
```

---

## 2. Mocking Database Sessions

Unit tests should use in-memory SQLite fixtures or SQLAlchemy mock sessions to prevent writing temporary records into the development MySQL database.
