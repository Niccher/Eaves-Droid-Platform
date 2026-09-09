# Engineering: Database and Migrations

This guide covers CodeIgniter 4 database migrations, seeders, and schema conventions for `db_eaves_droid`.

---

## 1. Migrations Overview

All schema modifications are tracked in `app/Database/Migrations/`. Migrations must be reversible and maintain backward compatibility for connected clients.

### Running Migrations
```bash
# Run all pending migrations across application and modules (including Shield)
php spark migrate --all

# Check migration status
php spark migrate:status

# Rollback the last batch
php spark migrate:rollback
```

---

## 2. Seeders

Initial reference data, default plans, and superadmin credentials are managed through seeders in `app/Database/Seeds/`:

| Seeder | Purpose | Command |
|--------|---------|---------|
| **`PlanSeeder`** | Seeds Free, Gold, and Platinum subscription plans and limits | `php spark db:seed PlanSeeder` |
| **`SuperAdminSeeder`** | Seeds the initial root superadmin account and groups | `php spark db:seed SuperAdminSeeder` |

---

## 3. Polyrepo Shared Database Convention

> [!WARNING]
> The MySQL database `db_eaves_droid` is shared with the **ML Eaves Droid** service.
> * CodeIgniter owns all forensic tables (`tbl_*`), user accounts, and billing data.
> * The ML backend owns `ml_jobs`, `ml_results`, and `ml_analysis_tracking`.
> * Never rename or drop columns in forensic tables without checking `app/models/` in the ML repository.
