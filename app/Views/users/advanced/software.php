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
            </div>
        </div>
    </section>
</div>