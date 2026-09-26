# Architecture: Deployment Guide

This document describes deployment architectures for Eaves Droid WebApp across local Docker Compose, Railway PaaS, and dedicated Linux servers (Nginx + PHP-FPM).

---

## 1. Docker Compose (Full Stack)

The `docker-compose.yml` deploys the WebApp, Python ML microservice, and MySQL database:

```yaml
version: '3.8'

services:
  eaves-droid:
    build: .
    container_name: eaves-droid-webapp
    restart: unless-stopped
    ports:
      - "9007:80"
    environment:
      CI_ENVIRONMENT: production
      database.default.hostname: mysql
      database.default.database: db_eaves_droid
      database.default.username: eaves_user
      database.default.password: eaves_secure_password
      ml_python_url: http://ml-eaves-droid:9070
    volumes:
      - ./writable:/var/www/html/writable
    depends_on:
      mysql:
        condition: service_healthy

  ml-eaves-droid:
    image: ghcr.io/niccher/ml-eaves-droid:latest
    container_name: ml-eaves-droid
    restart: unless-stopped
    ports:
      - "9070:9070"
    environment:
      MYSQL_URL: mysql+pymysql://eaves_user:eaves_secure_password@mysql:3306/db_eaves_droid
    depends_on:
      mysql:
        condition: service_healthy

  mysql:
    image: mysql:8.4
    container_name: eaves-droid-mysql
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: root_secure_password
      MYSQL_DATABASE: db_eaves_droid
      MYSQL_USER: eaves_user
      MYSQL_PASSWORD: eaves_secure_password
    volumes:
      - mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 5s
      timeout: 5s
      retries: 5

volumes:
  mysql_data:
```

---

## 2. Railway PaaS Deployment

Railway automatically deploys both services via connected GitHub repositories:
* **WebApp Service (`Niccher/Eaves-Droid-WebApp`):** Built via Apache/PHP Dockerfile; configured with `MYSQL_URL` / `DATABASE_URL` and `ml_python_url: http://ml-eaves-droid.railway.internal:9070`.
* **ML Microservice (`Niccher/ML-Eaves-Droid`):** Built via Python 3.12 Dockerfile with FastAPI and scikit-learn.
* **Database:** Shared Railway MySQL database instance.

---

## 3. Production Nginx & PHP-FPM Configuration

For standalone virtual machines (Ubuntu 22.04 / 24.04 LTS):

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name eaves.yourdomain.com;
    root /var/www/eaves-droid/public;
    index index.php index.html;

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```
