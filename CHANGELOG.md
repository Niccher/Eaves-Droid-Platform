# Platform Changelog — Eaves Droid Ecosystem

All notable changes across the Eaves Droid platform (`WebApp`, `Android Client`, and `ML Engine`) will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
