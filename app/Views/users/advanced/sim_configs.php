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
$rows = $data ?? [];
$deviceOptions = '';
foreach (($devices ?? []) as $d) {
    $val = htmlspecialchars($d['device_id'] ?? '', ENT_QUOTES);
    $sel = $selectedDevice === ($d['device_id'] ?? null) ? ' selected' : '';
    $deviceOptions .= '<option value="' . $val . '"' . $sel . '>' . $val . '</option>';
}
$navFilter = '<div class="d-inline-flex align-items-center flex-wrap" style="gap:8px;">'
    . '<div class="input-group input-group-sm" style="max-width:220px;">'
    . '<div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>'
    . '<input type="text" class="form-control table-search" id="simSearch" placeholder="Search SIM configs...">'
    . '</div>'
    . '<select class="form-control form-control-sm" id="deviceFilter" style="width:auto;max-width:180px;" onchange="location.href=\'?device=\' + this.value">'
    . '<option value="">All Devices</option>' . $deviceOptions . '</select>'
    . ($nav_urls ?? '')
    . '</div>';
?>
<?= view('users/advanced/_card_table', [
    'title'    => 'SIM Configs',
    'subtitle' => 'SIM card configurations and change history',
    'icon'     => 'fas fa-sim-card',
    'tableId'  => 'simConfigsTable',
    'columns'  => [
        ['field' => 'sim_serial',         'label' => 'SIM Serial',    'format' => 'code', 'icon' => 'fas fa-id-badge'],
        ['field' => 'subscriber_id',      'label' => 'Subscriber ID', 'format' => 'code', 'icon' => 'fas fa-user'],
        ['field' => 'sim_operator_name',  'label' => 'Operator',      'format' => 'text', 'icon' => 'fas fa-building'],
        ['field' => 'sim_country_iso',    'label' => 'Country',       'format' => 'text', 'icon' => 'fas fa-globe'],
        ['field' => 'sim_state',          'label' => 'State',         'format' => 'badge', 'map' => ['READY' => 'success', 'ready' => 'success', 'ABSENT' => 'secondary', 'absent' => 'secondary', 'UNKNOWN' => 'warning', 'unknown' => 'warning'], 'default' => 'info', 'icon' => 'fas fa-info-circle'],
        ['field' => 'is_sim_changed',     'label' => 'Changed',       'format' => 'yesno', 'icon' => 'fas fa-exchange-alt'],
        ['field' => 'captured_at',        'label' => 'Captured',      'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'secondary' => [
        ['field' => 'device_id',   'label' => 'Device ID',  'format' => 'text', 'icon' => 'fas fa-mobile-alt'],
        ['field' => 'phone_type',  'label' => 'Phone Type', 'format' => 'text', 'icon' => 'fas fa-phone'],
        ['field' => 'extracted_at','label' => 'Extracted',  'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'rows'     => $rows,
    'pager'    => $pager,
    'total'    => $total,
    'nav_urls' => $navFilter,
    'deleteUrl'=> base_url('advanced/hardware/sim-configs/delete'),
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('simSearch')?.addEventListener('keyup', function() {
        var keyword = this.value.toLowerCase();
        var table = document.getElementById('simConfigsTable');
        if (!table) return;
        table.querySelectorAll('tbody tr').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().indexOf(keyword) > -1 ? '' : 'none';
        });
    });

    var sortTh = document.querySelectorAll('#simConfigsTable thead th');
    sortTh.forEach(function(th) {
        if (th.classList.contains('no-sort')) return;
        th.style.cursor = 'pointer';
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
});
</script>

<style>
    .table-sortable thead th { cursor: pointer; user-select: none; }
    .table-sortable thead th.sort-asc::after { content: ' \25B2'; font-size: 0.7em; }
    .table-sortable thead th.sort-desc::after { content: ' \25BC'; font-size: 0.7em; }
</style>
