<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Generate Custom Report</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/reports') ?>">Reports</a></li>
                        <li class="breadcrumb-item active">Generate</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Generate Report</h5>
                        <p class="mb-0 small text-muted">Create a custom report by selecting date range, data categories, user filters, and output format (HTML, CSV, PDF).</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-sliders-h mr-1"></i> Report Criteria</h3>
                        </div>
                        <form method="post" action="<?= base_url('admin/reports/generate') ?>">
                            <?= csrf_field() ?>
                            <div class="card-body">
                                <div class="form-group">
                                    <label><i class="fas fa-user mr-1"></i> User</label>
                                    <select name="user_id" class="form-control">
                                        <option value="all" <?= ($selected_user_id === 'all') ? 'selected' : '' ?>>
                                            <i class="fas fa-users mr-1"></i> All Users
                                        </option>
                                        <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($selected_user_id === (string)$u['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($u['username']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-calendar-alt mr-1"></i> Date From</label>
                                            <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-calendar-alt mr-1"></i> Date To</label>
                                            <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-database mr-1"></i> Data Types</label>
                                    <div class="row">
                                        <?php
                                        $types = [
                                            'sms' => ['label' => 'SMS', 'icon' => 'fa-sms'],
                                            'calls' => ['label' => 'Call Logs', 'icon' => 'fa-phone'],
                                            'contacts' => ['label' => 'Contacts', 'icon' => 'fa-address-book'],
                                            'apps' => ['label' => 'Apps', 'icon' => 'fa-th'],
                                            'locations' => ['label' => 'Locations', 'icon' => 'fa-map-marker-alt'],
                                            'activities' => ['label' => 'Activities', 'icon' => 'fa-running'],
                                            'notifications' => ['label' => 'Notifications', 'icon' => 'fa-bell'],
                                            'accounts' => ['label' => 'Accounts', 'icon' => 'fa-user-circle'],
                                            'bluetooth' => ['label' => 'Bluetooth', 'icon' => 'fa-bluetooth'],
                                            'calendar' => ['label' => 'Calendar', 'icon' => 'fa-calendar'],
                                            'uploads' => ['label' => 'Uploads', 'icon' => 'fa-upload'],
                                        ];
                                        $half = ceil(count($types) / 2);
                                        $chunks = array_chunk($types, $half, true);
                                        foreach ($chunks as $chunk):
                                        ?>
                                        <div class="col-md-6">
                                            <?php foreach ($chunk as $val => $t): ?>
                                            <div class="custom-control custom-checkbox mb-1">
                                                <input type="checkbox" class="custom-control-input" id="dt_<?= $val ?>" name="data_types[]" value="<?= $val ?>" <?= in_array($val, $selected_types) ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="dt_<?= $val ?>">
                                                    <i class="fas <?= $t['icon'] ?> text-muted mr-1" style="width: 16px;"></i>
                                                    <?= $t['label'] ?>
                                                </label>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search mr-1"></i> Generate Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-7">
                    <?php if ($results !== null): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-1"></i> Results
                                <?php if ($selected_user): ?>
                                <small class="text-muted ml-2">— <?= htmlspecialchars($selected_user['username']) ?></small>
                                <?php else: ?>
                                <small class="text-muted ml-2">— All Users</small>
                                <?php endif; ?>
                            </h3>
                            <div class="card-tools">
                                <form method="post" action="<?= base_url('admin/reports/export') ?>" style="display:inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="date_from" value="<?= $date_from ?>">
                                    <input type="hidden" name="date_to" value="<?= $date_to ?>">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-download mr-1"></i> Export CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Data Type</th>
                                        <th class="text-center">Records</th>
                                        <th class="text-center">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($results)): ?>
                                    <tr><td colspan="3" class="text-muted text-center">No data found for the selected criteria.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($results as $r): ?>
                                    <tr>
                                        <td>
                                            <i class="fas <?= $r['icon'] ?> text-muted mr-1" style="width: 18px;"></i>
                                            <?= $r['label'] ?>
                                        </td>
                                        <td class="text-center"><?= number_format($r['count']) ?></td>
                                        <td class="text-center"><?= $grand_total > 0 ? round($r['count'] / $grand_total * 100, 1) : 0 ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold bg-light">
                                        <td><i class="fas fa-calculator mr-1"></i> Total</td>
                                        <td class="text-center"><?= number_format($grand_total) ?></td>
                                        <td class="text-center">100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <?php if (!empty($results)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Distribution</h3>
                        </div>
                        <div class="card-body">
                            <div class="progress-group mb-0">
                                <?php foreach ($results as $r): ?>
                                <?php $pct = $grand_total > 0 ? round($r['count'] / $grand_total * 100, 1) : 0; ?>
                                <div class="mb-3">
                                    <span class="progress-text">
                                        <i class="fas <?= $r['icon'] ?> mr-1"></i> <?= $r['label'] ?>
                                    </span>
                                    <span class="float-right font-weight-bold"><?= number_format($r['count']) ?> (<?= $pct ?>%)</span>
                                    <div class="progress progress-sm mt-1">
                                        <div class="progress-bar bg-<?= ['primary','success','info','warning','danger','secondary','dark','primary','success','info','warning'][array_search($r['type'], array_column($results, 'type')) % 11] ?>" style="width: <?= $pct ?>%"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-chart-pie fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Report Generated Yet</h5>
                            <p class="text-muted">Select your criteria on the left and click <strong>Generate Report</strong>.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>