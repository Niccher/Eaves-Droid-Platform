<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'device_id',
    ['adapter_name', 'adapter_address'],
    ['paired_devices'],
    'bt_address'
);
?>

<style>
.bt-card         { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.bt-hero         { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; }
.bt-kv           { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.bt-kv:last-child { border-bottom: none; }
.bt-kv .bk       { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.bt-kv .bv       { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.bt-badge        { border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
.section-label   { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .4px; }
.dev-list-box    { border: 1px solid #dee2e6; border-radius: 6px; background: #f8f9fa; max-height: 250px; overflow-y: auto; }
.dev-list-item   { border-bottom: 1px solid #e9ecef; padding: 10px; font-size: 12px; }
.dev-list-item:last-child { border-bottom: none; }
.uuid-pill       { display: inline-block; background: #e9ecef; border-radius: 6px; padding: 0 6px; font-size: 9px; font-family: monospace; color: #495057; margin: 1px; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fab fa-bluetooth-b text-primary mr-2"></i>Bluetooth Diagnostics</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active Snapshots: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Local radio controller state, hardware MAC mapping, and remote paired device histories</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Consistent Security Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fab fa-bluetooth-b mr-2"></i>Bluetooth Telemetry &amp; Radio Auditing</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Audits local Bluetooth controller properties and paired device histories. Tracking adapter states, service UUID maps, RSSI strengths, and bond histories highlights passive pairing exploits or tracking beacons.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Local Adapter:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Adapter State:</b> Verifies if local radio controllers are broadcasting.</li>
              <li><b>MAC Coordinates:</b> Confirms physical radio hardware addresses.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Paired Devices:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>UUID Maps:</b> Decodes services declared by paired accessories.</li>
              <li><b>Connection History:</b> Monitors timestamps, connection count stats, and byte traffic.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Telemetry Metrics:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>RSSI Strength:</b> Traces nearby radio proximity indicators.</li>
              <li><b>Bond State:</b> Checks encryption bonding parameters.</li>
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
          <i class="fab fa-bluetooth-b fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Bluetooth Data Detected</h4>
          <p class="text-muted">Bluetooth records will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          $isEnabled = !empty($r['is_enabled']);
          $paired = $r['paired_devices'] ?? [];
          ?>

          <div class="card bt-card mb-4">
            <!-- Hero -->
            <div class="bt-hero">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <h5 class="font-weight-bold mb-0"><i class="fab fa-bluetooth-b mr-2"></i><?= esc($r['adapter_name'] ?? 'Bluetooth Controller') ?></h5>
                  <small class="d-block mt-1" style="opacity: 0.85;">MAC: <code><?= esc($r['adapter_address'] ?? '—') ?></code></small>
                </div>
                <div class="text-right">
                  <span class="badge badge-<?= $isEnabled ? 'success' : 'secondary' ?> bt-badge p-2">
                    <?= $isEnabled ? 'Enabled' : 'Disabled' ?>
                  </span>
                  <span class="badge badge-light bt-badge ml-1"><i class="fas fa-link mr-1"></i><?= count($paired) ?> Paired</span>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="row">
                
                <!-- Col 1: Adapter Diagnostics -->
                <div class="col-md-4 mb-3">
                  <div class="section-label mb-2"><i class="fas fa-info-circle mr-1"></i>Adapter Configurations</div>
                  <div class="bt-kv"><span class="bk">Power State</span><span class="bv"><?= $isEnabled ? 'Enabled' : 'Disabled' ?></span></div>
                  <div class="bt-kv"><span class="bk">Adapter Name</span><span class="bv"><?= esc($r['adapter_name'] ?? '—') ?></span></div>
                  <div class="bt-kv"><span class="bk">Hardware Address</span><span class="bv"><code><?= esc($r['adapter_address'] ?? '—') ?></code></span></div>
                  <div class="bt-kv"><span class="bk">Total Bonded count</span><span class="bv"><span class="badge badge-primary"><?= (int)($r['paired_count'] ?? 0) ?></span></span></div>
                  <div class="bt-kv"><span class="bk">Extracted At</span><span class="bv" style="font-size:11px;"><?= $ts ?></span></div>
                </div>

                <!-- Col 2 & 3: Paired Devices list -->
                <div class="col-md-8 mb-3">
                  <div class="section-label mb-2"><i class="fas fa-list mr-1"></i>Paired &amp; Bonded Devices (<?= count($paired) ?>)</div>
                  
                  <?php if (!empty($paired)): ?>
                    <div class="dev-list-box">
                      <?php foreach ($paired as $d):
                        $connState = (int)($d['connection_state'] ?? 0);
                        $isLe = !empty($d['is_le']);
                        $lastSeen = $d['last_seen_time'] ?? null;
                        $lastConn = $d['last_connected_time'] ?? null;
                        
                        // Parse declared service UUIDs
                        $uuids = $d['uuids'] ?? '';
                        if (is_string($uuids)) {
                            $uuids = array_filter(array_map('trim', explode(',', $uuids)));
                        }
                        ?>
                        <div class="dev-list-item">
                          <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                              <strong><?= esc($d['bt_name'] ?? 'Unnamed Device') ?></strong>
                              <code class="text-muted ml-1" style="font-size:10.5px;"><?= esc($d['bt_address'] ?? '—') ?></code>
                              <?php if (!empty($d['alias']) && $d['alias'] !== $d['bt_name']): ?>
                                <small class="text-muted d-block">Alias: "<?= esc($d['alias']) ?>"</small>
                              <?php endif; ?>
                            </div>
                            <div class="text-right">
                              <span class="badge badge-<?= $connState > 0 ? 'success' : 'secondary' ?> py-1">
                                <?= $connState > 0 ? 'Connected' : 'Disconnected' ?>
                              </span>
                              <?php if ($isLe): ?>
                                <span class="badge badge-info py-1">BLE</span>
                              <?php endif; ?>
                            </div>
                          </div>

                          <div class="row mt-2" style="font-size: 11px;">
                            <div class="col-6">
                              <div>Type: <strong><?= esc($d['bt_type'] ?? 'Classic') ?></strong></div>
                              <div>Bond State: <strong><?= esc($d['bond_state'] ?? 'Bonded') ?></strong></div>
                              <?php if (isset($d['rssi']) && $d['rssi'] < 0): ?>
                                <div>RSSI Signal: <strong><?= esc($d['rssi']) ?> dBm</strong></div>
                              <?php endif; ?>
                              <?php if (isset($d['mtu'])): ?>
                                <div>MTU/ATT-MTU: <strong><?= esc($d['mtu']) ?> / <?= esc($d['att_mtu'] ?? '—') ?></strong></div>
                              <?php endif; ?>
                            </div>
                            
                            <div class="col-6 text-right">
                              <?php if (!empty($d['total_bytes_sent'])): ?>
                                <div>Data: <strong><?= esc(round($d['total_bytes_sent']/1024, 1)) ?> KB sent</strong></div>
                              <?php endif; ?>
                              <?php if ($lastConn): ?>
                                <div class="text-muted">Last Conn: <?= format_timestamp_display((int)$lastConn) ?></div>
                              <?php endif; ?>
                              <?php if ($lastSeen): ?>
                                <div class="text-muted">Last Seen: <?= format_timestamp_display((int)$lastSeen) ?></div>
                              <?php endif; ?>
                            </div>
                          </div>

                          <!-- Service UUID Tags -->
                          <?php if (!empty($uuids)): ?>
                            <div class="mt-2">
                              <span class="text-muted small" style="font-size: 9px; text-transform:uppercase;">Declared UUID Services:</span><br>
                              <?php foreach (array_slice($uuids, 0, 8) as $uuid): ?>
                                <span class="uuid-pill"><?= esc(strlen($uuid) > 8 ? substr($uuid, 0, 8) . '...' : $uuid) ?></span>
                              <?php endforeach; ?>
                              <?php if (count($uuids) > 8): ?>
                                <span class="uuid-pill">+<?= count($uuids) - 8 ?> more</span>
                              <?php endif; ?>
                            </div>
                          <?php endif; ?>

                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <div class="text-center text-muted py-5" style="background:#f8f9fa; border:1px dashed #dee2e6; border-radius:6px;">
                      <i class="fas fa-link-slash fa-2x mb-2 d-block"></i>
                      <span>No paired or bonded devices logged</span>
                    </div>
                  <?php endif; ?>
                </div>

              </div>

              <!-- Footer -->
              <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                <small class="text-muted">Row ID: <?= $rid ?></small>
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