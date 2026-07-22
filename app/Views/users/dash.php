<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-tachometer-alt text-primary mr-2"></i>Dashboard
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- Device Status Bar -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-outline card-<?= (isset($user_token['status']) && $user_token['status'] == '00') ? 'danger' : 'success' ?> shadow-sm">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-mobile-alt mr-2"></i>
                                    <strong>Device:</strong>
                                    <?php if (!empty($device_health)): ?>
                                        <?= $device_health['device_model'] ?? 'Unknown' ?>
                                        <span class="text-muted ml-2">|</span>
                                        <span class="ml-2"><i class="fas fa-battery-<?= ($device_health['battery_level'] ?? 50) > 50 ? 'full' : (($device_health['battery_level'] ?? 50) > 20 ? 'half' : 'empty') ?> mr-1"></i> <?= $device_health['battery_level'] ?? 'N/A' ?>%</span>
                                        <span class="text-muted ml-2">|</span>
                                        <span class="ml-2"><i class="fas fa-wifi mr-1"></i> <?= $device_health['network_operator'] ?? 'N/A' ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">No device data</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if (isset($user_token['status']) && $user_token['status'] == '00'): ?>
                                        <span class="badge badge-danger px-3 py-2"><i class="fas fa-times-circle mr-1"></i> Disconnected</span>
                                    <?php else: ?>
                                        <span class="badge badge-success px-3 py-2"><i class="fas fa-check-circle mr-1"></i> Connected</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stat Boxes -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= number_format($total_sms) ?></h3>
                            <p>SMS Messages</p>
                        </div>
                        <div class="icon"><i class="fas fa-sms"></i></div>
                        <a href="<?= base_url('sms') ?>" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= number_format($total_calls) ?></h3>
                            <p>Call Logs</p>
                        </div>
                        <div class="icon"><i class="fas fa-phone-alt"></i></div>
                        <a href="<?= base_url('call_logs') ?>" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= number_format($total_contacts) ?></h3>
                            <p>Contacts</p>
                        </div>
                        <div class="icon"><i class="fas fa-id-card"></i></div>
                        <a href="<?= base_url('contacts') ?>" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?= number_format($total_apps) ?></h3>
                            <p>Installed Apps</p>
                        </div>
                        <div class="icon"><i class="fas fa-mobile-alt"></i></div>
                        <a href="<?= base_url('apps') ?>" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= number_format($total_files) ?></h3>
                            <p>Files</p>
                        </div>
                        <div class="icon"><i class="fas fa-file"></i></div>
                        <a href="<?= base_url('files') ?>" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= number_format($total_locations) ?></h3>
                            <p>Locations</p>
                        </div>
                        <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                        <a href="<?= base_url('location') ?>" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= number_format($total_activities) ?></h3>
                            <p>Activities</p>
                        </div>
                        <div class="icon"><i class="fas fa-walking"></i></div>
                        <a href="<?= base_url('activities') ?>" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-dark">
                        <div class="inner">
                            <h3><?= number_format(($total_device ?? 0) + ($total_network ?? 0) + ($total_accounts ?? 0) + ($total_calendar ?? 0) + ($total_app_usage ?? 0) + ($total_notifications ?? 0) + ($total_bluetooth ?? 0) + ($total_sensors ?? 0) + ($total_media ?? 0)) ?></h3>
                            <p>Device Metrics</p>
                        </div>
                        <div class="icon"><i class="fas fa-microchip"></i></div>
                        <a href="<?= base_url('advanced/device') ?>" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Most Frequent Calls -->
                <div class="col-md-6">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-phone-alt mr-2"></i>Most Frequent Calls</h3>
                            <div class="card-tools">
                                <span class="badge badge-success"><?= count($active_calls) ?> contacts</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Number</th>
                                            <th>Saved Name</th>
                                            <th class="text-center">Interactions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; foreach ($active_calls as $calls => $callsinfo): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><a href="#" class="text-dark"><b><?= $callsinfo['Caller'] ?></b></a></td>
                                            <td><?= $callsinfo['Saved'] ?></td>
                                            <td class="text-center"><span class="badge badge-success px-3 py-1"><?= $callsinfo['Totals'] ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($active_calls)): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No call data available</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Most Frequent SMS -->
                <div class="col-md-6">
                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-sms mr-2"></i>Most Frequent SMS</h3>
                            <div class="card-tools">
                                <span class="badge badge-info"><?= count($active_sms) ?> contacts</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Number</th>
                                            <th>Saved Name</th>
                                            <th class="text-center">Interactions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $modFinder = new \App\Models\Mod_Finder();
                                        $i = 1; foreach ($active_sms as $sms => $smsinfo):
                                            $number = substr($smsinfo['sms_number'] ?? '', 0, 1);
                                            $old_number = $smsinfo['sms_number'] ?? '';
                                            if ($number == 0) $old_number = substr($smsinfo['sms_number'], 1);
                                            else if ($number == "+") $old_number = substr($smsinfo['sms_number'], 4);
                                            $nom = '<i class="text-danger">Unsaved</i>';
                                            if (is_numeric($smsinfo['sms_number'] ?? '')) {
                                                $contactName = $modFinder->get_contact($old_number);
                                                if (!empty($contactName['Name'])) $nom = htmlspecialchars($contactName['Name']);
                                            } else if (!empty($smsinfo['sms_number'])) {
                                                $nom = htmlspecialchars($smsinfo['sms_number']);
                                            }
                                        ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><a href="#" class="text-dark"><b><?= htmlspecialchars($smsinfo['sms_number'] ?? 'Unknown') ?></b></a></td>
                                            <td><?= $nom ?></td>
                                            <td class="text-center"><span class="badge badge-info px-3 py-1"><?= $smsinfo['Totals'] ?? 0 ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($active_sms)): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No SMS data available</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Call Distribution Pie -->
                <div class="col-md-6">
                    <div class="card card-outline card-warning shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Call Distribution</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="canvas_call_distribution" style="min-height:280px; max-height:280px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- SMS Distribution Pie -->
                <div class="col-md-6">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>SMS Distribution</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="canvas_sms_distribution" style="min-height:280px; max-height:280px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-secondary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-link mr-2"></i>Quick Access</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-2 col-4 mb-3">
                                    <a href="<?= base_url('analysis') ?>" class="btn btn-outline-info btn-block py-3">
                                        <i class="fas fa-chart-bar fa-2x d-block mb-2"></i>
                                        <small>Analysis</small>
                                    </a>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <a href="<?= base_url('call_logs') ?>" class="btn btn-outline-success btn-block py-3">
                                        <i class="fas fa-phone-alt fa-2x d-block mb-2"></i>
                                        <small>Call Logs</small>
                                    </a>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <a href="<?= base_url('sms') ?>" class="btn btn-outline-danger btn-block py-3">
                                        <i class="fas fa-sms fa-2x d-block mb-2"></i>
                                        <small>SMS</small>
                                    </a>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <a href="<?= base_url('contacts') ?>" class="btn btn-outline-warning btn-block py-3">
                                        <i class="fas fa-id-card fa-2x d-block mb-2"></i>
                                        <small>Contacts</small>
                                    </a>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <a href="<?= base_url('location') ?>" class="btn btn-outline-primary btn-block py-3">
                                        <i class="fas fa-map-marker-alt fa-2x d-block mb-2"></i>
                                        <small>Locations</small>
                                    </a>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <a href="<?= base_url('account/profile') ?>" class="btn btn-outline-secondary btn-block py-3">
                                        <i class="fas fa-user-circle fa-2x d-block mb-2"></i>
                                        <small>Profile</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>