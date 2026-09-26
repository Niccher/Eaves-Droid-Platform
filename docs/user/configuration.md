# User Configuration Reference

This document catalogs the primary environment variables configured in `.env` (copied from `env`).

---

## Core Application Configuration

| Variable | Default | Purpose |
|----------|---------|---------|
| `CI_ENVIRONMENT` | `production` | Set to `development` for verbose debug error pages |
| `app.baseURL` | `http://localhost:9007/` | Canonical base URL used for asset loading and redirects |
| `app.forceGlobalSecureRequests` | `false` | Set to `true` behind HTTPS reverse proxies |
| `app.sessionDriver` | `CodeIgniter\Session\Handlers\FileHandler` | Session persistence mechanism |
| `encryption.key` | *(hex key)* | Application encryption key; generate via `php spark key:generate` |

---

## Database Settings (MySQL)

| Variable | Default | Purpose |
|----------|---------|---------|
| `database.default.hostname` | `mysql` | Hostname of the MySQL container (`shared-mysql`) or local IP |
| `database.default.database` | `db_eaves_droid` | Primary database name |
| `database.default.username` | `root` | Database username |
| `database.default.password` | `root_password` | Database password |
| `database.default.DBDriver` | `MySQLi` | Database driver |
| `database.default.port` | `3306` | Internal database port |

---

## ML Engine Integration

| Variable | Default | Purpose |
|----------|---------|---------|
| `PYTHON_BACKEND_HOST` | `ml-eaves-droid` | Docker hostname or IP of the Python ML FastAPI backend |
| `PYTHON_BACKEND_PORT` | `9070` | Port of the Python ML FastAPI service |

---

## Billing & Payment Gateway (Pesapal v3)

| Variable | Default | Purpose |
|----------|---------|---------|
| `pesapal.env` | `sandbox` | `sandbox` or `live` |
| `pesapal.consumer_key` | *(placeholder)* | Pesapal API Consumer Key |
| `pesapal.consumer_secret` | *(placeholder)* | Pesapal API Consumer Secret |
| `pesapal.ipn_url` | `http://localhost:9007/billing/pesapal/ipn` | Registered IPN webhook callback URL |

---

## Email & Notifications (SMTP)

| Variable | Default | Purpose |
|----------|---------|---------|
| `email.protocol` | `smtp` | Mail transport protocol |
| `email.SMTPHost` | `smtp.example.com` | Mail server hostname |
| `email.SMTPPort` | `587` | SMTP port |
| `email.SMTPUser` | `admin@example.com` | Mail account username |
| `email.SMTPCrypto` | `tls` | Encryption protocol (`tls` / `ssl`) |
| `email.fromEmail` | `no-reply@example.com` | Sender address for system emails |
