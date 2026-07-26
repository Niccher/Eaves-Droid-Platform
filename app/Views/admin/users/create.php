<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create User</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/users') ?>">Users</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
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
                        <h5 class="text-info font-weight-bold mb-1">Create User</h5>
                        <p class="mb-0 small text-muted">Register a new user account manually. Set the username, email, password, and assign initial role groups and permissions.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">New User Details</h3>
                </div>
                <form action="<?= base_url('admin/users/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" class="form-control" value="<?= old('username') ?>" required minlength="3" maxlength="30">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= old('email') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required minlength="8">
                        </div>
                        <div class="form-group">
                            <label for="group">Role</label>
                            <select name="group" id="group" class="form-control">
                                <option value="user" <?= old('group') === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="beta" <?= old('group') === 'beta' ? 'selected' : '' ?>>Beta User</option>
                                <option value="admin" <?= old('group') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="developer" <?= old('group') === 'developer' ? 'selected' : '' ?>>Developer</option>
                                <option value="superadmin" <?= old('group') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create User</button>
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
