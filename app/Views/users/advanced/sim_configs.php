<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
$rows = $data ?? [];
$seen    = [];
$unique  = [];
foreach ($rows as $r) {
    $key = !empty($r['sim_serial'])    ? 'serial_' . $r['sim_serial']
         : (!empty($r['subscriber_id']) ? 'sub_'    . $r['subscriber_id']
         : 'id_' . ($r['id'] ?? uniqid()));
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[]   = $r;
    }
}
$rows = $unique;

// SIM state decode
$stateColors = [
    'READY'           => 'success',
    'ABSENT'          => 'secondary',
    'NOT_READY'       => 'warning',
    'NETWORK_LOCKED'  => 'danger',
    'PIN_REQUIRED'    => 'warning',
    'PUK_REQUIRED'    => 'danger',
    'CARD_IO_ERROR'   => 'danger',
    'UNKNOWN'         => 'secondary',
];
$stateIcons = [
    'READY'          => 'fa-check-circle',
    'ABSENT'         => 'fa-times-circle',
    'NOT_READY'      => 'fa-exclamation-circle',
    'NETWORK_LOCKED' => 'fa-lock',
    'PIN_REQUIRED'   => 'fa-key',
    'PUK_REQUIRED'   => 'fa-key',
    'CARD_IO_ERROR'  => 'fa-exclamation-triangle',
    'UNKNOWN'        => 'fa-question-circle',
];
$phoneTypeIcons = [
    'GSM'   => 'fa-signal',
    'CDMA'  => 'fa-broadcast-tower',
    'SIP'   => 'fa-voicemail',
    'NONE'  => 'fa-ban',
];
?>

