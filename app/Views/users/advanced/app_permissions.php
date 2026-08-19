<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
foreach ($rows as &$r) {
    $r['perm_unique_key'] = ($r['device_id'] ?? 'default') . '_' . ($r['package_name'] ?? 'unknown') . '_' . ($r['permission_name'] ?? 'unknown');
}
unset($r);

helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'perm_unique_key',
    ['package_name', 'permission_name', 'is_granted', 'is_runtime', 'is_revoked', 'is_requested', 'is_system_fixed', 'grant_time', 'last_used_time', 'flags', 'is_one_time', 'is_auto_revoke_whitelisted', 'user_set', 'fixed_policy', 'is_hard_restricted', 'is_soft_restricted']
);

// Group permissions by package name
$groupedApps = [];
$highRiskCount = 0;
$dangerousPermissions = [
    'android.permission.CAMERA',
    'android.permission.ACCESS_FINE_LOCATION',
    'android.permission.ACCESS_COARSE_LOCATION',
    'android.permission.RECORD_AUDIO',
    'android.permission.READ_SMS',
    'android.permission.RECEIVE_SMS',
    'android.permission.SEND_SMS',
    'android.permission.READ_CONTACTS',
    'android.permission.WRITE_CONTACTS',
    'android.permission.READ_CALL_LOG',
    'android.permission.WRITE_CALL_LOG',
    'android.permission.READ_PHONE_STATE'
];

foreach ($rows as $row) {
    $pkg = $row['package_name'] ?? 'unknown';
    if (!isset($groupedApps[$pkg])) {
        $groupedApps[$pkg] = [
            'package_name' => $pkg,
            'permissions' => [],
            'has_high_risk' => false,
            'granted_count' => 0,
            'extracted_at' => $row['extracted_at']
        ];
    }
    
    $isGranted = !empty($row['is_granted']);
    $permName = $row['permission_name'] ?? '';
    
    if ($isGranted) {
        $groupedApps[$pkg]['granted_count']++;
        if (in_array($permName, $dangerousPermissions)) {
            $groupedApps[$pkg]['has_high_risk'] = true;
        }
    }
    
    $groupedApps[$pkg]['permissions'][] = $row;
}

foreach ($groupedApps as $app) {
    if ($app['has_high_risk']) {
        $highRiskCount++;
    }
}
?>

