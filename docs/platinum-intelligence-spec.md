# Platinum Intelligence Features — Implementation & Design Spec

**Scope:** Four flagship features delivered to the client `analysis` group, gated by plan tier (Free / Gold / Platinum).

- **#5** Cross-category Correlation Engine (Platinum)
- **#6** Risk Score → actionable Care Plan (Free raw / Gold trends / Platinum action plan)
- **#1** Unified Smart Timeline (Free 7d / Gold 30d / Platinum full)
- **#2** Health & Wellbeing Intelligence (Gold/Platinum)

---

## 0. Cross-cutting: plan gating & shared plumbing

### 0.1 New feature flags (in `plan_versions.features` JSON)
Add keys so `PlanGate::hasFeature()` and the `planGate` filter can gate the new pages:

| Key | Free | Gold | Platinum |
|---|---|---|---|
| `smart_timeline` | ✓ | ✓ | ✓ |
| `wellbeing` | ✗ | ✓ | ✓ |
| `correlation` | ✗ | ✗ | ✓ |
| `care_plan` | ✗ (raw only) | ✗ (trend only) | ✓ (full) |

**Note:** The existing flag is `wellbeing` (already `true` for gold/platinum). Reuse it — do NOT add a separate flag. Add only `smart_timeline`, `correlation`, `care_plan`.

Update: `PlanSeeder.php`, `SubscriptionModel::getFreeLimits()`, and the `plan_versions` rows (via a migration or `php spark` update) so the flags match the table above.

### 0.2 History-depth gate (new helper)
Add to `PlanGate`:
```php
public function reportDepth(int $userId): string
```
- Reads the active plan's `wellbeing_depth` column (values `3|7|14|21|30|60|90|120|180|all`).
- Returns the days int for `all` → 365 (or max history), else the numeric days.
- Used by #1 and #2 to bound all date ranges uniformly.

`getHistoryDays()` already exists and is the primary bound for #1/#2. Prefer `getHistoryDays()`; add `reportDepth()` only if wellbeing_depth semantics differ.

### 0.3 Depth helper in views
In each new view, compute:
```php
$gate = new \App\Services\PlanGate();
$depthDays = $gate->getHistoryDays($this->userId); // bound for queries
$tier = $gate->limits($this->userId)['plan'] ?? 'free';
```
Controller passes these into view data so views never call the gate directly.

### 0.4 New service scaffolding
Follow `RiskScoreService` conventions (constructor accepts optional `?BaseConnection $db`, uses `Services::database()`, `setting()` for config, try/catch + `log_message`).

New services:
- `app/Services/CorrelationService.php`
- `app/Services/CarePlanService.php`

---

## 1. Feature #5 — Cross-category Correlation Engine (Platinum)

### Goal
A relationship graph linking a contact ↔ (call frequency) ↔ (SMS frequency) ↔ (shared geofence co-occurrence) ↔ (common app usage) to surface **hidden connections** not obvious from any single category.

### Data sources (already stored)
- `tbl_sms` (address, sms_date ms, body)
- `tbl_logs` (phone_number, contact_name, call_date ms, duration_seconds, call_type)
- `tbl_contacts` (phone_numbers JSON, display_name)
- `tbl_location` (coords, location_time ms) + `tbl_geo_events` / zones
- `tbl_app_usage` (package, foreground_time_ms)

### New service: `CorrelationService`
Methods:

**`buildGraph(int $userId, array $opts): array`**
Returns `['nodes' => [], 'edges' => [], 'stats' => []]`:
1. **Seeds:** `Mod_Finder::get_social_graph($userId, 50)` → top contacts by SMS+call score (reuse existing).
2. **Edge weights (contact ↔ contact):**
   - `SMS + call`: shared contact graph via number resolution. For each contact number, count interactions (already have per-contact SMS/call counts).
   - **Shared location co-occurrence:** find location timestamps where contact A and contact B phones co-occur. Implementation: derive a lightweight `phone ↔ location-timestamp` from `tbl_sms`/`tbl_logs` timestamps matched to `tbl_location` within a tolerance window (e.g. ±10 min). This is heuristic; document it as such.
   - **Common app usage:** group apps used around contact interaction timestamps (heuristic) — or simpler: measure **co-activity** = fraction of days both contact had interaction.
