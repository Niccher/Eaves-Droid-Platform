<?php
/** @var array $rows @var int $total @var int $total_notifications @var object $pager @var string $nav_urls */
/** @var bool $detail_mode @var string|null $group_key @var array|null $summary @var array|null $app_detail @var string|null $back_url */
if (!empty($detail_mode)) {
    $appName = $summary['display_name'] ?? $summary['app_name'] ?? $group_key;
    $pkg = $summary['package_name'] ?? $group_key;
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-12">
                    <a href="<?= esc($back_url) ?>" class="btn btn-primary" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="fas fa-arrow-left mr-1"></i> Back to Alerts</a>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-lg-8">
                    <h1 class="h2 mb-0"><i class="fas fa-bell text-secondary mr-2"></i><?= htmlspecialchars($appName) ?></h1>
                    <p class="text-muted mt-1 mb-0"><code><?= htmlspecialchars($pkg) ?></code></p>
                </div>
                <div class="col-lg-4">
                    <?php if (!empty($app_detail)): ?>
                    <div class="card card-primary card-outline shadow-sm mb-0">
                        <div class="card-body py-2 px-3">
                            <div class="row text-center">
                                <div class="col-4 border-right">
                                    <small class="text-muted d-block">Permissions</small>
                                    <strong><?= (int)($app_detail['permission_count'] ?? 0) ?></strong>
                                </div>
                                <div class="col-4 border-right">
                                    <small class="text-muted d-block">Size</small>
                                    <strong><?= $app_detail['app_size_display'] ?? '—' ?></strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Version</small>
                                    <strong><?= htmlspecialchars($app_detail['version_name'] ?? '—') ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-bell text-dark"></i></span>
                        <div class="info-box-content text-dark">
                            <span class="info-box-text font-weight-bold">Total Notifications</span>
                            <span class="info-box-number font-weight-bold"><?= (int) ($summary['notification_count'] ?? $total) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-tv"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text font-weight-bold">Screen Alerts</span>
                            <span class="info-box-number font-weight-bold"><?= (int) ($summary['screen_count'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text font-weight-bold">Last Arrived</span>
                            <span class="info-box-number font-weight-bold" style="font-size:1rem;"><?= esc($summary['latest_ts_rel'] ?? '—') ?></span>
                            <span class="progress-description"><?= esc($summary['latest_ts_abs'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Redesigned Push Notification Feed -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-warning shadow-sm">
                        <div class="card-header bg-white py-2">
                            <h3 class="card-title font-weight-bold mb-0 text-dark"><i class="fas fa-stream mr-2"></i>Notifications Feed</h3>
                        </div>
                        <div class="card-body bg-light px-3 py-4">
                            <div class="row justify-content-center">
                                <div class="col-lg-8 col-md-10">
                                    <?php if (empty($rows)): ?>
                                        <div class="text-center py-5 bg-white rounded shadow-sm">
                                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                            <p class="mb-0 text-muted">No notifications cataloged for this app.</p>
                                        </div>
                                    <?php else: foreach ($rows as $r): ?>
                                        <?php
                                            $act = strtoupper($r['action'] ?? 'POSTED');
                                            $actCol = $act === 'POSTED' ? 'success' : 'secondary';
                                            $isScreen = !empty($r['is_screen_notification']);
                                            $senderName = $r['sender'] ?: ($r['title'] ?: 'Alert');
                                        ?>
                                        <!-- Simulated Mobile Notification Card -->
                                        <div class="card shadow-sm border mb-3" style="border-radius: 12px; overflow: hidden; border-left: 5px solid <?= $isScreen ? '#ffc107' : '#17a2b8' ?> !important;">
                                            <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #f4f6f9;">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle p-1 mr-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                        <i class="fas fa-bell text-muted" style="font-size: 0.85rem;"></i>
                                                    </div>
                                                    <span class="font-weight-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($appName) ?></span>
                                                    <span class="text-muted mx-2">•</span>
                                                    <span class="text-muted small"><?= esc($r['ts_rel'] ?? '—') ?></span>
                                                </div>
                                                <div>
                                                    <span class="badge badge-<?= $actCol ?> small"><?= $act ?></span>
                                                    <?php if ($isScreen): ?>
                                                        <span class="badge badge-warning text-dark ml-1"><i class="fas fa-tv mr-1"></i>Screen</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="card-body py-2 px-3 bg-white">
                                                <h6 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($r['title'] ?: $senderName) ?></h6>
                                                <p class="text-muted mb-0" style="font-size: 0.9rem;"><?= htmlspecialchars($r['text'] ?? '') ?></p>
                                                <?php if (!empty($r['sub_text'])): ?>
                                                    <div class="mt-2 text-muted small border-top pt-1">
                                                        <i class="fas fa-info-circle mr-1"></i><?= htmlspecialchars($r['sub_text']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-footer py-1 px-3 bg-light text-right text-muted small" style="font-size: 0.75rem;">
                                                <i class="far fa-clock mr-1"></i><?= esc($r['ts_abs'] ?? '—') ?>
                                            </div>
                                        </div>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
} else {
    // Redesigned Summary Mode
?>
<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-bell text-primary mr-2"></i>Notifications
                        </h1>
                        <span class="badge badge-primary border p-2">
                            <i class="fas fa-database mr-1"></i>Active Senders: <b><?= (int)$total ?></b>
                        </span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Notifications log grouped by application or sender source.</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?? '' ?></div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Informative Callout Alert -->
            <div class="callout callout-info shadow-sm mb-4">
                <h5><i class="fas fa-info-circle text-info mr-2"></i>About Notifications Logs</h5>
                <p class="mb-0">Captures in-flight notification stream events posted or dismissed by applications, allowing inspection of message payloads, confirmation codes, and background alerts.</p>
            </div>

            <!-- Notifications List -->
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header d-flex align-items-center py-2">
                    <h5 class="card-title font-weight-bold mb-0 text-dark"><i class="fas fa-history mr-2"></i>Recent Notifications Activity</h5>
                    <div class="card-tools ml-auto">
                        <input type="text" id="notificationSearch" class="form-value form-control form-control-sm" placeholder="Search apps...">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Source / Application</th>
                                    <th>Alerts Count</th>
                                    <th>Last Received</th>
                                    <th>Latest Preview</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="notificationTableBody">
                                <?php if (empty($rows)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No notification logs recorded.</td></tr>
                                <?php else: foreach ($rows as $row): 
                                    $pkgEnc = $row['group_url_enc'] ?? '';
                                    $appName = $row['app_name'] ?? '';
                                    $groupKey = $row['group_key'] ?? '';
                                ?>
                                    <tr class="notification-row" data-name="<?= esc(strtolower($appName)) ?>" data-key="<?= esc(strtolower($groupKey)) ?>">
                                        <td>
                                            <span class="font-weight-bold d-block text-dark"><?= esc($appName ?: 'Unknown App') ?></span>
                                            <small class="text-muted"><?= esc($groupKey) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning px-3 py-1 font-weight-bold text-dark">
                                                <?= esc($row['notification_count']) ?> alerts
                                            </span>
                                        </td>
                                        <td><small class="text-muted"><?= esc($row['latest_ts_abs'] ?? '—') ?></small></td>
                                        <td>
                                            <span class="text-secondary small d-block text-truncate" style="max-width: 260px;" title="<?= esc($row['latest_title_short']) ?>">
                                                <?= esc($row['latest_title_short']) ?>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <div class="btn-group" role="group">
                                                <?php if ($pkgEnc !== ''): ?>
                                                    <a href="<?= base_url('advanced/software/notifications/' . $pkgEnc) ?>" class="btn btn-sm btn-outline-warning text-dark mr-1" title="View alerts timeline">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-notifications-group" data-pkg="<?= esc($pkgEnc) ?>" data-name="<?= esc($appName ?: $groupKey) ?>" title="Delete alerts history">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light py-2">
                    <div class="float-right"><?= $pager->links('default', 'bootstrap5_full') ?></div>
                </div>
            </div>

        </div>
    </section>
</div>
<?php
}
?>

<?php include __DIR__ . '/_adv_delete_script.php'; ?>
<script>
var CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
var CSRF_TOKEN_HASH = '<?= csrf_hash() ?>';
document.addEventListener('DOMContentLoaded', function() {
    // Search filter
    const searchInput = document.getElementById('notificationSearch');
    const tableRows = document.querySelectorAll('.notification-row');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            tableRows.forEach(row => {
                const name = row.getAttribute('data-name');
                const key = row.getAttribute('data-key');
                if (name.includes(query) || key.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Delete group trigger
    document.querySelectorAll('.delete-notifications-group').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var pkg = this.getAttribute('data-pkg');
            var name = this.getAttribute('data-name') || pkg;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete All Notifications?',
                    html: 'This will permanently delete <strong>ALL</strong> notifications for <strong>"' + name + '"</strong> and all of its associated data.'
                       + '<br><span class="text-danger mt-1 d-inline-block"><i class="fas fa-exclamation-triangle mr-1"></i>This action cannot be undone.</span>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash-alt"></i> Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var postData = new URLSearchParams();
                        postData.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);
                        fetch('<?= base_url('advanced/software/notifications/delete') ?>/' + pkg, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: postData
                        }).then(function(r) { return r.json(); }).then(function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', 'All notifications for "' + name + '" have been deleted.', 'success').then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.message || 'Failed to delete notifications.', 'error');
                            }
                        }).catch(function() {
                            Swal.fire('Error!', 'Failed to delete notifications.', 'error');
                        });
                    }
                });
            }
        });
    });
});
</script>
