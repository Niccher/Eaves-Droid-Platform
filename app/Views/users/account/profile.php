<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-user-circle text-primary mr-2"></i>
                            My Profile
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-id-card text-primary mr-1"></i>
                                Account: <b><?php echo htmlspecialchars($user_info['username'] ?? 'User'); ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Manage your personal information and account settings</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="float-right mt-2">
                        <span class="badge badge-<?php echo $android_connected ? 'success' : 'danger'; ?> p-2">
                            <i class="fas fa-<?php echo $android_connected ? 'check-circle' : 'times-circle'; ?> mr-1"></i>
                            Android: <?php echo $android_connected ? 'Connected' : 'Not Connected'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left Column - Profile Info -->
                <div class="col-lg-4">
                    <!-- Profile Card -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user mr-2"></i>
                                Profile Information
                            </h3>
                        </div>
                        <div class="card-body text-center">
                            <!-- Profile Image -->
                            <div class="mb-4">
                                <?php if (!empty($user_info['profile_image'])): ?>
                                    <img src="<?php echo base_url('uploads/profiles/' . htmlspecialchars($user_info['profile_image'])); ?>"
                                         class="img-circle elevation-2" alt="Profile" style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="img-circle elevation-2 bg-primary d-flex align-items-center justify-content-center"
                                         style="width: 150px; height: 150px; margin: 0 auto;">
                                        <i class="fas fa-user fa-4x text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h4 class="mb-1"><?php echo htmlspecialchars($user_info['username'] ?? 'User'); ?></h4>
                            <p class="text-muted">
                                <i class="fas fa-envelope mr-1"></i>
                                <?php echo htmlspecialchars($user_info['email'] ?? 'No email'); ?>
                            </p>

                            <div class="mt-4">
                                <p class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        Joined: <?php echo !empty($user_info['created_at']) ? date('M d, Y', strtotime($user_info['created_at'])) : 'Unknown'; ?>
                                    </small>
                                </p>
                                <p class="mb-0">
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        Last seen: <?php echo !empty($user_info['last_seen_at']) ? date('M d, Y H:i', strtotime($user_info['last_seen_at'])) : 'Never'; ?>
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Account Stats -->
                    <div class="card card-info mt-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Account Statistics
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-mobile-alt text-primary mr-2"></i> Applications</span>
                                    <span class="badge badge-primary badge-pill"><?php echo $total_apps; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-address-book text-success mr-2"></i> Contacts</span>
                                    <span class="badge badge-success badge-pill"><?php echo $total_contacts; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-sms text-info mr-2"></i> SMS Messages</span>
                                    <span class="badge badge-info badge-pill"><?php echo $total_sms; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-phone text-warning mr-2"></i> Call Logs</span>
                                    <span class="badge badge-warning badge-pill"><?php echo $total_calls; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-key text-danger mr-2"></i> API Tokens</span>
                                    <span class="badge badge-danger badge-pill"><?php echo $total_tokens; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-laptop text-secondary mr-2"></i> Connected Devices</span>
                                    <span class="badge badge-secondary badge-pill"><?php echo $connected_devices; ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Profile Tabs -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab">
                                        <i class="fas fa-user-edit mr-2"></i>Edit Profile
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="export-tab" data-toggle="tab" href="#export" role="tab">
                                        <i class="fas fa-download mr-2"></i>Export Data
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="delete-tab" data-toggle="tab" href="#delete" role="tab">
                                        <i class="fas fa-trash-alt mr-2"></i>Delete Data
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab">
                                        <i class="fas fa-shield-alt mr-2"></i>Security
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content" id="profileTabsContent">

                                <!-- Edit Profile Tab -->
                                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                                    <form id="profileForm" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="username"><i class="fas fa-user mr-1"></i> Username</label>
                                                    <input type="text" class="form-control" id="username" name="username"
                                                           value="<?php echo htmlspecialchars($user_info['username'] ?? ''); ?>"
                                                           placeholder="Enter your username" required>
                                                    <small class="form-text text-muted">This is how you'll appear to others</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="email"><i class="fas fa-envelope mr-1"></i> Email Address</label>
                                                    <input type="email" class="form-control bg-light" id="email"
                                                           value="<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>"
                                                           readonly disabled>
                                                    <small class="form-text text-muted">
                                                        <i class="fas fa-lock mr-1"></i> Email cannot be changed for security reasons
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="bio"><i class="fas fa-pen mr-1"></i> Bio</label>
                                            <textarea class="form-control" id="bio" name="bio" rows="3"
                                                      placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user_info['bio'] ?? ''); ?></textarea>
                                            <small class="form-text text-muted">Share a brief description about yourself (max 500 characters)</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="profile_image"><i class="fas fa-camera mr-1"></i> Profile Picture</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="profile_image" name="profile_image" accept="image/*">
                                                <label class="custom-file-label" for="profile_image">Choose new profile picture</label>
                                            </div>
                                            <small class="form-text text-muted">Upload a JPG, PNG or GIF image (max 2MB)</small>
                                        </div>

                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            <strong>Note:</strong> Changes will be saved immediately. Your email address cannot be changed.
                                        </div>

                                        <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                                            <i class="fas fa-save mr-1"></i> Save Changes
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="cancelChanges">
                                            <i class="fas fa-times mr-1"></i> Cancel
                                        </button>
                                    </form>

                                    <div id="profileMessage" class="mt-3"></div>
                                </div>

                                <!-- Export Data Tab -->
                                <div class="tab-pane fade" id="export" role="tabpanel">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Export Your Data:</strong> Download your data in JSON format. This may take a few moments depending on data size.
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-mobile-alt fa-3x text-primary"></i>
                                                    </div>
                                                    <h5 class="card-title">Applications</h5>
                                                    <p class="card-text"><?php echo $total_apps; ?> installed apps</p>
                                                    <a href="<?php echo base_url('account/exportData/apps'); ?>" class="btn btn-outline-primary btn-block">
                                                        <i class="fas fa-download mr-1"></i> Export Apps
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-address-book fa-3x text-success"></i>
                                                    </div>
                                                    <h5 class="card-title">Contacts</h5>
                                                    <p class="card-text"><?php echo $total_contacts; ?> saved contacts</p>
                                                    <a href="<?php echo base_url('account/exportData/contacts'); ?>" class="btn btn-outline-success btn-block">
                                                        <i class="fas fa-download mr-1"></i> Export Contacts
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-sms fa-3x text-info"></i>
                                                    </div>
                                                    <h5 class="card-title">SMS Messages</h5>
                                                    <p class="card-text"><?php echo $total_sms; ?> SMS messages</p>
                                                    <a href="<?php echo base_url('account/exportData/sms'); ?>" class="btn btn-outline-info btn-block">
                                                        <i class="fas fa-download mr-1"></i> Export SMS
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-phone fa-3x text-warning"></i>
                                                    </div>
                                                    <h5 class="card-title">Call Logs</h5>
                                                    <p class="card-text"><?php echo $total_calls; ?> call records</p>
                                                    <a href="<?php echo base_url('account/exportData/calls'); ?>" class="btn btn-outline-warning btn-block">
                                                        <i class="fas fa-download mr-1"></i> Export Calls
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0">
                                                    <i class="fas fa-boxes mr-2"></i>
                                                    Complete Data Export
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text">Export all your data in a single JSON file containing:</p>
                                                <ul>
                                                    <li>All installed applications</li>
                                                    <li>All saved contacts</li>
                                                    <li>All SMS messages</li>
                                                    <li>All call logs</li>
                                                    <li>Export metadata and timestamps</li>
                                                </ul>
                                                <a href="<?php echo base_url('account/exportData/all'); ?>" class="btn btn-primary btn-lg btn-block">
                                                    <i class="fas fa-file-archive mr-2"></i> Export All Data
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Data Tab -->
                                <div class="tab-pane fade" id="delete" role="tabpanel">
                                    <div class="alert alert-danger">
                                        <h5><i class="fas fa-exclamation-triangle mr-2"></i>Warning: Permanent Deletion</h5>
                                        <p class="mb-0">Deleting data is permanent and cannot be undone. Please proceed with caution.</p>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border border-danger">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-mobile-alt fa-3x text-danger"></i>
                                                    </div>
                                                    <h5 class="card-title text-danger">Delete Applications</h5>
                                                    <p class="card-text"><?php echo $total_apps; ?> apps will be removed</p>
                                                    <a href="<?php echo base_url('account/deleteData/apps'); ?>" class="btn btn-outline-danger btn-block">
                                                        <i class="fas fa-trash mr-1"></i> Delete Apps
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border border-danger">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-address-book fa-3x text-danger"></i>
                                                    </div>
                                                    <h5 class="card-title text-danger">Delete Contacts</h5>
                                                    <p class="card-text"><?php echo $total_contacts; ?> contacts will be removed</p>
                                                    <a href="<?php echo base_url('account/deleteData/contacts'); ?>" class="btn btn-outline-danger btn-block">
                                                        <i class="fas fa-trash mr-1"></i> Delete Contacts
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border border-danger">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-sms fa-3x text-danger"></i>
                                                    </div>
                                                    <h5 class="card-title text-danger">Delete SMS</h5>
                                                    <p class="card-text"><?php echo $total_sms; ?> messages will be removed</p>
                                                    <a href="<?php echo base_url('account/deleteData/sms'); ?>" class="btn btn-outline-danger btn-block">
                                                        <i class="fas fa-trash mr-1"></i> Delete SMS
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border border-danger">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-phone fa-3x text-danger"></i>
                                                    </div>
                                                    <h5 class="card-title text-danger">Delete Call Logs</h5>
                                                    <p class="card-text"><?php echo $total_calls; ?> call records will be removed</p>
                                                    <a href="<?php echo base_url('account/deleteData/call_logs'); ?>" class="btn btn-outline-danger btn-block">
                                                        <i class="fas fa-trash mr-1"></i> Delete Calls
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="card border-danger">
                                            <div class="card-header bg-danger text-white">
                                                <h5 class="mb-0">
                                                    <i class="fas fa-skull-crossbones mr-2"></i>
                                                    Complete Data Wipe
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text">This will permanently delete ALL your data including:</p>
                                                <ul class="text-danger">
                                                    <li>All installed applications</li>
                                                    <li>All saved contacts</li>
                                                    <li>All SMS messages</li>
                                                    <li>All call logs</li>
                                                </ul>
                                                <p><strong>This action cannot be undone!</strong></p>
                                                <a href="<?php echo base_url('account/deleteData/all'); ?>" class="btn btn-danger btn-lg btn-block">
                                                    <i class="fas fa-bomb mr-2"></i> Delete All My Data
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Security Tab -->
                                <div class="tab-pane fade" id="security" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card border-info">
                                                <div class="card-header bg-info text-white">
                                                    <h5 class="mb-0">
                                                        <i class="fas fa-shield-alt mr-2"></i>
                                                        Account Security
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <h6><i class="fas fa-key mr-2"></i> Last Password Change</h6>
                                                        <p class="text-muted"><?php echo !empty($user_info['updated_at']) ? date('M d, Y H:i', strtotime($user_info['updated_at'])) : 'Never'; ?></p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6><i class="fas fa-user-shield mr-2"></i> Two-Factor Authentication</h6>
                                                        <p class="text-muted">Not enabled</p>
                                                        <button class="btn btn-outline-info btn-sm" disabled>
                                                            <i class="fas fa-cog mr-1"></i> Enable 2FA
                                                        </button>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6><i class="fas fa-history mr-2"></i> Last Login</h6>
                                                        <p class="text-muted"><?php echo !empty($user_info['last_seen_at']) ? date('M d, Y H:i', strtotime($user_info['last_seen_at'])) : 'Never'; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card border-warning">
                                                <div class="card-header bg-warning text-white">
                                                    <h5 class="mb-0">
                                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                                        Security Recommendations
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-unstyled">
                                                        <li class="mb-2">
                                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                                            <span>Use a strong, unique password</span>
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-times-circle text-danger mr-2"></i>
                                                            <span>Enable two-factor authentication</span>
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                                            <span>Regularly review access logs</span>
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                                            <span>Log out from unused devices</span>
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-times-circle text-danger mr-2"></i>
                                                            <span>Set up recovery email</span>
                                                        </li>
                                                    </ul>
                                                    <a href="<?php echo base_url('account/access_logs'); ?>" class="btn btn-warning btn-block">
                                                        <i class="fas fa-history mr-1"></i> Review Access Logs
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .nav-tabs .nav-link {
        border-radius: 0;
        padding: 15px 20px;
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        background-color: #fff;
        border-bottom-color: #fff;
    }

    .card.h-100 {
        transition: transform 0.3s ease;
    }

    .card.h-100:hover {
        transform: translateY(-5px);
    }

    .img-circle {
        border-radius: 50% !important;
        border: 3px solid #dee2e6;
    }

    .list-group-item {
        border-left: 0;
        border-right: 0;
    }

    .list-group-item:first-child {
        border-top: 0;
    }

    .list-group-item:last-child {
        border-bottom: 0;
    }
