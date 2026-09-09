# Architecture: Data and Storage

This document details the database architecture, schema categories, and data retention rules across the Eaves Droid platform.

---

## 1. Primary Datastore (MySQL 8.4)

The database `db_eaves_droid` serves as the centralized source of truth for all components. It organizes data into distinct functional categories:

### Data Tables (Forensic Evidence)

| Category | Primary Tables | Primary Key | Description |
|----------|----------------|-------------|-------------|
| **SMS** | `tbl_sms` | `id` | Incoming/outgoing SMS messages, timestamps, threads |
| **Contacts** | `tbl_contacts` | `id` | Address book entries, phone numbers, interaction counts |
| **Call Logs** | `tbl_logs` | `id` | Call duration, direction, phone number, timestamps |
| **Locations** | `tbl_location` | `id` | Latitude, longitude, altitude, accuracy, GPS speed |
| **Applications** | `tbl_apps` | `id` | Package names, install dates, granted permissions |
| **App Usage** | `tbl_app_usage` | `id` | Foreground duration, launch frequency, last time used |
| **Files** | `tbl_device_files` | `id` | Scanned file metadata, extensions, byte sizes, paths |
| **Device Info** | `tbl_device_profile` | `id` | Hardware identifiers, OS version, battery status, network state |
| **Notifications**| `tbl_notifications` | `id` | Intercepted system notifications and alert text |

### System & Access Tables

| Category | Tables | Description |
|----------|--------|-------------|
| **Auth & RBAC** | `users`, `auth_identities`, `auth_groups_users`, `auth_permissions_users` | CodeIgniter Shield user accounts and role assignments |
| **Plans & Billing** | `tbl_plans`, `tbl_plan_versions`, `tbl_subscriptions`, `tbl_ipn_logs` | Plan gating limits, version diffs, subscription history |
| **Tokens & Devices** | `tbl_tokens`, `tbl_devices`, `tbl_remote_commands` | Mobile authentication tokens and remote command dispatch |
| **ML Coordination** | `ml_jobs`, `ml_results`, `ml_analysis_tracking` | Job dispatch tracking and anomaly detector findings |

---

## 2. File Storage (`writable/`)

* **Forensic Payload Backups:** Uploaded encrypted raw payloads are archived in `writable/loot/` for offline inspection or forensic verification.
* **Logs:** CI4 application runtime logs are written to `writable/logs/`.
* **Export Artifacts:** Legal forensic bundles and ZIP packages are generated in `writable/exports/`.

---

## 3. Data Retention Lifecycle

Data retention is strictly enforced by the background cron engine according to user subscription tiers:
* **Free Tier:** Automatically purges records older than **10 days**.
* **Gold Tier:** Retains forensic history for **60 days**.
* **Platinum Tier:** Extended forensic archive of **180 days**.

Purging is scheduled via `php spark cron:run` and removes records in batch chunks to maintain database responsiveness.
