<?php
/**
 * Location History View
 *
 * @var array $location_dump
 * @var int $totalLocations
 * @var object $pager
 * @var bool $has_coords_filter
 */
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                            Location History
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-chart-bar text-primary mr-1"></i>
                                Total: <b><?php echo $totalLocations ?? 0 ?></b>
                            </span>
                        </div>
                        <div class="ml-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="hasCoordsToggle"<?php echo !empty($has_coords_filter) ? ' checked' : ''; ?>
                                       onchange="window.location.href='<?php echo current_url(); ?>?has_coords=' + (this.checked ? '1' : '0') + (location.search.match(/[?&]page=/)?'&page=1':'');">
                                <label class="custom-control-label" for="hasCoordsToggle">
                                    <small>Show only with coordinates</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">GPS and Network location tracking history</p>
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
                                <i class="fas fa-history mr-2"></i>
                                History
                                <small class="text-muted ml-2">Showing <?php echo count($location_dump) ?> entries</small>
                            </h3>
                            <div class="card-tools my-2">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="border-bottom px-3 py-2">
                            <div class="input-group input-group-sm" style="max-width:350px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" class="form-control table-search" placeholder="Search by location or provider..." data-table="table-sortable">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered mb-0 table-sortable">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>Position</th>
                                        <th>Accuracy</th>
                                        <th>Movement</th>
                                        <th>Source</th>
                                        <th>Location Time</th>
                                        <th>Extracted</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($location_dump)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                                                    <h4>No locations recorded</h4>
                                                    <p class="text-muted">Location data will appear here</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($location_dump as $loc): ?>
                                            <?php
                                            $lat = $loc['latitude'] ?? null;
                                            $lng = $loc['longitude'] ?? null;
                                            $hasCoords = $lat !== null && $lng !== null;
                                            $hasLocationTime = !empty($loc['location_time']);
                                            $locationTime = $hasLocationTime ? format_timestamp_display((int)$loc['location_time']) : (!empty($loc['extracted_at']) ? format_timestamp_display((int)$loc['extracted_at']) : '—');
                                            $extractedAt = !empty($loc['extracted_at']) ? format_timestamp_display((int)$loc['extracted_at']) : '—';
                                            $statusClass = ($loc['status'] === 'success') ? 'badge-success' : 'badge-warning';
                                            $accuracy = isset($loc['accuracy']) ? (int)$loc['accuracy'] : null;
                                            $speed = isset($loc['speed']) ? round((float)$loc['speed'], 1) : null;
                                            $bearing = isset($loc['bearing']) ? round((float)$loc['bearing'], 0) : null;
                                            $provider = $loc['provider'] ?? null;

                                            $accClass = 'badge-success';
                                            if ($accuracy !== null) {
                                                if ($accuracy > 100) $accClass = 'badge-danger';
                                                elseif ($accuracy > 50) $accClass = 'badge-warning';
                                            }

                                            $provIcon = 'fa-satellite';
                                            if ($provider === 'network') $provIcon = 'fa-wifi';
                                            elseif ($provider === 'gps') $provIcon = 'fa-globe';
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php if ($hasCoords): ?>
                                                        <div class="font-weight-bold text-monospace">
                                                            <span class="text-info"><?php echo $lat; ?></span>,
                                                            <span class="text-info"><?php echo $lng; ?></span>
                                                        </div>
                                                        <small class="text-muted"><?php echo number_format($lat, 2); ?>, <?php echo number_format($lng, 2); ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted"><i class="fas fa-minus-circle mr-1"></i>No coordinates</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($accuracy !== null): ?>
                                                        <span class="badge <?php echo $accClass; ?>"><?php echo $accuracy; ?>m</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($speed !== null): ?>
                                                        <div><i class="fas fa-tachometer-alt mr-1"></i> <?php echo $speed; ?> m/s</div>
                                                    <?php else: ?>
                                                        <div class="text-muted"><i class="fas fa-tachometer-alt mr-1"></i> —</div>
                                                    <?php endif; ?>
                                                    <?php if ($bearing !== null): ?>
                                                        <small class="text-muted"><i class="fas fa-compass mr-1"></i> <?php echo $bearing; ?>°</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($provider): ?>
                                                        <span class="badge badge-secondary"><i class="fas <?php echo $provIcon; ?> mr-1"></i><?php echo strtoupper($provider); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($hasLocationTime): ?>
                                                        <div><?php echo format_timestamp_display((int)$loc['location_time']); ?></div>
                                                        <small class="text-muted"><i class="fas fa-mobile-alt mr-1"></i>Device time</small>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($loc['extracted_at'])): ?>
                                                        <div><?php echo $extractedAt; ?></div>
                                                        <small class="text-muted"><i class="fas fa-cloud-upload-alt mr-1"></i>Uploaded</small>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $statusClass; ?>"><?php echo strtoupper($loc['status']); ?></span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($hasCoords): ?>
                                                            <a href="https://www.google.com/maps?q=<?php echo $lat; ?>,<?php echo $lng; ?>" target="_blank" class="btn btn-outline-primary" title="Open in Google Maps">
                                                                <i class="fas fa-external-link-alt"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-outline-danger delete-location"
                                                                data-id="<?= $loc['counter'] ?? '' ?>"
                                                                title="Delete location entry">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
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
    document.querySelectorAll('.delete-location').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Location Entry?',
                    text: 'Are you sure you want to delete this location entry?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash"></i> Delete'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        fetch(base_url('location/delete/' + id), {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(function(r) { return r.json(); }).then(function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', 'Location entry has been deleted.', 'success').then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.message || 'Failed to delete location entry.', 'error');
                            }
                        }).catch(function() {
                            Swal.fire('Error!', 'Failed to delete location entry.', 'error');
                        });
                    }
                });
            }
        });
    });
});
</script>
