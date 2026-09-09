# Runbook: Backup & Disaster Recovery

Standard operating procedures for snapshotting relational database state and restoring uploaded forensic archives.

---

## 1. Automated Database Backup

Create a timestamped compressed SQL snapshot using `mysqldump`:

```bash
# 1. Create backups directory
mkdir -p backups

# 2. Dump MySQL database from container
docker compose exec mysql mysqldump -u eaves_user -peaves_secure_password db_eaves_droid | gzip > backups/eaves_droid_$(date +%Y%m%d_%H%M%S).sql.gz

# 3. Archive loot uploads and attachments
tar -czvf backups/loot_uploads_$(date +%Y%m%d_%H%M%S).tar.gz writable/loot/ writable/uploads/
```

---

## 2. Disaster Recovery & Restoration

To restore a database snapshot into a fresh or existing database instance:

```bash
# 1. Decompress SQL dump
gunzip -c backups/eaves_droid_YYYYMMDD_HHMMSS.sql.gz > restore.sql

# 2. Import into MySQL container
docker compose exec -T mysql mysql -u eaves_user -peaves_secure_password db_eaves_droid < restore.sql

# 3. Extract loot filesystem archive
tar -xzvf backups/loot_uploads_YYYYMMDD_HHMMSS.tar.gz -C /

# 4. Fix permissions
chmod -R 777 writable/
```
