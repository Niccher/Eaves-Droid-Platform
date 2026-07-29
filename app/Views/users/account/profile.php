<div class="content-wrapper">
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
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user mr-2"></i>
                                Profile Information
                            </h3>
                        </div>
                        <div class="card-body text-center">
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

                    <!-- Reset App -->
                    <div class="card card-outline card-danger mt-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Reset Android App
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">
                                <i class="fas fa-info-circle mr-1"></i>
                                Removes stealth disguise, restores default icon, resets launch codes
                                (<code>*#007#</code>, <code>1234</code>).
                            </p>
                            <button type="button" class="btn btn-danger btn-block" id="resetDeviceBtn">
                                <i class="fas fa-undo mr-1"></i> Reset App on Device
                            </button>
                            <div id="resetMessage" class="mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Tabs -->
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
                                <li class="nav-item">
                                    <a class="nav-link" id="statistics-tab" data-toggle="tab" href="#statistics" role="tab">
                                        <i class="fas fa-chart-bar mr-2"></i>Statistics
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content" id="profileTabsContent">

                                <!-- ============================== -->
                                <!-- EDIT PROFILE TAB -->
                                <!-- ============================== -->
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

                                <!-- ============================== -->
                                <!-- EXPORT DATA TAB -->
                                <!-- ============================== -->
                                <div class="tab-pane fade" id="export" role="tabpanel">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Export Your Data:</strong> Choose a format and date range, then download your data.
                                    </div>

                                    <!-- Format & Date Filters -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="text-muted small"><i class="fas fa-file-export mr-1"></i> Export Format</label>
                                            <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                                                <label class="btn btn-outline-info active" id="format-json">
                                                    <input type="radio" name="export-format" value="json" checked>
                                                    <i class="fas fa-code mr-1"></i> JSON
                                                </label>
                                                <label class="btn btn-outline-info" id="format-csv">
                                                    <input type="radio" name="export-format" value="csv">
                                                    <i class="fas fa-table mr-1"></i> CSV
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="text-muted small"><i class="fas fa-calendar-alt mr-1"></i> From</label>
                                            <input type="date" class="form-control form-control-sm" id="export-date-from">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="text-muted small"><i class="fas fa-calendar-alt mr-1"></i> To</label>
                                            <input type="date" class="form-control form-control-sm" id="export-date-to">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <?php
                                        $miscSoftwareCount = ($total_device + $total_network + $total_accounts + $total_calendar + $total_app_usage + $total_notifications + $total_accessibility + $total_input_methods + $total_security_audit + $total_proc_info + $total_data_usage + $total_saved_wifi + $total_default_apps + $total_alarms + $total_app_security + $total_network_security + $total_telephony_network + $total_system_locale);
                                        $miscHardwareCount = ($total_hardware_graphics + $total_hardware_network + $total_camera_info + $total_battery_stats + $total_sensors + $total_bluetooth + $total_cell_towers + $total_display_info + $total_storage + $total_thermal + $total_nfc + $total_processes);
                                        $exportTypes = [
                                            'apps'           => ['icon' => 'fa-mobile-alt', 'color' => 'primary', 'label' => 'Applications', 'count' => $total_apps],
                                            'contacts'       => ['icon' => 'fa-address-book', 'color' => 'success', 'label' => 'Contacts', 'count' => $total_contacts],
                                            'sms'            => ['icon' => 'fa-sms', 'color' => 'info', 'label' => 'SMS Messages', 'count' => $total_sms],
                                            'calls'          => ['icon' => 'fa-phone', 'color' => 'warning', 'label' => 'Call Logs', 'count' => $total_calls],
                                            'files'          => ['icon' => 'fa-file-alt', 'color' => 'secondary', 'label' => 'Files Metadata', 'count' => $total_files],
                                            'locations'      => ['icon' => 'fa-map-marked-alt', 'color' => 'danger', 'label' => 'Location History', 'count' => $total_locations],
                                            'misc_software'  => ['icon' => 'fa-laptop-code', 'color' => 'info', 'label' => 'Misc Software', 'count' => $miscSoftwareCount, 'subs' => [
                                                ['icon' => 'fa-user', 'label' => 'Accounts', 'count' => $total_accounts],
                                                ['icon' => 'fa-calendar-alt', 'label' => 'Calendar', 'count' => $total_calendar],
                                                ['icon' => 'fa-chart-bar', 'label' => 'App Usage', 'count' => $total_app_usage],
                                                ['icon' => 'fa-bell', 'label' => 'Notifications', 'count' => $total_notifications],
                                                ['icon' => 'fa-info-circle', 'label' => 'Device Context', 'count' => $total_device],
                                                ['icon' => 'fa-network-wired', 'label' => 'Network Info', 'count' => $total_network],
                                                ['icon' => 'fa-universal-access', 'label' => 'Accessibility', 'count' => $total_accessibility],
                                                ['icon' => 'fa-keyboard', 'label' => 'Input Methods', 'count' => $total_input_methods],
                                                ['icon' => 'fa-shield-alt', 'label' => 'Security Audit', 'count' => $total_security_audit],
                                                ['icon' => 'fa-microchip', 'label' => 'Proc Info', 'count' => $total_proc_info],
                                                ['icon' => 'fa-chart-line', 'label' => 'Data Usage', 'count' => $total_data_usage],
                                                ['icon' => 'fa-wifi', 'label' => 'Saved WiFi', 'count' => $total_saved_wifi],
                                                ['icon' => 'fa-th-list', 'label' => 'Default Apps', 'count' => $total_default_apps],
                                                ['icon' => 'fa-clock', 'label' => 'Alarms', 'count' => $total_alarms],
                                                ['icon' => 'fa-lock', 'label' => 'App Security', 'count' => $total_app_security],
                                                ['icon' => 'fa-shield-virus', 'label' => 'Network Security', 'count' => $total_network_security],
                                                ['icon' => 'fa-sim-card', 'label' => 'Telephony Network', 'count' => $total_telephony_network],
                                                ['icon' => 'fa-language', 'label' => 'System Locale', 'count' => $total_system_locale],
                                            ]],
                                            'misc_hardware'  => ['icon' => 'fa-microchip', 'color' => 'primary', 'label' => 'Misc Hardware', 'count' => $miscHardwareCount, 'subs' => [
                                                ['icon' => 'fa-palette', 'label' => 'Hardware Graphics', 'count' => $total_hardware_graphics],
                                                ['icon' => 'fa-network-wired', 'label' => 'Hardware Network', 'count' => $total_hardware_network],
                                                ['icon' => 'fa-camera', 'label' => 'Camera Info', 'count' => $total_camera_info],
                                                ['icon' => 'fa-battery-full', 'label' => 'Battery Stats', 'count' => $total_battery_stats],
                                                ['icon' => 'fa-ruler', 'label' => 'Sensors', 'count' => $total_sensors],
                                                ['icon' => 'fa-bluetooth-b', 'label' => 'Bluetooth', 'count' => $total_bluetooth],
                                                ['icon' => 'fa-broadcast-tower', 'label' => 'Cell Towers', 'count' => $total_cell_towers],
                                                ['icon' => 'fa-tv', 'label' => 'Display Info', 'count' => $total_display_info],
                                                ['icon' => 'fa-hdd', 'label' => 'Storage', 'count' => $total_storage],
                                                ['icon' => 'fa-thermometer-half', 'label' => 'Thermal', 'count' => $total_thermal],
                                                ['icon' => 'fa-credit-card', 'label' => 'NFC', 'count' => $total_nfc],
                                                ['icon' => 'fa-tasks', 'label' => 'Processes', 'count' => $total_processes],
                                            ]],
                                        ];
                                        foreach ($exportTypes as $key => $et):
                                        ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas <?php echo $et['icon']; ?> fa-3x text-<?php echo $et['color']; ?>"></i>
                                                    </div>
                                                    <h5 class="card-title"><?php echo $et['label']; ?></h5>
                                                    <p class="card-text"><?php echo $et['count']; ?> records</p>
                                                    <?php if (!empty($et['subs'])): ?>
                                                        <button class="btn btn-outline-<?php echo $et['color']; ?> btn-sm btn-block mb-1 view-subs-btn"
                                                                data-key="<?php echo $key; ?>">
                                                            <i class="fas fa-list mr-1"></i> View Breakdown
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-outline-<?php echo $et['color']; ?> btn-block export-trigger-btn"
                                                            data-type="<?php echo $key; ?>"
                                                            data-label="<?php echo $et['label']; ?>">
                                                        <i class="fas fa-download mr-1"></i> Export
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
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
                                                <p class="card-text">Export all your data in a single file:</p>
                                                <ul>
                                                    <li>All installed applications</li>
                                                    <li>All saved contacts</li>
                                                    <li>All SMS messages</li>
                                                    <li>All call logs</li>
                                                    <li>Location history and activities</li>
                                                    <li>All misc software data (accounts, calendar, app usage, notifications, device, network, accessibility, security, locale, and more)</li>
                                                    <li>All misc hardware data (graphics, network, camera, battery, sensors, bluetooth, storage, thermal, NFC, processes)</li>
                                                </ul>
                                                <button class="btn btn-primary btn-lg btn-block export-trigger-btn" data-type="all" data-label="All Data">
                                                    <i class="fas fa-file-archive mr-2"></i> Export All Data
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ============================== -->
                                <!-- DELETE DATA TAB -->
                                <!-- ============================== -->
                                <div class="tab-pane fade" id="delete" role="tabpanel">
                                    <div class="alert alert-danger">
                                        <h5><i class="fas fa-exclamation-triangle mr-2"></i> Warning: Permanent Deletion</h5>
                                        <p class="mb-0">Deleting data is permanent and cannot be undone. Please proceed with caution.</p>
                                    </div>

                                    <div class="row">
                                        <?php
                                        $deleteTypes = [
                                            'apps'           => ['icon' => 'fa-mobile-alt', 'label' => 'Applications', 'count' => $total_apps],
                                            'contacts'       => ['icon' => 'fa-address-book', 'label' => 'Contacts', 'count' => $total_contacts],
                                            'sms'            => ['icon' => 'fa-sms', 'label' => 'SMS Messages', 'count' => $total_sms],
                                            'calls'          => ['icon' => 'fa-phone', 'label' => 'Call Logs', 'count' => $total_calls],
                                            'files'          => ['icon' => 'fa-file-excel', 'label' => 'Files Metadata', 'count' => $total_files],
                                            'locations'      => ['icon' => 'fa-map-marker-alt', 'label' => 'Locations & Activities', 'count' => $total_locations + $total_activities],
                                            'misc_software'  => ['icon' => 'fa-laptop-code', 'label' => 'Misc Software', 'count' => $miscSoftwareCount, 'subs' => $exportTypes['misc_software']['subs']],
                                            'misc_hardware'  => ['icon' => 'fa-microchip', 'label' => 'Misc Hardware', 'count' => $miscHardwareCount, 'subs' => $exportTypes['misc_hardware']['subs']],
                                        ];
                                        foreach ($deleteTypes as $key => $dt):
                                        ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border border-danger">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas <?php echo $dt['icon']; ?> fa-3x text-danger"></i>
                                                    </div>
                                                    <h5 class="card-title text-danger">Delete <?php echo $dt['label']; ?></h5>
                                                    <p class="card-text"><?php echo $dt['count']; ?> records will be removed</p>
                                                    <?php if (!empty($dt['subs'])): ?>
                                                        <button class="btn btn-outline-danger btn-sm btn-block mb-1 view-subs-btn"
                                                                data-key="<?php echo $key; ?>">
                                                            <i class="fas fa-list mr-1"></i> View Breakdown
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-outline-danger btn-block delete-btn"
                                                            data-type="<?php echo $key; ?>"
                                                            data-label="<?php echo $dt['label']; ?>">
                                                        <i class="fas fa-trash mr-1"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
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
                                                    <li>All file metadata and location history</li>
                                                    <li>All misc software data (accounts, calendar, app usage, notifications, device, network, accessibility, security, locale, and more)</li>
                                                    <li>All misc hardware data (graphics, network, camera, battery, sensors, bluetooth, storage, thermal, NFC, processes)</li>
                                                </ul>
                                                <p><strong>This action cannot be undone!</strong></p>
                                                <button class="btn btn-danger btn-lg btn-block delete-btn" data-type="all" data-label="ALL">
                                                    <i class="fas fa-bomb mr-2"></i> Delete All My Data
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ============================== -->
                                <!-- SECURITY TAB -->
                                <!-- ============================== -->
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

                                <!-- ============================== -->
                                <!-- STATISTICS TAB -->
                                <!-- ============================== -->
                                <div class="tab-pane fade" id="statistics" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card card-info card-outline">
                                                <div class="card-header">
                                                    <h5 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Data Overview</h5>
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
                                        <div class="col-md-6">
                                            <div class="card card-outline card-info">
                                                <div class="card-header">
                                                    <h5 class="card-title">
                                                        <i class="fas fa-database mr-2"></i>
                                                        Data Summary
                                                    </h5>
                                                </div>
                                                <div class="card-body pt-2">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span><i class="fas fa-box mr-1"></i> Estimated Storage</span>
                                                        <span class="badge badge-light"><?php echo $estimated_storage ?? '~0 B'; ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span><i class="fas fa-download mr-1"></i> Total Exports</span>
                                                        <span class="badge badge-light"><?php echo $export_count ?? 0; ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span><i class="fas fa-clock mr-1"></i> Last Export</span>
                                                        <span class="badge badge-light">
                                                            <?php echo !empty($last_exported_at) ? date('M d, Y', strtotime($last_exported_at)) : 'Never'; ?>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span><i class="fas fa-trash mr-1"></i> Last Deletion</span>
                                                        <span class="badge badge-light">
                                                            <?php echo !empty($last_deleted_data_at) ? date('M d, Y', strtotime($last_deleted_data_at)) : 'Never'; ?>
                                                        </span>
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
        </div>
    </section>
</div>

<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="exportModalDesc">Choose how you want to export your data.</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary export-option" data-method="download" style="cursor:pointer;">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-download fa-3x text-primary mb-3"></i>
                                <h5>Direct Download</h5>
                                <p class="text-muted small mb-0">Download the file immediately</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info export-option" data-method="email" style="cursor:pointer;">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-envelope fa-3x text-info mb-3"></i>
                                <h5>Email</h5>
                                <p class="text-muted small mb-0">Get the export sent to your inbox</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="exportEmailSection" style="display:none;" class="mt-3">
                    <label class="text-muted small"><i class="fas fa-envelope mr-1"></i> Email Address</label>
                    <input type="email" class="form-control" id="exportEmailInput"
                           value="<?= htmlspecialchars($user_info['email'] ?? '') ?>"
                           placeholder="your@email.com">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="exportModalConfirmBtn" disabled>
                    <i class="fas fa-download mr-1"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="subsModal" tabindex="-1" role="dialog" aria-labelledby="subsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subsModalLabel">Data Breakdown</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="subsModalBody">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Sub-Type</th><th class="text-right">Records</th></tr></thead>
                    <tbody id="subsModalTableBody"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
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
    .bg-gradient-navy {
        background: linear-gradient(135deg, #001a33 0%, #003366 100%);
    }
</style>

<script>
    $(document).ready(function() {
        // ========================
        // EDIT PROFILE
        // ========================
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = $('#saveProfileBtn');
            const originalText = submitBtn.html();

            submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
            submitBtn.prop('disabled', true);
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
                error: function() {
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

        $('#cancelChanges').on('click', function() {
            window.location.reload();
        });

        $('#profile_image').on('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Choose file';
            const label = $(this).next('.custom-file-label');
            label.text(fileName);
        });

        // ========================
        // EXPORT MODAL
        // ========================
        var exportType = '';
        var exportLabel = '';

        $('.export-trigger-btn').on('click', function() {
            exportType = $(this).data('type');
            exportLabel = $(this).data('label');
            $('#exportModalLabel').text('Export ' + exportLabel);
            $('#exportModalDesc').text('Choose how you want to export your ' + exportLabel.toLowerCase() + ' data.');
            $('#exportEmailSection').hide();
            $('#exportEmailInput').val('<?= htmlspecialchars($user_info['email'] ?? '') ?>');
            $('.export-option').removeClass('border-primary border-info').addClass('border');
            $('#exportModalConfirmBtn').prop('disabled', true).html('<i class="fas fa-download mr-1"></i> Download');
            $('#exportModal').modal('show');
        });

        $('.export-option').on('click', function() {
            var method = $(this).data('method');
            $('.export-option').removeClass('border-primary border-info').addClass('border');
            if (method === 'download') {
                $(this).removeClass('border').addClass('border-primary');
                $('#exportEmailSection').hide();
                $('#exportModalConfirmBtn').prop('disabled', false).html('<i class="fas fa-download mr-1"></i> Download');
            } else {
                $(this).removeClass('border').addClass('border-info');
                $('#exportEmailSection').show();
                var email = $('#exportEmailInput').val().trim();
                $('#exportModalConfirmBtn').prop('disabled', !email).html('<i class="fas fa-envelope mr-1"></i> Send Email');
            }
        });

        $('#exportEmailInput').on('input', function() {
            var method = $('.export-option.border-primary, .export-option.border-info').data('method');
            if (method === 'email') {
                var email = $(this).val().trim();
                $('#exportModalConfirmBtn').prop('disabled', !email);
            }
        });

        $('#exportModalConfirmBtn').on('click', function() {
            var method = $('.export-option.border-primary, .export-option.border-info').data('method');
            if (!method) return;

            var format = $('input[name="export-format"]:checked').val() || 'json';
            var dateFrom = $('#export-date-from').val();
            var dateTo = $('#export-date-to').val();

            if (method === 'download') {
                let url = '<?php echo base_url("account/exportData"); ?>/' + exportType + '?format=' + format;
                if (dateFrom) url += '&date_from=' + dateFrom;
                if (dateTo) url += '&date_to=' + dateTo;
                $('#exportModal').modal('hide');
                window.location.href = url;
            } else {
                var email = $('#exportEmailInput').val().trim();
                if (!email) return;
                var btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Sending...');
                btn.prop('disabled', true);

                $.ajax({
                    url: '<?= base_url("account/export-email") ?>',
                    method: 'POST',
                    data: { type: exportType, format: format, email: email },
                    dataType: 'json'
                }).done(function(r) {
                    if (r.success) {
                        $('#exportModal').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Sent', text: r.message, timer: 4000, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: r.message });
                    }
                }).fail(function(xhr) {
                    var msg = 'Request failed';
                    try { var r = JSON.parse(xhr.responseText); msg = r.message || msg; } catch(e) {}
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }).always(function() {
                    btn.html('<i class="fas fa-envelope mr-1"></i> Send Email');
                    btn.prop('disabled', false);
                });
            }
        });

        // ========================
        // DELETE DATA (Inline SweetAlert2)
        // ========================
        $('.delete-btn').on('click', function() {
            const type = $(this).data('type');
            const label = $(this).data('label');
            const btn = $(this);
            const csrf = '<?php echo $csrf_token; ?>';

            Swal.fire({
                title: 'Delete ' + label + '?',
                text: 'This action is permanent and cannot be undone. Type "DELETE" in the box below to confirm.',
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'Type DELETE here',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                preConfirm: (input) => {
                    if (input !== 'DELETE') {
                        Swal.showValidationMessage('You must type "DELETE" exactly to confirm');
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');
                    btn.prop('disabled', true);

                    $.ajax({
                        url: '<?php echo base_url("account/deleteData"); ?>/' + type,
                        type: 'POST',
                        data: {
                            csrf_token: csrf,
                            confirmation: 'DELETE'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(function() {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Failed to delete data'
                                });
                                btn.html('<i class="fas fa-trash mr-1"></i> Delete');
                                btn.prop('disabled', false);
                            }
                        },
                        error: function(xhr) {
                            let msg = 'An error occurred';
                            try {
                                const resp = JSON.parse(xhr.responseText);
                                msg = resp.message || msg;
                            } catch(e) {}
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg
                            });
                            btn.html('<i class="fas fa-trash mr-1"></i> Delete');
                            btn.prop('disabled', false);
                        }
                    });
                }
            });
        });

        // ========================
        // VIEW BREAKDOWN MODALS
        // ========================
        var subsData = <?php echo json_encode([
            'misc_software' => $exportTypes['misc_software']['subs'],
            'misc_hardware' => $exportTypes['misc_hardware']['subs'],
        ]); ?>;

        $('.view-subs-btn').on('click', function() {
            var key = $(this).data('key');
            var items = subsData[key] || [];
            var label = $(this).closest('.card-body').find('.card-title').text().replace('Delete ', '');
            $('#subsModalLabel').text(label + ' - Breakdown');
            var tbody = $('#subsModalTableBody');
            tbody.empty();
            if (items.length === 0) {
                tbody.append('<tr><td colspan="2" class="text-center text-muted">No sub-categories</td></tr>');
            } else {
                items.forEach(function(item) {
                    var iconHtml = item.icon ? '<i class="fas ' + item.icon + ' mr-2"></i>' : '';
                    tbody.append('<tr><td>' + iconHtml + item.label + '</td><td class="text-right">' + item.count + '</td></tr>');
                });
            }
            $('#subsModal').modal('show');
        });

        // ========================
        // TAB SWITCHING
        // ========================
        $('.nav-tabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // ========================
        // FORMAT TOGGLE UI
        // ========================
        $('#format-json, #format-csv').on('click', function() {
            $('#format-json, #format-csv').removeClass('active');
            $(this).addClass('active');
        });

        $('[data-toggle="tooltip"]').tooltip();

        // ========================
        // RESET ANDROID APP
        // ========================
        $('#resetDeviceBtn').on('click', function() {
            const btn = $(this);
            Swal.fire({
                title: 'Reset Android App?',
                html: 'This will send a command to your Android device to:<br>' +
                      '• Remove stealth disguise<br>' +
                      '• Restore default app icon<br>' +
                      '• Reset dial code to <code>*#007#</code><br>' +
                      '• Reset calculator code to <code>1234</code><br>' +
                      '• Disable ghost mode',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, reset app',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Sending...');
                    btn.prop('disabled', true);
                    $('#resetMessage').html('');

                    $.ajax({
                        url: '<?= base_url("account/reset-device") ?>',
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Command Sent',
                                    text: response.message,
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed',
                                    text: response.message || 'Could not send reset command'
                                });
                            }
                            btn.html('<i class="fas fa-undo mr-1"></i> Reset App on Device');
                            btn.prop('disabled', false);
                        },
                        error: function(xhr) {
                            let msg = 'Network error';
                            try {
                                const resp = JSON.parse(xhr.responseText);
                                msg = resp.message || resp.messages?.error || msg;
                            } catch(e) {}
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg
                            });
                            btn.html('<i class="fas fa-undo mr-1"></i> Reset App on Device');
                            btn.prop('disabled', false);
                        }
                    });
                }
            });
        });
    });
</script>