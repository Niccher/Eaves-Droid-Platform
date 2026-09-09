<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Role Matrix</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/home') ?>">Super Admin</a></li>
                        <li class="breadcrumb-item active">Role Matrix</li>
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

            <div class="callout callout-danger bg-light shadow-sm border-left-danger mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exchange-alt text-danger fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-danger font-weight-bold mb-1">Promote / Demote</h5>
                        <p class="mb-0 small text-muted">Change a user's privilege level. You cannot demote your own superadmin account. Every change is logged at <code>critical</code> severity.</p>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-danger shadow-sm mb-3">
                <div class="card-header p-2 bg-light">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a href="<?= base_url('admin/users') ?>" class="nav-link">
                                <i class="fas fa-users mr-1 text-primary"></i> User Directory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('superadmin/users') ?>" class="nav-link active">
                                <i class="fas fa-user-shield mr-1 text-danger"></i> Role Matrix <span class="badge badge-danger ml-1">SuperAdmin</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/tokens') ?>" class="nav-link">
                                <i class="fas fa-key mr-1 text-warning"></i> API Tokens
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Accounts</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover" id="roleMatrixTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Current Role</th>
                                <th>Status</th>
                                <th>Change Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <?php
                                $groups = $user_groups[$u['id']] ?? ['user'];
                                $currentRole = $groups[0] ?? 'user';
                                $isSuspended = ($u['status'] ?? '') === 'suspended' || ($u['status'] ?? '') === 'banned';
                                $isDeleted = !empty($u['deleted_at']);
                                $isActive = !$isSuspended && !$isDeleted;
                                $isSelf = (int)$u['id'] === (int)$user_info['id'];
                            ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['username']) ?><?= $isSelf ? ' <span class="badge badge-dark">You</span>' : '' ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                                <td>
                                    <span class="badge badge-<?= $currentRole === 'superadmin' ? 'danger' : ($currentRole === 'admin' ? 'warning' : ($currentRole === 'developer' ? 'info' : ($currentRole === 'beta' ? 'secondary' : 'primary'))) ?>">
                                        <?= htmlspecialchars($currentRole) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isDeleted): ?>
                                    <span class="badge badge-danger">Deleted</span>
                                    <?php elseif ($isSuspended): ?>
                                    <span class="badge badge-warning">Suspended</span>
                                    <?php else: ?>
                                    <span class="badge badge-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <form method="post" action="<?= base_url('superadmin/users/role/' . $u['id']) ?>" class="form-inline action-form mr-2" data-confirm-title="Change Role" data-confirm-text="Change role of <?= htmlspecialchars($u['username']) ?>? This is logged as a critical action.">
                                            <?= csrf_field() ?>
                                            <select name="group" class="form-control form-control-sm mr-1" <?= $isSelf ? 'disabled' : '' ?>>
                                                <?php $roles = ['superadmin', 'admin', 'developer', 'beta', 'user']; ?>
                                                <?php foreach ($roles as $role): ?>
                                                <option value="<?= $role ?>" <?= $currentRole === $role ? 'selected' : '' ?>><?= ucfirst($role) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if ($isSelf): ?>
                                            <button type="button" class="btn btn-sm btn-secondary" disabled title="You cannot change your own role">Locked</button>
                                            <?php else: ?>
                                            <button type="submit" class="btn btn-sm btn-danger" title="Save Role"><i class="fas fa-exchange-alt"></i></button>
                                            <?php endif; ?>
                                        </form>
                                        <?php if (!$isSelf && !$isDeleted): ?>
                                        <form method="post" action="<?= base_url('superadmin/impersonate/act-as/' . $u['id']) ?>" class="d-inline action-form" data-confirm-title="Impersonate User" data-confirm-text="Are you sure you want to impersonate <?= htmlspecialchars($u['username']) ?>?">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-dark" title="Impersonate <?= htmlspecialchars($u['username']) ?>">
                                                <i class="fas fa-user-secret mr-1"></i> Impersonate
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_users > 25): ?>
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
    $('#roleMatrixTable').DataTable({
        order: [[0, 'desc']],
        searching: true,
        paging: false,
        responsive: true,
    });
});
</script>
