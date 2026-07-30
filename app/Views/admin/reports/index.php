<?php $tab = $active_tab ?? 'dashboard'; ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1><i class="fas fa-chart-bar mr-2"></i>Reports</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= session()->getFlashdata('message') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <?php
            $callouts = [
                'dashboard' => ['title' => 'Reports Dashboard', 'desc' => 'Generate and view system reports — data usage summaries, user activity logs, performance metrics, and custom report exports.'],
                'user-activity' => ['title' => 'User Activity Report', 'desc' => 'Detailed log of user actions — logins, page views, feature usage, and administrative operations with timestamps and IP addresses. Select a user below for deeper analysis.'],
                'data-usage' => ['title' => 'Data Usage Report', 'desc' => 'Aggregated statistics on data volumes — uploaded file sizes, SMS and call counts, storage consumption per user, and platform-wide growth trends.'],
                'performance' => ['title' => 'Performance Report', 'desc' => 'System performance metrics — response times, query execution speeds, cache hit rates, memory usage, and PHP-FPM worker statistics.'],
                'generate' => ['title' => 'Generate Report', 'desc' => 'Create a custom report by selecting date range, data categories, user filters, and output format (HTML, CSV, PDF).'],
            ];
            $ct = $callouts[$tab] ?? $callouts['dashboard'];
            ?>
            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1"><?= $ct['title'] ?></h5>
                        <p class="mb-0 small text-muted"><?= $ct['desc'] ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('admin/reports') ?>"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'user-activity' ? 'active' : '' ?>" href="<?= base_url('admin/reports/user-activity') ?>"><i class="fas fa-user mr-1"></i>User Activity <span class="badge badge-secondary ml-1"><?= number_format(count($users)) ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'data-usage' ? 'active' : '' ?>" href="<?= base_url('admin/reports/data-usage') ?>"><i class="fas fa-database mr-1"></i>Data Usage <span class="badge badge-secondary ml-1"><?= number_format($grand_total) ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'performance' ? 'active' : '' ?>" href="<?= base_url('admin/reports/performance') ?>"><i class="fas fa-tachometer-alt mr-1"></i>Performance</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'generate' ? 'active' : '' ?>" href="<?= base_url('admin/reports/generate') ?>"><i class="fas fa-file-export mr-1"></i>Generate</a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <!-- ===== DASHBOARD ===== -->
                    <?php if ($tab === 'dashboard'): ?>
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info"><div class="inner"><h3><?= number_format($total_users) ?></h3><p>Total Users</p></div><div class="icon"><i class="fas fa-users"></i></div></div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success"><div class="inner"><h3><?= number_format($users_with_data) ?></h3><p>Users With Data</p></div><div class="icon"><i class="fas fa-database"></i></div></div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning"><div class="inner"><h3><?= number_format($total_uploads) ?></h3><p>Total Uploads</p></div><div class="icon"><i class="fas fa-upload"></i></div></div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger"><div class="inner"><h3><?= number_format($total_storage / 1048576, 1) ?> MB</h3><p>Storage Used</p></div><div class="icon"><i class="fas fa-hdd"></i></div></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1 text-info"></i>Data Type Distribution</h3></div>
                                <div class="card-body"><canvas id="dataTypeChart" height="250"></canvas></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title"><i class="fas fa-link mr-1 text-info"></i>Quick Links</h3></div>
                                <div class="card-body">
                                    <a href="<?= base_url('admin/reports/user-activity') ?>" class="btn btn-outline-primary btn-block mb-2"><i class="fas fa-user mr-1"></i> User Activity Reports</a>
                                    <a href="<?= base_url('admin/reports/data-usage') ?>" class="btn btn-outline-info btn-block mb-2"><i class="fas fa-chart-pie mr-1"></i> Data Usage Reports</a>
                                    <a href="<?= base_url('admin/reports/performance') ?>" class="btn btn-outline-warning btn-block mb-2"><i class="fas fa-tachometer-alt mr-1"></i> System Performance</a>
                                    <a href="<?= base_url('admin/reports/generate') ?>" class="btn btn-outline-success btn-block"><i class="fas fa-file-export mr-1"></i> Generate Custom Report</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ===== USER ACTIVITY ===== -->
                    <?php if ($tab === 'user-activity'): ?>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="input-group input-group-lg" style="max-width: 400px;">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                                <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
                            </div>
                        </div>
                    </div>
                    <div class="row" id="userCards">
                        <?php foreach ($users as $u): ?>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3 user-card-wrapper">
                            <a href="<?= base_url('admin/reports/user-activity/' . urlencode($u['username'])) ?>" class="text-decoration-none">
                                <div class="card card-hover shadow-sm border-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                                    <i class="fas fa-user fa-2x text-white"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3 flex-grow-1">
                                                <h5 class="mb-1 font-weight-bold text-dark"><?= htmlspecialchars($u['username']) ?></h5>
                                                <small class="text-muted"><i class="fas fa-envelope mr-1"></i><?= htmlspecialchars($u['email'] ?? 'No email') ?></small>
                                            </div>
                                            <div class="ml-2"><i class="fas fa-chevron-right text-muted"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (empty($users)): ?><div class="alert alert-info">No users found.</div><?php endif; ?>
                    <?php endif; ?>

                    <!-- ===== DATA USAGE ===== -->
                    <?php if ($tab === 'data-usage'): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="dataUsageTable">
                            <thead><tr><th>User</th><th class="text-center">Total</th><th class="text-center">Categories</th><th></th></tr></thead>
                            <tbody>
                                <?php if (empty($user_data)): ?>
                                <tr><td colspan="4" class="text-center text-muted">No data found.</td></tr>
                                <?php else: ?>
                                <?php foreach ($user_data as $ud): ?>
                                <?php $activeCats = array_filter($ud['categories'], fn($c) => $c > 0); ?>
                                <tr>
                                    <td><i class="fas fa-user-circle fa-lg text-primary mr-1"></i><strong><?= htmlspecialchars($ud['username']) ?></strong></td>
                                    <td class="text-center"><span class="badge badge-dark badge-lg" style="font-size:1rem;"><?= number_format($ud['total']) ?></span></td>
                                    <td class="text-center"><span class="text-muted"><?= count($activeCats) ?> categories</span></td>
                                    <td class="text-right"><button class="btn btn-sm btn-outline-info show-dist-btn" data-user="<?= htmlspecialchars($ud['username'], ENT_QUOTES) ?>" data-categories='<?= json_encode($ud['categories'], JSON_HEX_APOS) ?>' data-tables='<?= json_encode($data_tables, JSON_HEX_APOS) ?>'><i class="fas fa-chart-pie mr-1"></i>View</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($user_data)): ?>
                            <tfoot><tr class="font-weight-bold bg-light">
                                <td><i class="fas fa-users text-primary mr-1"></i> All Users</td>
                                <td class="text-center"><span class="badge badge-primary badge-lg" style="font-size:1rem;"><?= number_format($grand_total) ?></span></td>
                                <td colspan="2" class="text-muted small"><?= number_format(count($user_data)) ?> users with data</td>
                            </tr></tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                    <?php endif; ?>

                    <!-- ===== PERFORMANCE ===== -->
                    <?php if ($tab === 'performance'): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card"><div class="card-header"><h3 class="card-title"><i class="fas fa-server mr-1 text-info"></i>Server Info</h3></div>
                                <div class="card-body p-0"><table class="table table-sm"><tr><td>PHP Version</td><td><strong><?= $php_version ?></strong></td></tr><tr><td>Server Software</td><td><?= htmlspecialchars($server_software) ?></td></tr><tr><td>Memory Limit</td><td><?= $memory_limit ?></td></tr><tr><td>Max Upload</td><td><?= $max_upload ?></td></tr><tr><td>Max POST</td><td><?= $max_post ?></td></tr><tr><td>Max Execution</td><td><?= $max_execution ?>s</td></tr></table></div>
                            </div>
                            <div class="card"><div class="card-header"><h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1 text-warning"></i>Error Rate</h3></div>
                                <div class="card-body">
                                    <?php $total = ($error_rate->total ?? 0) ?: 0; $failed = ($error_rate->failed ?? 0) ?: 0; $rate = $total > 0 ? round($failed / $total * 100, 2) : 0; ?>
                                    <h3><?= $rate ?>%</h3>
                                    <small class="text-muted"><?= number_format($failed) ?> failed out of <?= number_format($total) ?> actions</small>
                                    <div class="progress mt-2"><div class="progress-bar bg-<?= $rate > 10 ? 'danger' : ($rate > 5 ? 'warning' : 'success') ?>" style="width:<?= min($rate, 100) ?>%"></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card"><div class="card-header"><h3 class="card-title"><i class="fas fa-database mr-1 text-info"></i>Database Overview</h3></div>
                                <div class="card-body p-0"><table class="table table-sm"><tr><td>Total Tables</td><td><strong><?= count($table_sizes) ?></strong></td></tr><tr><td>Total Records</td><td><strong><?= number_format($total_records) ?></strong></td></tr><tr><td>Total DB Size</td><td><strong><?= number_format($total_db_size / 1048576, 2) ?> MB</strong></td></tr></table></div>
                            </div>
                            <div class="card"><div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line mr-1 text-success"></i>Uploads (Last 7 Days)</h3></div>
                                <div class="card-body"><canvas id="uploadTrend" height="150"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="card"><div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-1 text-info"></i>Largest Tables</h3></div>
                        <div class="card-body p-0"><table class="table table-sm" id="tableSizes"><thead><tr><th>Table</th><th>Rows</th><th>Size</th></tr></thead>
                            <tbody><?php foreach (array_slice($table_sizes, 0, 20) as $ts): ?><tr><td><code><?= $ts['name'] ?></code></td><td><?= number_format($ts['rows']) ?></td><td><?= number_format($ts['size'] / 1024, 1) ?> KB</td></tr><?php endforeach; ?></tbody></table></div>
                    </div>
                    <?php endif; ?>

                    <!-- ===== GENERATE ===== -->
                    <?php if ($tab === 'generate'): ?>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title"><i class="fas fa-sliders-h mr-1"></i> Report Criteria</h3></div>
                                <form method="post" action="<?= base_url('admin/reports/generate') ?>" id="reportForm">
                                    <?= csrf_field() ?>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label><i class="fas fa-user mr-1"></i> User</label>
                                            <select name="user_id" class="form-control">
                                                <option value="all" <?= ($selected_user_id === 'all') ? 'selected' : '' ?>>All Users</option>
                                                <?php foreach ($users as $u): ?>
                                                <option value="<?= $u['id'] ?>" <?= ($selected_user_id === (string)$u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['username']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6"><div class="form-group"><label><i class="fas fa-calendar-alt mr-1"></i> Date From</label><input type="date" name="date_from" class="form-control" value="<?= $date_from ?>"></div></div>
                                            <div class="col-md-6"><div class="form-group"><label><i class="fas fa-calendar-alt mr-1"></i> Date To</label><input type="date" name="date_to" class="form-control" value="<?= $date_to ?>"></div></div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-database mr-1"></i> Data Types</label>
                                            <div class="mb-2">
                                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="$('.dt-checkbox').prop('checked', true)">Select All</button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="$('.dt-checkbox').prop('checked', false)">Clear</button>
                                            </div>
                                            <div class="row" style="max-height:320px;overflow-y:auto;">
                                                <?php $chunks = array_chunk($table_map, ceil(count($table_map) / 2), true); foreach ($chunks as $chunk): ?>
                                                <div class="col-md-6">
                                                    <?php foreach ($chunk as $val => $t): ?>
                                                    <div class="custom-control custom-checkbox mb-1">
                                                        <input type="checkbox" class="custom-control-input dt-checkbox" id="dt_<?= $val ?>" name="data_types[]" value="<?= $val ?>" <?= in_array($val, $selected_types) ? 'checked' : '' ?>>
                                                        <label class="custom-control-label" for="dt_<?= $val ?>"><i class="fas <?= $t['icon'] ?> text-muted mr-1" style="width:16px;"></i><?= $t['label'] ?></label>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><i class="fas fa-file-export mr-1"></i> Output Format</label>
                                            <select name="format" class="form-control">
                                                <option value="html" <?= ($format ?? 'html') === 'html' ? 'selected' : '' ?>>HTML</option>
                                                <option value="csv" <?= ($format ?? '') === 'csv' ? 'selected' : '' ?>>CSV</option>
                                                <option value="pdf" <?= ($format ?? '') === 'pdf' ? 'selected' : '' ?>>PDF</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-cog mr-1"></i> Generate Report</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h3 class="card-title mb-0"><i class="fas fa-history mr-1"></i> Generated Reports</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-hover table-striped mb-0" id="reportHistoryTable">
                                        <thead><tr><th>#</th><th>Generated At</th><th>User</th><th>Data Types</th><th>Records</th><th class="text-center">Actions</th></tr></thead>
                                        <tbody>
                                            <?php if (empty($report_history)): ?>
                                            <tr><td colspan="6" class="text-muted text-center py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>No reports generated yet.</td></tr>
                                            <?php else: ?>
                                            <?php foreach ($report_history as $rh): ?>
                                            <tr>
                                                <td><?= $rh['id'] ?></td>
                                                <td><small><?= date('M j, Y g:i A', strtotime($rh['created_at'])) ?></small></td>
                                                <td><span class="badge badge-info"><?= htmlspecialchars($rh['user_scope']) ?></span></td>
                                                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($rh['data_types_labels']) ?>"><?= htmlspecialchars($rh['data_types_labels']) ?></td>
                                                <td><span class="badge badge-dark"><?= number_format($rh['record_count']) ?></span></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-primary view-report-btn" data-id="<?= $rh['id'] ?>"><i class="fas fa-eye"></i></button>
                                                    <a href="<?= base_url('admin/reports/download-report/' . $rh['id']) ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="userDistModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info">
                <h5 class="modal-title"><i class="fas fa-chart-pie mr-2"></i>Data Distribution: <span id="distUserTitle"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="userDistBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reportViewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary">
                <h5 class="modal-title"><i class="fas fa-file-alt mr-2"></i>Report: <span id="reportViewTitle"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0" id="reportViewBody" style="min-height:400px;"></div>
            <div class="modal-footer">
                <span class="text-muted small mr-auto" id="reportViewMeta"></span>
                <a href="#" class="btn btn-success btn-sm" id="reportDownloadBtn"><i class="fas fa-download mr-1"></i>Download</a>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const distColors = ['#17a2b8','#28a745','#ffc107','#dc3545','#6610f2','#e83e8c','#20c997','#fd7e14','#007bff','#6c757d','#343a40','#5bc0de','#5cb85c','#f0ad4e','#d9534f','#7b1fa2','#c2185b','#00acc1','#ff7043','#9ccc65'];

function showUserDist(username, categories, dataTables) {
    $('#distUserTitle').text(username);
    var total = Object.values(categories).reduce(function(a, b) { return a + b; }, 0);
    var html = '<div class="row">';
    var idx = 0;
    var sorted = Object.keys(categories).filter(function(k) { return categories[k] > 0; }).sort(function(a, b) { return categories[b] - categories[a]; });
    sorted.forEach(function(label) {
        var count = categories[label];
        var pct = total > 0 ? (count / total * 100).toFixed(1) : 0;
        var info = dataTables[label] || { icon: 'fa-circle', label: label };
        var color = distColors[idx % distColors.length];
        html += '<div class="col-md-6 mb-3">' +
            '<div class="card card-outline shadow-sm h-100" style="border-left: 4px solid ' + color + ';">' +
            '<div class="card-body py-2">' +
            '<div class="d-flex justify-content-between align-items-center">' +
            '<div><i class="fas ' + info.icon + ' mr-2" style="color:' + color + '"></i><strong>' + label + '</strong></div>' +
            '<div><span class="badge badge-dark badge-pill">' + number_format(count) + ' of ' + number_format(total) + '</span></div>' +
            '</div>' +
            '<div class="progress progress-xs mt-2"><div class="progress-bar" style="width:' + pct + '%;background:' + color + '"></div></div>' +
            '<small class="text-muted">' + pct + '% of total</small>' +
            '</div></div></div>';
        idx++;
    });
    html += '</div>';
    if (sorted.length === 0) html = '<div class="text-center text-muted py-5"><i class="fas fa-inbox fa-3x mb-3"></i><p>No records found for this user.</p></div>';
    $('#userDistBody').html(html);
    $('#userDistModal').modal('show');
}

function number_format(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

<?php if ($tab === 'generate'): ?>
$(document).ready(function() {
    $(document).on('click', '.view-report-btn', function() {
        var id = $(this).data('id');
        $.get('<?= base_url('admin/reports/view-report') ?>/' + id, function(r) {
            if (r.error) { alert(r.error); return; }
            $('#reportViewTitle').text(r.user_scope + ' — ' + r.data_types);
            $('#reportViewBody').html(r.html);
            $('#reportViewMeta').text('Records: ' + r.record_count + ' | Size: ' + (r.file_size / 1024).toFixed(1) + ' KB | ' + r.created_at);
            $('#reportDownloadBtn').attr('href', '<?= base_url('admin/reports/download-report') ?>/' + id);
            $('#reportViewModal').modal('show');
        }, 'json');
    });
});
<?php endif; ?>
<?php if ($tab === 'dashboard'): ?>
new Chart(document.getElementById('dataTypeChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($data_type_counts, 'label')) ?>,
        datasets: [{ data: <?= json_encode(array_column($data_type_counts, 'count')) ?>, backgroundColor: ['#17a2b8','#28a745','#ffc107','#dc3545','#6610f2','#e83e8c','#20c997','#fd7e14'] }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});
<?php endif; ?>
<?php if ($tab === 'performance'): ?>
new Chart(document.getElementById('uploadTrend'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($uploads_by_day, 'date')) ?: '[]' ?>,
        datasets: [{ label: 'Uploads', data: <?= json_encode(array_column($uploads_by_day, 'count')) ?: '[]' ?>, borderColor: '#28a745', fill: false, tension: 0.3 }]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
});
<?php endif; ?>
<?php if ($tab === 'user-activity'): ?>
$(document).ready(function() {
    $('#userSearch').on('keyup', function() {
        var value = this.value.toLowerCase();
        $('#userCards .user-card-wrapper').each(function() { $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1); });
    });
});
<?php endif; ?>
<?php if ($tab === 'data-usage'): ?>
$(document).ready(function() {
    $('#dataUsageTable').DataTable({ order: [[1, 'desc']], paging: false, searching: false, responsive: true });
    $(document).on('click', '.show-dist-btn', function() {
        showUserDist($(this).data('user'), $(this).data('categories'), $(this).data('tables'));
    });
});
<?php endif; ?>
</script>

<style>
.card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.card-hover:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important; border-color: #007bff !important; }
.text-decoration-none:hover { text-decoration: none; }
</style>
