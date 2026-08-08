<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
$sensors         = [];
$securityWarnings = [];

$typeLabels     = ['1' => 'Fingerprint', '2' => 'Face', '3' => 'Iris', '4' => 'Voice', '5' => 'Vein', 1 => 'Fingerprint', 2 => 'Face', 3 => 'Iris', 4 => 'Voice', 5 => 'Vein'];
$strengthLabels = ['1' => 'Weak', '2' => 'Standard', '3' => 'Strong', 1 => 'Weak', 2 => 'Standard', 3 => 'Strong', 'WEAK' => 'Weak', 'STRONG' => 'Strong', 'CONVENIENCE' => 'Standard'];
$strengthColors = ['Weak' => 'danger', 'Standard' => 'warning', 'Strong' => 'success'];

$seenTypes = []; // track sensor types already shown

foreach ($rows as $r) {
    $sensorId   = $r['sensor_id'] ?? 'Unknown';
    $typeId     = $r['sensor_type'] ?? 0;
    $typeName   = $typeLabels[$typeId] ?? 'Other';

    // Deduplicate: only show one card per unique sensor type
    $dedupeKey = $typeName . '_' . ($r['sensor_id'] ?? 'default');
    // If same type AND same sensor_id already shown, skip
    // If same type but different sensor_id (multi-sensor device), allow it
    $typeKey = strtolower($typeName);
    if (!isset($seenTypes[$typeKey])) {
        $seenTypes[$typeKey] = [];
    }
    $sensorKey = strtolower(trim((string)($r['sensor_id'] ?? 'default')));
    if (in_array($sensorKey, $seenTypes[$typeKey], true)) {
        continue; // duplicate sensor_id for this type — skip
    }
    $seenTypes[$typeKey][] = $sensorKey;

    $strengthRaw = $r['sensor_strength'] ?? 'Unknown';
    $strengthName = $strengthLabels[$strengthRaw] ?? ucfirst(strtolower($strengthRaw));
    $strengthColor = $strengthColors[$strengthName] ?? 'secondary';

    $enrollments = (int)($r['current_enrollments'] ?? 0);
    $failedAtt   = (int)($r['failed_attempts'] ?? 0);
    $lockoutMs   = (int)($r['lockout_time'] ?? 0);
    $lockoutPerm = !empty($r['lockout_permanent']);
    $isLockedOut = $lockoutPerm || $lockoutMs > 0;
    $reEnrollInvalidated = !empty($r['invalidated_by_reenrollment']);

    // Enrolled users JSON
    $enrolledUsers = [];
    if (!empty($r['enrolled_users'])) {
        $d = is_string($r['enrolled_users']) ? json_decode($r['enrolled_users'], true) : $r['enrolled_users'];
        $enrolledUsers = is_array($d) ? $d : [];
    }

    // Weak auth timeout: convert ms to human readable
    $weakTimeout = (int)($r['weak_auth_timeout_ms'] ?? 0);
    $weakTimeoutStr = $weakTimeout > 0
        ? ($weakTimeout >= 60000 ? round($weakTimeout/60000, 1) . ' min' : round($weakTimeout/1000, 1) . ' sec')
        : '—';

    // Lockout time human readable
    $lockoutStr = '—';
    if ($lockoutPerm) {
        $lockoutStr = 'Permanent Lockout';
    } elseif ($lockoutMs > 0) {
        $lockoutStr = $lockoutMs >= 60000 ? round($lockoutMs/60000, 1) . ' min remaining' : round($lockoutMs/1000, 0) . 's remaining';
    }

    if ($failedAtt > 0) {
        $securityWarnings[] = "Sensor <b>#{$sensorId} ({$typeName})</b> logged <b>{$failedAtt} failed</b> authentication attempts.";
    }
    if ($isLockedOut) {
        $securityWarnings[] = "Sensor <b>#{$sensorId} ({$typeName})</b> is currently in a <b>Lockout State</b> ({$lockoutStr}).";
    }
    if ($reEnrollInvalidated) {
        $securityWarnings[] = "Sensor <b>#{$sensorId} ({$typeName})</b> has <b>Re-enrollment Invalidation</b> active — crypto keys invalidated on new template.";
    }

    $sensors[] = [
        'id'              => $sensorId,
        'type'            => $typeName,
        'type_id'         => $typeId,
        'strength'        => $strengthName,
        'strength_color'  => $strengthColor,
        'vendor'          => $r['vendor'] ?? '—',
        'version'         => $r['version'] ?? '—',
        'template_ver'    => $r['template_version'] ?? '—',
        'enrolled'        => $enrollments,
        'max'             => (int)($r['max_enrollments'] ?? 5),
        'enrolled_users'  => $enrolledUsers,
        'has_enrollments' => !empty($r['has_enrollments']),
        'auth_id'         => $r['authenticator_id'] ?? 'N/A',
        'challenge_cnt'   => (int)($r['challenge_counter'] ?? 0),
        'failed'          => $failedAtt,
        'lockout_str'     => $lockoutStr,
        'lockout_perm'    => $lockoutPerm,
        'is_locked'       => $isLockedOut,
        'hw_token'        => $r['hardware_auth_token'] ?? '—',
        'crypto'          => !empty($r['crypto_object_supported']),
        're_enroll_inv'   => $reEnrollInvalidated,
        'weak_timeout'    => $weakTimeoutStr,
        'hw_detected'     => !empty($r['is_hardware_detected']),
        'hw_available'    => !empty($r['is_hardware_available']),
        'device_secure'   => !empty($r['device_secure']),
        'progress'        => (float)($r['enrollment_progress'] ?? 0),
        'extracted'       => !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—',
        'row_id'          => $r['id'] ?? 0,
    ];
}

