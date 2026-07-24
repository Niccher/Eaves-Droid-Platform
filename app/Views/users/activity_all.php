<?php
/**
 * Device Activity View
 *
 * @var array $activity_dump
 * @var int $totalActivities
 * @var object $pager
 */
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-walking text-primary mr-2"></i>
                            Device Activity
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-chart-bar text-primary mr-1"></i>
                                Total: <b><?php echo $totalActivities ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Hardware status and user activity monitoring</p>
                </div>
            </div>

            <?php $stats = $activity_stats ?? []; ?>
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $stats['today'] ?? 0 ?></h3>
                            <p>Today's Activities</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-day"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= htmlspecialchars(ucwords(str_replace('_', ' ', $stats['top_type'] ?? 'N/A'))) ?></h3>
                            <p>Most Common (<?= $stats['top_type_count'] ?? 0 ?>)</p>
                        </div>
                        <div class="icon"><i class="fas fa-chart-pie"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $stats['avg_battery'] ?? 0 ?>%</h3>
                            <p>Average Battery</p>
                        </div>
                        <div class="icon"><i class="fas fa-battery-half"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= count($stats['distinct_types'] ?? []) ?></h3>
                            <p>Activity Types</p>
                        </div>
                        <div class="icon"><i class="fas fa-tags"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>
                                Events
                                <small class="text-muted ml-2">Showing <?php echo count($activity_dump) ?> entries</small>
                            </h3>
                            <div class="card-tools my-2">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="border-bottom px-3 py-2 d-flex flex-wrap align-items-center">
                            <div class="input-group input-group-sm mr-3" style="max-width:300px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" class="form-control table-search" placeholder="Search by activity or network..." data-table="table-sortable">
                            </div>
                            <form method="get" class="form-inline">
                                <select name="type" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                    <option value="all">All Types</option>
                                    <?php foreach (($stats['distinct_types'] ?? []) as $t): ?>
                                        <option value="<?= htmlspecialchars($t) ?>" <?= ($current_type_filter ?? '') === $t ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $t))) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <noscript><button type="submit" class="btn btn-sm btn-primary">Filter</button></noscript>
                            </form>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered mb-0 table-sortable">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>Activity</th>
                                        <th>Battery</th>
                                        <th>Screen</th>
                                        <th>Network</th>
                                        <th>Device</th>
                                        <th>Timestamp</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($activity_dump)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-running fa-3x text-muted mb-3"></i>
                                                    <h4>No activity logs</h4>
                                                    <p class="text-muted">Device events will appear here</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($activity_dump as $act): ?>
                                            <?php
                                            $activityTime = !empty($act['activity_time']) ? date('D, M d, Y H:i', (int)($act['activity_time'] / 1000)) : '—';
                                            $uploadedAt = !empty($act['created_at']) ? date('D, M d, Y H:i', strtotime($act['created_at'])) : '—';
                                            $isInteractive = ($act['is_interactive'] ?? 0) == 1;
                                            $screenOn = ($act['screen_on'] ?? 0) == 1;
                                            $battery = $act['battery_level'] ?? null;
                                            $charging = $act['charging_status'] ?? '';
                                            $network = strtoupper($act['network_type'] ?? '—');
                                            $activity = strtoupper($act['activity_type'] ?? $act['status'] ?? '—');
                                            $confidence = $act['confidence'] ?? null;
                                            $info = $act['info'] ?? null;
                                            $deviceModel = $act['device_model'] ?? '';

                                            $actIcon = 'fa-question-circle';
                                            $actColor = 'secondary';
                                            if (strpos($activity, 'WALK') !== false || strpos($activity, 'RUN') !== false) {
                                                $actIcon = 'fa-running'; $actColor = 'success';
                                            } elseif (strpos($activity, 'STILL') !== false || strpos($activity, 'IDLE') !== false) {
                                                $actIcon = 'fa-stop-circle'; $actColor = 'secondary';
                                            } elseif (strpos($activity, 'VEHICLE') !== false || strpos($activity, 'DRIV') !== false) {
                                                $actIcon = 'fa-car'; $actColor = 'info';
                                            } elseif (strpos($activity, 'BICYCLE') !== false) {
                                                $actIcon = 'fa-bicycle'; $actColor = 'warning';
                                            } elseif (strpos($activity, 'TILT') !== false) {
                                                $actIcon = 'fa-mobile-alt'; $actColor = 'dark';
                                            } elseif (strpos($activity, 'UNKNOWN') !== false) {
                                                $actIcon = 'fa-question'; $actColor = 'light';
                                            }

                                            $battColor = 'success';
                                            if ($battery !== null) {
                                                if ($battery <= 15) $battColor = 'danger';
                                                elseif ($battery <= 30) $battColor = 'warning';
                                            }

                                            $netColor = 'secondary';
                                            if (strpos($network, 'WIFI') !== false) $netColor = 'primary';
                                            elseif (strpos($network, '4G') !== false || strpos($network, 'LTE') !== false) $netColor = 'success';
                                            elseif (strpos($network, '3G') !== false) $netColor = 'info';
                                            elseif (strpos($network, '2G') !== false || strpos($network, 'EDGE') !== false) $netColor = 'warning';
                                            elseif (strpos($network, '—') === false || !empty($act['network_type'])) $netColor = 'light';

                                            $confColor = 'secondary';
                                            $confIcon = 'fa-circle';
                                            if ($confidence !== null && $confidence > 0) {
                                                if ($confidence >= 80) { $confColor = 'success'; $confIcon = 'fa-check-circle'; }
                                                elseif ($confidence >= 50) { $confColor = 'warning'; $confIcon = 'fa-adjust'; }
                                                else { $confColor = 'danger'; $confIcon = 'fa-times-circle'; }
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge badge-<?php echo $actColor; ?> p-2 mr-2" style="font-size:1rem;width:36px;">
                                                            <i class="fas <?php echo $actIcon; ?>"></i>
                                                        </span>
                                                        <div>
                                                            <strong><?php echo $activity; ?></strong>
                                                            <br><small class="text-muted"><?php echo $isInteractive ? 'Interactive' : 'Background'; ?></small>
                                                            <?php if ($confidence !== null && $confidence > 0): ?>
                                                                <br><span class="badge badge-<?= $confColor ?> mt-1" style="font-size:0.7rem;">
                                                                    <i class="fas <?= $confIcon ?> mr-1"></i><?= $confidence ?>% confidence
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <?php if ($battery !== null): ?>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress progress-xs flex-grow-1 mr-2" style="max-width:60px;height:6px;">
                                                                <div class="progress-bar bg-<?php echo $battColor; ?>" style="width:<?php echo $battery; ?>%"></div>
                                                            </div>
                                                            <small class="font-weight-bold text-<?php echo $battColor; ?>"><?php echo $battery; ?>%</small>
                                                        </div>
                                                        <?php if ($charging): ?>
                                                            <small class="text-muted"><i class="fas fa-plug mr-1"></i><?php echo ucfirst($charging); ?></small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge badge-<?php echo $screenOn ? 'success' : 'secondary'; ?> p-2" style="font-size:0.85rem;">
                                                        <i class="fas <?php echo $screenOn ? 'fa-sun' : 'fa-moon'; ?> mr-1"></i>
                                                        <?php echo $screenOn ? 'ON' : 'OFF'; ?>
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge badge-<?php echo $netColor; ?> p-2"><?php echo $network; ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <?php if ($deviceModel): ?>
                                                        <small><i class="fas fa-mobile-alt mr-1"></i><?= htmlspecialchars($deviceModel) ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle" style="min-width:170px;">
                                                    <div><i class="fas fa-microchip text-secondary mr-1"></i> <?php echo $activityTime; ?></div>
                                                    <?php if (!empty($act['created_at'])): ?>
                                                        <small class="text-muted border-top pt-1 d-block mt-1"><i class="fas fa-cloud-upload-alt text-info mr-1"></i> <?php echo $uploadedAt; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-activity"
                                                            data-id="<?= $act['counter'] ?? '' ?>"
                                                            title="Delete activity entry">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="float-right my-2">
                                <?php if (isset($pager)): ?>
                                    <?= $pager->links('default', 'bootstrap5_full') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .avatar-circle-sm { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .empty-state { padding: 3rem 1rem; text-align: center; }
    .empty-state i { opacity: 0.5; }
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .table-sortable thead th { cursor: pointer; user-select: none; }
    .table-sortable thead th.sort-asc::after { content: ' \25B2'; font-size: 0.7em; }
    .table-sortable thead th.sort-desc::after { content: ' \25BC'; font-size: 0.7em; }
</style>
<script>
var base_url = function(path) { return '<?= base_url() ?>' + path; };
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.table-search')?.addEventListener('keyup', function() {
        var keyword = this.value.toLowerCase();
        var target = this.getAttribute('data-table');
        document.querySelectorAll('.' + target + ' tbody tr').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().indexOf(keyword) > -1 ? '' : 'none';
        });
    });
    document.querySelectorAll('.table-sortable thead th').forEach(function(th) {
        th.addEventListener('click', function() {
            var table = this.closest('table');
            var tbody = table.querySelector('tbody');
            var index = Array.prototype.indexOf.call(this.parentNode.children, this);
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
            var asc = !this.classList.contains('sort-asc');
            table.querySelectorAll('thead th').forEach(function(h) { h.classList.remove('sort-asc', 'sort-desc'); });
            this.classList.toggle('sort-asc', asc);
            this.classList.toggle('sort-desc', !asc);
            rows.sort(function(a, b) {
                var aVal = (a.querySelectorAll('td')[index]?.textContent || '').trim();
                var bVal = (b.querySelectorAll('td')[index]?.textContent || '').trim();
                var aNum = parseFloat(aVal), bNum = parseFloat(bVal);
                if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
                return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
            });
            rows.forEach(function(row) { tbody.appendChild(row); });
        });
    });
    document.querySelectorAll('.delete-activity').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Activity Entry?',
                    text: 'Are you sure you want to delete this activity entry?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash"></i> Delete'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        fetch(base_url('activities/delete/' + id), {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(function(r) { return r.json(); }).then(function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', 'Activity entry has been deleted.', 'success').then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.message || 'Failed to delete activity entry.', 'error');
                            }
                        }).catch(function() {
                            Swal.fire('Error!', 'Failed to delete activity entry.', 'error');
                        });
                    }
                });
            }
        });
    });
});
</script>
