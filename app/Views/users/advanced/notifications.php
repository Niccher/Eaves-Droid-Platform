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
                        <span class="info-box-icon"><i class="fas fa-bell"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Notifications</span>
                            <span class="info-box-number"><?= (int) ($summary['notification_count'] ?? $total) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-tv"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Screen Alerts</span>
                            <span class="info-box-number"><?= (int) ($summary['screen_count'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Last Arrived</span>
                            <span class="info-box-number" style="font-size:1rem;"><?= esc($summary['latest_ts_rel'] ?? '—') ?></span>
                            <span class="progress-description"><?= esc($summary['latest_ts_abs'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card card-primary shadow-sm">
                        <div class="card-header text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-list mr-2"></i>All Notifications <small class="ml-2"><?= count($rows) ?> on this page</small></h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool btn-sm text-white" data-card-widget="collapse" data-toggle="tooltip" title="Collapse / Expand"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light text-white">
                                    <tr>
                                        <th><i class="fas fa-clock mr-1"></i>Arrived</th>
                                        <th><i class="fas fa-user mr-1"></i>Sender</th>
                                        <th><i class="fas fa-heading mr-1"></i>Title</th>
                                        <th><i class="fas fa-comment-alt mr-1"></i>Content</th>
                                        <th><i class="fas fa-tv mr-1"></i>Screen</th>
                                        <th><i class="fas fa-tag mr-1"></i>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No notifications for this app.</td></tr>
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
                                        </tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-primary text-white"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
} else {
?>
<?php
    $actions = static function ($r) {
        $pkgEnc = $r['group_url_enc'] ?? '';
        $html = '';
        if ($pkgEnc !== '') {
            $html .= '<a href="' . base_url('advanced/software/notifications/' . $pkgEnc) . '" class="btn btn-sm btn-outline-warning mr-1" title="View all notifications"><i class="fas fa-eye"></i></a>';
            $html .= '<button type="button" class="btn btn-sm btn-outline-danger delete-notifications-group" data-pkg="' . esc($pkgEnc) . '" data-name="' . esc($r['app_name'] ?? '') . '" title="Delete all notifications for this app"><i class="fas fa-trash-alt"></i></button>';
        }
        return $html;
    };
?>
<?= view('users/advanced/_card_table', [
    'title'    => 'Notifications',
    'subtitle' => 'Notification log grouped by app name (sender or package when unnamed)',
    'icon'     => 'fas fa-bell',
    'tableId'  => 'notificationsTable',
    'columns'  => [
        ['field' => 'app_name',          'sub_field' => 'group_key', 'label' => 'Source', 'format' => 'stacked', 'icon' => 'fas fa-mobile-alt'],
        ['field' => 'notification_count','label' => 'Alerts',        'format' => 'badge', 'default' => 'warning', 'icon' => 'fas fa-bell'],
        ['field' => 'latest_ts_abs',     'label' => 'Last Arrived',  'format' => 'text', 'icon' => 'fas fa-clock'],
        ['field' => 'latest_title_short','label' => 'Latest Preview','format' => 'text', 'truncate' => 60, 'icon' => 'fas fa-comment-alt'],
    ],
    'rows'     => $rows,
    'pager'    => $pager,
    'total'    => $total,
    'nav_urls' => $nav_urls ?? '',
    'perPage'  => 25,
    'actions'  => $actions,
    'noExpand' => true,
]) ?>
<?php
}
?>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
<script>
var CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
var CSRF_TOKEN_HASH = '<?= csrf_hash() ?>';
document.addEventListener('DOMContentLoaded', function() {
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
