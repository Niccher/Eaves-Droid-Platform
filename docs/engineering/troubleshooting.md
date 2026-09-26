# Engineering: Developer Troubleshooting

This guide addresses common development, build, and environment issues encountered by software engineers.

---

## 1. PHP Extension & Composer Failures

### Symptom: `ext-intl or ext-mbstring missing`
Install the missing PHP extensions:
```bash
sudo apt-get install php8.3-intl php8.3-mbstring php8.3-xml php8.3-curl php8.3-gd
```

### Symptom: `Class "CodeIgniter\Shield\..." not found`
Reinstall Composer dependencies with optimized classmap:
```bash
composer install --optimize-autoloader
```

---

## 2. Database Migration & Deadlock Issues

### Symptom: `Table already exists` during `php spark migrate`
Check migration history in `migrations` table and rollback or sync:
```bash
php spark migrate:status
php spark migrate:rollback
php spark migrate --all
```

### Symptom: MySQL Connection Timeout inside Docker
Ensure the MySQL container healthcheck has succeeded and ports are mapped correctly. Confirm that `.env` uses `database.default.hostname = mysql` when running inside Docker or `127.0.0.1` when running locally.

---

## 3. Cache & Compiled Views Invalidation

When modifying view layouts, navigation sidebars, or configuration files, clear the internal CodeIgniter cache:
```bash
php spark cache:clear
rm -rf writable/cache/* writable/debugbar/*
```
