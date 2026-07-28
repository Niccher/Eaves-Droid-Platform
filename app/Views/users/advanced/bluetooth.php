<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fab fa-bluetooth-b text-primary mr-2"></i>Bluetooth</h1>
                        <span class="badge badge-primary border p-2"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Bluetooth adapter snapshots and paired device profiles</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fab fa-bluetooth-b mr-2"></i>Bluetooth Adapters</h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($rows)): ?>
                <div class="text-center py-5">
                    <div class="empty-state"><i class="fab fa-bluetooth fa-3x text-muted mb-3"></i><h4>No Bluetooth data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                </div>
                <?php else: ?>
                <table id="bluetooth-table" class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Adapter Name</th>
                            <th>Status</th>
                            <th>Paired Devices</th>
                            <th>Date Extracted</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                        <?php
                            $pairedDevices = $r['paired_devices'] ?? [];
                            $pairedCount = $r['paired_count'] ?? count($pairedDevices);
                        ?>
                        <tr class="bluetooth-parent-row" data-devices="<?= base64_encode(json_encode($pairedDevices)) ?>">
                            <td>
                                <i class="fab fa-bluetooth mr-1 text-primary"></i>
                                <strong><?= htmlspecialchars($r['adapter_name'] ?? '—') ?></strong>
                                <?php if (!empty($r['adapter_address'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($r['adapter_address']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= !empty($r['is_enabled']) ? 'success' : 'danger' ?>">
                                    <i class="fas fa-<?= !empty($r['is_enabled']) ? 'check-circle' : 'times-circle' ?> mr-1"></i>
                                    <?= !empty($r['is_enabled']) ? 'Enabled' : 'Disabled' ?>
                                </span>
                            </td>
                            <td><span class="badge badge-warning"><?= $pairedCount ?></span></td>
                            <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—' ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary details-row"
                                        data-data='<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>'
                                        data-title="Bluetooth Adapter Details"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $r['id'] ?? '' ?>"
                                        data-url="<?= base_url('advanced/bluetooth/delete') ?>"
                                        title="Delete this row">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    var table = $('#bluetooth-table').DataTable({
        paging: false,
        searching: false,
        info: false,
        order: []
    });

    $('#bluetooth-table tbody').on('click', 'tr.bluetooth-parent-row', function() {
        var tr = $(this);
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            var raw = tr.data('devices');
            var devices = raw ? JSON.parse(atob(raw)) : [];
            var html = '';

            if (devices.length > 0) {
                html += '<div class="table-responsive"><table class="table table-hover table-sm mb-0 child-table">';
                html += '<thead class="thead-light"><tr>';
                html += '<th>Device Name</th><th>Address</th><th>Type</th><th>Bond State</th><th>Alias</th>';
                html += '</tr></thead><tbody>';

                $.each(devices, function(i, dev) {
                    var bondBadge = dev.bond_state === 'BONDED' ? 'success' : 'warning';
                    html += '<tr>';
                    html += '<td><i class="fab fa-bluetooth mr-1 text-primary"></i><strong>' + $('<div>').text(dev.bt_name || '—').html() + '</strong></td>';
                    html += '<td><code>' + $('<div>').text(dev.bt_address || '—').html() + '</code></td>';
                    html += '<td><span class="badge badge-secondary">' + $('<div>').text(dev.bt_type || '—').html() + '</span></td>';
                    html += '<td><span class="badge badge-' + bondBadge + '">' + $('<div>').text(dev.bond_state || '—').html() + '</span></td>';
                    html += '<td class="text-muted">' + $('<div>').text(dev.alias || '—').html() + '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
            } else {
                html += '<div class="alert alert-info text-center mb-0"><i class="fas fa-info-circle mr-2"></i>No paired devices found for this snapshot.</div>';
            }

            row.child(html).show();
            tr.addClass('shown');
        }
    });
});
</script>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
