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
                                        <th><i class="fas fa-user mr-1"></i>Sender</th>
                                        <th><i class="fas fa-heading mr-1"></i>Title</th>
                                        <th><i class="fas fa-comment-alt mr-1"></i>Content</th>
                                        <th><i class="fas fa-tv mr-1"></i>Screen</th>
                                        <th><i class="fas fa-tag mr-1"></i>Action</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="7" class="text-center py-4 text-muted">No notifications for this app.</td></tr>
                                    <?php else: foreach ($rows as $r): ?>
                                        <?php
                                            $act   = strtoupper($r['action'] ?? '');
                                            $actCol = $act === 'POSTED' ? 'success' : 'secondary';
                                            $isScreen = !empty($r['is_screen_notification']);
                                            $rowClass = $isScreen ? 'table-warning' : '';
                                        ?>
                                        <tr class="<?= $rowClass ?>">
                                            <td>
                                                <small class="d-block font-weight-bold"><?= esc($r['ts_rel'] ?? '—') ?></small>
                                                <small class="text-muted"><?= esc($r['ts_abs'] ?? '—') ?></small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($r['sender'] ?? '—') ?></small>
                                                <?php if (!empty($r['sub_text'])): ?>
                                                    <small class="text-muted d-block"><?= htmlspecialchars(mb_strimwidth($r['sub_text'], 0, 40, '…')) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="font-weight-bold"><?= htmlspecialchars($r['title'] ?? '—') ?></td>
                                            <td><small class="text-muted"><?= htmlspecialchars(mb_strimwidth($r['text'] ?? '—', 0, 120, '…')) ?></small></td>
                                            <td>
                                                <?php if ($isScreen): ?>
                                                    <span class="badge badge-warning"><i class="fas fa-tv mr-1"></i>Screen</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light text-muted">—</span>
                                                <?php endif; ?>
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
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-notification').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            if (!id) return;
            Swal.fire({
                title: 'Delete Notification?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: '<i class="fas fa-trash mr-1"></i> Delete',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    fetch(base_url('advanced/notifications/delete-row/' + id), { method: 'POST' })
                        .then(function(r) { return r.json(); })
                        .then(function(resp) {
                            if (resp.success) {
                                Swal.fire('Deleted!', resp.message, 'success').then(function() { location.reload(); });
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
