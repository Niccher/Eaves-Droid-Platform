<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
foreach ($rows as &$r) {
    $r['account_unique_key'] = ($r['device_id'] ?? 'default') . '_' . ($r['account_name'] ?? 'unknown');
}
unset($r);

helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'account_unique_key',
    ['account_name', 'account_type', 'account_label', 'total_count', 'summary_json', 'is_syncable', 'last_sync_time', 'last_sync_result', 'auth_token_type', 'features']
);
?>

<style>
.acc-card     { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.acc-hero     { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; position: relative; }
.acc-hero::after { content: ''; position: absolute; right: 15px; bottom: 15px; font-family: "Font Awesome 5 Free"; font-weight: 900; content: "\f2bd"; font-size: 54px; opacity: 0.05; }
.acc-kv       { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.acc-kv:last-child { border-bottom: none; }
.acc-kv .ak   { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.acc-kv .av   { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.pill-badge   { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; margin: 2px; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-user-circle text-info mr-2"></i>User Accounts</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Authenticated device accounts, synchronizations, authentication tokens, and scopes</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>
      
      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-key mr-2"></i>Identity &amp; Authentication Auditing</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing system-wide accounts detects if rogue WhatsApp profiles, unauthorized Google authentication configurations, or suspicious social identifiers have access to the device.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Account Info:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Identity Type:</b> Identifies account providers (Google, WhatsApp, Email).</li>
              <li><b>User Label:</b> Shows user-facing display names or account names.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Sync Telemetry:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Last Sync Time:</b> Audits when data was last uploaded to provider servers.</li>
              <li><b>Sync Results:</b> Verifies if sync operations are succeeding or failing.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Access Scope:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Auth Tokens:</b> Identifies specific oauth/credential token types.</li>
              <li><b>Feature Flags:</b> Maps account privileges active on the OS.</li>
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
          <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No User Accounts</h4>
          <p class="text-muted">Active accounts will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $r):
          $name = $r['account_name'] ?? 'Generic Account';
          $type = $r['account_type'] ?? '—';
          $label = $r['account_label'] ?? 'System account';
          $syncable = !empty($r['is_syncable']);
          $syncTime = $r['last_sync_time'] ? format_timestamp_display((int)$r['last_sync_time']) : '—';
          $syncResult = $r['last_sync_result'] ?? '—';
          $token = $r['auth_token_type'] ?? '—';
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $features = $r['features'] ?? [];
          if (is_string($features)) $features = json_decode($features, true) ?: [];

          $isGoogle = str_contains(strtolower($type), 'google');
          $isWhatsapp = str_contains(strtolower($type), 'whatsapp');
          ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="acc-card h-100">
              
              <!-- Hero section -->
              <div class="acc-hero" style="background: <?= $isGoogle ? 'linear-gradient(135deg, #ea4335 0%, #c5221f 100%)' : ($isWhatsapp ? 'linear-gradient(135deg, #25d366 0%, #128c7e 100%)' : 'linear-gradient(135deg, #6c757d 0%, #495057 100%)') ?>;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="badge badge-light text-dark font-weight-bold" style="border-radius:12px; padding:3px 9px;">
                    <i class="fas fa-user mr-1"></i><?= esc($label) ?>
                  </span>
                  <div>
                    <?php if ($syncable): ?>
                      <span class="badge badge-success font-weight-bold">SYNCABLE</span>
                    <?php else: ?>
                      <span class="badge badge-secondary font-weight-bold">LOCAL ONLY</span>
                    <?php endif; ?>
                  </div>
                </div>
                <h5 class="font-weight-bold mb-1 text-truncate" title="<?= esc($name) ?>"><?= esc($name) ?></h5>
                <small class="d-block" style="opacity:0.75; font-family:monospace;"><?= esc($type) ?></small>
              </div>

              <!-- Details section -->
              <div class="p-3">
                <div class="acc-kv">
                  <span class="ak">Last Sync</span>
                  <span class="av"><?= $syncTime ?></span>
                </div>
                <?php if ($syncResult !== '—' && $syncResult !== ''): ?>
                <div class="acc-kv">
                  <span class="ak">Sync Result</span>
                  <span class="av text-truncate" title="<?= esc($syncResult) ?>"><?= esc($syncResult) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($token !== '—' && $token !== ''): ?>
                <div class="acc-kv">
                  <span class="ak">Token Type</span>
                  <span class="av text-truncate" style="font-family:monospace; font-size:11px;" title="<?= esc($token) ?>"><?= esc($token) ?></span>
                </div>
                <?php endif; ?>
                
                <!-- Lists -->
                <?php if (!empty($features)): ?>
                <div class="mt-3 mb-1">
                  <span class="ak d-block mb-1" style="font-size:10px; font-weight:700; color:#6c757d; text-transform:uppercase;">Account Features</span>
                  <div>
                    <?php foreach ($features as $f): ?>
                      <span class="pill-badge bg-light border text-dark font-weight-normal"><?= esc($f) ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <div class="acc-kv mt-3">
                  <span class="ak">Extracted</span>
                  <span class="sv" style="font-size:11px;"><?= $ts ?></span>
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