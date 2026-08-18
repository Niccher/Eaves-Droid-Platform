<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
foreach ($rows as &$r) {
    $r['email_unique_key'] = ($r['device_id'] ?? 'default') . '_' . strtolower(trim((string)($r['account_email'] ?? 'unknown')));
}
unset($r);

helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'email_unique_key',
    ['account_email', 'account_type', 'provider', 'is_primary', 'last_sync_time', 'folder']
);
?>

<style>
.email-card     { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.email-hero     { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; position: relative; }
.email-hero::after { content: ''; position: absolute; right: 15px; bottom: 15px; font-family: "Font Awesome 5 Free"; font-weight: 900; content: "\f0e0"; font-size: 54px; opacity: 0.05; }
.email-kv       { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.email-kv:last-child { border-bottom: none; }
.email-kv .ak   { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.email-kv .av   { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.pill-badge     { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; margin: 2px; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-envelope text-info mr-2"></i>Email Accounts</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Configured email accounts, synchronizations, folder properties, and providers</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>
      
      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-envelope-open-text mr-2"></i>Email Config Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing configured email interfaces, server endpoints, IMAP/POP providers, active sync windows, and credentials.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Mailboxes:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Active Accounts:</b> Logs client configurations (Gmail, Yahoo, custom domains).</li>
              <li><b>Default Sender:</b> Tracks designated primary messaging accounts.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Endpoints:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Provider Type:</b> Maps authentication mechanisms and mail transports.</li>
              <li><b>Folder Scopes:</b> Identifies cataloged directory trees (Inbox, Sent, Archive).</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Sync Latency:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Update Intervals:</b> Audits background push notifications and pull synchronizations.</li>
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
          <i class="fas fa-envelope-slash fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Email Accounts</h4>
          <p class="text-muted">Configured mail boxes will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $email = $r['account_email'] ?? '—';
          $type = $r['account_type'] ?? '—';
          $provider = $r['provider'] ?? 'Generic IMAP';
          $isPrimary = !empty($r['is_primary']);
          $folder = $r['folder'] ?? '—';
          $syncTime = $r['last_sync_time'] ? format_timestamp_display((int)$r['last_sync_time']) : '—';
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';

          $isGoogle = str_contains(strtolower($type), 'google') || str_contains(strtolower($provider), 'google') || str_contains(strtolower($provider), 'gmail');
          $isOutlook = str_contains(strtolower($type), 'exchange') || str_contains(strtolower($provider), 'outlook') || str_contains(strtolower($provider), 'microsoft');
          ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="email-card h-100">
              
              <!-- Hero section -->
              <div class="email-hero" style="background: <?= $isGoogle ? 'linear-gradient(135deg, #ea4335 0%, #c5221f 100%)' : ($isOutlook ? 'linear-gradient(135deg, #0078d4 0%, #005a9e 100%)' : 'linear-gradient(135deg, #6c757d 0%, #495057 100%)') ?>;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="badge badge-light text-dark font-weight-bold" style="border-radius:12px; padding:3px 9px;">
                    <i class="fas fa-server mr-1"></i><?= esc($provider) ?>
                  </span>
                  <div>
                    <?php if ($isPrimary): ?>
                      <span class="badge badge-warning text-dark font-weight-bold"><i class="fas fa-star mr-1"></i>PRIMARY</span>
                    <?php endif; ?>
                  </div>
                </div>
                <h5 class="font-weight-bold mb-1 text-truncate" title="<?= esc($email) ?>"><?= esc($email) ?></h5>
                <small class="d-block" style="opacity:0.75; font-family:monospace;"><?= esc($type) ?></small>
              </div>

              <!-- Details section -->
              <div class="p-3">
                <div class="email-kv">
                  <span class="ak">Active Folder</span>
                  <span class="av"><span class="badge badge-secondary"><?= esc($folder) ?></span></span>
                </div>
                <div class="email-kv">
                  <span class="ak">Last Synced</span>
                  <span class="av"><?= $syncTime ?></span>
                </div>
                
                <div class="email-kv mt-3">
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
<?php include __DIR__ . '/_adv_style.php'; ?>
