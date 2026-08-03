<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-map-marked-alt text-primary mr-2"></i>
                            Location & Activity
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-map-pin text-primary mr-1"></i>
                                Locations: <b><?php echo $totalLocations ?? 0 ?></b>
                            </span>
                        </div>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-walking text-success mr-1"></i>
                                Activities: <b><?php echo $totalActivities ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-white mt-2 mb-0">Unified timeline of GPS locations and device activity events</p>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header d-flex align-items-center">
                             <h3 class="card-title">
                                 <i class="fas fa-history mr-2"></i>
                                 Timeline
                                 <small class="text-white ml-2">Showing <?php echo count($timeline) ?> entries</small>
                             </h3>
                             <div class="card-tools ml-auto">
                                 <button type="button" class="btn btn-success btn-sm" id="pdfExport" title="Export PDF">
                                     <i class="fas fa-file-pdf mr-1"></i> Export
                                 </button>
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
                                <input type="text" class="form-control table-search" placeholder="Search entries..." data-table="table-sortable">
                            </div>
                            <div class="custom-control custom-switch ml-auto">
                                <input type="checkbox" class="custom-control-input" id="hasCoordsToggle"<?php echo !empty($has_coords_filter) ? ' checked' : ''; ?>
                                       onchange="window.location.href='<?php echo current_url(); ?>?has_coords=' + (this.checked ? '1' : '0') + (location.search.match(/[?&]page=/)?'&page=1':'');">
                                <label class="custom-control-label" for="hasCoordsToggle">
                                    <small>Show only with coordinates</small>
                                </label>
                            </div>
                            <div class="ml-3">
                                <select class="form-control form-control-sm" id="typeFilter" onchange="filterByType(this.value)">
                                    <option value="all">All Types</option>
                                    <option value="location">Locations Only</option>
                                    <option value="activity">Activities Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0 table-sortable" id="timelineTable">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:40px"></th>
                                        <th>Type</th>
                                        <th>Position / Activity</th>
                                        <th>Accuracy / Battery</th>
                                        <th>Source / Network</th>
                                        <th>Device</th>
                                        <th>Timestamp</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($timeline)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                                                    <h4>No data recorded</h4>
                                                    <p class="text-muted">Location and activity data will appear here</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($timeline as $index => $entry): ?>
                                            <?php if ($entry['_type'] === 'location'): ?>
                                                <?php
                                                $lat = $entry['latitude'] ?? null;
                                                $lng = $entry['longitude'] ?? null;
                                                $hasCoords = $lat !== null && $lng !== null;
                                                $locTime = !empty($entry['location_time']) ? date('D, M d, Y H:i', (int)($entry['location_time'] / 1000)) : (!empty($entry['extracted_at']) ? date('D, M d, Y H:i', (int)($entry['extracted_at'] / 1000)) : '—');
                                                $uploadedAt = !empty($entry['created_at']) ? date('D, M d, Y H:i', strtotime($entry['created_at'])) : '—';
                                                $accuracy = isset($entry['accuracy']) ? (int)$entry['accuracy'] : null;
                                                $speed = isset($entry['speed']) ? round((float)$entry['speed'], 1) : null;
                                                $bearing = isset($entry['bearing']) ? round((float)$entry['bearing'], 0) : null;
                                                $provider = $entry['provider'] ?? null;
                                                $altitude = isset($entry['altitude']) ? round((float)$entry['altitude'], 1) : null;

                                                $accClass = $accuracy !== null ? ($accuracy > 100 ? 'danger' : ($accuracy > 50 ? 'warning' : 'success')) : 'secondary';
                                                $provIcon = $provider === 'network' ? 'fa-wifi' : ($provider === 'gps' ? 'fa-globe' : 'fa-satellite');
                                                $statusBadge = ($entry['status'] === 'success') ? 'badge-success' : 'badge-warning';
                                                ?>
                                                <tr class="accordion-toggle expandable-row"
                                                    data-target="#entry-details-<?php echo $index; ?>">
                                                    <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                                    <td><span class="badge badge-primary p-2"><i class="fas fa-map-marker-alt mr-1"></i>Location</span></td>
                                                    <td>
                                                        <?php if ($hasCoords): ?>
                                                            <div class="font-weight-bold text-monospace small">
                                                                <span class="text-info"><?php echo $lat; ?></span>, <span class="text-info"><?php echo $lng; ?></span>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted"><i class="fas fa-minus-circle mr-1"></i>No coordinates</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($accuracy !== null): ?>
                                                            <span class="badge badge-<?php echo $accClass; ?>"><?php echo $accuracy; ?>m</span>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif; ?>
                                                        <?php if ($speed !== null): ?>
                                                            <br><small><i class="fas fa-tachometer-alt mr-1"></i><?php echo $speed; ?> m/s</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($provider): ?>
                                                            <span class="badge badge-secondary"><i class="fas <?php echo $provIcon; ?> mr-1"></i><?php echo strtoupper($provider); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php $locDev = $entry['device_model'] ?? ''; ?><?php echo $locDev ? '<small><i class="fas fa-mobile-alt mr-1"></i>' . htmlspecialchars($locDev) . '</small>' : '<span class="text-muted">—</span>'; ?></td>
                                                    <td style="min-width:150px;">
                                                        <div><i class="fas fa-microchip text-secondary mr-1"></i> <?php echo $locTime; ?></div>
                                                        <small class="text-muted"><i class="fas fa-cloud-upload-alt text-info mr-1"></i> <?php echo $uploadedAt; ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <?php if ($hasCoords): ?>
                                                                <a href="https://www.google.com/maps?q=<?php echo $lat; ?>,<?php echo $lng; ?>" target="_blank" class="btn btn-outline-primary" title="Open in Maps"><i class="fas fa-external-link-alt"></i></a>
                                                            <?php endif; ?>
                                                            <button type="button" class="btn btn-outline-danger delete-row" data-type="location" data-id="<?= $entry['counter'] ?? '' ?>" data-url="<?= base_url('location/delete') ?>"><i class="fas fa-trash"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="expandable-content" style="display:none;">
                                                    <td colspan="8" class="p-0 border-0">
                                                        <div id="entry-details-<?php echo $index; ?>" style="display:none;">
                                                            <div class="card card-body bg-light border-0 m-0 p-3">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <h6><i class="fas fa-map-marked-alt mr-2"></i>Location Details</h6>
                                                                        <table class="table table-sm table-borderless mb-0 small">
                                                                            <tr><th>Latitude</th><td><?php echo $lat ?? '—'; ?></td></tr>
                                                                            <tr><th>Longitude</th><td><?php echo $lng ?? '—'; ?></td></tr>
                                                                            <tr><th>Altitude</th><td><?php echo $altitude !== null ? $altitude . ' m' : '—'; ?></td></tr>
                                                                            <tr><th>Accuracy</th><td><?php echo $accuracy !== null ? $accuracy . ' m' : '—'; ?></td></tr>
                                                                            <tr><th>Provider</th><td><?php echo strtoupper($provider ?? '—'); ?></td></tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <h6><i class="fas fa-info-circle mr-2"></i>Movement & Status</h6>
                                                                        <table class="table table-sm table-borderless mb-0 small">
                                                                            <tr><th>Speed</th><td><?php echo $speed !== null ? $speed . ' m/s' : '—'; ?></td></tr>
                                                                            <tr><th>Bearing</th><td><?php echo $bearing !== null ? $bearing . '°' : '—'; ?></td></tr>
                                                                            <tr><th>Status</th><td><span class="badge <?php echo $statusBadge; ?>"><?php echo strtoupper($entry['status']); ?></span></td></tr>
                                                                            <tr><th>Uploaded</th><td><?php echo $uploadedAt; ?></td></tr>
                                                                            <tr><th>Entry ID</th><td><code><?php echo $entry['counter'] ?? '—'; ?></code></td></tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php
                                                $actTime = !empty($entry['activity_time']) ? date('D, M d, Y H:i', (int)($entry['activity_time'] / 1000)) : '—';
                                                $uplAt = !empty($entry['created_at']) ? date('D, M d, Y H:i', strtotime($entry['created_at'])) : '—';
                                                $isInteractive = ($entry['is_interactive'] ?? 0) == 1;
                                                $screenOn = ($entry['screen_on'] ?? 0) == 1;
                                                $battery = $entry['battery_level'] ?? null;
                                                $charging = $entry['charging_status'] ?? '';
                                                $network = strtoupper($entry['network_type'] ?? '—');
                                                $activity = strtoupper($entry['activity_type'] ?? $entry['status'] ?? '—');
                                                $confidence = $entry['confidence'] ?? null;
                                                $deviceModel = $entry['device_model'] ?? '';
                                                $deviceBrand = $entry['device_brand'] ?? '';
                                                $androidVer = $entry['android_version'] ?? '';
                                                $info = $entry['info'] ?? '';

                                                $actIcon = 'fa-question-circle';
                                                $actColor = 'secondary';
                                                if (strpos($activity, 'WALK') !== false || strpos($activity, 'RUN') !== false) { $actIcon = 'fa-running'; $actColor = 'success'; }
                                                elseif (strpos($activity, 'STILL') !== false || strpos($activity, 'IDLE') !== false) { $actIcon = 'fa-stop-circle'; $actColor = 'secondary'; }
                                                elseif (strpos($activity, 'VEHICLE') !== false || strpos($activity, 'DRIV') !== false) { $actIcon = 'fa-car'; $actColor = 'info'; }
                                                elseif (strpos($activity, 'BICYCLE') !== false) { $actIcon = 'fa-bicycle'; $actColor = 'warning'; }
                                                elseif (strpos($activity, 'TILT') !== false) { $actIcon = 'fa-mobile-alt'; $actColor = 'dark'; }
                                                elseif (strpos($activity, 'UNKNOWN') !== false) { $actIcon = 'fa-question'; $actColor = 'light'; }

                                                $battColor = 'success';
                                                if ($battery !== null) { if ($battery <= 15) $battColor = 'danger'; elseif ($battery <= 30) $battColor = 'warning'; }

                                                $netColor = 'secondary';
                                                if (strpos($network, 'WIFI') !== false) $netColor = 'primary';
                                                elseif (strpos($network, '4G') !== false || strpos($network, 'LTE') !== false) $netColor = 'success';
                                                elseif (strpos($network, '3G') !== false) $netColor = 'info';
                                                elseif (strpos($network, '2G') !== false || strpos($network, 'EDGE') !== false) $netColor = 'warning';

                                                $confColor = 'secondary';
                                                $confIcon = 'fa-circle';
                                                if ($confidence !== null && $confidence > 0) {
                                                    if ($confidence >= 80) { $confColor = 'success'; $confIcon = 'fa-check-circle'; }
                                                    elseif ($confidence >= 50) { $confColor = 'warning'; $confIcon = 'fa-adjust'; }
                                                    else { $confColor = 'danger'; $confIcon = 'fa-times-circle'; }
                                                }
                                                ?>
                                                <tr class="accordion-toggle expandable-row"
                                                    data-target="#entry-details-<?php echo $index; ?>">
                                                    <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                                    <td><span class="badge badge-<?php echo $actColor; ?> p-2"><i class="fas <?php echo $actIcon; ?> mr-1"></i>Activity</span></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge badge-<?php echo $actColor; ?> p-2 mr-2" style="font-size:1rem;width:36px;"><i class="fas <?php echo $actIcon; ?>"></i></span>
                                                            <div>
                                                                <strong><?php echo $activity; ?></strong>
                                                                <br><small class="text-muted"><?php echo $isInteractive ? 'Interactive' : 'Background'; ?></small>
                                                                <?php if ($confidence !== null && $confidence > 0): ?>
                                                                    <br><span class="badge badge-<?= $confColor ?> mt-1" style="font-size:0.7rem;"><i class="fas <?= $confIcon ?> mr-1"></i><?= $confidence ?>%</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <?php if ($info): ?>
                                                            <div class="mt-1 small text-muted"><i class="fas fa-info-circle mr-1"></i><?php echo htmlspecialchars(mb_substr($info, 0, 80)) . (mb_strlen($info) > 80 ? '...' : ''); ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($battery !== null): ?>
                                                            <div class="d-flex align-items-center">
                                                                <div class="progress progress-xs flex-grow-1 mr-2" style="max-width:50px;height:6px;">
                                                                    <div class="progress-bar bg-<?php echo $battColor; ?>" style="width:<?php echo $battery; ?>%"></div>
                                                                </div>
                                                                <small class="font-weight-bold text-<?php echo $battColor; ?>"><?php echo $battery; ?>%</small>
                                                            </div>
                                                            <?php if ($charging): ?><small class="text-muted"><i class="fas fa-plug mr-1"></i><?php echo ucfirst($charging); ?></small><?php endif; ?>
                                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $netColor; ?> p-2"><?php echo $network; ?></span>
                                                        <?php if ($screenOn): ?><br><span class="badge badge-success p-1"><i class="fas fa-sun mr-1"></i>Screen ON</span><?php endif; ?>
                                                    </td>
                                                    <td><?php echo $deviceModel ? '<small><i class="fas fa-mobile-alt mr-1"></i>' . htmlspecialchars($deviceModel) . '</small>' : '<span class="text-muted">—</span>'; ?></td>
                                                    <td style="min-width:150px;">
                                                        <div><i class="fas fa-microchip text-secondary mr-1"></i> <?php echo $actTime; ?></div>
                                                        <small class="text-muted"><i class="fas fa-cloud-upload-alt text-info mr-1"></i> <?php echo $uplAt; ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-row" data-type="activity" data-id="<?= $entry['counter'] ?? '' ?>" data-url="<?= base_url('activities/delete') ?>"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                                <tr class="expandable-content" style="display:none;">
                                                    <td colspan="8" class="p-0 border-0">
                                                        <div id="entry-details-<?php echo $index; ?>" style="display:none;">
                                                            <div class="card card-body bg-light border-0 m-0 p-3">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <h6><i class="fas fa-running mr-2"></i>Activity Details</h6>
                                                                        <table class="table table-sm table-borderless mb-0 small">
                                                                            <tr><th>Activity Type</th><td><span class="badge badge-<?php echo $actColor; ?>"><?php echo $activity; ?></span></td></tr>
                                                                            <tr><th>Confidence</th><td><?php echo $confidence !== null ? $confidence . '%' : '—'; ?></td></tr>
                                                                            <tr><th>Mode</th><td><?php echo $isInteractive ? 'Interactive' : 'Background'; ?></td></tr>
                                                                            <tr><th>Activity Time</th><td><?php echo $actTime; ?></td></tr>
                                                                            <?php if ($info): ?>
                                                                            <tr><th>Info</th><td><pre style="white-space:pre-wrap;margin:0;font-size:0.85rem;background:#f0f0f0;padding:6px 8px;border-radius:4px;max-height:120px;overflow-y:auto;"><?php echo htmlspecialchars($info); ?></pre></td></tr>
                                                                            <?php endif; ?>
                                                                            <tr><th>Entry ID</th><td><code><?php echo $entry['counter'] ?? '—'; ?></code></td></tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <h6><i class="fas fa-battery-half mr-2"></i>Device State</h6>
                                                                        <table class="table table-sm table-borderless mb-0 small">
                                                                            <tr><th>Battery Level</th><td><?php echo $battery !== null ? $battery . '%' : '—'; ?></td></tr>
                                                                            <tr><th>Charging Status</th><td><?php echo $charging ? ucfirst($charging) : 'Not charging'; ?></td></tr>
                                                                            <tr><th>Screen State</th><td><span class="badge badge-<?php echo $screenOn ? 'success' : 'secondary'; ?>"><?php echo $screenOn ? 'ON' : 'OFF'; ?></span></td></tr>
                                                                            <tr><th>Network Type</th><td><?php echo $network; ?></td></tr>
                                                                        </table>
                                                                        <h6 class="mt-3"><i class="fas fa-info-circle mr-2"></i>Device Info</h6>
                                                                        <table class="table table-sm table-borderless mb-0 small">
                                                                            <tr><th>Model</th><td><?php echo $deviceModel ?: '—'; ?></td></tr>
                                                                            <tr><th>Brand</th><td><?php echo $deviceBrand ?: '—'; ?></td></tr>
                                                                            <tr><th>Android</th><td><?php echo $androidVer ?: '—'; ?></td></tr>
                                                                            <tr><th>Uploaded</th><td><?php echo $uplAt; ?></td></tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
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
    .expandable-row { border-bottom: 2px solid #dee2e6; }
    .expandable-content { background-color: #f8f9fa; }
    .accordion-toggle { cursor: pointer; }
    .chevron-icon { transition: transform 0.3s ease; }
    .chevron-icon.rotated { transform: rotate(180deg); }
</style>

<script>
var base_url = function(path) { return '<?= base_url() ?>' + path; };

function filterByType(type) {
    document.querySelectorAll('#timelineTable tbody tr').forEach(function(row) {
        if (row.classList.contains('expandable-content')) return;
        var typeCell = row.querySelector('td:nth-child(2)');
        if (!typeCell) return;
        var isLocation = typeCell.textContent.indexOf('Location') > -1;
        if (type === 'all') { row.style.display = ''; }
        else if (type === 'location') { row.style.display = isLocation ? '' : 'none'; }
        else if (type === 'activity') { row.style.display = isLocation ? 'none' : ''; }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.table-search')?.addEventListener('keyup', function() {
        var keyword = this.value.toLowerCase();
        var target = this.getAttribute('data-table');
        document.querySelectorAll('.' + target + ' tbody tr:not(.expandable-content)').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().indexOf(keyword) > -1 ? '' : 'none';
        });
    });

    document.querySelectorAll('.table-sortable thead th').forEach(function(th) {
        th.addEventListener('click', function() {
            var table = this.closest('table');
            var tbody = table.querySelector('tbody');
            var index = Array.prototype.indexOf.call(this.parentNode.children, this);
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr:not(.expandable-content)'));
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

    document.querySelectorAll('.accordion-toggle').forEach(function(item) {
        item.addEventListener('click', function(e) {
            if (e.target.tagName === 'BUTTON' || e.target.closest('button') || e.target.closest('a')) return;
            var targetId = this.getAttribute('data-target');
            var target = document.querySelector(targetId);
            var chevron = this.querySelector('.chevron-icon');
            var expandableRow = this.closest('tr').nextElementSibling;
            if (!target || !expandableRow) return;
            var isOpen = target.style.display === 'block';
            document.querySelectorAll('.accordion-toggle').forEach(function(other) {
                if (other !== item) {
                    var otId = other.getAttribute('data-target');
                    var ot = document.querySelector(otId);
                    var oc = other.querySelector('.chevron-icon');
                    var oRow = other.closest('tr').nextElementSibling;
                    if (ot && oRow) { ot.style.display = 'none'; oRow.style.display = 'none'; if (oc) oc.classList.remove('rotated'); }
                }
            });
            if (!isOpen) { target.style.display = 'block'; expandableRow.style.display = 'table-row'; if (chevron) chevron.classList.add('rotated'); }
            else { target.style.display = 'none'; expandableRow.style.display = 'none'; if (chevron) chevron.classList.remove('rotated'); }
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.accordion-toggle') && !e.target.closest('.expandable-content')) {
            document.querySelectorAll('.accordion-toggle').forEach(function(item) {
                var tid = item.getAttribute('data-target');
                var t = document.querySelector(tid);
                var c = item.querySelector('.chevron-icon');
                var r = item.closest('tr').nextElementSibling;
                if (t && r) { t.style.display = 'none'; r.style.display = 'none'; if (c) c.classList.remove('rotated'); }
            });
        }
    });
    // PDF Export
    $('#pdfExport').on('click', function () {
        var element = document.querySelector('.table-sortable');
        if (!element) return;
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait while we prepare your document',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        html2pdf().set({
            margin:       10,
            filename:     'location_export_' + Date.now() + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, letterRendering: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
        }).from(element).save().then(function () {
            Swal.close();
            Swal.fire({ icon: 'success', title: 'Export Complete', text: 'PDF has been downloaded', timer: 2000, showConfirmButton: false });
        }).catch(function () {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Export Failed', text: 'Could not generate PDF', timer: 3000, showConfirmButton: false });
        });
    });
});
</script>
<?php include __DIR__ . '/partials/_delete_confirm.php'; ?>
