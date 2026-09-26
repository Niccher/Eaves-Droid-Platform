<?php
$pag = $pag ?? 'superadmin-impersonate';
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-user-secret mr-2"></i>Impersonate User</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('superadmin/home'); ?>">Super Admin</a></li>
                        <li class="breadcrumb-item active">Impersonate</li>
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
                    <h5><i class="icon fas fa-check"></i> Success</h5>
                    <?php echo session()->getFlashdata('message'); ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Error</h5>
                    <?php echo session()->getFlashdata('error'); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($impersonated_by)): ?>
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle mr-1"></i> Currently Impersonating</h5>
                    <p>You are currently impersonating a user. 
                    <form action="<?php echo base_url('superadmin/impersonate/stop'); ?>" method="post" class="d-inline">
                        <?php csrf_field(); ?>
                        <button type="submit" class="alert-link btn btn-link p-0" style="text-decoration: underline; border: none; background: none; color: inherit;">
                            Stop impersonation
                        </button>
                    </form>
                    </p>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Select User to Impersonate</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Groups</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo (int) $user->id; ?></td>
                                        <td><?php echo htmlspecialchars($user->username); ?></td>
                                        <td><?php echo htmlspecialchars($user->email ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $groups = $user_groups[$user->id] ?? [];
                                            echo implode(', ', array_map(function ($g) {
                                                return '<span class="badge badge-info">' . htmlspecialchars($g) . '</span>';
                                            }, $groups));
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($user->active): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="<?php echo base_url('superadmin/impersonate/act-as/' . $user->id); ?>" method="post" class="action-form" data-confirm-title="Impersonate User" data-confirm-text="Are you sure you want to impersonate <?php echo htmlspecialchars($user->username); ?>?">
                                                <?php csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-user-secret mr-1"></i>Impersonate
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>