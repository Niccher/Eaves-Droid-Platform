<?php
/**
 * SIM Config View
 *
 * @var array $data
 * @var int $total
 * @var object $pager
 * @var array $devices
 * @var string|null $selectedDevice
 */
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-sim-card text-primary mr-2"></i>
                            SIM Configs
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-chart-bar text-primary mr-1"></i>
                                Total: <b><?= $total ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">SIM card configurations and change history</p>
                </div>
                <div class="col-lg-4 col-md-6 text-right">
                    <div class="d-flex justify-content-end flex-wrap" style="gap: 8px;">
                        <a class="btn btn-sm btn-primary" href="<?php echo base_url('advanced/hardware'); ?>"><i class="fas fa-microchip mr-1"></i> Hardware</a>
                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo base_url('advanced/software'); ?>"><i class="fas fa-laptop-code mr-1"></i> Software</a>
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
                                SIM Records
                                <small class="text-muted ml-2">Showing <?= count($data) ?> entries</small>
                            </h3>
                            <div class="card-tools my-2">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="border-bottom px-3 py-2 d-flex justify-content-between align-items-center">
                            <div class="input-group input-group-sm" style="max-width:350px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" class="form-control table-search" placeholder="Search SIM configs..." data-table="table-sortable">
                            </div>
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <select class="form-control" id="deviceFilter" onchange="location.href='?device='+this.value">
                                    <option value="">All Devices</option>
                                    <?php foreach ($devices as $d): ?>
                                    <option value="<?= htmlspecialchars($d['device_id']) ?>" <?= $selectedDevice === $d['device_id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['device_id']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered mb-0 table-sortable">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>SIM Serial</th>
                                        <th>Subscriber ID</th>
                                        <th>Operator</th>
                                        <th>Country</th>
                                        <th>State</th>
                                        <th>Changed</th>
                                        <th>Captured</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($data)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-sim-card fa-3x text-muted mb-3"></i>
                                                    <h4>No SIM config data</h4>
                                                    <p class="text-muted">SIM configurations will appear here once extracted from the device</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: foreach ($data as $r): ?>
                                        <tr>
                                            <td class="align-middle"><?= htmlspecialchars($r['sim_serial'] ?? '—') ?></td>
                                            <td class="align-middle"><?= htmlspecialchars($r['subscriber_id'] ?? '—') ?></td>
                                            <td class="align-middle"><?= htmlspecialchars($r['sim_operator_name'] ?? '—') ?></td>
                                            <td class="align-middle"><?= htmlspecialchars($r['sim_country_iso'] ?? '—') ?></td>
                                            <td class="align-middle">
                                                <?php
                                                    $state = $r['sim_state'] ?? '—';
                                                    $badge = match (strtolower($state)) {
                                                        'ready'     => 'success',
                                                        'absent'    => 'secondary',
                                                        'unknown'   => 'warning',
                                                        default     => 'info',
                                                    };
                                                ?>
                                                <span class="badge badge-<?= $badge ?> p-2"><?= htmlspecialchars($state) ?></span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <?php if (!empty($r['is_sim_changed'])): ?>
                                                <span class="badge badge-danger p-2"><i class="fas fa-exclamation-triangle mr-1"></i>Yes</span>
                                                <?php else: ?>
                                                <span class="badge badge-success p-2">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle"><?= !empty($r['captured_at']) ? format_timestamp_display((int)$r['captured_at']) : '—' ?></td>
                                             <td class="text-center align-middle">
                                                 <button type="button" class="btn btn-sm btn-outline-danger delete-sim-config"
                                                        data-id="<?= $r['id'] ?>"
                                                        title="Delete SIM record">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
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

    document.querySelectorAll('.delete-sim-config').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete SIM Record?',
                    text: 'Are you sure you want to delete this SIM config record?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash"></i> Delete'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        fetch(base_url('sim-configs/delete/' + id), {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(function(r) { return r.json(); }).then(function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', 'SIM record has been deleted.', 'success').then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.error || 'Failed to delete SIM record.', 'error');
                            }
                        }).catch(function() {
                            Swal.fire('Error!', 'Failed to delete SIM record.', 'error');
                        });
                    }
                });
            }
        });
    });
});
</script>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
