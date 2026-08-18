<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
foreach ($rows as &$r) {
    $r['provider_unique_key'] = ($r['device_id'] ?? 'default') . '_' . strtolower(trim((string)($r['authority'] ?? 'unknown')));
}
unset($r);

helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'provider_unique_key',
    ['authority', 'package_name', 'is_exported', 'read_permission', 'write_permission', 'grant_uri_permissions', 'is_syncable', 'is_multiprocess', 'init_order', 'flags', 'authorities', 'path_permissions', 'types', 'stream_types']
);

function parseProviderList($val): array {
    if (empty($val)) return [];
    if (is_array($val)) return $val;
    $decoded = json_decode($val, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
    return [];
}
?>

<style>
.prov-card     { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.prov-hero     { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; position: relative; }
.prov-hero::after { content: ''; position: absolute; right: 15px; bottom: 15px; font-family: "Font Awesome 5 Free"; font-weight: 900; content: "\f1c0"; font-size: 54px; opacity: 0.05; }
.prov-kv       { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.prov-kv:last-child { border-bottom: none; }
.prov-kv .pk   { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.prov-kv .pv   { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.pill-badge     { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; margin: 2px; }
.danger-box { background: #fff5f5; border: 1px solid #fecaca; color: #c53030; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; font-size: 12px; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-database text-info mr-2"></i>Content Providers</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Registered database authorities, inter-process communication permissions, and exported provider flags</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>
      
      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-shield-alt mr-2"></i>Content Provider Access Security</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Content Providers share databases between packages. Exported providers with weak read/write permissions are critical vulnerability points frequently targeted by sandbox escapes and data harvester malware.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-6 border-right">
            <b class="d-block mb-1">Exposure Scope:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Exported Providers:</b> Flags databases accessible to other applications.</li>
              <li><b>Authority Name:</b> The URI namespace used to request access (e.g. `content://contacts`).</li>
            </ul>
          </div>
          <div class="col-md-6 pl-md-3">
            <b class="d-block mb-1">Permission Policies:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Read/Write Permissions:</b> Required Android manifest keys to read or write provider data.</li>
              <li><b>Path Exceptions:</b> Custom folders within the database that override standard permissions.</li>
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
          <i class="fas fa-database fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Content Providers</h4>
          <p class="text-muted">Registered provider authorities will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $auth = $r['authority'] ?? '—';
          $pkg = $r['package_name'] ?? '—';
          $isExported = !empty($r['is_exported']);
          $readPerm = $r['read_permission'] ?? '—';
          $writePerm = $r['write_permission'] ?? '—';
          $grantUri = !empty($r['grant_uri_permissions']);
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $pathPerms = parseProviderList($r['path_permissions'] ?? '');
          $types = parseProviderList($r['types'] ?? '');
          $streamTypes = parseProviderList($r['stream_types'] ?? '');
          ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="prov-card h-100">
              
              <!-- Hero section -->
              <div class="prov-hero" style="background: <?= $isExported ? 'linear-gradient(135deg, #dc3545 0%, #bd2130 100%)' : 'linear-gradient(135deg, #6c757d 0%, #495057 100%)' ?>;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="badge badge-light text-dark font-weight-bold" style="border-radius:12px; padding:3px 9px;">
                    <i class="fas fa-tag mr-1 text-muted"></i><?= esc(basename(str_replace('.', '/', $pkg))) ?>
                  </span>
                  <div>
                    <?php if ($isExported): ?>
                      <span class="badge badge-warning text-dark font-weight-bold"><i class="fas fa-globe mr-1"></i>EXPORTED (PUBLIC)</span>
                    <?php else: ?>
                      <span class="badge badge-secondary font-weight-bold">INTERNAL ONLY</span>
                    <?php endif; ?>
                  </div>
                </div>
                <h5 class="font-weight-bold mb-1 text-truncate" title="content://<?= esc($auth) ?>">content://<?= esc($auth) ?></h5>
                <small class="d-block" style="opacity:0.75; font-family:monospace;"><?= esc($pkg) ?></small>
              </div>

              <!-- Details section -->
              <div class="p-3">
                
                <?php if ($isExported && $readPerm == '—' && $writePerm == '—'): ?>
                  <div class="danger-box">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <b>CRITICAL:</b> This provider is public and has **no read/write permissions** configured. Any app on the device can access its database!
                  </div>
                <?php endif; ?>

                <div class="prov-kv">
                  <span class="ak">Read Permission</span>
                  <span class="av"><?= $readPerm !== '—' ? '<code>' . esc($readPerm) . '</code>' : '<span class="text-danger font-weight-bold">NONE</span>' ?></span>
                </div>
                <div class="prov-kv">
                  <span class="ak">Write Permission</span>
                  <span class="av"><?= $writePerm !== '—' ? '<code>' . esc($writePerm) . '</code>' : '<span class="text-danger font-weight-bold">NONE</span>' ?></span>
                </div>
                <div class="prov-kv">
                  <span class="ak">Grant URI Perms</span>
                  <span class="av"><?= $grantUri ? '<span class="text-warning font-weight-bold">Yes</span>' : '<span class="text-muted">No</span>' ?></span>
                </div>
                <div class="prov-kv">
                  <span class="ak">Syncable / Multiprocess</span>
                  <span class="av"><?= !empty($r['is_syncable']) ? 'Yes' : 'No' ?> / <?= !empty($r['is_multiprocess']) ? 'Yes' : 'No' ?></span>
                </div>

                <!-- Path Exceptions -->
                <?php if (!empty($pathPerms)): ?>
                <div class="mt-3 mb-1">
                  <span class="ak d-block mb-1" style="font-size:10px; color:#6c757d;">Path Permissions Override</span>
                  <div>
                    <?php foreach ($pathPerms as $path => $perm): 
                      $permStr = is_array($perm) ? json_encode($perm) : (string)$perm;
                      ?>
                      <span class="pill-badge bg-light border text-dark font-weight-normal"><code><?= esc($path) ?></code> -> <code><?= esc($permStr) ?></code></span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <!-- MIME types -->
                <?php if (!empty($types)): ?>
                <div class="mt-2 mb-1">
                  <span class="ak d-block mb-1" style="font-size:10px; color:#6c757d;">Supported MIME Types</span>
                  <div>
                    <?php foreach (array_slice($types, 0, 4) as $t): ?>
                      <span class="pill-badge bg-info text-white" style="font-size: 9px;"><?= esc(basename($t)) ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <div class="prov-kv mt-3 border-top pt-2">
                  <span class="ak">Extracted</span>
                  <span class="sv" style="font-size:11px;"><?= $ts ?></span>
                </div>

                <div class="d-flex justify-content-end mt-3 border-top pt-2">
                  <button class="btn btn-sm btn-outline-danger delete-row"
                          data-id="<?= $rid ?>"
                          data-url="<?= base_url('advanced/software/content_providers/delete') ?>"
                          title="Delete this provider">
                    <i class="fas fa-trash mr-1"></i> Delete
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
