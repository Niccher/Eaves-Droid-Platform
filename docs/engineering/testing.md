# Engineering: Testing Guide

This guide details how to execute the automated test suites in the Eaves Droid WebApp.

---

## 1. PHPUnit Test Suite

The test suite is built on **PHPUnit** and configured via `phpunit.xml.dist`.

### Running Tests Locally
```bash
# Run the complete test suite
vendor/bin/phpunit

# Run a specific test case
vendor/bin/phpunit tests/app/Controllers/ReceiveTest.php

# Run with testdox output formatting
vendor/bin/phpunit --testdox
```

### Running Tests Inside Docker
```bash
docker compose exec eaves-droid vendor/bin/phpunit
```

---

## 2. Writing Unit & Feature Tests

Tests reside in `tests/app/`:
* Extend `CodeIgniter\Test\CIUnitTestCase` for isolated model and parser unit tests.
* Extend `CodeIgniter\Test\FeatureTestTrait` for testing controllers, HTTP responses, and filters.