?>

<style>
.sensor-card { border-radius:8px; background:#fff; box-shadow:0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); border-left:5px solid #6c757d; transition:transform .15s,box-shadow .15s; }
.sensor-card:hover { transform:translateY(-3px); box-shadow:0 4px 12px rgba(0,0,0,.15); }
.sensor-card.type-fingerprint { border-left-color:#007bff; }
.sensor-card.type-face { border-left-color:#28a745; }
.sensor-card.type-iris { border-left-color:#17a2b8; }
.sensor-card.type-voice { border-left-color:#6f42c1; }
.sensor-card.type-other { border-left-color:#fd7e14; }
.sec-section { background:#fff5f5; border:1px solid #f5c6cb; border-radius:6px; padding:10px 12px; margin-bottom:10px; }
.sec-section.ok { background:#f0fff4; border-color:#c3e6cb; }
.data-kv { display:flex; justify-content:space-between; align-items:center; padding:4px 0; border-bottom:1px solid #f0f0f0; font-size:13px; }
.data-kv:last-child { border-bottom:none; }
.data-kv .dk { color:#6c757d; font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.3px; }
.data-kv .dv { font-weight:600; color:#343a40; text-align:right; max-width:65%; word-break:break-all; }
.user-tag { background:#e9ecef; border-radius:12px; padding:2px 8px; font-size:11px; font-weight:600; color:#495057; margin:2px; display:inline-block; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2 align-items-center">
        <div class="col-sm-6">
          <h1 class="h3 mb-0 font-weight-bold text-dark">
            <i class="fas fa-fingerprint text-secondary mr-2"></i>Biometric Credential Audit
          </h1>
        </div>
        <div class="col-sm-6 text-right"><?= $nav_urls ?></div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-fingerprint mr-2"></i>Biometric Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Forensic monitoring of enrolled biometric templates (fingerprints, face IDs). Adding unauthorized biometrics is a common technique for physical device access without the owner's knowledge.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Enrollment Monitoring:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Enrolled Count:</b> Number of distinct templates. A sudden increase = template manipulation.</li>
              <li><b>Enrolled Users:</b> User IDs with registered biometrics.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Security Flags:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Challenge Counter:</b> Cryptographic operations issued — tracks auth event frequency.</li>
              <li><b>Re-enrollment Invalidation:</b> Whether adding new templates invalidates old crypto keys.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Lockout Telemetry:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li>Flags excessive failures that triggered PIN lockouts — shows manual extraction attempts.</li>
              <li><b>Weak Auth Timeout:</b> Time after which convenience biometrics require re-confirmation.</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Security Alerts -->
      <?php if (!empty($securityWarnings)): ?>
      <div class="alert alert-danger shadow-sm mb-4">
        <h5><i class="icon fas fa-shield-alt"></i> Security Warning: Biometric Anomalies Flagged</h5>
        <ul class="pl-3 mb-0" style="font-size:13.5px;">
          <?php foreach ($securityWarnings as $w): ?><li><?= $w ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <!-- Sensor Cards -->
      <div class="row">
        <?php if (empty($sensors)): ?>
          <div class="col-12 text-center py-5">
            <i class="fas fa-fingerprint fa-3x text-muted mb-3"></i>
            <h4 class="text-secondary">No Biometric Hardware Found</h4>
            <p class="text-muted">Biometric profile extraction logs will appear here once retrieved.</p>
          </div>
        <?php else: ?>
          <?php foreach ($sensors as $s):
            $tid = strtolower($s['type']);
            $cardClass = "type-{$tid}";
            $iconClass = $s['type_id'] == 1 ? 'fa-fingerprint' : ($s['type_id'] == 2 ? 'fa-user-astronaut' : ($s['type_id'] == 3 ? 'fa-eye' : ($s['type_id'] == 4 ? 'fa-microphone' : 'fa-key')));
            ?>
            <div class="col-xl-6 mb-4">
              <div class="card sensor-card <?= $cardClass ?> h-100">
                <div class="card-body p-3">

                  <!-- Header -->
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h5 class="font-weight-bold mb-0">
                        <i class="fas <?= $iconClass ?> text-primary mr-2"></i><?= esc($s['type']) ?> Sensor
                      </h5>
                      <small class="text-muted">
                        ID: <code><?= esc($s['id']) ?></code> &middot;
                        Vendor: <b><?= esc($s['vendor']) ?></b> &middot;
                        FW: <?= esc($s['version']) ?>
                      </small>
                    </div>
                    <div class="text-right">
                      <span class="badge badge-<?= $s['strength_color'] ?> px-2 py-1 d-block mb-1"><?= esc($s['strength']) ?> Auth</span>
                      <?php if ($s['is_locked']): ?>
                        <span class="badge badge-danger px-2 py-1">LOCKED OUT</span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Section 1: Identity -->
                  <div class="mb-3">
                    <span class="font-weight-bold text-secondary d-block mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Identity &amp; Version</span>
                    <div class="data-kv"><span class="dk">Template Version</span><span class="dv"><?= esc($s['template_ver']) ?></span></div>
                    <div class="data-kv">
                      <span class="dk">Authenticator ID</span>
                      <span class="dv" title="<?= esc($s['auth_id']) ?>">
                        <code style="font-size:11px;"><?= esc(strlen($s['auth_id']) > 14 ? substr($s['auth_id'], 0, 14).'…' : $s['auth_id']) ?></code>
                      </span>
                    </div>
                    <div class="data-kv">
                      <span class="dk">Hardware Auth Token</span>
                      <span class="dv" title="<?= esc($s['hw_token']) ?>">
                        <code style="font-size:11px;"><?= esc(strlen($s['hw_token']) > 14 ? substr($s['hw_token'], 0, 14).'…' : $s['hw_token']) ?></code>
                      </span>
                    </div>
                  </div>

                  <!-- Section 2: Enrollment -->
                  <div class="mb-3">
                    <span class="font-weight-bold text-secondary d-block mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Enrollment Status</span>
                    <div class="row align-items-center mb-2">
                      <div class="col-7">
                        <?php if ($s['enrolled'] > 0): ?>
                          <h3 class="font-weight-bold text-success mb-0"><?= $s['enrolled'] ?> <span class="text-muted" style="font-size:14px;">/ <?= $s['max'] ?> enrolled</span></h3>
                        <?php else: ?>
                          <h3 class="font-weight-bold text-secondary mb-0">None enrolled</h3>
                        <?php endif; ?>
                      </div>
                      <div class="col-5 text-right">
                        <div class="badge badge-light border p-2">
                          <div style="font-size:15px;font-weight:800;"><?= number_format($s['progress'], 1) ?>%</div>
                          <small class="text-muted d-block" style="font-size:9px;">ENROLLMENT PROGRESS</small>
                        </div>
                      </div>
                    </div>
                    <?php if ($s['progress'] > 0): ?>
                    <div class="progress progress-xs mb-2"><div class="progress-bar bg-info" style="width:<?= min(100, $s['progress']) ?>%"></div></div>
                    <?php endif; ?>
                    <?php if (!empty($s['enrolled_users'])): ?>
                      <div>
                        <small class="text-muted font-weight-bold">Enrolled User IDs:</small>
                        <div class="mt-1">
                          <?php foreach ($s['enrolled_users'] as $uid): ?>
                            <span class="user-tag"><?= esc(is_array($uid) ? json_encode($uid) : $uid) ?></span>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    <?php else: ?>
                      <small class="text-muted">No enrolled user IDs logged</small>
                    <?php endif; ?>
                  </div>

                  <!-- Section 3: Security Analysis -->
                  <div class="mb-3">
                    <span class="font-weight-bold text-secondary d-block mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;"><i class="fas fa-shield-alt mr-1"></i>Security Analysis</span>
                    <div class="<?= $s['failed'] > 0 || $s['is_locked'] ? 'sec-section' : 'sec-section ok' ?>">
                      <div class="data-kv">
                        <span class="dk">Failed Attempts</span>
                        <span class="dv <?= $s['failed'] > 0 ? 'text-danger' : 'text-success' ?>"><?= $s['failed'] > 0 ? $s['failed'].' attempts' : 'None' ?></span>
                      </div>
                      <div class="data-kv">
                        <span class="dk">Lockout Status</span>
                        <span class="dv"><?= $s['is_locked'] ? '<span class="badge badge-danger">'.$s['lockout_str'].'</span>' : '<span class="badge badge-success">Not Locked</span>' ?></span>
                      </div>
                      <div class="data-kv">
                        <span class="dk">Challenge Counter</span>
                        <span class="dv"><code><?= number_format($s['challenge_cnt']) ?></code> operations</span>
                      </div>
                      <div class="data-kv">
                        <span class="dk">Re-enrollment Invalidation</span>
                        <span class="dv"><span class="badge badge-<?= $s['re_enroll_inv'] ? 'warning text-dark' : 'secondary' ?>"><?= $s['re_enroll_inv'] ? 'ENABLED (secure)' : 'Disabled' ?></span></span>
                      </div>
                      <div class="data-kv">
                        <span class="dk">Weak Auth Timeout</span>
                        <span class="dv"><?= $s['weak_timeout'] ?></span>
                      </div>
                    </div>
                  </div>

                  <!-- Section 4: Hardware Status -->
                  <div class="border-top pt-2">
                    <span class="font-weight-bold text-secondary d-block mb-1" style="font-size:11px;">Hardware Status:</span>
                    <div class="d-flex flex-wrap">
                      <span class="badge badge-<?= $s['hw_detected'] ? 'success' : 'danger' ?> mr-1 mb-1">HW Detected: <?= $s['hw_detected'] ? 'Yes' : 'No' ?></span>
                      <span class="badge badge-<?= $s['hw_available'] ? 'success' : 'danger' ?> mr-1 mb-1">HW Available: <?= $s['hw_available'] ? 'Yes' : 'No' ?></span>
                      <span class="badge badge-<?= $s['device_secure'] ? 'success' : 'secondary' ?> mr-1 mb-1">Device Secure: <?= $s['device_secure'] ? 'Yes' : 'No' ?></span>
                      <span class="badge badge-<?= $s['crypto'] ? 'success' : 'secondary' ?> mr-1 mb-1">Crypto Object: <?= $s['crypto'] ? 'Yes' : 'No' ?></span>
                      <span class="badge badge-<?= $s['has_enrollments'] ? 'info' : 'secondary' ?> mr-1 mb-1">Has Enrollments: <?= $s['has_enrollments'] ? 'Yes' : 'No' ?></span>
                    </div>
                  </div>

                  <div class="border-top pt-2 mt-2 text-muted d-flex justify-content-between" style="font-size:11px;">
                    <span><i class="fas fa-key mr-1"></i>Sensor #<?= esc($s['id']) ?></span>
                    <span><i class="fas fa-clock mr-1"></i><?= $s['extracted'] ?></span>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if (isset($pager)): ?>
      <div class="d-flex justify-content-end"><?= $pager->links('default', 'bootstrap5_full') ?></div>
      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>