# Architecture: Deployment and Infrastructure

This document outlines deployment configurations for the Eaves Droid WebApp across standalone Docker Compose, polyrepo shared networks, and cloud platforms.

---

## 1. Docker Compose (Standalone)

The standalone `docker-compose.yml` runs the WebApp alongside a dedicated MySQL 8.4 instance:

```yaml
version: '3.8'

services:
  mysql:
    image: mysql:8.4
    container_name: shared-mysql
    ports:
      - "9306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: db_eaves_droid
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-proot_password"]
      interval: 10s
      timeout: 5s
      retries: 10
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - shared-network

  eaves-droid:
    build:
      context: .
      dockerfile: Dockerfile
    image: eaves-droid-webapp:latest
    container_name: eaves-droid-webapp
    ports:
      - "9007:80"
    volumes:
      - .:/var/www/html
    depends_on:
      mysql:
        condition: service_healthy
    networks:
      - shared-network

volumes:
  mysql-data:

networks:
  shared-network:
    name: hosts-shared-network
```

### Shared Network (`hosts-shared-network`)
To allow the Python ML backend (`ml-eaves-droid`) to query MySQL and communicate with the WebApp, both compose stacks attach to the external bridge network named `hosts-shared-network`.

---

## 2. Cloud Deployment (Railway)

The repository includes a `railway.toml` deployment configuration:

* **Entrypoint:** `entrypoint.sh` runs migrations, checks database connectivity, and boots Apache/PHP-FPM.
* **Healthcheck:** Configured against `/api/health`.
* **Environment Configuration:** Injected through Railway variables matching `.env`.

---

## 3. Reverse Proxy & HTTPS Hardening

When deploying behind Nginx or Traefik:
1. Enable `app.forceGlobalSecureRequests = true` in `.env`.
2. Ensure `X-Forwarded-For` and `X-Forwarded-Proto` headers are preserved.
3. Configure file upload limits in Nginx:
   ```nginx
   client_max_body_size 64M;
   ```
