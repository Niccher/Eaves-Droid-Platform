# Service Guide: CodeIgniter 4 WebApp

This guide covers the internal layout, controllers, parsers, and machine learning components of the CodeIgniter 4 backend.

---

## 1. Directory Layout

```
app/
├── Config/             # Routes.php, Database.php, Shield configurations
├── Controllers/
│   ├── Receive.php     # Ingestion endpoint (POST /api/v1/files/upload)
│   ├── Admin/          # System administration, logs, user CRUD
│   ├── Superadmin/     # Fleet management, plan versioning, audit trail
│   ├── Billing.php     # Pesapal checkout and IPN webhook
│   └── Client/         # User forensic dashboards (SMS, Calls, Location, Apps, etc.)
├── Filters/
│   ├── RoleFilter.php  # Shield group & permission gating
│   └── PlanGate.php    # Subscription tier feature gating
├── Models/             # Models for tbl_sms, tbl_logs, tbl_tokens, tbl_plans, etc.
└── Modules/
    ├── Mod_Parse_Loot.php       # Ingestion parser for legacy categories
    ├── Mod_Parse_Advanced.php   # Ingestion parser for advanced telemetry
    └── Mod_Anomalies.php        # PHP-ML clustering & dispatch to Python engine
```

---

## 2. Forensic Parsers

When the Android client uploads forensic data, `Controllers\Receive.php` decodes the payload and dispatches it:

1. **`Mod_Parse_Loot`**:
   Parses core communication categories: SMS threads, call logs, contacts, calendar entries, and base system accounts.
2. **`Mod_Parse_Advanced`**:
   Parses deep telemetry: Wi-Fi/cell tower footprints, sensor readings, app usage statistics, Bluetooth scans, SIM configurations, and file lists.

---

## 3. Machine Learning (PHP-ML)

In-process analytics execute inside `Mod_Anomalies`:

* **K-Means Clustering (`PhpMl\Clustering\KMeans`)**:
  Used in `detectSmsCluster()` with $k=3$. Analyzes sender frequency, night-time ratio, and unique recipients. Outliers outside major clusters trigger suspicion flags.
* **DBSCAN Clustering (`PhpMl\Clustering\DBSCAN`)**:
  Used in `detectLocationDBSCAN()` with $\epsilon = 0.01$ and `minPts = 2`. Clusters geographic coordinates to detect unnatural trajectory outliers or spoofed GPS points.
* **Rolling Z-Score Monitors**:
  Calculates dynamic baselines over 7-day windows to detect spikes in late-night call duration and SMS frequency.

---

## 4. Useful Developer Commands

All standard CodeIgniter 4 CLI operations run via `spark`:

```bash
# Display all registered application routes
php spark routes

# Run pending database migrations
php spark migrate --all

# Clear compiled view and config cache
php spark cache:clear
```
