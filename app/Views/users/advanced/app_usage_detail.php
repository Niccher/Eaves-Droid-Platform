<?php
/** @var array $rows @var int $total @var object $pager @var string $nav_urls @var string $package_name @var array $summary @var array $sessions @var string $back_url */
$appName = $summary['app_name'] ?? $package_name;
$totalMs = (int) ($summary['foreground_time_ms'] ?? 0);
$totalHrs = round($totalMs / 3600000, 2);
$lastUsed = $summary['last_used_display'] ?? '—';
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent p-0 mb-2">
                            <li class="breadcrumb-item"><a href="<?= esc($back_url) ?>">App Usage</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($appName) ?></li>
                        </ol>
                    </nav>
                    <h1 class="h2 mb-0"><i class="fas fa-chart-pie text-info mr-2"></i><?= htmlspecialchars($appName) ?></h1>
                    <p class="text-muted mt-1 mb-0"><code><?= htmlspecialchars($package_name) ?></code></p>
                </div>
                <div class="col-lg-4 text-right">
                    <?= $nav_urls ?>
                    <a href="<?= esc($back_url) ?>" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left mr-1"></i> Back</a>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row mb-3 equal-info-boxes">
                <div class="col-md-3">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fas fa-stopwatch"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Screen Time</span>
                            <span class="info-box-number"><?php if ($totalHrs >= 24): ?><?= round($totalHrs / 24, 1) ?> Days<br><small><?= $totalHrs ?> h</small><?php else: ?><?= $totalHrs ?> h<?php endif; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-history"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Last Used</span>
                            <span class="info-box-number" style="font-size:1rem;"><?= $lastUsed ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Snapshots</span>
                            <span class="info-box-number"><?= (int) ($summary['snapshot_count'] ?? $total) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-cog"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">App Type</span>
                            <span class="info-box-number" style="font-size:1rem;"><?= !empty($summary['is_system_app']) ? 'System' : 'User' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Callout Card explaining calculations -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
                <h5 class="font-weight-bold text-info"><i class="fas fa-calculator mr-2"></i>Usage Calculations &amp; Snapshot Metrics</h5>
                <p class="text-secondary mb-2" style="font-size:14px;">Detailed breakdown of screen time counters and calculated background idle durations between extraction snapshots.</p>
                <div class="row" style="font-size:12px;">
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">Screen Time (Cumulative):</b>
                        <span class="text-muted">Direct reading of OS <code>foreground_time_ms</code> accumulated since device boot/reset. Represents total active display time.</span>
                    </div>
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">Last Used Timestamp:</b>
                        <span class="text-muted">Exact system clock time when the application was last active in the foreground prior to extraction.</span>
                    </div>
                    <div class="col-md-4">
                        <b class="d-block mb-1">Background Time:</b>
                        <span class="text-muted">Calculated as total wall-clock time elapsed between snapshot extractions minus active foreground screen time delta.</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list mr-2"></i>Usage Snapshots <small class="text-muted ml-2"><?= count($rows) ?> on this page</small></h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th><i class="fas fa-stopwatch mr-1"></i>Screen Time</th>
                                        <th><i class="fas fa-history mr-1"></i>Last Used</th>
                                        <th><i class="fas fa-moon mr-1"></i>Background Time</th>
                                        <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No snapshots for this app.</td></tr>
                                    <?php else: foreach ($rows as $r): ?>
                                        <?php
                                            $ms  = $r['foreground_time_ms'] ?? 0;
                                            $hrs = round($ms / 3600000, 2);
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold"><?= $hrs ?> h</span>
                                                <?php if ($hrs >= 24): ?><span class="badge badge-info ml-1"><?= round($hrs / 24, 1) ?> Days</span><?php endif; ?>
                                                <small class="text-muted d-block"><?= number_format($ms) ?> ms</small>
                                            </td>
                                            <td><small><?= esc($r['last_used_display'] ?? '—') ?></small></td>
                                            <td><?php
                                                $bgMs = (int) ($r['background_time_ms'] ?? 0);
                                                if ($bgMs >= 3600000):
                                                    echo round($bgMs / 3600000, 1) . ' <small>h</small>';
                                                elseif ($bgMs >= 60000):
                                                    echo round($bgMs / 60000, 1) . ' <small>min</small>';
                                                else:
                                                    echo round($bgMs / 1000, 1) . ' <small>s</small>';
                                                endif;
                                            ?></td>
                                            <td><i class="fas fa-clock text-muted mr-1"></i><small><?= esc($r['extracted_display'] ?? '—') ?></small></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-app-usage"
                                                        data-id="<?= $r['id'] ?? '' ?>"
                                                        title="Delete this snapshot">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<style>
.equal-info-boxes .info-box { min-height: 110px; }
</style>
<script>
var base_url = function(path) { return '<?= base_url() ?>' + path; };
var CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
var CSRF_TOKEN_HASH = '<?= csrf_hash() ?>';
var DELETE_SECTION = <?= json_encode($appName ?? '') ?>;
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-app-usage').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            if (!id) return;
            var section = this.getAttribute('data-delete-title') || DELETE_SECTION || 'this snapshot';
            Swal.fire({
                title: 'Delete this Entry?',
                html: 'This will permanently delete the <strong>"' + section + '"</strong> app usage snapshot and all of its associated data.'
                   + '<br><span class="text-danger mt-1 d-inline-block"><i class="fas fa-exclamation-triangle mr-1"></i>This action cannot be undone.</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: '<i class="fas fa-trash mr-1"></i> Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var postData = new URLSearchParams();
                    postData.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);
                    fetch(base_url('advanced/software/app-usage/delete/' + id), {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: postData
                    })
                        .then(function(r) { return r.json(); })
                        .then(function(resp) {
                            if (resp.success) {
                                Swal.fire('Deleted!', 'The "' + section + '" snapshot has been deleted.', 'success').then(function() { location.reload(); });
                            } else {
                                Swal.fire('Error', resp.message || 'Failed to delete.', 'error');
                            }
                        })
                        .catch(function() { Swal.fire('Error', 'Network error.', 'error'); });
                }
            });
        });
    });
});
</script>
