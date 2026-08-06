<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Super Admin Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Super Admin</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="callout callout-danger bg-light shadow-sm border-left-danger mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shield-alt text-danger fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-danger font-weight-bold mb-1">Privileged Administration</h5>
                        <p class="mb-0 small text-muted">This area is restricted to the superadmin group. Role changes and audit access are logged for accountability.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= number_format($total_users) ?></h3>
                            <p>Total Users</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <a href="<?= base_url('superadmin/users') ?>" class="small-box-footer">Role Matrix <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= number_format(($group_counts['superadmin'] ?? 0)) ?></h3>
                            <p>Super Admins</p>
                        </div>
                        <div class="icon"><i class="fas fa-crown"></i></div>
                        <a href="<?= base_url('superadmin/users') ?>" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= number_format($admin_actions) ?></h3>
                            <p>Admin Actions Logged</p>
                        </div>
                        <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                        <a href="<?= base_url('superadmin/audit') ?>" class="small-box-footer">Audit Trail <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-dark">
                        <div class="inner">
                            <h3><?= number_format($critical_actions) ?></h3>
                            <p>High/Critical Actions</p>
                        </div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <a href="<?= base_url('superadmin/audit') ?>" class="small-box-footer">Review <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><i class="fas fa-tags"></i></h3>
                            <p>Plans & Pricing</p>
                        </div>
                        <div class="icon"><i class="fas fa-tags"></i></div>
                        <a href="<?= base_url('superadmin/plans') ?>" class="small-box-footer">Manage Plans <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><i class="fas fa-shield-alt"></i></h3>
                            <p>Geofence Zones</p>
                        </div>
                        <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                        <a href="<?= base_url('superadmin/fleet/geo') ?>" class="small-box-footer">Manage Zones <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><i class="fas fa-shield-alt"></i></h3>
                            <p>Risk Scores</p>
                        </div>
                        <div class="icon"><i class="fas fa-shield-alt"></i></div>
                        <a href="<?= base_url('superadmin/analytics/risk') ?>" class="small-box-footer">View Risk <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><i class="fas fa-bell"></i></h3>
                            <p>Push Notifications</p>
                        </div>
                        <div class="icon"><i class="fas fa-bell"></i></div>
                        <a href="<?= base_url('superadmin/notifications') ?>" class="small-box-footer">Configure <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-shield mr-1"></i> Privileged Accounts</h3>
                            <div class="card-tools">
                                <a href="<?= base_url('superadmin/users') ?>" class="btn btn-sm btn-danger">Manage Roles</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($privileged_accounts)): ?>
                                    <tr><td colspan="3" class="text-center text-muted">No privileged accounts</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($privileged_accounts as $pa): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($pa['username']) ?></td>
                                        <td><?= htmlspecialchars($pa['email'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge badge-<?= $pa['group'] === 'superadmin' ? 'danger' : ($pa['group'] === 'admin' ? 'warning' : 'info') ?>">
                                                <?= htmlspecialchars($pa['group']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list-alt mr-1"></i> Role Distribution</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <?php
                                    $roles = ['superadmin', 'admin', 'developer', 'beta', 'user'];
                                    $roleColors = ['superadmin' => 'danger', 'admin' => 'warning', 'developer' => 'info', 'beta' => 'secondary', 'user' => 'primary'];
                                ?>
                                <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-<?= $roleColors[$role] ?>"><?= htmlspecialchars($role) ?></span>
                                    </td>
                                    <td class="text-right"><strong><?= number_format($group_counts[$role] ?? 0) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                            <p class="mb-0 small text-muted"><i class="fas fa-database mr-1"></i><?= number_format($security_snapshots) ?> device security snapshots recorded.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history mr-1"></i> Recent Admin Actions</h3>
                            <div class="card-tools">
                                <a href="<?= base_url('superadmin/audit') ?>" class="btn btn-sm btn-outline-secondary">Full Audit Trail</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Severity</th>
                                        <th>Outcome</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_admin_actions)): ?>
                                    <tr><td colspan="5" class="text-center text-muted">No admin actions logged</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($recent_admin_actions as $act): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($act['username'] ?? 'Unknown') ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($act['action_type'] ?? '-') ?></span></td>
                                        <td>
                                            <span class="badge badge-<?= $act['action_severity'] === 'critical' ? 'danger' : ($act['action_severity'] === 'high' ? 'warning' : ($act['action_severity'] === 'medium' ? 'secondary' : 'success')) ?>">
                                                <?= htmlspecialchars($act['action_severity'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (($act['success'] ?? 1) == 1): ?>
                                            <span class="badge badge-success">Success</span>
                                            <?php else: ?>
                                            <span class="badge badge-danger">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($act['created_at'] ?? '-') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
