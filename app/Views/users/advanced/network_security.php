<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
// Extract VPN interface name and active status from JSON
foreach ($rows as &$row) {
    $vpn = $row['vpn_config'] ?? [];
    $row['vpn_interface_name'] = $vpn['interface_name'] ?? ($vpn['interface'] ?? '—');
    $row['vpn_is_active'] = isset($vpn['is_active']) ? ($vpn['is_active'] ? 1 : 0) : (isset($vpn['vpn_active']) ? ($vpn['vpn_active'] ? 1 : 0) : 0);
}
?>
<?= view('users/advanced/_card_table', [
    'title'    => 'Network Security',
    'subtitle' => 'DNS Configuration and VPN Status',
    'tableId'  => 'networkSecurityTable',
    'columns'  => [
        ['field' => 'extracted_at',       'label' => 'Extracted',        'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'vpn_interface_name', 'label' => 'Interface Name',   'format' => 'text', 'icon' => 'fas fa-sitemap'],
        ['field' => 'vpn_is_active',      'label' => 'VPN Config Active','format' => 'yesno', 'icon' => 'fas fa-toggle-on'],
    ],
    'secondary' => [
        ['field' => 'dns_config_json', 'label' => 'DNS Config', 'format' => 'json', 'jsonTitle' => 'DNS Config', 'icon' => 'fas fa-server'],
        ['field' => 'vpn_config_json', 'label' => 'VPN Config', 'format' => 'json', 'jsonTitle' => 'VPN Config', 'icon' => 'fas fa-network-wired'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/network_security/delete'),
]) ?>
