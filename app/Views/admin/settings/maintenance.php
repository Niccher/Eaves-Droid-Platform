<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-tools mr-2"></i>Maintenance</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Maintenance</li>
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
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Maintenance Mode</h5>
                        <p class="mb-0 small text-muted">Enable or disable maintenance mode, set scheduled maintenance windows, define allowed IP ranges, and configure the maintenance page message shown to users.</p>
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
                                <li class="nav-item"><a href="<?= base_url('admin/settings/notifications') ?>" class="nav-link"><i class="fas fa-bell mr-1"></i> Notifications</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/maintenance') ?>" class="nav-link active"><i class="fas fa-tools mr-1"></i> Maintenance</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage') ?>" class="nav-link"><i class="fas fa-hdd mr-1"></i> Storage Monitor</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage-cleanup') ?>" class="nav-link"><i class="fas fa-broom mr-1"></i> Storage Cleanup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/email-triggers') ?>" class="nav-link"><i class="fas fa-envelope mr-1"></i> Email Triggers</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-hdd mr-1"></i> Backup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/cron') ?>" class="nav-link"><i class="fas fa-clock mr-1"></i> Cron Jobs</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-shield-alt mr-2"></i>Maintenance Mode</h3>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="maintenanceTabsContent">

                                <!-- ==================== MAINTENANCE MODE TAB ==================== -->
                                <div class="tab-pane fade show active" id="tab-mode" role="tabpanel">
                                        <div class="card card-outline card-<?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'danger' : 'success' ?> shadow-sm mb-0">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-<?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'exclamation-triangle text-danger' : 'check-circle text-success' ?> mr-1"></i>
                                                Maintenance Mode
                                                <?php if (($settings['maintenance_mode'] ?? '0') === '1'): ?>
                                                    <span class="badge badge-danger ml-2">ACTIVE</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success ml-2">INACTIVE</span>
                                                <?php endif; ?>
                                            </h3>
                                        </div>
                                        <?php
                                        $maintenanceMode = $settings['maintenance_mode'] ?? '0';
                                        $maintenanceType = $settings['maintenance_type'] ?? 'now_until_unknown';
                                        $startTime = $settings['maintenance_start'] ?? null;
                                        $endTime = $settings['maintenance_end'] ?? null;
                                        $now = date('Y-m-d\TH:i:s');
                                        ?>
                                        <?php if ($maintenanceMode === '1' && $maintenanceType === 'now_until' && $endTime): ?>
                                        <div class="card-footer bg-light py-2">
                                            <small class="text-muted"><i class="fas fa-hourglass-half mr-1"></i>Maintenance ends in: <strong><span class="countdown-timer" data-target="<?= date('Y-m-d\TH:i:s', strtotime($endTime)) ?>">--</span></strong></small>
                                        </div>
                                        <?php elseif ($maintenanceMode === '1' && $maintenanceType === 'scheduled' && $endTime): ?>
                                        <div class="card-footer bg-light py-2">
                                            <small class="text-muted"><i class="fas fa-hourglass-half mr-1"></i>Maintenance ends in: <strong><span class="countdown-timer" data-target="<?= date('Y-m-d\TH:i:s', strtotime($endTime)) ?>">--</span></strong></small>
                                        </div>
                                        <?php elseif ($maintenanceMode === '0' && $maintenanceType === 'scheduled' && $startTime && $now < $startTime): ?>
                                        <div class="card-footer bg-info py-2">
                                            <small class="text-white"><i class="fas fa-clock mr-1"></i>Maintenance starts in: <strong><span class="countdown-timer" data-target="<?= date('Y-m-d\TH:i:s', strtotime($startTime)) ?>">--</span></strong></small>
                                        </div>
                                        <?php endif; ?>
                                        <form action="<?= base_url('admin/settings/update') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="section" value="app">
                                            <div class="card-body">
                                                <div class="callout callout-<?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'danger' : 'info' ?> bg-light py-2 px-3 mb-3 small">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    When maintenance mode is <strong>active</strong>, only administrators (admin/superadmin) can log in and access the system. Public landing pages remain accessible. All other users will see a 503 Service Unavailable page.
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label font-weight-bold">Maintenance Mode</label>
                                                    <div class="col-sm-8">
                                                        <div class="custom-control custom-switch">
                                                            <input type="hidden" name="maintenance_mode" value="0">
                                                            <input type="checkbox" class="custom-control-input" id="maintenance_mode" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                                                            <label class="custom-control-label" for="maintenance_mode">Enable maintenance mode</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label font-weight-bold">Schedule Type</label>
                                                    <div class="col-sm-8">
                                                        <select name="maintenance_type" class="form-control" id="maintenance_type">
                                                            <option value="now_until_unknown" <?= ($settings['maintenance_type'] ?? 'now_until_unknown') === 'now_until_unknown' ? 'selected' : '' ?>>Now until manually disabled</option>
                                                            <option value="now_until" <?= ($settings['maintenance_type'] ?? 'now_until_unknown') === 'now_until' ? 'selected' : '' ?>>From now until a specific time</option>
                                                            <option value="scheduled" <?= ($settings['maintenance_type'] ?? 'now_until_unknown') === 'scheduled' ? 'selected' : '' ?>>Schedule between start and end time</option>
                                                        </select>
                                                        <small class="text-muted">
                                                            <span id="type_desc_now_until_unknown">Maintenance runs immediately when enabled and continues until an admin disables it.</span>
                                                            <span id="type_desc_now_until" style="display:none;">Maintenance starts immediately when enabled and automatically ends at the specified date/time.</span>
                                                            <span id="type_desc_scheduled" style="display:none;">Maintenance automatically activates at the start time and deactivates at the end time.</span>
                                                        </small>
                                                    </div>
                                                </div>

                                                <div id="schedule_fields" style="display:none;">
                                                    <div class="form-group row" id="field_start">
                                                        <label class="col-sm-4 col-form-label">Start Date & Time</label>
                                                        <div class="col-sm-8">
                                                            <input type="datetime-local" name="maintenance_start" class="form-control" value="<?= htmlspecialchars($settings['maintenance_start'] ?? '') ?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row" id="field_end">
                                                        <label class="col-sm-4 col-form-label">End Date & Time</label>
                                                        <div class="col-sm-8">
                                                            <input type="datetime-local" name="maintenance_end" class="form-control" value="<?= htmlspecialchars($settings['maintenance_end'] ?? '') ?>">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="callout callout-warning bg-light py-2 px-3 mb-0 small">
                                                    <i class="fas fa-clock text-warning mr-1"></i>
                                                    Server time: <strong id="server-time"><?= date('Y-m-d H:i:s') ?></strong> (<?= date_default_timezone_get() ?>).
                                                </div>
                                                <div class="callout callout-info bg-light py-2 px-3 mb-0 small mt-1">
                                                    <i class="fas fa-info-circle text-info mr-1"></i>
                                                    <strong>Scheduled maintenance</strong> requires the cron job <code>maintenance:check</code> to be running. Add it from the <a href="<?= base_url('admin/settings/cron') ?>">Cron Jobs</a> page with schedule <code>* * * * *</code> (every minute).
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Maintenance Settings</button>
                                                <?php if (($settings['maintenance_mode'] ?? '0') === '1'): ?>
                                                    <a href="<?= base_url('logout') ?>" class="btn btn-outline-secondary float-right" onclick="return confirm('Test maintenance mode: you will be logged out and should see the 503 page when trying to log back in as a non-admin. Continue?')">
                                                        <i class="fas fa-shield-alt mr-1"></i> Test Maintenance Mode
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                    </div>
                                </div>

        </div>
    </section>
