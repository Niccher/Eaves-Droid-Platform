# Runbook: Container Restart & Recovery

Standard operating procedures for restarting containers, recovering from database locks, and performing clean rebuilds without data loss.

---

## 1. Graceful Container Restart

To restart the stack while preserving database volumes and persistent logs:

```bash
# Graceful stop and restart
docker compose restart

# View real-time container logs
docker compose logs -f eaves-droid
```

---

## 2. Clean Rebuild (Without Data Loss)

When dependencies or Dockerfiles change, rebuild containers cleanly without deleting MySQL storage volumes:

```bash
docker compose down
docker compose build --no-cache
docker compose up -d
```

---

## 3. MySQL Deadlock & Transaction Recovery

If MySQL encounters deadlocks or unresponsive lock wait timeouts:

```bash
# 1. Connect to MySQL container CLI
docker compose exec mysql mysql -u root -proot_secure_password db_eaves_droid

# 2. Check running InnoDB transactions
SHOW ENGINE INNODB STATUS\G

# 3. Kill blocking connection thread
SHOW PROCESSLIST;
KILL <process_id>;
```

---

## 4. Emergency Nuclear Reset (Development Only)

> [!CAUTION]
> This command will permanently erase all local database records and reset container state to factory default.

```bash
docker compose down -v
docker compose build --no-cache
docker compose up -d
docker compose exec eaves-droid php spark migrate --all
docker compose exec eaves-droid php spark db:seed PlanSeeder
docker compose exec eaves-droid php spark db:seed SuperAdminSeeder
```
