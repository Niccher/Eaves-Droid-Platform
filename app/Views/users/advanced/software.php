<?php /** @var array $counts */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-laptop-code text-secondary mr-2"></i>Software</h1>
                    <p class="text-muted mt-1 mb-0">Installed apps, system services, and configuration data</p>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-user-circle fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Accounts</h6>
                            <p class="text-muted small mb-2">User accounts, email addresses, and credentials</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_accounts'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/accounts'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-calendar-alt fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Calendar</h6>
                            <p class="text-muted small mb-2">Calendar events, reminders, and schedule data</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_calendar'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/calendar'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-chart-pie fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">App Usage</h6>
                            <p class="text-muted small mb-2">Application usage time and frequency statistics</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_app_usage'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/app-usage'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-bell fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Notifications</h6>
                            <p class="text-muted small mb-2">Notifications from apps with timestamps and titles</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_notifications'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/notifications'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-shield-alt fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Security Audit</h6>
                            <p class="text-muted small mb-2">Security findings, permissions, and vulnerability data</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_security_audit'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/security_audit'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-photo-video fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Remote Media</h6>
                            <p class="text-muted small mb-2">Photos, videos, and media files on the device</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_media'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/media'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-universal-access fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Accessibility</h6>
                            <p class="text-muted small mb-2">Enabled accessibility services and settings</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_accessibility'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/accessibility'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-keyboard fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Input Methods</h6>
                            <p class="text-muted small mb-2">Active keyboards and input method engines</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_input_methods'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/input_methods'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-chart-line fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Data Usage</h6>
                            <p class="text-muted small mb-2">Per-app mobile and WiFi data usage</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_data_usage'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/data_usage'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-wifi fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Saved WiFi</h6>
                            <p class="text-muted small mb-2">Configured WiFi networks and security types</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_saved_wifi'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/saved_wifi'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-cogs fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Default Apps</h6>
                            <p class="text-muted small mb-2">Default browser, dialer, SMS, launcher handlers</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_default_apps'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/default_apps'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-clock fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Alarms & Jobs</h6>
                            <p class="text-muted small mb-2">Scheduled JobScheduler jobs and AlarmManager alarms</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_alarms'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/alarms'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-user-shield fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">App Security</h6>
                            <p class="text-muted small mb-2">Device admins, permission maps, running services</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_app_security'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/app_security'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-lock fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Network Security</h6>
                            <p class="text-muted small mb-2">DNS config, VPN status, proxy settings</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_network_security'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/network_security'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-signal fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Mobile Network</h6>
                            <p class="text-muted small mb-2">IMS/VoLTE, data roaming, carrier config</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_telephony_network'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/telephony_network'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-language fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">System Locale</h6>
                            <p class="text-muted small mb-2">Locale, region, timezone, system fonts</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_system_locale'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/software/system_locale'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>