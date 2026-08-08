<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Group power rails by device_id and keep only unique rails
$seen = [];
$unique = [];
foreach ($rows as $r) {
    $railKey = ($r['device_id'] ?? 'default') . '_' . ($r['rail_name'] ?? 'unknown');
    $key = strtolower(trim($railKey));
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[] = $r;
    }
}
$rows = $unique;
?>

<style>
.rail-card       { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.rail-hero       { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; }
.rail-kv         { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.rail-kv:last-child { border-bottom: none; }
.rail-kv .rk     { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.rail-kv .rv     { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.rail-badge      { border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
.section-label   { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .4px; }
.consumers-pill  { display: inline-block; background: #e9ecef; border-radius: 10px; padding: 1px 7px; font-size: 10px; color: #495057; margin: 2px; font-family: monospace; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-bolt text-primary mr-2"></i>Power Rails</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active Rails: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Voltage regulators, power lines, consumption values, and hardware thermal sensors</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Consistent Security Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-shield-alt mr-2"></i>Power Management &amp; Hardware Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Audits physical voltage lines, current levels, and regulatory components. Monitoring active consumers, efficiency curves, and power rail configurations detects unauthorized hardware extensions or suspicious execution-induced power spikes.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Voltages &amp; Currents:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Active Load:</b> Tracks voltage (mV), current (mA), and power (mW) per rail.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Base Regulator specs:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Regulator Types:</b> Identifies BUCK, LDO, or external converters.</li>
              <li><b>Mode &amp; Efficiency:</b> Tracks power efficiency and thermal characteristics.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Hardware Consumers:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Active Consumers:</b> Maps hardware controllers (Camera, Audio, Wi-Fi, CPU) using specific power rails.</li>
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
          <i class="fas fa-bolt fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Power Rail Data Detected</h4>
          <p class="text-muted">Power rail snaps will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $isEnabled = !empty($r['is_enabled']);
          $volt = $r['voltage_mv'] ?? null;
          $current = $r['current_ma'] ?? null;
          $power = $r['power_mw'] ?? null;
          $temp = $r['temperature_c'] ?? null;
          
          $consumers = $r['consumer_names'] ?? [];
          if (is_string($consumers)) {
              $consumers = json_decode($consumers, true) ?: array_filter(array_map('trim', explode(',', $consumers)));
          }
          
          $constraints = $r['constraints'] ?? [];
          if (is_string($constraints)) {
              $constraints = json_decode($constraints, true) ?: [];
          }
          ?>

          <div class="col-md-6 col-lg-4 mb-4">
            <div class="rail-card h-100">
              <!-- Hero -->
              <div class="rail-hero">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div>
                    <span class="rail-badge badge badge-light">
                      <?= esc($r['rail_type'] ?? 'Regulator') ?>
                    </span>
                  </div>
                  <span class="badge badge-<?= $isEnabled ? 'success' : 'secondary' ?> p-2">
                    <?= $isEnabled ? 'Active' : 'Idle' ?>
                  </span>
                </div>
                <div class="font-weight-bold" style="font-size: 16px; letter-spacing:0.3px; line-height: 1.2;">
                  <?= esc($r['rail_name'] ?? '—') ?>
                </div>
                <div style="font-size: 11px; opacity: 0.85; margin-top: 2px;">
                  Regulator: <?= esc($r['regulator_type'] ?? '—') ?>
                </div>
              </div>

              <!-- Content Body -->
              <div class="p-3">
                <div class="section-label mb-2"><i class="fas fa-plug mr-1"></i>Electrical Telemetry</div>
                
                <?php if ($volt !== null): ?>
                  <div class="rail-kv"><span class="rk">Voltage (mV)</span><span class="rv"><?= esc($volt) ?> mV</span></div>
                <?php endif; ?>
                <?php if (!empty($r['voltage_min_mv']) || !empty($r['voltage_max_mv'])): ?>
                  <div class="rail-kv"><span class="rk">Operational Limits</span><span class="rv"><?= esc($r['voltage_min_mv'] ?? '—') ?> – <?= esc($r['voltage_max_mv'] ?? '—') ?> mV</span></div>
                <?php endif; ?>
                
                <?php if ($current !== null): ?>
                  <div class="rail-kv"><span class="rk">Current (mA)</span><span class="rv"><?= esc($current) ?> mA</span></div>
                <?php endif; ?>
                <?php if (!empty($r['current_max_ma'])): ?>
                  <div class="rail-kv"><span class="rk">Max Peak Current</span><span class="rv"><?= esc($r['current_max_ma']) ?> mA</span></div>
                <?php endif; ?>
                
                <?php if ($power !== null): ?>
                  <div class="rail-kv"><span class="rk">Active Power Load</span><span class="rv"><strong><?= esc($power) ?> mW</strong></span></div>
                <?php endif; ?>
                
                <?php if ($temp !== null): ?>
                  <div class="rail-kv"><span class="rk">Temperature</span><span class="rv"><?= esc($temp) ?> °C</span></div>
                <?php endif; ?>

                <?php if (!empty($r['mode']) || !empty($r['efficiency_percent'])): ?>
                  <div class="section-label mt-3 mb-2"><i class="fas fa-chart-line mr-1"></i>Efficiency &amp; Mode</div>
                  <div class="rail-kv"><span class="rk">Regulator Mode</span><span class="rv"><code><?= esc($r['mode'] ?? '—') ?></code></span></div>
                  <?php if (!empty($r['efficiency_percent'])): ?>
                    <div class="rail-kv"><span class="rk">Conversion Efficiency</span><span class="rv"><?= esc($r['efficiency_percent']) ?>%</span></div>
                  <?php endif; ?>
                <?php endif; ?>

                <!-- Enriched constraints block -->
                <?php if (!empty($constraints) && is_array($constraints)): ?>
                  <div class="section-label mt-3 mb-2"><i class="fas fa-balance-scale mr-1"></i>LDO Constraints</div>
                  <?php foreach ($constraints as $ck => $cv): ?>
                    <div class="rail-kv">
                      <span class="rk" style="font-size: 9px;"><?= esc(str_replace('_', ' ', $ck)) ?></span>
                      <span class="rv" style="font-size: 11px;"><?= esc(is_array($cv) ? json_encode($cv) : ($cv === true ? 'Yes' : ($cv === false ? 'No' : $cv))) ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>

                <!-- Active Consumers -->
                <?php if (!empty($consumers)): ?>
                  <div class="section-label mt-3 mb-1"><i class="fas fa-users mr-1"></i>Active Line Consumers (<?= count($consumers) ?>)</div>
                  <div style="max-height:80px; overflow-y:auto; padding:4px 0;">
                    <?php foreach ($consumers as $consumer): ?>
                      <span class="consumers-pill"><?= esc(is_array($consumer) ? json_encode($consumer) : $consumer) ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-3">
                  <small class="text-muted" style="font-size:10px;">Extracted: <?= $ts ?></small>
                  <button class="btn btn-xs btn-outline-danger delete-row"
                    data-id="<?= $rid ?>"
                    data-url="<?= base_url('advanced/hardware/power_rails/delete') ?>">
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