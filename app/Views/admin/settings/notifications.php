<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Email & SMTP Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Emails & SMTP</li>
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

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-envelope-open-text text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Email & SMTP Server Configuration</h5>
                        <p class="mb-0 small text-muted">Configure outbound SMTP mail server credentials for security notifications, digest emails, user password resets, and critical system alerts.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card card-outline card-primary shadow-sm mb-3">
                        <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h mr-1 text-primary"></i> Settings Hub</h3></div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><a href="<?= base_url('admin/settings') ?>" class="nav-link"><i class="fas fa-cog mr-2 text-primary"></i> General</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/api') ?>" class="nav-link"><i class="fas fa-plug mr-2 text-info"></i> API</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/security') ?>" class="nav-link"><i class="fas fa-shield-alt mr-2 text-danger"></i> Security</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/notifications') ?>" class="nav-link active"><i class="fas fa-envelope mr-2 text-primary"></i> Emails & SMTP</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/fcm') ?>" class="nav-link"><i class="fas fa-fire mr-2 text-warning"></i> Firebase (FCM)</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/maintenance') ?>" class="nav-link"><i class="fas fa-tools mr-2 text-secondary"></i> Maintenance</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage') ?>" class="nav-link"><i class="fas fa-chart-pie mr-2 text-success"></i> Storage Monitor</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage-cleanup') ?>" class="nav-link"><i class="fas fa-broom mr-2 text-warning"></i> Storage Cleanup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/email-triggers') ?>" class="nav-link"><i class="fas fa-bell mr-2 text-info"></i> Email Triggers</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-database mr-2 text-info"></i> Backup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/cron') ?>" class="nav-link"><i class="fas fa-clock mr-2 text-purple"></i> Cron Jobs</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="card card-outline card-secondary shadow-sm">
                        <div class="card-header"><h3 class="card-title font-weight-bold text-dark"><i class="fas fa-paper-plane mr-1 text-primary"></i> Related Hubs</h3></div>
                        <div class="card-body p-2">
                            <a href="<?= base_url('admin/settings/email-triggers') ?>" class="btn btn-outline-info btn-block text-left mb-2">
                                <i class="fas fa-bell mr-2"></i> Email Trigger Events
                            </a>
                            <a href="<?= base_url('admin/settings/fcm') ?>" class="btn btn-outline-warning btn-block text-left text-dark">
                                <i class="fas fa-fire mr-2 text-warning"></i> Firebase (FCM) Keys
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <!-- ==================== SMTP CONFIGURATION CARD ==================== -->
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-envelope mr-2 text-primary"></i> SMTP Server Configuration
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-outline-info btn-sm font-weight-bold" onclick="testEmail()">
                                    <i class="fas fa-paper-plane mr-1"></i> Test Email Delivery
                                </button>
                            </div>
                        </div>
                        <form action="<?= base_url('admin/settings/update') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="section" value="notification">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="font-weight-bold">SMTP Host <span class="text-danger">*</span></label>
                                            <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" placeholder="e.g. smtp.gmail.com, mail.example.com">
                                            <small class="form-text text-muted">Outbound mail server hostname or IP address.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold">SMTP Port <span class="text-danger">*</span></label>
                                            <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>" placeholder="587">
                                            <small class="form-text text-muted">Usually 587 (TLS) or 465 (SSL).</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">SMTP Username <span class="text-danger">*</span></label>
                                            <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>" placeholder="user@example.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">SMTP Password</label>
                                            <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>" placeholder="Leave empty to keep current password">
                                            <small class="form-text text-muted">For Gmail, use a 16-character App Password.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">From Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="smtp_from_email" class="form-control" value="<?= htmlspecialchars($settings['smtp_from_email'] ?? '') ?>" placeholder="noreply@example.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">From Display Name <span class="text-danger">*</span></label>
                                            <input type="text" name="smtp_from_name" class="form-control" value="<?= htmlspecialchars($settings['smtp_from_name'] ?? 'Eaves Droid') ?>" placeholder="Eaves Droid">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                                        <i class="fas fa-save mr-1"></i> Save SMTP Settings
                                    </button>
                                    <button type="button" class="btn btn-outline-info ml-2 shadow-sm" onclick="testEmail()">
                                        <i class="fas fa-paper-plane mr-1"></i> Send Test Email
                                    </button>
                                </div>
                                <small class="text-muted"><i class="fas fa-lock text-success mr-1"></i> TLS / SSL Protected</small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Test SMTP Email
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
            if (!email) {
                Swal.showValidationMessage('Please enter a recipient email address');
                return false;
            }
            return $.ajax({
                url: '<?= base_url('admin/settings/notifications/test-email') ?>',
                method: 'POST',
                data: {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                    email: email,
                    smtp_host: $('input[name="smtp_host"]').val(),
                    smtp_port: $('input[name="smtp_port"]').val(),
                    smtp_user: $('input[name="smtp_user"]').val(),
                    smtp_pass: $('input[name="smtp_pass"]').val(),
                    smtp_from_email: $('input[name="smtp_from_email"]').val(),
                    smtp_from_name: $('input[name="smtp_from_name"]').val()
                },
                dataType: 'json'
            }).then(r => {
                if (!r.success) {
                    Swal.showValidationMessage(r.message || 'Test email failed');
                    return false;
                }
                return r;
            }).catch(err => {
                var msg = (err && err.responseJSON && err.responseJSON.message) 
                    ? err.responseJSON.message 
                    : ((err && err.message) ? err.message : 'Request failed');
                if (Swal.getPopup()) {
                    Swal.showValidationMessage(msg);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
                return false;
            });
        }
    }).then(r => {
        if (r.isConfirmed) {
            Swal.fire({ icon: 'success', title: 'Sent', text: r.value.message, timer: 3000, showConfirmButton: false });
        }
    });
}
</script>
