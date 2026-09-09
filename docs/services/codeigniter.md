# Service Guide: CodeIgniter 4 WebApp

This guide covers the internal layout, controllers, parsers, filters, and machine learning components of the CodeIgniter 4 backend (`Niccher/Eaves-Droid-WebApp`).

---

## 1. Directory Layout

```
app/
├── Config/             # Routes.php, Database.php, Shield configurations
├── Controllers/
│   ├── Receive.php     # Ingestion endpoint (POST /api/v1/files/upload)
│   ├── admin/          # Admin dashboard, logs, user CRUD, ML settings, telemetry
│   ├── superadmin/     # Role matrix, fleet management, plan versioning, audit trail
│   ├── billing/        # Pesapal checkout and IPN webhook
│   └── users/          # User forensic dashboards (SMS, Calls, Location, Apps, etc.)
├── Filters/
│   ├── RoleFilter.php  # Shield group & permission gating
│   └── PlanGate.php    # Subscription tier feature gating
├── Models/             # Models for tbl_sms, tbl_logs, tbl_tokens, tbl_plans, AnomaliesModel, etc.
└── Views/
    ├── admin/          # AdminLTE 3 views (ml.php, users, reports, logs)
    ├── superadmin/     # SuperAdmin views (role_matrix.php, infrastructure.php, audit.php)
    ├── users/          # Client forensic dashboards & correlation engine
    └── headers_footers/# Global navigation, omni search modal & sidebars
```

---

## 2. Forensic Parsers

When the Android client uploads forensic data, `Controllers\Receive.php` decodes the payload and dispatches it:

1. **`Mod_Parse_Loot`**:
   Parses core communication categories: SMS threads, call logs, contacts, calendar entries, and base system accounts.
2. **`Mod_Parse_Advanced`**:
   Parses deep telemetry: Wi-Fi/cell tower footprints, sensor readings, app usage statistics, Bluetooth scans, SIM configurations, and file lists.

---

## 3. Local Machine Learning Engine (PHP-ML)

In-process analytics execute inside `AnomaliesModel` / `Mod_Anomalies`:

* **K-Means Clustering (`PhpMl\Clustering\KMeans`)**:
  Used in `detectSmsCluster()` with configurable $K$ (default: 3). Clusters sender frequency, night-time ratio, and unique recipients to flag outlier communications.
* **DBSCAN Density Clustering (`PhpMl\Clustering\DBSCAN`)**:
  Used in `detectLocationDBSCAN()` with $\epsilon = 0.01$ and `minPts = 2`. Clusters geographic coordinates to detect unnatural trajectory outliers or spoofed GPS points.
* **Isolation Forest (`PhpMl`)**:
  Constructs random decision trees to isolate multi-dimensional feature anomalies across SMS, calls, and app activity.

---

## 4. Useful CLI Commands

All standard CodeIgniter 4 CLI operations run via `spark`:

```bash
# Display all registered application routes
php spark routes

# Run pending database migrations
php spark migrate --all

# Clear compiled view and config cache
php spark cache:clear

# Execute test seeder
php spark db:seed PlanSeeder
```