<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-key text-primary mr-2"></i>App Permissions
                        </h1>
                        <span class="badge badge-primary border p-2">
                            <i class="fas fa-database mr-1"></i>Total Packages: <b><?= count($groupedApps) ?></b>
                        </span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Requested, granted, and runtime permissions per package.</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Informative Callout Alert -->
            <div class="callout callout-info shadow-sm mb-4">
                <h5><i class="fas fa-info-circle text-info mr-2"></i>Understanding App Permissions</h5>
                <ul class="mb-0 pl-3">
                    <li><strong>Permissions</strong>: Specific access rights requested or held by applications to interact with device APIs, user data, or hardware layers (e.g., Internet, Camera, SMS).</li>
                    <li><strong>High-Risk</strong>: Flagged applications that request access to sensitive resources (like Location, Contacts, Microphone, Camera, or SMS) posing potential privacy vulnerabilities.</li>
                    <li><strong>Analyzed Packages</strong>: Represents the total number of distinct applications monitored and logged on the device.</li>
                </ul>
            </div>

            <!-- Summary Widgets -->
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="info-box shadow-sm border">
                        <span class="info-box-icon bg-info"><i class="fas fa-cubes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text font-weight-bold">Analyzed Packages</span>
                            <span class="info-box-number h4 font-weight-bold text-info mb-0"><?= count($groupedApps) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="info-box shadow-sm border">
                        <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text font-weight-bold">High-Risk Packages</span>
                            <span class="info-box-number h4 font-weight-bold text-danger mb-0"><?= $highRiskCount ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="info-box shadow-sm border mb-0">
                        <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text font-weight-bold">Total Permission Checkpoints</span>
                            <span class="info-box-number h4 font-weight-bold text-success mb-0"><?= count($rows) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls Panel -->
            <div class="card card-outline card-secondary shadow-sm mb-4">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" id="permissionSearch" class="form-value form-control" placeholder="Search app package or permission...">
                            </div>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <button type="button" class="btn btn-sm btn-outline-danger filter-btn mr-1" data-filter="high-risk">
                                <i class="fas fa-exclamation-circle mr-1"></i>High-Risk Only
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary filter-btn" data-filter="all">
                                Show All
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- App Listing Accordion -->
            <div class="accordion" id="permissionAccordion">
                <?php if (empty($groupedApps)): ?>
                    <div class="card shadow-sm text-center py-5">
                        <div class="card-body">
                            <i class="fas fa-shield-alt fa-4x text-muted mb-3"></i>
                            <h5>No permissions metadata cataloged</h5>
                            <p class="text-muted">Telemetry payload is missing or empty.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php $index = 0; foreach ($groupedApps as $pkg => $app): $index++; ?>
                        <div class="card card-outline <?= $app['has_high_risk'] ? 'card-danger' : 'card-primary' ?> shadow-sm mb-3 app-card" data-package="<?= esc($pkg) ?>" data-high-risk="<?= $app['has_high_risk'] ? 'true' : 'false' ?>">
                            <div class="card-header d-flex align-items-center py-2 collapse-trigger cursor-pointer" data-toggle="collapse" data-target="#collapse_<?= $index ?>" aria-expanded="false">
                                <div class="d-flex align-items-center flex-wrap flex-grow-1" style="gap: 8px;">
                                    <span class="font-weight-bold text-dark text-truncate" style="max-width: 320px;" title="<?= esc($pkg) ?>"><?= esc($pkg) ?></span>
                                    <span class="badge badge-light border"><i class="fas fa-shield-alt mr-1 text-primary"></i>Granted: <?= $app['granted_count'] ?></span>
                                    <?php if ($app['has_high_risk']): ?>
                                        <span class="badge badge-danger"><i class="fas fa-exclamation-triangle mr-1"></i>High-Risk</span>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $runtimeCount = 0;
                                $normalCount = 0;
                                foreach ($app['permissions'] as $p) {
                                    if (!empty($p['is_runtime'])) $runtimeCount++;
                                    else $normalCount++;
                                }
                                ?>
                                <div class="card-tools ml-auto text-muted small">
                                    <span class="mr-2 bg-light border p-1 rounded">
                                        <i class="fas fa-check-circle text-success mr-1"></i>Granted Permissions (<?= $app['granted_count'] ?>/<?= count($app['permissions']) ?>)
                                    </span>
                                    <span class="bg-light border p-1 rounded">
                                        <i class="fas fa-cogs text-primary mr-1"></i>Permission types: runtime <?= $runtimeCount ?>, normal <?= $normalCount ?>
                                    </span>
                                    <button type="button" class="btn btn-tool ml-2"><i class="fas fa-chevron-down"></i></button>
                                </div>
                            </div>
                            <div id="collapse_<?= $index ?>" class="collapse" data-parent="#permissionAccordion">
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Permission</th>
                                                <th>Status</th>
                                                <th>Type</th>
                                                <th class="d-none d-md-table-cell">Flags</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($app['permissions'] as $perm): ?>
                                                <tr>
                                                    <td>
                                                        <code class="text-secondary"><?= esc($perm['permission_name']) ?></code>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($perm['is_granted'])): ?>
                                                            <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Granted</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary"><i class="fas fa-times-circle mr-1"></i>Denied</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= !empty($perm['is_runtime']) ? 'primary' : 'light border' ?>">
                                                            <?= !empty($perm['is_runtime']) ? 'Runtime' : 'Normal' ?>
                                                        </span>
                                                    </td>
                                                    <td class="small d-none d-md-table-cell text-muted">
                                                        <?= esc($perm['flags'] ?: 'None') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="mt-4">
                <?= $pager->links('default', 'bootstrap5_full') ?>
            </div>

        </div>
    </section>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.collapse-trigger:hover { background-color: #f8fafc; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('permissionSearch');
    const appCards = document.querySelectorAll('.app-card');
    const filterButtons = document.querySelectorAll('.filter-btn');
    let currentFilter = 'all';

    function filterApps() {
        const query = searchInput.value.toLowerCase().trim();
        appCards.forEach(card => {
            const pkg = card.getAttribute('data-package').toLowerCase();
            const isHighRisk = card.getAttribute('data-high-risk') === 'true';
            
            let matchesQuery = pkg.includes(query);
            let matchesFilter = true;
            
            if (currentFilter === 'high-risk') {
                matchesFilter = isHighRisk;
            }
            
            if (matchesQuery && matchesFilter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterApps);
    }

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.replace('btn-danger', 'btn-outline-danger'));
            filterButtons.forEach(b => b.classList.replace('btn-secondary', 'btn-outline-secondary'));
            
            const filter = this.getAttribute('data-filter');
            currentFilter = filter;
            
            if (filter === 'high-risk') {
                this.classList.replace('btn-outline-danger', 'btn-danger');
            } else {
                this.classList.replace('btn-outline-secondary', 'btn-secondary');
            }
            
            filterApps();
        });
    });
});
</script>
