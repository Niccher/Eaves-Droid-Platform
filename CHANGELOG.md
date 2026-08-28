# Platform Changelog — Eaves Droid Ecosystem

All notable changes across the Eaves Droid platform (`WebApp`, `Android Client`, and `ML Engine`) will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.7.1] - 2026-08-28 — Secure Support Chat, Private File Gateway, UUID Obfuscation & PDF Sharing

### Added
- **Secure Support Chat & Polling** (`webapp`): Migrated client and admin chat views from SSE to 5-second AJAX polling with live badges.
- **PDF Uploads & Sniffing Validation** (`webapp`): Allowed PDF document sharing with dynamic icon layout and binary MIME header validation to prevent shell executions.
- **Payment History Dashboard** (`webapp`): Rebuilt the payments list to show total paid, transaction status badges, method icons, and copyable references.

### Security
- **Private Attachments & Access Control** (`webapp`): Stored user file uploads outside the public web root with credentials/ownership checks on access.
- **UUID Request Obfuscation** (`webapp`): Replaced sequential message IDs with unique UUID identifiers to eliminate data exposure risk.

---

## [2.7.0] - 2026-08-27 — Security Hardening, Plans Definitions UI, Correlation Tier Gating & Label Cleanup

### Added
- **Live Feature-Tier Definitions Management**: New superadmin UI at `GET /superadmin/plans/definitions` reads all feature tiers from `tbl_feature_tiers` and allows live updates via `POST /superadmin/plans/updateDefinitions`. Superadmins can now change which plan tier (free/gold/platinum) gates any feature without a code deploy.
- **Correlation Tier Gating — Intelligence Timeline** (`webapp`): The unified timeline now resolves the authenticated user's plan and applies a plan-scaled result limit: Free = 50 events, Gold = 100 events, Platinum = 150 events. Replaced the previous dual-tab Basic/Advanced approach.
- **Correlation Tier Gating — Behavioral Anomalies** (`webapp`): Server-side anomaly type filter applied per plan: Free sees call anomalies only; Gold sees call + communication + app_usage; Platinum sees all types.

### Changed
- **Dompdf SSRF & LFI Hardening**: Both PDF export paths (portrait + landscape) now instantiate `\Dompdf\Options` with `isRemoteEnabled=false` and `isLocalFilesystemEnabled=false` before constructing the Dompdf instance. Eliminates SSRF and LFI attack vectors via PDF rendering.
- **Session Driver**: Migrated from `FileHandler` (filesystem sessions) to `DatabaseHandler` (`ci_sessions` table). Ensures session consistency across multi-process and containerised deployments.
- **`PlansController` Refactor** (`webapp`): Renamed `getFeatureDefinitions()` → `getStandardFeatures()` and stripped all FCM command feature keys (now DB-managed in `tbl_feature_tiers`). Stripe price ID fields hardcoded to `null` (Pesapal is the active payment provider). Push notification field hardcoded to `0`. Added `software_profile` and `fcm_groups` to plan version feature assembly from form inputs.
- **`SubscriptionModel` Cleanup** (`webapp`): Removed ~20 hardcoded FCM feature flags from `getAdminLimits()` and `getFreeLimits()` (now stored in `tbl_feature_tiers`). Removed `push_notifications` from free limits. Fixed missing newline at end of file.
- **`PlanGate` Filter**: `before()` now resolves plan via `SubscriptionModel::getPlanLimits()` and queries `required_tier` directly from `tbl_feature_tiers` per slug. Covers 11 analysis suite routes + dynamic `advanced/hardware/*` and `advanced/software/*` slug resolution.
- **`FinderModel` Facade Decomposition** (`webapp`): Refactored the 3,500+ line monolith into a thin facade with lazy-initialised sub-models: `FinderComms`, `FinderSystem`, `FinderEnvironment`, `FinderUser`. Zero functional change; all callers unaffected.
- **Controller Label Fixes**: Stripped leaked PHP controller class name fragments from all user-facing strings across `HomeController`, `AnomaliesController`, `BaseClientController`, `BillingController`, and `TelemetryExportController` — affecting page titles, nav labels, billing diff labels, export label maps, email footers, and PDF footers.
- **New Routes** (`webapp`): `GET superadmin/plans/definitions` (alias: `superadmin-plans-definitions`) and `POST superadmin/plans/updateDefinitions` (alias: `superadmin-plans-update-definitions`).

---

## [2.5.1] - 2026-08-28 — Private File Gateway, UUID Obfuscation & PDF Uploads

### Added
- **Secure Support Chat Gateway & Polling** (`webapp`): Migrated real-time communications to a highly efficient 5-second AJAX polling infrastructure, complete with a live navbar unread badge indicator.
- **PDF Uploads & Sniffing Validation** (`webapp`): Allowed PDF document sharing with dynamic icon layout and binary MIME header validation to prevent shell executions.

### Security
- **Private Attachments & Access Control** (`webapp`): Stored user file uploads outside the public web root with credentials/ownership checks on access.
- **UUID Request Obfuscation** (`webapp`): Replaced sequential message IDs with unique UUID identifiers to eliminate data exposure risk.

---

## [2.5.0] - 2026-08-26 — Analysis Suites Feature-Gating & Clean File Management

---
