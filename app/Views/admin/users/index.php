<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>User Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= session()->getFlashdata('message') ?>
            </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= session()->getFlashdata('error') ?>
            </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $e): ?>
                    <li><?= esc($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">User Management</h5>
                        <p class="mb-0 small text-muted">View, search, and manage all registered users. Create new accounts, edit profiles, assign roles, reset passwords, and suspend or delete accounts.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Users</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create User
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Last Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <?php
                                $groups = $user_groups[$u['id']] ?? ['user'];
                                $role = implode(', ', $groups);
                                $isActive = $u['active'] ?? 1;
                                $canManageRoles = auth()->user()->can('users.manage-roles');
                                $targetPrivileged = array_intersect($groups, ['superadmin', 'admin', 'developer']) !== [];
                            ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                                <td>
                                    <?php foreach ($groups as $g): ?>
                                    <span class="badge badge-<?= $g === 'superadmin' ? 'danger' : ($g === 'admin' ? 'warning' : ($g === 'developer' ? 'info' : ($g === 'beta' ? 'secondary' : 'primary'))) ?>">
                                        <?= htmlspecialchars($g) ?>
                                    </span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php if ($u['deleted_at']): ?>
                                    <span class="badge badge-danger">Deleted</span>
                                    <?php elseif (!$isActive): ?>
                                    <span class="badge badge-secondary">Suspended</span>
                                    <?php else: ?>
                                    <span class="badge badge-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($u['created_at'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($u['last_active'] ?? '-') ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('admin/users/edit/' . $u['id']) ?>" class="btn btn-info" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="<?= base_url('admin/users/data/' . $u['id']) ?>" class="btn btn-primary" title="View Data"><i class="fas fa-database"></i></a>
                                        <?php if ($isActive && ($canManageRoles || !$targetPrivileged)): ?>
                                        <form method="post" action="<?= base_url('admin/users/suspend/' . $u['id']) ?>" class="d-inline action-form" data-confirm-title="Suspend User" data-confirm-text="Are you sure you want to suspend this user? They will not be able to log in.">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-warning btn-sm" title="Suspend"><i class="fas fa-pause"></i></button>
                                        </form>
                                        <?php elseif (!$isActive && ($canManageRoles || !$targetPrivileged)): ?>
                                        <form method="post" action="<?= base_url('admin/users/activate/' . $u['id']) ?>" class="d-inline action-form" data-confirm-title="Activate User" data-confirm-text="Are you sure you want to activate this user?">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-success btn-sm" title="Activate"><i class="fas fa-play"></i></button>
                                        </form>
                                        <?php endif; ?>
                                        <?php if ($u['id'] !== $user_info['id'] && ($canManageRoles || !$targetPrivileged)): ?>
                                        <form method="post" action="<?= base_url('admin/users/delete/' . $u['id']) ?>" class="d-inline action-form" data-confirm-title="Delete User" data-confirm-text="Are you sure you want to permanently delete this user? This action cannot be undone.">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_users > $per_page): ?>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="entry-info">
                                Showing <?= (($current_page - 1) * $per_page) + 1 ?>
                                to <?= min($current_page * $per_page, $total_users) ?>
                                of <?= number_format($total_users) ?> entries
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
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        order: [[0, 'desc']],
        searching: false,
        paging: false,
        responsive: true,
    });
});
</script>
