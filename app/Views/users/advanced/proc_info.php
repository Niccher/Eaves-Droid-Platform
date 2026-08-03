<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
// Helper to get meminfo value with multiple key variations (case-insensitive, with/without _kB)
function get_meminfo_val(array $meminfo, string $key) {
    $variations = [
        $key,
        $key . '_kB',
        $key . '_kb',
        strtolower($key),
        strtolower($key) . '_kb',
        ucfirst(strtolower($key)),
        ucfirst(strtolower($key)) . '_kB',
    ];
    foreach ($variations as $k) {
        if (isset($meminfo[$k]) && $meminfo[$k] !== '' && $meminfo[$k] !== null) {
            return $meminfo[$k];
        }
    }
    return null;
}

// Helper to get value from cpuinfo (handle both array of cores and single object)
function get_cpu_count($cpuinfo) {
    if (!is_array($cpuinfo)) return 0;
    if (isset($cpuinfo[0]) && is_array($cpuinfo[0])) {
        return count($cpuinfo);
    }
    if (isset($cpuinfo['processor'])) {
        return 1;
    }
    return 0;
}

foreach ($rows as &$row) {
    $meminfo = $row['meminfo_json'] ?? [];
    if (is_string($meminfo)) {
        $meminfo = json_decode($meminfo, true) ?? [];
    }
    if (!is_array($meminfo)) $meminfo = [];

    $cpuinfo = $row['cpuinfo_json'] ?? [];
    if (is_string($cpuinfo)) {
        $cpuinfo = json_decode($cpuinfo, true) ?? [];
    }
    if (!is_array($cpuinfo)) $cpuinfo = [];

    $row['cpu_count'] = get_cpu_count($cpuinfo);

    $kb_to_bytes = 1024;
    $row['mem_available']     = get_meminfo_val($meminfo, 'MemAvailable')     * $kb_to_bytes ?? null;
    $row['swap_free']         = get_meminfo_val($meminfo, 'SwapFree')         * $kb_to_bytes ?? null;
    $row['active_mem']        = get_meminfo_val($meminfo, 'Active')           * $kb_to_bytes ?? null;
    $row['cached_mem']        = get_meminfo_val($meminfo, 'Cached')           * $kb_to_bytes ?? null;
    $row['slab_mem']          = get_meminfo_val($meminfo, 'Slab')             * $kb_to_bytes ?? null;
    $row['anon_pages']        = get_meminfo_val($meminfo, 'AnonPages')        * $kb_to_bytes ?? null;
    $row['cma_free']          = get_meminfo_val($meminfo, 'CmaFree')          * $kb_to_bytes ?? null;
    $row['mem_total']         = get_meminfo_val($meminfo, 'MemTotal')         * $kb_to_bytes ?? null;
    $row['mem_free']          = get_meminfo_val($meminfo, 'MemFree')          * $kb_to_bytes ?? null;
    $row['swap_total']        = get_meminfo_val($meminfo, 'SwapTotal')        * $kb_to_bytes ?? null;
}
?>

<?php
$legend = '<div class="row text-muted small mt-2">
    <div class="col-md-6">
        <strong>MemAvailable:</strong> Free RAM for new apps. Below 300-400MB = aggressive app killing.<br>
        <strong>SwapFree:</strong> Emergency RAM storage. Near 0 = device freeze/crash.<br>
        <strong>Active:</strong> Currently used RAM. Maxed out = CPU overworked.<br>
        <strong>Cached:</strong> App cache. High = fast app opens. Near 0 = RAM starvation.
    </div>
    <div class="col-md-6">
        <strong>Slab:</strong> Kernel overhead. Growing = memory leak (reboot needed).<br>
        <strong>AnonPages:</strong> App private data. High = heavy apps running.<br>
        <strong>CmaFree:</strong> Camera/video reserve. 0 = camera crash/black screen.<br>
        <strong>CPU Count:</strong> Cores from /proc/cpuinfo.
    </div>
</div>';
?>
<?= view('users/advanced/_card_table', [
    'title'    => 'Proc Info',
    'subtitle' => 'Processor, memory, kernel, uptime, and network interface snapshots',
    'legend'   => $legend,
    'tableId'  => 'procInfoTable',
    'columns'  => [
        ['field' => 'version',        'label' => 'Kernel Version', 'format' => 'text', 'truncate' => 60, 'icon' => 'fas fa-code-branch'],
        ['field' => 'cpu_count',      'label' => 'CPU Count',      'format' => 'text', 'icon' => 'fas fa-microchip'],
        ['field' => 'mem_available',  'label' => 'MemAvailable',   'format' => 'bytes', 'icon' => 'fas fa-memory'],
        ['field' => 'swap_free',      'label' => 'SwapFree',       'format' => 'bytes', 'icon' => 'fas fa-hdd'],
        ['field' => 'active_mem',     'label' => 'Active',         'format' => 'bytes', 'icon' => 'fas fa-tachometer-alt'],
        ['field' => 'cached_mem',     'label' => 'Cached',         'format' => 'bytes', 'icon' => 'fas fa-database'],
        ['field' => 'slab_mem',       'label' => 'Slab',           'format' => 'bytes', 'icon' => 'fas fa-cogs'],
        ['field' => 'anon_pages',     'label' => 'AnonPages',      'format' => 'bytes', 'icon' => 'fas fa-layer-group'],
        ['field' => 'cma_free',       'label' => 'CmaFree',        'format' => 'bytes', 'icon' => 'fas fa-camera'],
        ['field' => 'extracted_at',   'label' => 'Extracted',      'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'secondary' => [
        ['field' => 'meminfo_json',          'label' => 'Memory Info (Full)', 'format' => 'json', 'jsonTitle' => 'Memory Info', 'icon' => 'fas fa-memory'],
        ['field' => 'cpuinfo_json',          'label' => 'CPU Info',             'format' => 'json', 'jsonTitle' => 'CPU Info', 'icon' => 'fas fa-microchip'],
        ['field' => 'stat_json',             'label' => 'Stat',                 'format' => 'json', 'jsonTitle' => 'Stat', 'icon' => 'fas fa-chart-line'],
        ['field' => 'uptime_json',           'label' => 'Uptime',               'format' => 'json', 'jsonTitle' => 'Uptime', 'icon' => 'fas fa-clock'],
        ['field' => 'net_interfaces_json',   'label' => 'Network Interfaces',   'format' => 'json', 'jsonTitle' => 'Network Interfaces', 'icon' => 'fas fa-sitemap'],
        ['field' => 'net_connections_json',  'label' => 'Network Connections',  'format' => 'json', 'jsonTitle' => 'Network Connections', 'icon' => 'fas fa-link'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'deleteUrl' => base_url('advanced/hardware/proc_info/delete'),
]) ?>
