<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Notification Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Notifications</li>
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

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Notification Settings</h5>
                        <p class="mb-0 small text-muted">Email and push notification preferences — SMTP server configuration, notification triggers, rate limits, and per-channel enable/disable toggles.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Sections</h3></div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><a href="<?= base_url('admin/settings') ?>" class="nav-link"><i class="fas fa-cog mr-1"></i> General</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/api') ?>" class="nav-link"><i class="fas fa-plug mr-1"></i> API</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/security') ?>" class="nav-link"><i class="fas fa-shield-alt mr-1"></i> Security</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/notifications') ?>" class="nav-link active"><i class="fas fa-bell mr-1"></i> Notifications</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/maintenance') ?>" class="nav-link"><i class="fas fa-tools mr-1"></i> Maintenance</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage') ?>" class="nav-link"><i class="fas fa-hdd mr-1"></i> Storage Monitor</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/email-triggers') ?>" class="nav-link"><i class="fas fa-envelope mr-1"></i> Email Triggers</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-hdd mr-1"></i> Backup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/cron') ?>" class="nav-link"><i class="fas fa-clock mr-1"></i> Cron Jobs</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">SMTP Configuration</h3></div>
                        <form action="<?= base_url('admin/settings/update') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="section" value="notification">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>SMTP Host</label>
                                    <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" placeholder="smtp.example.com">
                                </div>
                                <div class="form-group">
                                    <label>SMTP Port</label>
                                    <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>" placeholder="587">
                                </div>
                                <div class="form-group">
                                    <label>SMTP Username</label>
                                    <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>" placeholder="user@example.com">
                                </div>
                                <div class="form-group">
                                    <label>SMTP Password</label>
                                    <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>" placeholder="Leave empty to keep current">
                                </div>
                                <div class="form-group">
                                    <label>From Email</label>
                                    <input type="email" name="smtp_from_email" class="form-control" value="<?= htmlspecialchars($settings['smtp_from_email'] ?? '') ?>" placeholder="noreply@example.com">
                                </div>
                                <div class="form-group">
                                    <label>From Name</label>
                                    <input type="text" name="smtp_from_name" class="form-control" value="<?= htmlspecialchars($settings['smtp_from_name'] ?? 'Eaves Droid') ?>">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Settings</button>
                                <button type="button" class="btn btn-outline-info ml-2" onclick="testEmail()"><i class="fas fa-envelope mr-1"></i> Test Email</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function testEmail() {
    Swal.fire({
        title: 'Send Test Email',
        input: 'email',
        inputValue: '<?= htmlspecialchars($settings['smtp_from_email'] ?? '') ?>',
        text: 'Enter the recipient email address:',
        showCancelButton: true,
        confirmButtonText: 'Send Test',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: (email) => {
            return $.ajax({
                url: '<?= base_url('admin/settings/notifications/test-email') ?>',
                method: 'POST',
                data: {
                    email: email,
                    smtp_host: $('input[name=\"smtp_host\"]').val(),
                    smtp_port: $('input[name=\"smtp_port\"]').val(),
                    smtp_user: $('input[name=\"smtp_user\"]').val(),
                    smtp_pass: $('input[name=\"smtp_pass\"]').val(),
                    smtp_from_email: $('input[name=\"smtp_from_email\"]').val(),
                    smtp_from_name: $('input[name=\"smtp_from_name\"]').val()
                },
                dataType: 'json'
            }).then(r => {
                if (!r.success) throw new Error(r.message);
                return r;
            }).catch(err => {
                Swal.showValidationMessage(err.responseJSON?.message || err.message || 'Request failed');
            });
        }
    }).then(r => {
        if (r.isConfirmed) {
            Swal.fire({ icon: 'success', title: 'Sent', text: r.value.message, timer: 3000, showConfirmButton: false });
        }
    });
}
</script>