3. **Node score:** aggregate = `sms*1 + call*5 + cooccurrence*3 + coactivity*2` (match `get_social_graph` scoring so it's consistent).
4. **Edges** only for pairs above a threshold; cap graph size (e.g. 25 nodes / 60 edges) for render sanity.

**`topLinks(int $userId, int $limit = 20): array`** — flat ranked list of strongest relationships with an explainable reason string (e.g. "12 calls + 40 SMS + 3 shared locations").

**`clusterContacts(int $userId): array`** — optional: `Mod_ML_Analyzer::kmeans()`/`dbscan()` over normalized interaction vectors to detect **unexpected clusters** (e.g. a phone-number group that only ever contacts each other).

### Controller method (in `clients/Correlation.php`)
```php
public function correlation_engine()
```
- Guard: if `!$this->finderModel` plan has `correlation` feature → flash error + redirect back ("Requires Platinum").
- Load `CorrelationService`, build graph, stats.
- Render `users/correlation/correlation_engine` view.

### Route (inside `analysis` group, `Routes.php`)
```php
$routes->get('correlation-engine', 'Correlation::correlation_engine', ['as' => 'analysis-correlation-engine']);
```

### View `users/correlation/correlation_engine.php`
- **Force-directed graph** rendered with a lightweight JS (Vis.js / Cytoscape.js) from `nodes`+`edges` JSON.
- Node size ∝ interaction score; edge thickness ∝ link weight; color = dominant signal (calls=SMS/green, geo=blue, app=purple).
- **Top Links table** with the explainable `reason`.
- **Clusters panel** (if implemented).
- Chart.js optional for the interaction mix per node.

### Plan gating
`planGate` filter already maps route prefixes → features. Add `correlation` feature + map `analysis/correlation-engine` in `app/Filters/PlanGate.php` `$featureRoutes`.

---

## 2. Feature #6 — Risk Score → Care Plan (Free/Gold/Platinum)

### Goal
Wrap the existing `device_risk.score` (0-100) in a human layer:
- **Free:** raw score + severity counts (no interpretation).
- **Gold:** adds trend line (score over time) + percentile vs cohort.
- **Platinum:** adds a generated **priority action list** ("recurring late-night calls with X", "geo exit from home zone", "forensic red flags").

### New service: `CarePlanService`
Reads `device_risk` (the persisted score) + `ml_results` (raw findings) + `tbl_sms`/`tbl_logs`/`tbl_location`.

**`current(int $userId, ?string $deviceId): ?array`** — latest `device_risk` row per device (there's a unique `(user_id, device_id)`; for multi-device pick highest or active device).

**`trend(int $userId, ?string $deviceId, int $days = 90): array`** — time series of `score` per `computed_at` from `device_risk` history (only latest per device exists → need to add history). **Requirement:** add a `device_risk_history` table (migration) so trend has data. `RiskScoreService::saveScore()` also writes an append-only row there.

**`percentile(int $score, int $userId): array`** — cohort = other users' scores; `percentile = count(below)/count(total)*100`. Use a quick `SELECT` over `device_risk` grouped by `user_id` taking each user's max score.

**`actionPlan(int $userId, ?string $deviceId, array $risk): array`** — Platinum only. Generates ordered actions:
1. **Forensic red flags:** from `top_findings` with `severity high/critical` → "Investigate forensic finding: {title}".
2. **Recurring late-night contact:** query `tbl_logs`/`tbl_sms` between 23:00–05:00 grouped by number, top N → "Recurring late-night contact with {name} ({count}×)".
3. **Geo exit from home zone:** use `tbl_geo_events`/zones → "Left designated safe zone {zone} at {time}".
4. **Suspicious pattern:** reuse `Mod_Finder::get_behavioral_anomalies()` / anomaly alerts.
5. Score thresholds → priority: `critical` (score≥70), `high` (40–69), `medium` (20–39), `info` (<20).

Each action = `['priority', 'type', 'title', 'description', 'evidence_count', 'category']`.

### Controller method (in `clients/Correlation.php`)
```php
public function risk_care_plan()
```
- Determine tier via gate.
- Free → pass raw score + severity counts (hide trend/actions).
- Gold → + trend + percentile.
- Platinum → + actionPlan.
- Render `users/correlation/risk_care_plan` view.

### Route
```php
$routes->get('risk-care-plan', 'Correlation::risk_care_plan', ['as' => 'analysis-risk-care-plan']);
```

### Migration
`device_risk_history`: `id, user_id, device_id, score, severity_counts JSON, top_findings JSON, computed_at, created_at` (append-only, indexed `(user_id, device_id, computed_at)`). Update `RiskScoreService::saveScore()` to also insert history.

### View `users/correlation/risk_care_plan.php`
- **Score gauge** (Chart.js doughnut / progress bar).
- **Trend line** (Gold+): Chart.js line of `trend`.
- **Percentile badge** (Gold+): "top 12% riskier than cohort".
- **Priority action list** (Platinum): colored cards (red=critical, amber=high…) with title + evidence count + drill link.
- **Severity breakdown** bars.

---

## 3. Feature #1 — Unified Smart Timeline (Free/Gold/Platinum)

### Goal
Crisis-mode single-pull "digital lifeline": contacts + SMS + location + browser + wellbeing over time with a pivot. Free=7d, Gold=30d, Platinum=full.

### Reuse
`Mod_Finder::get_unified_timeline($userId, $limit)` (merges 10 sources incl. health data) and `get_unified_timeline_filtered()` already exist. Also `intelligence_timeline()` controller + view exist. **Enhance rather than duplicate.**

### Changes
1. **Controller `Correlation::intelligence_timeline()`** — accept a `?days=` param; default to `$gate->getHistoryDays($userId)`. Pass `depthDays` to the view. Add type filter (`sms|call|location|browser|wellbeing|all`).
2. **`Mod_Finder::get_unified_timeline_filtered()`** — add an optional `?int $sinceDays = null` to bound the query window (currently unbounded 1000-cap). Add `browser_history` source if not present (verify; browser may live in `tbl_browser_history`).
3. **Pivot table** — add a "pivot" tab: aggregate events into rows by day × category (SMS count, call count, location pings, wellbeing snapshots). New small method `get_timeline_pivot(int $userId, int $days)`.

### View `users/correlation/intelligence_timeline.php`
- Tabs: **Stream** (existing timeline feed) / **Pivot** (new table).
- Range selector bound by plan depth (Free max 7, Gold 30, Platinum all).
- A badge showing "History window: 7 days (Free)".
- Pivot: per-day columns, category counts.

### Route
Already exists (`analysis/timeline`). No new route needed; only query/window params.

---

## 4. Feature #2 — Health & Wellbeing Intelligence (Gold/Platinum)

### Goal
Sleep inference, screen-time & app addiction report, step/activity & battery-health trends — depth driven by `wellbeing_depth`.

### Reuse
`Correlation::digital_wellbeing()` + view exist; `Mod_Finder` has `get_digital_wellbeing()`, `get_health_data()`, `get_daily_usage_heatmap()`, `get_dopamine_vs_productivity()`, `get_top_time_sink_apps()`. `tbl_health_data` has sleep/step/heart-rate columns.

### New `Mod_Finder` methods (add to existing model)
1. **`get_sleep_intervals(int $userId, int $days): array`** — from `tbl_health_data` where `data_type` in sleep types (`sleep`, `sleep_stage`, `heart_rate`); group into nightly intervals; derive `sleep_start`, `sleep_end`, `duration_hours`, `efficiency`, `stages`.
2. **`get_daily_screen_time(int $userId, int $days): array`** — from `tbl_digital_wellbeing.total_daily_usage_minutes` + `tbl_digital_wellbeing_apps.daily_usage_minutes`; per-day totals.
3. **`get_app_addiction_report(int $userId, int $days, int $limit=8): array`** — top apps by daily minutes, category, % of daily total; flag apps > threshold (e.g. >25% of daily screen time) as "addiction risk".
4. **`get_activity_battery_trends(int $userId, int $days): array`** — steps (`tbl_health_data step_count`), battery health (`tbl_battery_stats` or health), per-day series.

### Controller `Correlation::digital_wellbeing()`
Enhance: add `$depthDays = $gate->getHistoryDays($userId)` and pass into all new queries. Keep `wellbeing` feature gate (already enforced). Add the 4 new datasets to view data.

### View `users/correlation/digital_wellbeing.php`
- **Sleep panel** (Gold): night-by-night chart (sleep duration + efficiency), stages breakdown.
- **Screen-time panel**: existing heatmap + per-day total line; tier-based range.
- **App addiction report** (Gold/Platinum): top apps table with "addiction risk" flags.
- **Activity & battery trends** (Platinum): steps line + battery health gauge.
- Depth badge reflecting `wellbeing_depth` (e.g. "Report window: 14 days").

---

## 5. Plan-gating wiring summary

| Feature | Route (`analysis/...`) | Feature flag | Free | Gold | Platinum |
|---|---|---|---|---|---|
| Smart Timeline | `timeline` | `smart_timeline` | 7d | 30d | full |
| Wellbeing | `wellbeing` | `wellbeing` (exists) | block | depth | depth |
| Correlation Engine | `correlation-engine` (new) | `correlation` | block | block | ✓ |
| Risk Care Plan | `risk-care-plan` (new) | `care_plan` | raw | +trend | +actions |

- Add new flags to `PlanSeeder`, `SubscriptionModel::getFreeLimits()`, and the 3 `plan_versions` rows.
- Add route→feature mapping in `app/Filters/PlanGate.php` `$featureRoutes`.
- In each controller method, guard with `PlanGate::hasFeature()` for a graceful in-app message (filter handles hard 403).

---

## 6. Migration / file checklist

**Migrations (new):**
1. `device_risk_history` (for #6 trend) + update `RiskScoreService::saveScore()`.
2. (Optional) none for #1/#2/#5 — all reuse existing tables.

**New files:**
- `app/Services/CorrelationService.php`
- `app/Services/CarePlanService.php`
- `app/Views/users/correlation/correlation_engine.php`
- `app/Views/users/correlation/risk_care_plan.php`

**Modified files:**
- `app/Controllers/clients/Correlation.php` (3 new methods + 2 enhanced)
- `app/Models/Mod_Finder.php` (timeline pivot, sleep/screen/addiction/activity methods)
- `app/Services/RiskScoreService.php` (history write)
- `app/Services/PlanGate.php` (+`reportDepth()`, maybe)
- `app/Filters/PlanGate.php` (feature routes)
- `app/Config/Routes.php` (2 new routes)
- `app/Models/SubscriptionModel.php` + `PlanSeeder.php` + `plan_versions` rows (new flags)
- `app/Views/users/correlation/intelligence_timeline.php`, `digital_wellbeing.php` (enhance)

---

## 7. Suggested build order
1. **#6 Care Plan** — self-contained, reuses RiskScoreService, highest "wow" value.
2. **#2 Wellbeing** — data + views already exist; mostly new Mod_Finder queries.
3. **#1 Timeline** — enhance existing; add depth + pivot.
4. **#5 Correlation Engine** — most novel (graph), do last.
5. Add plan flags + gating alongside each (not upfront), so each feature is independently shippable.
