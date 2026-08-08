<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Group by device_id and keep only unique NFC snapshots
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
.nfc-card        { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.nfc-hero        { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; }
.nfc-kv          { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.nfc-kv:last-child { border-bottom: none; }
.nfc-kv .nk      { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.nfc-kv .nv      { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.nfc-badge       { border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
.section-label   { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .4px; }
.feat-pill       { display: inline-block; background: #e9ecef; border-radius: 10px; padding: 1px 7px; font-size: 10px; color: #495057; margin: 2px; font-family: monospace; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-credit-card text-primary mr-2"></i>NFC Profile</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active Snapshots: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Near Field Communication parameters, secure element availability, and features</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Consistent Security Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-shield-alt mr-2"></i>NFC &amp; Secure Element Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing built-in Near Field Communication hardware configurations. Monitoring chip availability, secure element properties, and active system service features tracks potential vectors for contact-less data extraction or credit-card transaction intercepts.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">NFC Status:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>NFC Available:</b> Identifies if physical near-field radio chips are present.</li>
              <li><b>Enabled State:</b> Tracks active NFC polling settings.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Secure element:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Secure NFC:</b> Flags system-level secure routing configs.</li>
              <li><b>Secure Element Support:</b> Validates dedicated hardware storage limits.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Radio Features:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Feature Matrix:</b> Maps system service descriptors linked to target NFC APIs.</li>
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
          <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No NFC Data Detected</h4>
          <p class="text-muted">NFC details will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $nfcAvail = !empty($r['nfc_available']);
          $nfcEnabled = !empty($r['nfc_enabled']);
          $nfcSupp = !empty($r['nfc_supported']);
          $secureNfc = !empty($r['nfc_secure_nfc']);
          $secureSupp = !empty($r['nfc_secure_supported']);
          
          // Parse JSON features list
          $features = $r['features'] ?? $r['features_json'] ?? [];
          if (is_string($features)) {
              $features = json_decode($features, true) ?: [];
          }
          
          // Separate NFC sub-features
          $readerMode = false;
          $cardEmulation = false;
          $p2p = false;
          foreach ($features as $f) {
              $fl = strtolower($f);
              if (str_contains($fl, 'reader') || str_contains($fl, 'tag')) $readerMode = true;
              if (str_contains($fl, 'hce') || str_contains($fl, 'card') || str_contains($fl, 'emulation')) $cardEmulation = true;
              if (str_contains($fl, 'p2p') || str_contains($fl, 'beam')) $p2p = true;
          }
          ?>

          <div class="col-md-6 col-lg-4 mb-4">
            <div class="nfc-card h-100">
              <!-- Hero -->
              <div class="nfc-hero">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div>
                    <span class="nfc-badge badge badge-light">
                      NFC Status
                    </span>
                  </div>
                  <span class="badge badge-<?= $nfcEnabled ? 'success' : 'secondary' ?> p-2">
                    <?= $nfcEnabled ? 'Enabled' : 'Disabled' ?>
                  </span>
                </div>
                <div class="font-weight-bold" style="font-size: 16px; letter-spacing:0.3px; line-height: 1.2;">
                  Near Field Communication
                </div>
                <div style="font-size: 11px; opacity: 0.85; margin-top: 2px;">
                  Device ID: <?= esc($r['device_id'] ?? '—') ?>
                </div>
              </div>

              <!-- Content Body -->
              <div class="p-3">
                <div class="section-label mb-2"><i class="fas fa-credit-card mr-1"></i>Hardware Capabilities</div>
                <div class="nfc-kv"><span class="nk">Physical Chip Present</span><span class="nv"><?= $nfcAvail ? 'Yes' : 'No' ?></span></div>
                <div class="nfc-kv"><span class="nk">NFC Service Supported</span><span class="nv"><?= $nfcSupp ? 'Yes' : 'No' ?></span></div>
                <div class="nfc-kv"><span class="nk">Secure NFC Routing</span><span class="nv"><?= $secureNfc ? '<span class="badge badge-success">Enabled</span>' : 'Disabled' ?></span></div>
                <div class="nfc-kv"><span class="nk">Secure Element Support</span><span class="nv"><?= $secureSupp ? 'Yes' : 'No' ?></span></div>

                <!-- Parsed sub-features checklist -->
                <?php if ($nfcAvail): ?>
                  <div class="section-label mt-3 mb-2"><i class="fas fa-tasks mr-1"></i>Capability Checklist</div>
                  <div class="nfc-kv"><span class="nk">Tag Reader / Writer Mode</span><span class="nv"><?= $readerMode ? '<span class="text-success"><i class="fas fa-check-circle"></i> Supported</span>' : '<span class="text-muted"><i class="fas fa-times-circle"></i> Unsupported</span>' ?></span></div>
                  <div class="nfc-kv"><span class="nk">Host Card Emulation (HCE)</span><span class="nv"><?= $cardEmulation ? '<span class="text-success"><i class="fas fa-check-circle"></i> Supported</span>' : '<span class="text-muted"><i class="fas fa-times-circle"></i> Unsupported</span>' ?></span></div>
                  <div class="nfc-kv"><span class="nk">P2P Beam Sharing</span><span class="nv"><?= $p2p ? '<span class="text-success"><i class="fas fa-check-circle"></i> Supported</span>' : '<span class="text-muted"><i class="fas fa-times-circle"></i> Unsupported</span>' ?></span></div>
                <?php endif; ?>

                <!-- Features list -->
                <?php if (!empty($features)): ?>
                  <div class="section-label mt-3 mb-2"><i class="fas fa-cogs mr-1"></i>System Declared Features (<?= count($features) ?>)</div>
                  <div style="max-height:100px; overflow-y:auto; padding:4px 0;">
                    <?php foreach ($features as $f): ?>
                      <span class="feat-pill"><?= esc($f) ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="section-label mt-3 mb-2"><i class="fas fa-cogs mr-1"></i>NFC Service Features</div>
                  <div class="text-muted small py-2 text-center" style="background:#f8f9fa; border:1px dashed #dee2e6; border-radius:4px;">
                    No additional features declared
                  </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-3">
                  <small class="text-muted" style="font-size:10px;">Extracted: <?= $ts ?></small>
                  <button class="btn btn-xs btn-outline-danger delete-row"
                    data-id="<?= $rid ?>"
                    data-url="<?= base_url('advanced/hardware/nfc/delete') ?>">
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