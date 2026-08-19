<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>
<?php $latest = !empty($rows) ? $rows[0] : null; ?>

<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-heartbeat text-primary mr-2"></i>Digital Wellbeing
                        </h1>
                        <span class="badge badge-primary border p-2">
                            <i class="fas fa-database mr-1"></i>Total Records: <b><?= (int)$total ?></b>
                        </span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Focus/bedtime modes, screen time, and per-app usage limits.</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <?php if ($latest): ?>
                <div class="row">
                    <!-- Column 1: Today's Overview -->
                    <div class="col-md-5">
                        <div class="card card-outline card-primary shadow-sm mb-4">
                            <div class="card-body text-center py-4">
                                <h5 class="text-muted font-weight-bold mb-4">Today's Screen Time</h5>
                                <div class="position-relative d-inline-flex align-items-center justify-content-center" style="width: 160px; height: 160px;">
                                    <svg class="w-100 h-100 position-absolute" style="transform: rotate(-90deg);" viewBox="0 0 140 140">
                                        <circle cx="70" cy="70" r="60" stroke="#f3f3f3" stroke-width="8" fill="transparent" />
                                        <circle cx="70" cy="70" r="60" stroke="#007bff" stroke-width="8" fill="transparent"
                                                stroke-dasharray="377" stroke-dashoffset="<?= max(0, 377 - (377 * min(1, ($latest['total_daily_usage_minutes'] ?? 0) / 360))) ?>" />
                                    </svg>
                                    <div class="text-center">
                                        <span class="h2 font-weight-bold d-block mb-0"><?= round(($latest['total_daily_usage_minutes'] ?? 0) / 60, 1) ?>h</span>
                                        <small class="text-muted"><?= ($latest['total_daily_usage_minutes'] ?? 0) ?> mins</small>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-6 border-right">
                                        <span class="h4 font-weight-bold text-info"><?= $latest['unlock_count'] ?? 0 ?></span>
                                        <p class="text-muted small mb-0"><i class="fas fa-unlock mr-1 text-info"></i>Unlocks</p>
                                    </div>
                                    <div class="col-6">
                                        <span class="h4 font-weight-bold text-warning"><?= $latest['notification_count'] ?? 0 ?></span>
                                        <p class="text-muted small mb-0"><i class="fas fa-bell mr-1 text-warning"></i>Notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Device Modes -->
                        <div class="card card-outline card-secondary shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold"><i class="fas fa-cogs mr-2"></i>Device Modes</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <div class="p-3 border rounded text-center <?= ($latest['focus_mode_enabled'] ?? 0) ? 'border-primary bg-light' : '' ?>">
                                            <i class="fas fa-brain fa-2x mb-2 <?= ($latest['focus_mode_enabled'] ?? 0) ? 'text-primary' : 'text-muted' ?>"></i>
                                            <h6 class="font-weight-bold mb-1">Focus Mode</h6>
                                            <span class="badge <?= ($latest['focus_mode_enabled'] ?? 0) ? 'badge-primary' : 'badge-secondary' ?>">
                                                <?= ($latest['focus_mode_enabled'] ?? 0) ? 'Enabled' : 'Disabled' ?>
                                            </span>
                                            <?php if (!empty($latest['focus_mode_apps'])): ?>
                                                <div class="mt-2 small">
                                                    <a href="#" data-toggle="modal" data-target="#focusAppsModal">View restricted apps</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <div class="p-3 border rounded text-center <?= ($latest['bedtime_mode_enabled'] ?? 0) ? 'border-info bg-light' : '' ?>">
                                            <i class="fas fa-moon fa-2x mb-2 <?= ($latest['bedtime_mode_enabled'] ?? 0) ? 'text-info' : 'text-muted' ?>"></i>
                                            <h6 class="font-weight-bold mb-1">Bedtime Mode</h6>
                                            <span class="badge <?= ($latest['bedtime_mode_enabled'] ?? 0) ? 'badge-info' : 'badge-secondary' ?>">
                                                <?= ($latest['bedtime_mode_enabled'] ?? 0) ? 'Enabled' : 'Disabled' ?>
                                            </span>
                                            <?php if (!empty($latest['bedtime_schedule'])): ?>
                                                <div class="mt-2 small">
                                                    <a href="#" data-toggle="modal" data-target="#bedtimeScheduleModal">View schedule</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Category Breakdown -->
                    <div class="col-md-7">
                        <div class="card card-outline card-success shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold text-success"><i class="fas fa-chart-pie mr-2"></i>Usage Breakdown</h3>
                            </div>
                            <div class="card-body">
                                <?php
                                $social = (int)($latest['social_minutes'] ?? 0);
                                $prod = (int)($latest['productivity_minutes'] ?? 0);
                                $ent = (int)($latest['entertainment_minutes'] ?? 0);
                                $other = (int)($latest['other_minutes'] ?? 0);
                                $total_cat = $social + $prod + $ent + $other;
                                if ($total_cat === 0) $total_cat = 1;
                                $p_social = round(($social / $total_cat) * 100);
                                $p_prod = round(($prod / $total_cat) * 100);
                                $p_ent = round(($ent / $total_cat) * 100);
                                $p_other = max(0, 100 - ($p_social + $p_prod + $p_ent));
                                ?>
                                <div class="progress mb-4" style="height: 25px; border-radius: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $p_social ?>%" title="Social: <?= $social ?>m"><?= $p_social ?>%</div>
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $p_prod ?>%" title="Productivity: <?= $prod ?>m"><?= $p_prod ?>%</div>
                                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="width: <?= $p_ent ?>%" title="Entertainment: <?= $ent ?>m"><?= $p_ent ?>%</div>
                                    <div class="progress-bar bg-secondary" role="progressbar" style="width: <?= $p_other ?>%" title="Other: <?= $other ?>m"><?= $p_other ?>%</div>
                                </div>
                                <ul class="list-group list-group-unbordered">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-users text-primary mr-2"></i>Social Usage</span>
                                        <span class="badge badge-primary px-3 py-2"><?= $social ?> mins</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-briefcase text-success mr-2"></i>Productivity Usage</span>
                                        <span class="badge badge-success px-3 py-2"><?= $prod ?> mins</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-gamepad text-warning mr-2"></i>Entertainment Usage</span>
                                        <span class="badge badge-warning px-3 py-2 text-dark"><?= $ent ?> mins</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 mb-0">
                                        <span><i class="fas fa-ellipsis-h text-secondary mr-2"></i>Other Usage</span>
                                        <span class="badge badge-secondary px-3 py-2"><?= $other ?> mins</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Bedtime / Winddown Details -->
                        <div class="card card-outline card-info shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold text-info"><i class="fas fa-moon mr-2"></i>Wind Down Details</h3>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-unbordered">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-hourglass-start mr-2 text-info"></i>Wind Down State</span>
                                        <span class="badge badge-info px-3 py-2"><?= ($latest['wind_down_enabled'] ?? 0) ? 'Active' : 'Inactive' ?></span>
                                    </li>
                                    <?php if (!empty($latest['wind_down_schedule'])): ?>
                                        <li class="list-group-item border-bottom-0 mb-0">
                                            <p class="font-weight-bold mb-1"><i class="fas fa-calendar-alt text-info mr-2"></i>Wind Down Schedule</p>
                                            <pre class="bg-light p-2 rounded mb-0" style="font-size: 85%;"><code><?= htmlspecialchars(json_encode(json_decode($latest['wind_down_schedule'], true), JSON_PRETTY_PRINT)) ?></code></pre>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm text-center py-5">
                    <div class="card-body">
                        <i class="fas fa-heartbeat fa-4x text-muted mb-3"></i>
                        <h5>No Digital Wellbeing snapshots uploaded yet</h5>
                        <p class="text-muted">Once telemetry is parsed from the device, the wellbeing stats will show here.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Historical Snapshots Table -->
            <?php if (!empty($rows)): ?>
                <div class="card card-outline card-secondary shadow-sm mt-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-history mr-2"></i>Wellbeing Snapshots History</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Screen Time</th>
                                    <th>Unlocks</th>
                                    <th>Notifications</th>
                                    <th>Extracted Time</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $index => $row): ?>
                                    <tr id="row_<?= esc($row['id']) ?>">
                                        <td><?= round(((int)$row['total_daily_usage_minutes']) / 60, 1) ?> hours (<?= esc($row['total_daily_usage_minutes']) ?> mins)</td>
                                        <td><?= esc($row['unlock_count']) ?></td>
                                        <td><?= esc($row['notification_count']) ?></td>
                                        <td><?= esc(date('Y-m-d H:i:s', $row['extracted_at'] / 1000)) ?></td>
                                        <td class="text-right">
                                            <button class="btn btn-sm btn-danger btn-delete-row" data-id="<?= esc($row['id']) ?>" data-url="<?= base_url('advanced/software/digital_wellbeing/delete/' . esc($row['id'])) ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Modal Dialogs -->
<?php if ($latest): ?>
    <?php if (!empty($latest['focus_mode_apps'])): ?>
        <div class="modal fade" id="focusAppsModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-brain text-primary mr-2"></i>Focus Mode Apps</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php $apps = json_decode($latest['focus_mode_apps'], true) ?: []; ?>
                            <?php foreach ($apps as $app): ?>
                                <li class="list-group-item"><code><?= htmlspecialchars($app) ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($latest['bedtime_schedule'])): ?>
        <div class="modal fade" id="bedtimeScheduleModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-moon text-info mr-2"></i>Bedtime Schedule</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <pre class="bg-light p-3 rounded mb-0"><code><?= htmlspecialchars(json_encode(json_decode($latest['bedtime_schedule'], true), JSON_PRETTY_PRINT)) ?></code></pre>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?= view('users/advanced/_adv_delete_script') ?>