</div>

<script>
$(document).ready(function() {

    function updateScheduleFields() {
        const type = $('#maintenance_type').val();
        const $scheduleFields = $('#schedule_fields');
        const $fieldStart = $('#field_start');
        const $fieldEnd = $('#field_end');

        $scheduleFields.hide();
        $fieldStart.show();
        $fieldEnd.show();

        if (type === 'now_until_unknown') {
            $scheduleFields.hide();
        } else if (type === 'now_until') {
            $scheduleFields.show();
            $fieldStart.hide();
        } else if (type === 'scheduled') {
            $scheduleFields.show();
            $fieldStart.show();
            $fieldEnd.show();
        }

        $('#type_desc_now_until_unknown, #type_desc_now_until, #type_desc_scheduled').hide();
        $('#type_desc_' + type).show();
    }

    $('#maintenance_type').on('change', updateScheduleFields);
    updateScheduleFields();

    setInterval(function() {
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const str = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate()) + ' ' +
                    pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
        $('#server-time').text(str);
    }, 1000);

    function updateCountdowns() {
        $('.countdown-timer').each(function() {
            const target = new Date($(this).data('target')).getTime();
            const now = new Date().getTime();
            const diff = target - now;
            if (diff <= 0) { $(this).text('NOW'); return; }
            const days = Math.floor(diff / (1000*60*60*24));
            const hrs = Math.floor((diff % (1000*60*60*24)) / (1000*60*60));
            const mins = Math.floor((diff % (1000*60*60)) / (1000*60));
            const secs = Math.floor((diff % (1000*60)) / 1000);
            let s = '';
            if (days > 0) s += days + 'd ';
            s += String(hrs).padStart(2,'0') + ':' + String(mins).padStart(2,'0') + ':' + String(secs).padStart(2,'0');
            $(this).text(s);
        });
    }
    setInterval(updateCountdowns, 1000);
    updateCountdowns();
});
</script>
