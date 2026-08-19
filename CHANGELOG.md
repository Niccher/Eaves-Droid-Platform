# Platform Changelog — Eaves Droid Ecosystem

All notable changes across the Eaves Droid platform (`WebApp`, `Android Client`, and `ML Engine`) will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.5.0] - 2026-08-20 — Analysis Suites Feature-Gating & Clean File Management

### Added
- **Feature-Gating Strategy Across 11 Analysis Suites**: Enforced 3-tier access control matrix mapping **Free** (`storage`, `apps`, `lifestyle`), **Gold** (`social`, `privacy`, `subscriptions`, `sentiment`), and **Platinum** (`finance`, `location`, `hotspots`, `report`) via `PlanGate.php` filter.
- **Feature Tiers Migration & Seeder**: Created migration `20260820000500_configure_analysis_page_tiers.php` and seeder `AnalysisTiersSeeder.php` registering 11 analysis routes in `tbl_feature_tiers`.
- **Sentiment Profiler & Polarity Score Display**: Upgraded Sentiment Analysis view (`sentiment_analysis.php`) with contact names in bold, italicized muted phone numbers, polarity progress bars, numerical sentiment scores (e.g., `+0.75`, `88%`), Swahili/Sheng lexicons, and brand/bank shortcode filtering.
- **Subscriptions Intelligence & Renewal Forecasting**: Added regex-based billing extraction for utilities (`KPLC`, `Zuku`, `DStv`, `GOtv`, `Showmax`, `Netflix`) with renewal date projections and formatted merchant icons.
- **System Version v2.5.0 Migration**: Created migration `20260820005000_update_system_version_to_v2_5_0.php` updating `system_versions` and `db_versions` database tables.

### Changed
- **File Manager Exclusions & Censorship**: Overhauled `applyExclusionFilters()` in `FilesController.php` to exclude folder entries (`is_directory = 0`), 0-byte empty files (`size_bytes > 0`), hidden/thumbnail paths (`/.thumbnails/`, `/.cache/`, `/.trashed-*`, `/.nomedia/`, `/Android/data/`, `/Android/obb/`, `/LOST.DIR/`), and temp/junk extensions (`.tmp`, `.log`, `.bak`, `.swp`, `Thumbs.db`).
- **Sidebar Menu Restructuring**: Moved `Billing / Upgrade` out of the collapsible Account Accordion to a top-level item directly above `FAQs` under `SUBSCRIPTIONS & HELP` in `sidebar_users.php`.
- **Billing & Plan Comparison View**: Cleaned up feature labels on `/billing` to reflect active tier capabilities and simulated checkout workflows.

### Removed
- **Obsolete Correlation Engine & Care Plan Modules**: Completely deleted `/analysis/care-plan` and `/analysis/correlation-engine` routes, controller methods, view templates, and sidebar links per user request.

---

## [2.4.0] - 2026-08-19 — Enterprise Telemetry & Real-Time Forensic Suite

### Added
- **Real-Time Forensic Exporting**: Synchronous ZIP generation for complete telemetry exports with on-the-fly download links (`ForensicExportController.php`).
- **SMTP HTML Email Notifications**: Automated delivery of export notifications containing secure download links (`sendExportReadyEmail()`).
- **Impersonation Redirection & Top Warning Banner**: Impersonating a user now redirects directly to their target dashboard (`/home`) with a persistent top navigation exit banner.
- **Interactive Version Capabilities Modal**: Added 3-column aligned footer with clickable `v2.4.0` badge displaying release capabilities.
- **Centralized Platform Version Management**: Added `VERSION.json` version descriptor across all 3 repository workspaces.

### Changed
- **Admin Subscription Bypass**: Granted full unlimited plan access for `admin` and `superadmin` accounts across all feature gates and analytics views.
- **Staff Filtering in Analytics**: Excluded administrative staff accounts from billing tables, subscriber metrics, and forensic target selectors.
- **Composite Telemetry Packaging**: Streamlined hardware and software telemetry into composite payloads (`misc_hardware` and `misc_software`).
- **Data-at-Rest Encryption Blueprint**: Designed AES-256-GCM field-level encryption for sensitive telemetry fields with blind indexing.
- **Unified Repository Versioning**: Updated WebApp, Android app (`versionName "2.4.0"`, `versionCode 20400`), and Python ML backend (`2.4.0`) to unified `2.4.0` versioning.

### Removed
- **Obsolete Extractor Cleanup**: Deleted legacy extractors (`BrowserHistoryComposite`, `BrowserHistoryExtractor`, `EmailExtractor`, `PowerRailExtractor`, `ProcessExtractor`, `RunningProcessesDetailedExtractor`, `ThermalExtractor`) across Android client and WebApp views.

### Fixed
- **PDF Location Trail Compilation Exception**: Resolved `Undefined array key "extracted_at"` exception during PDF export compilation.
- **Dynamic Date Column Inspection**: Resolved `Unknown column 'created_at'` error by inspecting dynamic table timestamp columns (`uploaded_at`, `created_at`, `updated_at`).