<style>
.sim-card     { border-radius:8px; background:#fff; box-shadow:0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); }
.sim-hero     { background:linear-gradient(135deg,#6c757d 0%,#5a6268 50%,#495057 100%); border-radius:8px 8px 0 0; padding:20px 22px 16px; color:#fff; position:relative; overflow:hidden; }
.sim-hero::before { content:''; position:absolute; top:-30px; right:-30px; width:120px; height:120px; border-radius:50%; background:rgba(255,255,255,.04); }
.sim-hero::after  { content:''; position:absolute; bottom:-20px; right:30px; width:80px; height:80px; border-radius:50%; background:rgba(255,255,255,.03); }
.sim-chip     { display:inline-block; width:38px; height:28px; background:linear-gradient(135deg,#ffd700,#c8a600); border-radius:4px; margin-right:12px; position:relative; flex-shrink:0; }
.sim-chip::after { content:''; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:20px; height:16px; border:1px solid rgba(0,0,0,.2); border-radius:2px; background:linear-gradient(135deg,#e8c200,#b89000); }
.sim-serial   { font-family:monospace; font-size:11px; color:rgba(255,255,255,0.8); letter-spacing:.5px; }
.sim-kv       { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-bottom:1px solid #f0f0f0; font-size:13px; }
.sim-kv:last-child  { border-bottom:none; }
.sim-kv .sk   { color:#6c757d; font-weight:600; font-size:11px; text-transform:uppercase; }
.sim-kv .sv   { font-weight:700; color:#343a40; }
.changed-alert { background:#fff3cd; border:1px solid #ffc107; border-radius:5px; padding:6px 10px; font-size:12px; font-weight:700; color:#856404; margin-top:8px; display:flex; align-items:center; gap:6px; }
.country-flag  { font-size:18px; }
.signal-bars   { display:inline-flex; align-items:flex-end; gap:2px; height:16px; }
.signal-bar    { width:4px; border-radius:1px; background:#28a745; }
.signal-bar.inactive { background:#dee2e6; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-sim-card text-primary mr-2"></i>SIM Card Configuration</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Unique SIMs: <b><?= count($rows) ?></b></span>
            <?php $changed = array_filter($rows, fn($r) => !empty($r['is_sim_changed'])); ?>
            <?php if (!empty($changed)): ?>
            <span class="badge badge-warning text-dark border p-2 ml-1"><i class="fas fa-exchange-alt mr-1"></i>SIM Change Detected!</span>
            <?php endif; ?>
          </div>
          <p class="text-muted mt-1 mb-0">SIM identity, operator, state, and tampering detection</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>
      
      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-sim-card mr-2"></i>SIM Card Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing target subscriber credentials (IMSI), chip serial configurations (ICCID), operator routing parameters, and active cellular hardware states.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Subscriber Identity:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>SIM Serial:</b> Audits ICCID serial sequences to detect swapped physical media.</li>
              <li><b>Subscriber ID:</b> Tracks subscriber IMSI parameters to identify carrier profile changes.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Network &amp; Carrier:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Operator:</b> Validates registered SIM carrier identification parameters.</li>
              <li><b>Country ISO:</b> Resolves mobile subscriber country codes.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Hardware Audit:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>State Tracking:</b> Monitors active state values (READY, PIN_REQUIRED, ABSENT).</li>
              <li><b>Tamper Detection:</b> Flags mismatch anomalies when new subscriber cards are introduced.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <?php if (empty($rows)): ?>
        <div class="text-center py-5">
          <i class="fas fa-sim-card fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No SIM Data</h4>
          <p class="text-muted">SIM configurations will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $idx => $r):
          $rid        = $r['id'] ?? 0;
          $serial     = $r['sim_serial'] ?? '—';
          $subId      = $r['subscriber_id'] ?? '—';
          $operator   = $r['sim_operator_name'] ?? '—';
          $country    = strtoupper($r['sim_country_iso'] ?? '');
          $state      = strtoupper($r['sim_state'] ?? 'UNKNOWN');
          $phoneType  = strtoupper($r['phone_type'] ?? 'GSM');
          $changed    = !empty($r['is_sim_changed']);
          $capturedTs = !empty($r['captured_at'])  ? format_timestamp_display((int)$r['captured_at'])  : '—';
          $extractedTs= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          $stateColor = $stateColors[$state] ?? 'secondary';
          $stateIcon  = $stateIcons[$state]  ?? 'fa-question-circle';
          $ptIcon     = $phoneTypeIcons[$phoneType] ?? 'fa-signal';
          $isReady    = $state === 'READY';
          ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="sim-card h-100">

              <!-- Hero -->
              <div class="sim-hero">
                <div class="d-flex align-items-center mb-3">
                  <div class="sim-chip"></div>
                  <div>
                    <div style="font-size:17px;font-weight:900;letter-spacing:.3px;">
                      <?= $operator !== '—' ? esc($operator) : 'Unknown Operator' ?>
                    </div>
                    <?php if ($country): ?>
                    <div style="font-size:12px;opacity:.7;"><i class="fas fa-globe mr-1"></i><?= esc($country) ?> &nbsp;<?= $phoneType ?></div>
                    <?php endif; ?>
                  </div>
                  <div class="ml-auto">
                    <span class="badge badge-<?= $stateColor ?> p-2">
                      <i class="fas <?= $stateIcon ?> mr-1"></i><?= $state ?>
                    </span>
                  </div>
                </div>
                <div class="sim-serial">ICCID: <?= esc($serial) ?></div>
                <?php if ($changed): ?>
                <div class="changed-alert mt-2">
                  <i class="fas fa-exclamation-triangle"></i> SIM Card Changed / Swapped Detected
                </div>
                <?php endif; ?>
              </div>

              <!-- Details -->
              <div class="p-3">
                <div class="sim-kv">
                  <span class="sk">Subscriber ID (IMSI)</span>
                  <span class="sv"><code style="font-size:11px;"><?= esc($subId) ?></code></span>
                </div>
                <div class="sim-kv">
                  <span class="sk">Country ISO</span>
                  <span class="sv"><?= $country ? esc($country) : '—' ?></span>
                </div>
                <div class="sim-kv">
                  <span class="sk">Phone Type</span>
                  <span class="sv"><i class="fas <?= $ptIcon ?> mr-1 text-primary"></i><?= esc($phoneType) ?></span>
                </div>
                <div class="sim-kv">
                  <span class="sk">SIM State</span>
                  <span class="sv"><span class="badge badge-<?= $stateColor ?>"><?= esc($state) ?></span></span>
                </div>
                <div class="sim-kv">
                  <span class="sk">SIM Changed</span>
                  <span class="sv">
                    <?php if ($changed): ?>
                      <span class="badge badge-danger"><i class="fas fa-exclamation mr-1"></i>YES — Alert</span>
                    <?php else: ?>
                      <span class="badge badge-success">No</span>
                    <?php endif; ?>
                  </span>
                </div>
                <div class="sim-kv">
                  <span class="sk">Captured</span>
                  <span class="sv" style="font-size:11px;"><?= $capturedTs ?></span>
                </div>
                <div class="sim-kv">
                  <span class="sk">Extracted</span>
                  <span class="sv" style="font-size:11px;"><?= $extractedTs ?></span>
                </div>

                <div class="mt-2 text-right">
                  <button class="btn btn-sm btn-outline-danger delete-row py-0"
                    data-id="<?= $rid ?>"
                    data-url="<?= base_url('advanced/hardware/sim-configs/delete') ?>">
                    <i class="fas fa-trash mr-1"></i>Remove
                  </button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        </div>

        <?php if (isset($pager)): ?>
          <div class="d-flex justify-content-end"><?= $pager->links('default', 'bootstrap5_full') ?></div>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
