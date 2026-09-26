<?php
/** @var array $rows @var int $total @var object $pager @var string $nav_urls @var string $group_key @var array $summary @var string $back_url */
$appName = $summary['display_name'] ?? $summary['app_name'] ?? $group_key;
$pkgName = $summary['package_name'] ?? $group_key;
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent p-0 mb-2">
                            <li class="breadcrumb-item"><a href="<?= esc($back_url) ?>">Notifications</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($appName) ?></li>
                        </ol>
                    </nav>
                    <h1 class="h2 mb-0"><i class="fas fa-bell text-warning mr-2"></i><?= htmlspecialchars($appName) ?></h1>
                    <p class="text-muted mt-1 mb-0"><code><?= htmlspecialchars($pkgName) ?></code></p>
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
                        <span class="info-box-icon"><i class="fas fa-bell"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Notifications</span>
                            <span class="info-box-number"><?= (int) ($summary['notification_count'] ?? $total) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-tv"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Screen Alerts</span>
                            <span class="info-box-number"><?= (int) ($summary['screen_count'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Last Arrived</span>
                            <span class="info-box-number" style="font-size:1rem;"><?= esc($summary['latest_ts_rel'] ?? '—') ?></span>
                            <span class="progress-description"><?= esc($summary['latest_ts_abs'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Extracted at</span>
                            <span class="info-box-number" style="font-size:1rem;"><?= esc($summary['latest_ts_abs'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Callout Card explaining Notification Actions & Data -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
                <h5 class="font-weight-bold text-info"><i class="fas fa-bell mr-2"></i>Status Bar Notification Telemetry</h5>
                <p class="text-secondary mb-2" style="font-size:14px;">Captures system status bar alerts, heads-up notifications, and background service events intercepted via Android NotificationListenerService.</p>
                <div class="row" style="font-size:12px;">
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">POSTED Action:</b>
                        <span class="text-muted">Active notification dispatched or updated on the device status bar / notification shade by the package.</span>
                    </div>
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">REMOVED / DISMISSED:</b>
                        <span class="text-muted">Notification cleared, swiped away by the target user, or automatically dismissed by the application.</span>
                    </div>
                    <div class="col-md-4">
                        <b class="d-block mb-1">Silent Background Records:</b>
                        <span class="text-muted">Background services (e.g. Google Quick Search, system sync) frequently post ongoing status banners or telemetry without title/content payloads.</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list mr-2"></i>All Notifications <small class="text-muted ml-2"><?= count($rows) ?> on this page</small></h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th><i class="fas fa-clock mr-1"></i>Arrived</th>
                                        <th><i class="fas fa-heading mr-1"></i>Title</th>
                                        <th><i class="fas fa-comment-alt mr-1"></i>Content</th>
                                        <th><i class="fas fa-tag mr-1"></i>Action</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No notifications for this app.</td></tr>
                                    <?php else: foreach ($rows as $r): ?>
                                        <?php
                                            $act   = strtoupper($r['action'] ?? '');
                                            $actCol = $act === 'POSTED' ? 'success' : 'secondary';
                                            $isScreen = !empty($r['is_screen_notification']);
                                            $rowClass = $isScreen ? 'table-warning' : '';
                                            $titleTxt = trim((string)($r['title'] ?? ''));
                                            $bodyTxt  = trim((string)($r['text'] ?? ''));
                                        ?>
                                        <tr class="<?= $rowClass ?>">
                                            <td>
                                                <small class="d-block font-weight-bold"><?= esc($r['ts_rel'] ?? '—') ?></small>
                                                <small class="text-muted"><?= esc($r['ts_abs'] ?? '—') ?></small>
                                            </td>
                                            <td class="font-weight-bold">
                                                <?= $titleTxt !== '' ? htmlspecialchars($titleTxt) : '<em class="text-muted font-weight-normal">(Silent Notification)</em>' ?>
                                            </td>
                                            <td>
                                                <small><?= $bodyTxt !== '' ? htmlspecialchars(mb_strimwidth($bodyTxt, 0, 120, '…')) : '<em class="text-muted">(No Content)</em>' ?></small>
                                            </td>
                                            <td><span class="badge badge-<?= $actCol ?>"><?= $act ?></span></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-notification"
                                                        data-id="<?= $r['id'] ?? '' ?>"
                                                        title="Delete this notification">
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
    document.querySelectorAll('.delete-notification').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            if (!id) return;
            var section = this.getAttribute('data-delete-title') || DELETE_SECTION || 'this notification';
            Swal.fire({
                title: 'Delete this Entry?',
                html: 'This will permanently delete the <strong>"' + section + '"</strong> notification and all of its associated data.'
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
                    fetch(base_url('advanced/software/notifications/delete-row/' + id), {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: postData
                    })
                        .then(function(r) { return r.json(); })
                        .then(function(resp) {
                            if (resp.success) {
                                Swal.fire('Deleted!', 'The "' + section + '" notification has been deleted.', 'success').then(function() { location.reload(); });
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
