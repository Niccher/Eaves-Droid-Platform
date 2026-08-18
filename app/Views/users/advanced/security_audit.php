<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?php
// Derive count columns from the decoded JSON arrays (populated by get_security_audit)
foreach ($rows as &$row) {
    $row['device_admin_count'] = count($row['device_admin_apps'] ?? []);
    $row['open_ports_count']   = count($row['open_ports'] ?? []);
    $dns = $row['dns_config'] ?? [];
    $row['dns_config_count']   = isset($dns['servers']) && is_array($dns['servers'])
        ? count($dns['servers'])
        : (is_array($dns) ? count($dns) : 0);
    $vpn = $row['vpn_config'] ?? [];
    $row['vpn_config_count']   = is_array($vpn) ? count($vpn) : 0;
}
unset($row);
?>
<?= view('users/advanced/_card_table', [
    'title'    => 'Security Audit',
    'subtitle' => 'VPN/Proxy status, open ports, user & system CA certificates, VPN config, device admin apps, DNS configuration',
    'tableId'  => 'securityAuditTable',
    'columns'  => [
        ['field' => 'vpn_active',      'label' => 'VPN',            'format' => 'yesno', 'icon' => 'fas fa-network-wired'],
        ['field' => 'proxy_active',    'label' => 'Proxy',          'format' => 'yesno', 'icon' => 'fas fa-sitemap'],
        ['field' => 'device_admin_count', 'label' => 'Device Admin Apps', 'format' => 'count', 'icon' => 'fas fa-user-shield'],
        ['field' => 'open_ports_count',   'label' => 'Open Ports',  'format' => 'count', 'icon' => 'fas fa-door-open'],
        ['field' => 'dns_config_count',   'label' => 'DNS Config',  'format' => 'count', 'icon' => 'fas fa-server'],
        ['field' => 'vpn_config_count',   'label' => 'VPN Configs', 'format' => 'count', 'icon' => 'fas fa-network-wired'],
        ['field' => 'audit_timestamp', 'label' => 'Audit Time','format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'secondary' => [
        ['field' => 'user_ca_certs_json',     'label' => 'User CA Certs',    'format' => 'json', 'jsonTitle' => 'User CA Certs', 'icon' => 'fas fa-certificate'],
        ['field' => 'system_ca_certs_json',   'label' => 'System CA Certs',  'format' => 'json', 'jsonTitle' => 'System CA Certs', 'icon' => 'fas fa-certificate'],
        ['field' => 'open_ports_json',        'label' => 'Open Ports',       'format' => 'json', 'jsonTitle' => 'Open Ports', 'icon' => 'fas fa-door-open'],
        ['field' => 'device_admin_apps_json', 'label' => 'Device Admin Apps','format' => 'json', 'jsonTitle' => 'Device Admin Apps', 'icon' => 'fas fa-user-shield'],
        ['field' => 'dns_config_json',        'label' => 'DNS Config',       'format' => 'json', 'jsonTitle' => 'DNS Config', 'icon' => 'fas fa-server'],
        ['field' => 'vpn_config_json',        'label' => 'VPN Config',       'format' => 'json', 'jsonTitle' => 'VPN Config', 'icon' => 'fas fa-network-wired'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/security_audit/delete'),
]) ?>
