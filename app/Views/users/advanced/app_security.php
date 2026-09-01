<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper("coalesce");
$rows = coalesce_snapshots(
    $rows,
    "device_id",
    ["device_admin_apps_json", "app_permissions_map_json", "running_services_json"]
);

$selectedIdx = (int)(service('request')->getGet('snapshot') ?? 0);
if ($selectedIdx < 0 || $selectedIdx >= count($rows)) {
    $selectedIdx = 0;
}

$latest = !empty($rows) ? $rows[$selectedIdx] : null;
$adminApps = [];
$permAppsList = [];
$runningSvcs = [];

$sensitivePermissions = [
    "android.permission.CAMERA",
    "android.permission.RECORD_AUDIO",
    "android.permission.ACCESS_FINE_LOCATION",
    "android.permission.ACCESS_COARSE_LOCATION",
    "android.permission.READ_SMS",
    "android.permission.SEND_SMS",
    "android.permission.RECEIVE_SMS",
    "android.permission.READ_CONTACTS",
    "android.permission.WRITE_CONTACTS",
    "android.permission.READ_CALL_LOG",
    "android.permission.WRITE_CALL_LOG",
    "android.permission.READ_PHONE_STATE"
];

if ($latest) {
    // 1. Device Admin Apps
    $rawAdmin = is_string($latest["device_admin_apps_json"] ?? null)
        ? (json_decode($latest["device_admin_apps_json"], true) ?: [])
        : ($latest["device_admin_apps_json"] ?? []);
    
    foreach ($rawAdmin as $app) {
        $pkg = is_array($app) ? ($app["package"] ?? $app["component"] ?? json_encode($app)) : (string)$app;
        $isSystem = str_starts_with($pkg, "android") || str_starts_with($pkg, "com.android");
        $isGoogle = str_starts_with($pkg, "com.google");
        $adminApps[] = [
            "package" => $pkg,
            "is_system" => $isSystem || $isGoogle,
            "type_label" => ($isSystem || $isGoogle) ? "SYSTEM ADMIN" : "HIGH RISK THIRD-PARTY"
        ];
    }

    // 2. Permissions Map JSON Structure
    $rawPerm = is_string($latest["app_permissions_map_json"] ?? null)
        ? (json_decode($latest["app_permissions_map_json"], true) ?: [])
        : ($latest["app_permissions_map_json"] ?? []);

    if (isset($rawPerm["apps"]) && is_array($rawPerm["apps"])) {
        $permAppsList = $rawPerm["apps"];
    } elseif (is_array($rawPerm)) {
        foreach ($rawPerm as $pkgKey => $val) {
            if (is_array($val)) {
                $permAppsList[] = array_merge(["package" => $pkgKey], $val);
            } else {
                $permAppsList[] = [
                    "package" => $pkgKey,
                    "granted_permissions" => [],
                    "denied_permissions" => []
                ];
            }
        }
    }

    // 3. Running Services JSON Structure
    $runningSvcs = is_string($latest["running_services_json"] ?? null)
        ? (json_decode($latest["running_services_json"], true) ?: [])
        : ($latest["running_services_json"] ?? []);
}

// Compute snapshot history deltas
$historyList = [];
$prevRow = null;
$totalRows = count($rows);

foreach ($rows as $origIdx => $r) {
    $aCount = count(is_string($r["device_admin_apps_json"] ?? null) ? (json_decode($r["device_admin_apps_json"], true) ?: []) : []);
    
    $pMap = is_string($r["app_permissions_map_json"] ?? null) ? (json_decode($r["app_permissions_map_json"], true) ?: []) : [];
    $pCount = isset($pMap["apps"]) ? count($pMap["apps"]) : count($pMap);
    
    $sCount = count(is_string($r["running_services_json"] ?? null) ? (json_decode($r["running_services_json"], true) ?: []) : []);

    $deltaA = $prevRow ? ($aCount - $prevRow["a_count"]) : 0;
    $deltaP = $prevRow ? ($pCount - $prevRow["p_count"]) : 0;
    $deltaS = $prevRow ? ($sCount - $prevRow["s_count"]) : 0;

    $item = [
        "id" => $r["id"],
        "orig_idx" => $origIdx,
        "extracted_at" => $r["extracted_at"],
        "a_count" => $aCount,
        "p_count" => $pCount,
        "s_count" => $sCount,
        "delta_a" => $deltaA,
        "delta_p" => $deltaP,
        "delta_s" => $deltaS
    ];

    $historyList[] = $item;
    $prevRow = $item;
}

