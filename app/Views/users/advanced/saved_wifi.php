<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Saved WiFi',
    'subtitle' => 'Configured/saved WiFi networks with security details',
    'tableId'  => 'savedWifiTable',
    'columns'  => [
        ['field' => 'extracted_at',  'label' => 'Extracted',     'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'network_count', 'label' => 'Networks',      'format' => 'badge', 'default' => 'info', 'icon' => 'fas fa-wifi'],
        ['field' => 'networks_json', 'label' => 'Saved Networks', 'format' => 'json', 'jsonTitle' => 'Saved Networks', 'icon' => 'fas fa-list'],
    ],
    'secondary' => [
        ['field' => 'device_id', 'label' => 'Device ID', 'format' => 'text', 'icon' => 'fas fa-mobile-alt'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/saved_wifi/delete'),
]) ?>
