<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
foreach ($rows as &$r) {
    $r['ime_unique_key'] = ($r['device_id'] ?? 'default') . '_' . ($r['ime_package'] ?? 'unknown');
}
unset($r);

helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'ime_unique_key',
    ['ime_label', 'ime_package', 'is_enabled', 'is_default', 'is_system_ime', 'is_auxiliary', 'supports_switching_to_next_input_method', 'subtypes']
);

function parseImeSubtypes($val): array {
    if (empty($val)) return [];
    if (is_array($val)) return $val;
    $decoded = json_decode($val, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
    return [];
}
?>

<style>
.keyboard-card     { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.keyboard-hero     { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; position: relative; }
.keyboard-hero::after { content: ''; position: absolute; right: 15px; bottom: 15px; font-family: "Font Awesome 5 Free"; font-weight: 900; content: "\f11c"; font-size: 54px; opacity: 0.05; }
.keyboard-hero.system-ime { background: linear-gradient(135deg, #5a6268 0%, #474f56 60%, #343a40 100%); }
.keyboard-kv       { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.keyboard-kv:last-child { border-bottom: none; }
.keyboard-kv .kk   { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.keyboard-kv .kv   { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.pill-badge        { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; margin: 2px; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-keyboard text-info mr-2"></i>Keyboard Input</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Installed keyboards, default input method editors, and active locales</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>
      
      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-keyboard mr-2"></i>Keyboard IME Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Keyboard configurations monitor active keystroke routing. Third-party keyboards require validation to detect key-logging spyware engines.</p>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <?php if (empty($rows)): ?>
        <div class="text-center py-5">
          <i class="fas fa-keyboard fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Keyboard Configs</h4>
          <p class="text-muted">Registered IMEs will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $idx => $r):
          $rid = $r['id'] ?? 0;
          $label = $r['ime_label'] ?? 'Unknown Keyboard';
          $pkg = $r['ime_package'] ?? '—';
          $isDefault = !empty($r['is_default']);
          $isEnabled = !empty($r['is_enabled']);
          $isSystem = !empty($r['is_system_ime']);
          $isAuxiliary = !empty($r['is_auxiliary']);
          $canSwitch = !empty($r['supports_switching_to_next_input_method']);
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $subtypes = parseImeSubtypes($r['subtypes'] ?? '');
          ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="keyboard-card h-100">
              
              <!-- Hero section -->
              <div class="keyboard-hero <?= $isSystem ? 'system-ime' : '' ?>">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="badge badge-light text-dark font-weight-bold" style="border-radius:12px; padding:3px 9px;">
                    <i class="fas fa-keyboard mr-1"></i><?= $isSystem ? 'SYSTEM' : 'USER' ?>
                  </span>
                  <div>
                    <?php if ($isDefault): ?>
                      <span class="badge badge-warning text-dark font-weight-bold"><i class="fas fa-star mr-1"></i>DEFAULT</span>
                    <?php endif; ?>
                    <?php if ($isEnabled): ?>
                      <span class="badge badge-success font-weight-bold">ENABLED</span>
                    <?php endif; ?>
                  </div>
                </div>
                <h5 class="font-weight-bold mb-1 text-truncate" title="<?= esc($label) ?>"><?= esc($label) ?></h5>
                <small class="d-block" style="opacity:0.75; font-family:monospace;"><?= esc($pkg) ?></small>
              </div>

              <!-- Details section -->
              <div class="p-3">
                <div class="keyboard-kv">
                  <span class="kk">Package</span>
                  <span class="av text-truncate" title="<?= esc($pkg) ?>"><code><?= esc($pkg) ?></code></span>
                </div>
                <div class="keyboard-kv">
                  <span class="kk">Auxiliary Layout</span>
                  <span class="av"><?= $isAuxiliary ? '<span class="text-warning font-weight-bold">Yes</span>' : 'No' ?></span>
                </div>
                <div class="keyboard-kv">
                  <span class="kk">Supports Switching</span>
                  <span class="av"><?= $canSwitch ? '<span class="text-success font-weight-bold">Yes</span>' : 'No' ?></span>
                </div>

                <!-- Subtypes -->
                <?php if (!empty($subtypes)): ?>
                <div class="mt-3 mb-1">
                  <span class="kk d-block mb-1" style="font-size:10px; color:#6c757d;">Subtypes Locales (<?= count($subtypes) ?>)</span>
                  <div>
                    <?php foreach (array_slice($subtypes, 0, 4) as $st): 
                      $mode = $st['mode'] ?? 'keyboard';
                      $locale = $st['locale'] ?? 'N/A';
                      ?>
                      <span class="pill-badge bg-light border text-dark font-weight-normal"><?= esc($locale) ?> (<?= esc($mode) ?>)</span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>
                
                <div class="keyboard-kv mt-3 border-top pt-2">
                  <span class="kk">Extracted</span>
                  <span class="sv" style="font-size:11px;"><?= $ts ?></span>
                </div>

                <div class="d-flex justify-content-end mt-3 border-top pt-2">
                  <button class="btn btn-sm btn-outline-danger delete-row"
                          data-id="<?= $rid ?>"
                          data-url="<?= base_url('advanced/software/keyboard_input/delete') ?>"
                          title="Delete this keyboard configuration">
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
