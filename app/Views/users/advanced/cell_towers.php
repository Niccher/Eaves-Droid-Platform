<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Group by device_id and show only the latest snapshot
$seen = [];
$unique = [];
foreach ($rows as $r) {
    $devId = $r['device_id'] ?? 'default';
    $key   = strtolower(trim((string)$devId));
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[]   = $r;
    }
}
$rows = $unique;
?>

<style>
.cell-card       { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.cell-hero       { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; }
.cell-kv         { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.cell-kv:last-child { border-bottom: none; }
.cell-kv .ck     { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.cell-kv .cv     { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.cell-badge      { border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
.section-label   { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .4px; }
.sig-bar-container { height: 8px; border-radius: 4px; background: #e9ecef; overflow: hidden; margin-top: 5px; }
.sig-bar-fill    { height: 100%; border-radius: 4px; transition: width .4s; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-broadcast-tower text-primary mr-2"></i>Cell Towers</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active Snapshots: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">GSM, LTE, and 5G NR transceiver towers, signal telemetry, and mobile carrier operator metadata</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Consistent Security Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-shield-alt mr-2"></i>Cellular Tower Telemetry &amp; Baseband Auditing</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Forensic inspection of neighboring base stations. Auditing Cell IDs (CID), Area Codes (LAC/TAC), and signal levels (RSRP/RSSI) enables the detection of malicious IMSI catchers (Stingrays) or unauthorized cellular tracking arrays.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Cell Identifiers:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>CID / NCI:</b> Unique sector transmitter coordinates linked to physical base station locations.</li>
              <li><b>LAC / TAC:</b> Regional location grouping boundaries used to route incoming traffic.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Signal Metrics:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>RSRP &amp; RSRQ:</b> Evaluates signal power and reception quality levels. Anomalous high signals can flag fake local transceiver arrays.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Operator Metadata:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>MCC / MNC:</b> Country and Mobile Operator codes verifying target carrier authenticity.</li>
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
          <i class="fas fa-broadcast-tower fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Cell Tower Data Detected</h4>
          <p class="text-muted">Cell tower snaps will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $type = $r['tower_type'] ?? 'Unknown';
          $cid = $r['cid'] ?? $r['nci'] ?? '—';
          $operator = $r['network_operator_name'] ?? $r['network_operator'] ?? '—';
          $isReg = !empty($r['is_registered']);
          
          $rsrp = $r['rsrp'] ?? null;
          $rsrq = $r['rsrq'] ?? null;
          $rssnr = $r['rssnr'] ?? null;
          $rssi = $r['rssi'] ?? null;
          
          // Signal bar calculation (based on typical LTE RSRP range: -140 to -44 dBm)
          $sigPct = 0;
          $sigColor = '#6c757d';
          if ($rsrp !== null) {
              $sigPct = min(100, max(0, round((($rsrp + 140) / 96) * 100)));
              if ($rsrp > -80) $sigColor = '#28a745'; // Good
              elseif ($rsrp > -95) $sigColor = '#007bff'; // Fair
              elseif ($rsrp > -110) $sigColor = '#ffc107'; // Poor
              else $sigColor = '#dc3545'; // Dead/Extremely weak
          }
          
          // Phone Type Decode
          $phoneTypeMap = [0 => 'None', 1 => 'GSM', 2 => 'CDMA', 3 => 'SIP'];
          $phoneType = $phoneTypeMap[$r['phone_type'] ?? ''] ?? 'Unknown';
          
          // SIM State Decode
          $simStateMap = [0 => 'Unknown', 1 => 'Absent', 2 => 'PIN Required', 3 => 'PUK Required', 4 => 'Network Locked', 5 => 'Ready'];
          $simState = $simStateMap[$r['sim_state'] ?? ''] ?? 'Unknown';
          ?>

          <div class="card cell-card mb-4">
            <!-- Hero -->
            <div class="cell-hero">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <h5 class="font-weight-bold mb-0"><i class="fas fa-broadcast-tower mr-2"></i>Cellular Tower Snapshot</h5>
                  <small class="d-block mt-1" style="opacity: 0.85;">Device ID: <?= esc($r['device_id'] ?? '—') ?></small>
                </div>
                <div class="text-right">
                  <span class="badge badge-light cell-badge"><i class="fas fa-network-wired mr-1"></i><?= esc($type) ?></span>
                  <span class="badge badge-<?= $isReg ? 'success' : 'secondary' ?> cell-badge ml-1">
                    <?= $isReg ? 'Registered' : 'Neighboring' ?>
                  </span>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="row">
                
                <!-- Col 1: Tower Identifiers -->
                <div class="col-md-4 mb-3">
                  <div class="section-label mb-2"><i class="fas fa-map-marker-alt mr-1"></i>Transceiver Identification</div>
                  <div class="ctx-kv"><span class="ck">Cell ID (CID)</span><span class="cv"><code><?= esc($cid) ?></code></span></div>
                  <?php if (!empty($r['nci'])): ?>
                    <div class="ctx-kv"><span class="ck">NR Cell Identity (NCI)</span><span class="cv"><code><?= esc($r['nci']) ?></code></span></div>
                  <?php endif; ?>
                  <div class="ctx-kv"><span class="ck">LAC / TAC</span><span class="cv"><code><?= esc($r['lac'] ?? $r['tac'] ?? '—') ?></code></span></div>
                  <div class="ctx-kv"><span class="ck">Physical Cell ID (PCI)</span><span class="cv"><code><?= esc($r['pci'] ?? '—') ?></code></span></div>
                  <div class="ctx-kv"><span class="ck">MCC (Country Code)</span><span class="cv"><code><?= esc($r['mcc'] ?? '—') ?></code></span></div>
                  <div class="ctx-kv"><span class="ck">MNC (Network Code)</span><span class="cv"><code><?= esc($r['mnc'] ?? '—') ?></code></span></div>
                  <?php if (!empty($r['bandwidth'])): ?>
                    <div class="ctx-kv"><span class="ck">Bandwidth</span><span class="cv"><?= esc($r['bandwidth']) ?> kHz</span></div>
                  <?php endif; ?>
                </div>

                <!-- Col 2: Signal Strengths -->
                <div class="col-md-4 mb-3">
                  <div class="section-label mb-2"><i class="fas fa-signal mr-1"></i>Signal Metrics</div>
                  
                  <?php if ($rsrp !== null): ?>
                    <div class="mb-3">
                      <div class="d-flex justify-content-between" style="font-size: 12px; font-weight: 700;">
                        <span class="text-muted">RSRP Strength</span>
                        <span><?= esc($rsrp) ?> dBm</span>
                      </div>
                      <div class="sig-bar-container">
                        <div class="sig-bar-fill" style="width: <?= $sigPct ?>%; background: <?= $sigColor ?>;"></div>
                      </div>
                    </div>
                  <?php endif; ?>

                  <?php if ($rsrq !== null): ?>
                    <div class="ctx-kv"><span class="ck">RSRQ (Quality)</span><span class="cv"><?= esc($rsrq) ?> dB</span></div>
                  <?php endif; ?>
                  <?php if ($rssnr !== null): ?>
                    <div class="ctx-kv"><span class="ck">RSSNR (Signal-to-Noise)</span><span class="cv"><?= esc($rssnr) ?> dB</span></div>
                  <?php endif; ?>
                  <?php if ($rssi !== null): ?>
                    <div class="ctx-kv"><span class="ck">RSSI</span><span class="cv"><?= esc($rssi) ?> dBm</span></div>
                  <?php endif; ?>
                  <?php if (!empty($r['cqi'])): ?>
                    <div class="ctx-kv"><span class="ck">CQI Index</span><span class="cv"><?= esc($r['cqi']) ?></span></div>
                  <?php endif; ?>
                  <?php if (!empty($r['asu_level'])): ?>
                    <div class="ctx-kv"><span class="ck">ASU Level</span><span class="cv"><?= esc($r['asu_level']) ?></span></div>
                  <?php endif; ?>
                </div>

                <!-- Col 3: Carrier & SIM State -->
                <div class="col-md-4 mb-3">
                  <div class="section-label mb-2"><i class="fas fa-building mr-1"></i>Operator &amp; Baseband State</div>
                  <div class="ctx-kv"><span class="ck">Operator Name</span><span class="cv"><?= esc($operator) ?></span></div>
                  <div class="ctx-kv"><span class="ck">Operator Code</span><span class="cv"><code><?= esc($r['network_operator'] ?? '—') ?></code></span></div>
                  <div class="ctx-kv"><span class="ck">Phone Radio Type</span><span class="cv"><?= esc($phoneType) ?></span></div>
                  <div class="ctx-kv"><span class="ck">SIM State</span><span class="cv"><?= esc($simState) ?></span></div>
                  <?php if (!empty($r['system_id'])): ?>
                    <div class="ctx-kv"><span class="ck">System ID</span><span class="cv"><code><?= esc($r['system_id']) ?></code></span></div>
                  <?php endif; ?>
                </div>

              </div>

              <!-- Footer -->
              <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                <small class="text-muted">Row ID: <?= $rid ?> · Extracted: <?= $ts ?></small>
                <button class="btn btn-sm btn-outline-danger delete-row py-0"
                  data-id="<?= $rid ?>"
                  data-url="<?= base_url('advanced/hardware/cell_towers/delete') ?>">
                  <i class="fas fa-trash mr-1"></i>Remove Snapshot
                </button>
              </div>
            </div>
          </div>

        <?php endforeach; ?>

        <?php if (isset($pager)): ?>
          <div class="d-flex justify-content-end"><?= $pager->links('default', 'bootstrap5_full') ?></div>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>