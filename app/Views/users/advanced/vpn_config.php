<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'device_id',
    ['vpn_active', 'vpn_protocol', 'vpn_server', 'vpn_package', 'vpn_is_always_on', 'vpn_interface', 'vpn_mtu', 'vpn_is_lockdown', 'vpn_label', 'vpn_block_non_vpn', 'vpn_auth_type', 'vpn_ca_cert_sha256', 'vpn_client_cert_sha256', 'vpn_port'],
    ['vpn_dns_servers', 'vpn_routes', 'vpn_apps', 'vpn_dns_search_domains', 'vpn_excluded_apps', 'vpn_included_apps']
);
?>

<?= view('users/advanced/_card_table', [
    'title'    => 'VPN Configuration',
    'subtitle' => 'Active VPN, protocol, DNS servers, routes, and excluded apps',
    'tableId'  => 'vpnConfigTable',
    'columns'  => [
        ['field' => 'extracted_at',   'label' => 'Extracted', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'vpn_active',     'label' => 'Active',    'format' => 'yesno', 'icon' => 'fas fa-toggle-on'],
        ['field' => 'vpn_protocol',   'label' => 'Protocol',  'format' => 'badge', 'default' => 'info', 'icon' => 'fas fa-network-wired'],
        ['field' => 'vpn_server',     'label' => 'Server',    'format' => 'text', 'icon' => 'fas fa-server'],
        ['field' => 'vpn_package',    'label' => 'Package',   'format' => 'text', 'icon' => 'fas fa-code'],
        ['field' => 'vpn_is_always_on', 'label' => 'Always On', 'format' => 'yesno', 'icon' => 'fas fa-lock'],
    ],
    'secondary' => [
        ['field' => 'vpn_interface',         'label' => 'Interface',        'format' => 'code', 'icon' => 'fas fa-sitemap'],
        ['field' => 'vpn_dns_servers',       'label' => 'DNS Servers',       'format' => 'json', 'jsonTitle' => 'DNS Servers', 'icon' => 'fas fa-server'],
        ['field' => 'vpn_routes',            'label' => 'Routes',            'format' => 'json', 'jsonTitle' => 'Routes', 'icon' => 'fas fa-route'],
        ['field' => 'vpn_mtu',               'label' => 'MTU',               'format' => 'text', 'icon' => 'fas fa-ruler'],
        ['field' => 'vpn_is_lockdown',       'label' => 'Lockdown',          'format' => 'yesno', 'icon' => 'fas fa-shield-alt'],
        ['field' => 'vpn_label',             'label' => 'Label',             'format' => 'text', 'icon' => 'fas fa-tag'],
        ['field' => 'vpn_apps',              'label' => 'VPN Apps',          'format' => 'json', 'jsonTitle' => 'VPN Apps', 'icon' => 'fas fa-list'],
        ['field' => 'vpn_dns_search_domains','label' => 'DNS Search Domains', 'format' => 'json', 'jsonTitle' => 'DNS Search Domains', 'icon' => 'fas fa-globe'],
        ['field' => 'vpn_excluded_apps',     'label' => 'Excluded Apps',     'format' => 'json', 'jsonTitle' => 'Excluded Apps', 'icon' => 'fas fa-ban'],
        ['field' => 'vpn_included_apps',     'label' => 'Included Apps',     'format' => 'json', 'jsonTitle' => 'Included Apps', 'icon' => 'fas fa-check-circle'],
        ['field' => 'vpn_block_non_vpn',     'label' => 'Block Non-VPN',     'format' => 'yesno', 'icon' => 'fas fa-ban'],
        ['field' => 'vpn_auth_type',         'label' => 'Auth Type',         'format' => 'text', 'icon' => 'fas fa-key'],
        ['field' => 'vpn_ca_cert_sha256',    'label' => 'CA Cert SHA256',    'format' => 'code', 'icon' => 'fas fa-certificate'],
        ['field' => 'vpn_client_cert_sha256','label' => 'ClientController Cert SHA256', 'format' => 'code', 'icon' => 'fas fa-certificate'],
        ['field' => 'vpn_port',              'label' => 'Port',              'format' => 'text', 'icon' => 'fas fa-door-open'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
]) ?>