</style>

<script>
    $(document).ready(function() {
        // Profile form submission
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = $('#saveProfileBtn');
            const originalText = submitBtn.html();

            // Show loading state
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
            submitBtn.prop('disabled', true);

            // Clear previous messages
            $('#profileMessage').html('').removeClass('alert alert-success alert-danger');

            $.ajax({
                url: '<?php echo base_url("account/updateProfile"); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#profileMessage').html(
                            '<div class="alert alert-success">' +
                            '<i class="fas fa-check-circle mr-2"></i>' +
                            response.message +
                            '</div>'
                        );

                        // Reload page after 2 seconds
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        $('#profileMessage').html(
                            '<div class="alert alert-danger">' +
                            '<i class="fas fa-exclamation-circle mr-2"></i>' +
                            response.message +
                            '</div>'
                        );
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    $('#profileMessage').html(
                        '<div class="alert alert-danger">' +
                        '<i class="fas fa-exclamation-circle mr-2"></i>' +
                        'An error occurred while saving. Please try again.' +
                        '</div>'
                    );
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                }
            });
        });

        // Cancel changes button
        $('#cancelChanges').on('click', function() {
            window.location.reload();
        });

        // Update file input label
        $('#profile_image').on('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Choose file';
            const label = $(this).next('.custom-file-label');
            label.text(fileName);
        });

        // Delete data confirmation
        $('a[href*="deleteData"]').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');

            // Show confirmation dialog
            Swal.fire({
                title: 'Confirm Deletion',
                text: 'You will be redirected to a confirmation page. Are you sure you want to proceed?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, continue',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });

        // Tab switching
        $('.nav-tabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>