// High risk admin apps count
$highRiskAdmins = array_filter($adminApps, fn($a) => !$a["is_system"]);
?>

<style>
.sec-card        { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); border: none; }
.sec-hero        { background: linear-gradient(135deg, #dc3545 0%, #b02a37 60%, #842029 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; position: relative; overflow: hidden; }
.sec-hero::after { content: "\f505"; position: absolute; right: 15px; bottom: -5px; font-family: "Font Awesome 5 Free"; font-weight: 900; font-size: 64px; opacity: 0.12; color: #fff; }
.sec-hero-blue   { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 60%, #084298 100%); }
.sec-hero-blue::after { content: "\f084"; }
.sec-hero-teal   { background: linear-gradient(135deg, #20c997 0%, #1aa179 60%, #147759 100%); }
.sec-hero-teal::after { content: "\f085"; }

.sec-kv          { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.sec-kv:last-child { border-bottom: none; }
.sec-kv .sk      { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
.sec-kv .sv      { font-weight: 700; color: #343a40; text-align: right; max-width: 65%; word-break: break-all; }

.pill-badge      { display: inline-block; padding: 3px 9px; border-radius: 12px; font-size: 11px; font-weight: 700; margin: 2px; }
.pill-danger     { background: #ffebe9; color: #cf222e; border: 1px solid #ffc1c0; }
.pill-success    { background: #dafbe1; color: #1a7f37; border: 1px solid #4ac26b; }
.pill-secondary  { background: #f6f8fa; color: #57606a; border: 1px solid #d0d7de; }
.pill-info       { background: #ddf4ff; color: #0969da; border: 1px solid #54aeff; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout Header (Matching audio_devices, hardware_graphics & accounts style) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-user-shield text-danger mr-2"></i>App Security Audit</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Device Administrator receivers, application runtime permission maps, and background execution services</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>
      
      <!-- Styled 3-Column Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #dc3545; background:#fdfdfd; border-radius:4px;">
        <h5 class="font-weight-bold text-danger"><i class="fas fa-shield-alt mr-2"></i>Security Audit Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing Android application privileges, administrative grants, granted/denied runtime capabilities, and active background execution vectors.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1 text-dark"><i class="fas fa-user-shield text-danger mr-1"></i>Device Admin Grants:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Admin Policy Receiver:</b> Apps with high-level system wipe, lock, and policy control rights.</li>
              <li><b>Threat Rating:</b> Flags non-system 3rd party administrator applications.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1 text-dark"><i class="fas fa-key text-primary mr-1"></i>Permission Map:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Runtime Access:</b> Traces exact granted vs denied system permissions.</li>
              <li><b>Sensitive Capabilities:</b> Highlights Camera, Location, Audio, and SMS access.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1 text-dark"><i class="fas fa-cogs text-info mr-1"></i>Background Services:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Active Processes:</b> Identifies background services alive during telemetry extraction.</li>
              <li><b>Process Ownership:</b> Distinguishes Google Services, OS Framework, and 3rd Party vectors.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <?php if ($latest): ?>
        <!-- Active Snapshot Dropdown Selector -->
        <?php if (count($rows) > 1): ?>
          <div class="sec-card p-3 mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
              <label for="snapshotSelect" class="mb-0 font-weight-bold text-secondary">
                <i class="fas fa-history text-danger mr-2"></i>Active Security Snapshot:
              </label>
              <select id="snapshotSelect" class="form-control form-control-sm w-auto font-weight-bold" onchange="location.href='?snapshot=' + this.value;">
                <?php foreach ($rows as $i => $r): 
                  $tsStr = !empty($r['extracted_at']) ? date('Y-m-d H:i:s', (int)$r['extracted_at'] / 1000) : 'Snapshot #' . ($i + 1);
                ?>
                  <option value="<?= $i ?>" <?= $i === $selectedIdx ? 'selected' : '' ?>>
                    Snapshot #<?= count($rows) - $i ?> (<?= $tsStr ?>) <?= $i === 0 ? '— Latest' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        <?php endif; ?>

        <!-- Hero Overview Cards (Audio & Accounts Card Style) -->
        <div class="row mb-4">
          
          <!-- Card 1: Device Admins -->
          <div class="col-md-4 mb-3 mb-md-0">
            <div class="sec-card h-100">
              <div class="sec-hero">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="badge badge-light text-danger font-weight-bold" style="border-radius:12px; padding:3px 9px;">
                    <i class="fas fa-user-shield mr-1"></i>ADMIN PRIVILEGES
                  </span>
                  <span class="badge badge-<?= count($highRiskAdmins) > 0 ? "warning" : "light" ?> font-weight-bold">
                    <?= count($highRiskAdmins) > 0 ? count($highRiskAdmins) . " UNKNOWN" : "SYSTEM ONLY" ?>
                  </span>
                </div>
                <h2 class="font-weight-bold mb-0"><?= count($adminApps) ?></h2>
                <small style="opacity:0.85;">Active Device Administrator Apps</small>
              </div>
              <div class="p-3">
                <div class="sec-kv">
                  <span class="sk">System Admins</span>
                  <span class="sv text-success"><?= count($adminApps) - count($highRiskAdmins) ?></span>
                </div>
                <div class="sec-kv">
                  <span class="sk">3rd-Party Admins</span>
                  <span class="sv <?= count($highRiskAdmins) > 0 ? "text-danger font-weight-bold" : "text-muted" ?>"><?= count($highRiskAdmins) ?></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 2: Mapped Packages -->
          <div class="col-md-4 mb-3 mb-md-0">
            <div class="sec-card h-100">
              <div class="sec-hero sec-hero-blue">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="badge badge-light text-primary font-weight-bold" style="border-radius:12px; padding:3px 9px;">
                    <i class="fas fa-key mr-1"></i>PERMISSIONS REGISTRY
                  </span>
                  <span class="badge badge-light font-weight-bold">CATALOGED</span>
                </div>
                <h2 class="font-weight-bold mb-0"><?= count($permAppsList) ?></h2>
                <small style="opacity:0.85;">Monitored App Packages</small>
              </div>
              <div class="p-3">
                <div class="sec-kv">
                  <span class="sk">System Packages</span>
                  <span class="sv"><?= count(array_filter($permAppsList, fn($p) => !empty($p["is_system_app"]))) ?></span>
                </div>
                <div class="sec-kv">
                  <span class="sk">User Packages</span>
                  <span class="sv"><?= count(array_filter($permAppsList, fn($p) => empty($p["is_system_app"]))) ?></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 3: Background Services -->
          <div class="col-md-4">
            <div class="sec-card h-100">
              <div class="sec-hero sec-hero-teal">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="badge badge-light text-dark font-weight-bold" style="border-radius:12px; padding:3px 9px;">
                    <i class="fas fa-cogs mr-1"></i>BACKGROUND PROCESSES
                  </span>
                  <span class="badge badge-light font-weight-bold">ACTIVE</span>
                </div>
                <h2 class="font-weight-bold mb-0"><?= count($runningSvcs) ?></h2>
                <small style="opacity:0.85;">Running Services at Snapshot</small>
              </div>
              <div class="p-3">
                <div class="sec-kv">
                  <span class="sk">Google / Framework</span>
                  <span class="sv"><?= count(array_filter($runningSvcs, fn($s) => str_contains(is_array($s)?($s["service"]??""):(string)$s, "google") || str_contains(is_array($s)?($s["service"]??""):(string)$s, "android"))) ?></span>
                </div>
                <div class="sec-kv">
                  <span class="sk">3rd-Party Services</span>
                  <span class="sv"><?= count(array_filter($runningSvcs, fn($s) => !str_contains(is_array($s)?($s["service"]??""):(string)$s, "google") && !str_contains(is_array($s)?($s["service"]??""):(string)$s, "android"))) ?></span>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Realtime Search and Control Toolbar -->
        <div class="sec-card p-3 mb-4">
          <div class="row align-items-center">
            <div class="col-md-6 mb-2 mb-md-0">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="text" id="secSearch" class="form-control border-left-0" placeholder="Filter admin packages, app names, or services...">
              </div>
            </div>
            <div class="col-md-6 text-md-right">
              <button type="button" class="btn btn-sm btn-outline-danger filter-sec-btn mr-1" data-filter="high-risk">
                <i class="fas fa-exclamation-triangle mr-1"></i>Sensitive / High-Risk Only
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary filter-sec-btn active" data-filter="all">
                Show All Items
              </button>
            </div>
          </div>
        </div>

        <!-- Main Security Data Cards / Tabs -->
        <div class="card card-danger card-outline card-outline-tabs shadow-sm mb-4" style="border-radius:8px;">
          <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="securityTabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active font-weight-bold" id="admin-tab" data-toggle="pill" href="#tab-admin" role="tab">
                  <i class="fas fa-user-shield mr-2 text-danger"></i>Device Administrators 
                  <span class="badge badge-danger ml-1"><?= count($adminApps) ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link font-weight-bold" id="permissions-tab" data-toggle="pill" href="#tab-permissions" role="tab">
                  <i class="fas fa-key mr-2 text-primary"></i>Permission Maps 
                  <span class="badge badge-primary ml-1"><?= count($permAppsList) ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link font-weight-bold" id="services-tab" data-toggle="pill" href="#tab-services" role="tab">
                  <i class="fas fa-cogs mr-2 text-info"></i>Background Services 
                  <span class="badge badge-info ml-1"><?= count($runningSvcs) ?></span>
                </a>
              </li>
            </ul>
          </div>
          
          <div class="card-body p-3">
            <div class="tab-content" id="securityTabsContent">
              
              <!-- TAB 1: DEVICE ADMINISTRATORS -->
              <div class="tab-pane fade show active" id="tab-admin" role="tabpanel">
                <?php if (count($highRiskAdmins) > 0): ?>
                  <div class="alert alert-danger shadow-sm d-flex align-items-center mb-3">
                    <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                    <div>
                      <strong>Security Warning:</strong> Detected <?= count($highRiskAdmins) ?> non-system Device Administrator(s). These applications hold complete administrative control over the device.
                    </div>
                  </div>
                <?php else: ?>
                  <div class="alert alert-success shadow-sm d-flex align-items-center mb-3">
                    <i class="fas fa-check-circle fa-2x mr-3"></i>
                    <div>
                      <strong>Administrator Audit Clean:</strong> All active Device Administrators belong to recognized system or core framework packages.
                    </div>
                  </div>
                <?php endif; ?>

                <?php if (empty($adminApps)): ?>
                  <div class="text-center py-5 text-muted">
                    <i class="fas fa-user-shield fa-3x mb-3 text-light"></i>
                    <p class="mb-0">No active device administrators registered.</p>
                  </div>
                <?php else: ?>
                  <div class="row">
                    <?php foreach ($adminApps as $adm): ?>
                      <div class="col-md-6 mb-3 sec-item" 
                           data-search="<?= esc(strtolower($adm["package"])) ?>" 
                           data-high-risk="<?= $adm["is_system"] ? "false" : "true" ?>">
                        <div class="sec-card p-3 h-100 border">
                          <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge badge-<?= $adm["is_system"] ? "secondary" : "danger" ?> px-2 py-1">
                              <i class="fas <?= $adm["is_system"] ? "fa-cog" : "fa-exclamation-circle" ?> mr-1"></i><?= esc($adm["type_label"]) ?>
                            </span>
                            <i class="fas fa-shield-alt fa-lg <?= $adm["is_system"] ? "text-secondary" : "text-danger" ?>"></i>
                          </div>
                          <strong class="text-dark d-block text-truncate" title="<?= esc($adm["package"]) ?>"><?= esc($adm["package"]) ?></strong>
                          <small class="text-muted d-block mt-1">Granted full Android DeviceAdmin receiver rights.</small>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

              <!-- TAB 2: PERMISSIONS REGISTRY ACCORDION -->
              <div class="tab-pane fade" id="tab-permissions" role="tabpanel">
                <?php if (empty($permAppsList)): ?>
                  <div class="text-center py-5 text-muted">
                    <i class="fas fa-key fa-3x mb-3 text-light"></i>
                    <p class="mb-0">No permission mapping entries recorded.</p>
                  </div>
                <?php else: ?>
                  <div class="accordion" id="permAccordion">
                    <?php $pIndex = 0; foreach ($permAppsList as $pApp): $pIndex++; 
                      $pkg = $pApp["package"] ?? "Unknown Package";
                      $label = $pApp["label"] ?? $pkg;
                      $granted = $pApp["granted_permissions"] ?? [];
                      $denied = $pApp["denied_permissions"] ?? [];
                      $isSys = !empty($pApp["is_system_app"]);
                      
                      $hasSensitive = false;
                      if (is_array($granted)) {
                        foreach ($granted as $gP) {
                          if (in_array($gP, $sensitivePermissions)) {
                            $hasSensitive = true;
                            break;
                          }
                        }
                      }
                    ?>
                      <div class="sec-card mb-2 border sec-item" 
                           data-search="<?= esc(strtolower($pkg . " " . $label)) ?>" 
                           data-high-risk="<?= $hasSensitive ? "true" : "false" ?>">
                        <div class="p-3 cursor-pointer d-flex align-items-center justify-content-between" 
                             data-toggle="collapse" 
                             data-target="#pCollapse_<?= $pIndex ?>">
                          <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <i class="fas fa-box text-primary mr-1"></i>
                            <strong class="text-dark"><?= esc($label) ?></strong>
                            <small class="text-muted"><code>(<?= esc($pkg) ?>)</code></small>
                            <?php if ($isSys): ?>
                              <span class="pill-badge pill-secondary">System App</span>
                            <?php endif; ?>
                          </div>
                          <div class="d-flex align-items-center" style="gap: 6px;">
                            <?php if ($hasSensitive): ?>
                              <span class="pill-badge pill-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Sensitive Access</span>
                            <?php endif; ?>
                            <span class="pill-badge pill-info">
                              Granted: <?= is_array($granted) ? count($granted) : 0 ?>
                            </span>
                            <i class="fas fa-chevron-down text-muted ml-2"></i>
                          </div>
                        </div>
                        <div id="pCollapse_<?= $pIndex ?>" class="collapse" data-parent="#permAccordion">
                          <div class="p-3 bg-light border-top">
                            <div class="row mb-2">
                              <div class="col-md-4"><small class="text-muted">Version Name:</small> <strong><?= esc($pApp["version_name"] ?? "N/A") ?></strong></div>
                              <div class="col-md-4"><small class="text-muted">Version Code:</small> <strong><?= esc($pApp["version_code"] ?? "N/A") ?></strong></div>
                              <div class="col-md-4"><small class="text-muted">SDK Target:</small> <strong><?= esc($pApp["target_sdk"] ?? "N/A") ?></strong> (Min: <?= esc($pApp["min_sdk"] ?? "N/A") ?>)</div>
                            </div>
                            
                            <hr class="my-2">
                            
                            <h6 class="font-weight-bold text-success mb-2"><i class="fas fa-check-circle mr-1"></i>Granted Permissions (<?= count($granted) ?>)</h6>
                            <?php if (!empty($granted) && is_array($granted)): ?>
                              <div class="d-flex flex-wrap" style="gap: 4px;">
                                <?php foreach ($granted as $gPerm): 
                                  $isSens = in_array($gPerm, $sensitivePermissions);
                                ?>
                                  <span class="pill-badge <?= $isSens ? "pill-danger" : "pill-success" ?>">
                                    <?php if ($isSens): ?><i class="fas fa-user-lock mr-1"></i><?php endif; ?>
                                    <?= esc($gPerm) ?>
                                  </span>
                                <?php endforeach; ?>
                              </div>
                            <?php else: ?>
                              <small class="text-muted">No permissions granted.</small>
                            <?php endif; ?>

                            <?php if (!empty($denied) && is_array($denied)): ?>
                              <h6 class="font-weight-bold text-secondary mt-3 mb-2"><i class="fas fa-times-circle mr-1"></i>Denied Permissions (<?= count($denied) ?>)</h6>
                              <div class="d-flex flex-wrap" style="gap: 4px;">
                                <?php foreach ($denied as $dPerm): ?>
                                  <span class="pill-badge pill-secondary" style="opacity:0.8;">
                                    <?= esc($dPerm) ?>
                                  </span>
                                <?php endforeach; ?>
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

              <!-- TAB 3: BACKGROUND SERVICES -->
              <div class="tab-pane fade" id="tab-services" role="tabpanel">
                <?php if (empty($runningSvcs)): ?>
                  <div class="text-center py-5 text-muted">
                    <i class="fas fa-cogs fa-3x mb-3 text-light"></i>
                    <p class="mb-0">No active background services cataloged.</p>
                  </div>
                <?php else: ?>
                  <div class="row">
                    <?php foreach ($runningSvcs as $svc): 
                      $svcStr = is_array($svc) ? ($svc["service"] ?? "") : (string)$svc;
                      $pkgStr = is_array($svc) ? ($svc["package"] ?? "") : "";
                      $labelStr = is_array($svc) ? ($svc["label"] ?? "") : "";
                      $procStr = is_array($svc) ? ($svc["process"] ?? "") : "";
                      
                      $shortName = strrpos($svcStr, ".") !== false ? substr($svcStr, strrpos($svcStr, ".") + 1) : $svcStr;
                      
                      $badgeClass = "pill-danger";
                      $badgeText = "THIRD PARTY";
                      if (str_contains($svcStr, "com.google")) {
                        $badgeClass = "pill-info";
                        $badgeText = "GOOGLE SERVICE";
                      } elseif (str_contains($svcStr, "android") || str_contains($svcStr, "systemui")) {
                        $badgeClass = "pill-secondary";
                        $badgeText = "SYSTEM SERVICE";
                      }
                    ?>
                      <div class="col-md-6 mb-3 sec-item" 
                           data-search="<?= esc(strtolower($svcStr . " " . $pkgStr . " " . $labelStr)) ?>" 
                           data-high-risk="<?= $badgeClass === "pill-danger" ? "true" : "false" ?>">
                        <div class="sec-card p-3 h-100 border">
                          <div class="d-flex align-items-center justify-content-between mb-2">
                            <strong class="text-dark text-truncate mr-2"><?= esc($shortName) ?></strong>
                            <span class="pill-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                          </div>
                          <?php if ($labelStr): ?>
                            <span class="pill-badge pill-secondary mb-2"><?= esc($labelStr) ?></span>
                          <?php endif; ?>
                          <small class="text-muted d-block font-monospace mb-1"><code><?= esc($svcStr) ?></code></small>
                          <?php if ($procStr && $procStr !== $pkgStr): ?>
                            <small class="text-muted">Process: <i><?= esc($procStr) ?></i></small>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="text-center py-5">
          <i class="fas fa-user-shield fa-4x text-muted mb-3"></i>
          <h5>No App Security snapshot uploaded yet</h5>
          <p class="text-muted">Security settings, admin apps, and services will display once device telemetry is captured.</p>
        </div>
      <?php endif; ?>

      <!-- Historical Snapshots Table with Deltas -->
      <?php if (!empty($historyList)): ?>
        <div class="sec-card p-3 mb-4">
          <h5 class="font-weight-bold mb-3"><i class="fas fa-history text-secondary mr-2"></i>Security Audit Snapshot History</h5>
          <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mb-0">
              <thead class="bg-light">
                <tr>
                  <th>Snapshot Timestamp</th>
                  <th>Device Admins</th>
                  <th>Mapped Packages</th>
                  <th>Running Services</th>
                  <th class="text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($historyList as $h): 
                  $isSel = isset($h["orig_idx"]) && $h["orig_idx"] === $selectedIdx;
                ?>
                  <tr id="row_<?= esc($h["id"]) ?>" class="<?= $isSel ? "table-primary font-weight-bold" : "" ?>">
                    <td>
                      <i class="far fa-clock text-muted mr-1"></i>
                      <?= esc(date("Y-m-d H:i:s", $h["extracted_at"] / 1000)) ?>
                      <?php if ($isSel): ?>
                        <span class="badge badge-primary ml-1">ACTIVE VIEW</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <strong><?= $h["a_count"] ?></strong>
                      <?php if ($h["delta_a"] > 0): ?>
                        <span class="pill-badge pill-danger">+<?= $h["delta_a"] ?>↑</span>
                      <?php elseif ($h["delta_a"] < 0): ?>
                        <span class="pill-badge pill-success"><?= $h["delta_a"] ?>↓</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <strong><?= $h["p_count"] ?></strong>
                      <?php if ($h["delta_p"] > 0): ?>
                        <span class="pill-badge pill-danger">+<?= $h["delta_p"] ?>↑</span>
                      <?php elseif ($h["delta_p"] < 0): ?>
                        <span class="pill-badge pill-success"><?= $h["delta_p"] ?>↓</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <strong><?= $h["s_count"] ?></strong>
                      <?php if ($h["delta_s"] > 0): ?>
                        <span class="pill-badge pill-danger">+<?= $h["delta_s"] ?>↑</span>
                      <?php elseif ($h["delta_s"] < 0): ?>
                        <span class="pill-badge pill-success"><?= $h["delta_s"] ?>↓</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-right">
                      <a href="?snapshot=<?= esc($h["orig_idx"] ?? 0) ?>" class="btn btn-xs btn-outline-primary mr-1">
                        <i class="fas fa-eye mr-1"></i>Inspect
                      </a>
                      <button class="btn btn-xs btn-danger btn-delete-row"
                              data-id="<?= esc($h["id"]) ?>"
                              data-url="<?= base_url("advanced/software/app_security/delete/" . esc($h["id"])) ?>">
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </section>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const secSearch = document.getElementById("secSearch");
    const filterBtns = document.querySelectorAll(".filter-sec-btn");
    let currentFilter = "all";

    function filterItems() {
        const query = secSearch.value.trim().toLowerCase();
        const items = document.querySelectorAll(".sec-item");

        items.forEach(item => {
            const searchData = item.getAttribute("data-search") || "";
            const isHighRisk = item.getAttribute("data-high-risk") === "true";

            const matchesSearch = !query || searchData.includes(query);
            const matchesRisk = currentFilter === "all" || (currentFilter === "high-risk" && isHighRisk);

            if (matchesSearch && matchesRisk) {
                item.classList.remove("d-none");
            } else {
                item.classList.add("d-none");
            }
        });
    }

    if (secSearch) {
        secSearch.addEventListener("keyup", filterItems);
    }

    filterBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            filterBtns.forEach(b => b.classList.remove("active"));
            this.classList.add("active");
            currentFilter = this.getAttribute("data-filter");
            filterItems();
        });
    });
});
</script>

<?= view("users/advanced/_adv_delete_script") ?>
