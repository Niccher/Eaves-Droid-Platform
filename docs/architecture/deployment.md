# Architecture: Deployment Guide

This document describes deployment architectures for the **Eaves Droid Platform** (Monorepo) across local Docker Compose, Google Cloud Platform (Compute Engine), Railway PaaS, and dedicated Linux servers (Nginx + PHP-FPM).

---

## 1. Docker Compose (Full Polyglot Stack)

The platform `docker-compose.yml` deploys the WebApp, Python ML microservice, Redis in-memory cache/session store, and MySQL 8.4 database:

```yaml
services:
  web:
    build:
      context: ./web
      dockerfile: Dockerfile
    container_name: eaves-droid-web
    restart: unless-stopped
    ports:
      - "${WEB_PORT:-9007}:80"
    environment:
      CI_ENVIRONMENT: production
      database.default.hostname: mysql
      database.default.database: ${DB_NAME:-db_eaves_droid}
      database.default.username: ${DB_USER:-eaves_user}
      database.default.password: ${DB_PASS:-eaves_secure_password}
      ml_python_url: http://ml:${ML_PORT:-9071}
      REDIS_HOST: redis
      REDIS_PORT: 6379
    volumes:
      - ./web/writable:/var/www/html/writable
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_healthy

  ml:
    build:
      context: ./ml
      dockerfile: Dockerfile
    container_name: eaves-droid-ml
    restart: unless-stopped
    ports:
      - "${ML_PORT:-9071}:9071"
    environment:
      MYSQL_URL: mysql+pymysql://${DB_USER:-eaves_user}:${DB_PASS:-eaves_secure_password}@mysql:3306/${DB_NAME:-db_eaves_droid}
    depends_on:
      mysql:
        condition: service_healthy

  mysql:
    image: mysql:8.4
    container_name: eaves-droid-mysql
    restart: unless-stopped
    ports:
      - "${MYSQL_PORT:-9306}:3306"
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASS:-root_secure_password}
      MYSQL_DATABASE: ${DB_NAME:-db_eaves_droid}
      MYSQL_USER: ${DB_USER:-eaves_user}
      MYSQL_PASSWORD: ${DB_PASS:-eaves_secure_password}
    volumes:
      - mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 5s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    container_name: eaves-droid-redis
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 5s
      timeout: 3s
      retries: 5

volumes:
  mysql_data:
```

---

## 2. One-Click Deployment Pipeline

The platform includes an enterprise automated 13-stage deployment pipeline:

```bash
# Full automated pre-flight, build, migration, seed, and health verification
bash scripts/deploy.sh
```

---

## 3. Cloud Deployments

- **Google Cloud Platform (GCE):** See [GCP Deployment Guide](deployment-gcp.md) for automated instance provisioning with `scripts/gcp_setup.sh`, UFW firewall configuration, and SSL termination.
- **Railway PaaS:** Built via connected GitHub repository with monorepo root settings pointing to `/web` and `/ml` services.
- **Dedicated Linux VM (Nginx + PHP-FPM):** Supported via standard FastCGI proxy to PHP 8.3-FPM with standalone Uvicorn ASGI daemon for FastAPI.

---

## 4. Resilience & Fallback Architecture

For comprehensive documentation on the zero-downtime Dual-Engine Session and Cache architecture (Redis with non-blocking 50ms `@fsockopen` probe transparently failing over to MySQL `ci_sessions`), see:
- [Resilience & Failover Specification](resilience-and-failover.md)
