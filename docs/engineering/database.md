# Engineering: Database and Migrations

This guide covers database migrations, seeders, schema conventions, and automated deployment provisioning for `db_eaves_droid` across the Eaves Droid Platform.

---

## 1. Single Authoritative Migration Engine

In the Eaves Droid Platform monorepo, **CodeIgniter 4 (`web/app/Database/Migrations/`) is the single authoritative source of truth** for all database schemas.

* CodeIgniter owns and migrates all 147+ tables, including:
  * User authentication & RBAC (`users`, `auth_identities`, `auth_groups_users`)
  * Subscriptions & billing (`plans`, `plan_versions`, `user_subscriptions`, `tbl_ipn_logs`)
  * Forensic telemetry tables (`tbl_extracted_sms`, `tbl_extracted_call_logs`, `tbl_extracted_locations`, etc.)
  * ML Anomaly detection tables (`ml_jobs`, `ml_results`, `ml_analysis_tracking`)
* The ML microservice (`ml/`) reads directly from these tables and writes findings into `ml_results`.
* **No manual SQL scripts are required.**

---

## 2. Automated Deployment Provisioning

During container startup (`web/entrypoint.sh`), CodeIgniter automatically runs migrations and seeders:

```bash
# Executed automatically on deployment when RUN_MIGRATIONS=true:
php spark migrate --all
php spark db:seed DatabaseSeeder
```

### Manual CLI Commands (Inside `web` container or local dev):
```bash
cd web

# Run all pending migrations
php spark migrate --all

# Check migration status
php spark migrate:status

# Rollback last migration batch
php spark migrate:rollback
```

---

## 3. Seeders Catalog

Baseline reference data, subscription tiers, superadmin credentials, and ML system settings are seeded via `web/app/Database/Seeds/`:

| Seeder | Purpose | Included in `DatabaseSeeder`? |
|--------|---------|---|
| **`PlansSeeder` & `PlanVersionsSeeder`** | Seeds Free, Gold, and Platinum subscription plans | Yes |
| **`FeatureTiersSeeder`** | Configures tiered feature gates and anomaly limits | Yes |
| **`MlSettingsSeeder`** | Seeds default ML URL (`http://ml:9070`), token, and detector thresholds | Yes |
| **`CronJobsSeeder`** | Seeds scheduled background analysis jobs | Yes |
| **`SystemVersionsSeeder`** | Tracks platform version history | Yes |
| **`SuperAdminSeeder`** | Seeds default root superadmin (`superadmin@eavesdroid.local`) | Yes |
| **`DemoDataSeeder`** | Seeds realistic mock telemetry for staging and dev testing | Yes |

To seed everything in one step:
```bash
php spark db:seed DatabaseSeeder
```
