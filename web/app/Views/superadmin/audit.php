<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Security Audit Trail</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/home') ?>">Super Admin</a></li>
                        <li class="breadcrumb-item active">Audit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="callout callout-info shadow-sm mb-4">
                <h5><i class="fas fa-info-circle mr-2"></i>Audit & Accountability</h5>
                <p class="mb-0">These audit logs are immutable and are strictly used for legal compliance. Every admin and system action is permanently recorded here.</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filters</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('superadmin/audit') ?>" class="form-inline">
                        <select name="severity" class="form-control form-control-sm mr-2 mb-1">
                            <option value="">All severities</option>
                            <?php foreach (['low', 'medium', 'high', 'critical'] as $s): ?>
                            <option value="<?= $s ?>" <?= $filters['severity'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="category" class="form-control form-control-sm mr-2 mb-1">
                            <option value="">All categories</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= $filters['category'] === $c ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($c)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="outcome" class="form-control form-control-sm mr-2 mb-1">
                            <option value="">All outcomes</option>
                            <option value="success" <?= $filters['outcome'] === 'success' ? 'selected' : '' ?>>Success</option>
                            <option value="failed" <?= $filters['outcome'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-danger mb-1"><i class="fas fa-filter"></i> Apply</button>
                        <a href="<?= base_url('superadmin/audit') ?>" class="btn btn-sm btn-outline-secondary ml-1 mb-1">Reset</a>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Action Log <span class="badge badge-secondary"><?= number_format($total_logs) ?> total</span></h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Category</th>
                                    <th>Action</th>
                                    <th>Severity</th>
                                    <th>Outcome</th>
                                    <th>IP</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                <tr><td colspan="8" class="text-center text-muted">No matching log entries</td></tr>
                                <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= $log['id'] ?></td>
                                    <td><?= htmlspecialchars($log['username'] ?? 'System/Unknown') ?></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($log['action_category'] ?? '-') ?></span></td>
                                    <td><code><?= htmlspecialchars($log['action_type'] ?? '-') ?></code></td>
                                    <td>
                                        <span class="badge badge-<?= $log['action_severity'] === 'critical' ? 'danger' : ($log['action_severity'] === 'high' ? 'warning' : ($log['action_severity'] === 'medium' ? 'secondary' : 'success')) ?>">
                                            <?= htmlspecialchars($log['action_severity'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($log['success'] ?? 1) == 1): ?>
                                        <span class="badge badge-success">Success</span>
                                        <?php else: ?>
                                        <span class="badge badge-danger">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars($log['ip_address'] ?? '-') ?></small></td>
                                    <td><small><?= htmlspecialchars($log['created_at'] ?? '-') ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($total_logs > 25): ?>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="entry-info">
                                Showing <?= (($current_page - 1) * $per_page) + 1 ?>
                                to <?= min($current_page * $per_page, $total_logs) ?>
                                of <?= number_format($total_logs) ?> entries
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="float-right">
                                <?= $pager->links('default', 'bootstrap5_full') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-mobile-alt mr-1"></i> Recent Device Security Snapshots</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Owner</th>
                                <th>Device ID</th>
                                <th>VPN</th>
                                <th>Proxy</th>
                                <th>Captured</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($security_snapshots)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No device security snapshots recorded</td></tr>
                            <?php else: ?>
                            <?php foreach ($security_snapshots as $snap): ?>
                            <tr>
                                <td><?= $snap['id'] ?></td>
                                <td><?= htmlspecialchars($snap['username'] ?? 'Unknown') ?></td>
                                <td><small><?= htmlspecialchars($snap['device_id'] ?? '-') ?></small></td>
                                <td>
                                    <span class="badge badge-<?= $snap['vpn_active'] ? 'danger' : 'success' ?>">
                                        <?= $snap['vpn_active'] ? 'Active' : 'Off' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $snap['proxy_active'] ? 'danger' : 'success' ?>">
                                        <?= $snap['proxy_active'] ? 'Active' : 'Off' ?>
                                    </span>
                                </td>
                                <td><small><?= htmlspecialchars($snap['created_at'] ?? '-') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
