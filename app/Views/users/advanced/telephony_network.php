<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
// Extract telephony details for display
foreach ($rows as &$row) {
    $ims = $row['ims_volte'] ?? [];
    $roaming = $row['data_roaming'] ?? [];
    $row['airplane_mode'] = $roaming['airplane_mode'] ?? ($ims['airplane_mode'] ?? '—');
    $row['data_state'] = $roaming['data_state'] ?? ($ims['data_state'] ?? '—');
    $row['carrier_name'] = $roaming['carrier_name'] ?? ($ims['carrier_name'] ?? '—');
    $row['data_network_type'] = $roaming['data_network_type'] ?? ($ims['data_network_type'] ?? '—');
    $row['call_waiting_enabled'] = $row['call_waiting_enabled'] ?? 0;
}
?>
<?= view('users/advanced/_card_table', [
    'title'    => 'Mobile Network',
    'subtitle' => 'IMS/VoLTE Status and Data Roaming Configuration',
    'tableId'  => 'telephonyNetworkTable',
    'columns'  => [
        ['field' => 'extracted_at',       'label' => 'Extracted',          'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'airplane_mode',      'label' => 'Airplane Mode',      'format' => 'yesno', 'icon' => 'fas fa-plane'],
        ['field' => 'data_state',         'label' => 'Data State',         'format' => 'text', 'icon' => 'fas fa-signal'],
        ['field' => 'carrier_name',       'label' => 'Carrier Name',       'format' => 'text', 'icon' => 'fas fa-tower-cell'],
        ['field' => 'data_network_type',  'label' => 'Data Network Type',  'format' => 'text', 'icon' => 'fas fa-network-wired'],
        ['field' => 'call_waiting_enabled','label' => 'Call Waiting',      'format' => 'yesno', 'icon' => 'fas fa-bell-slash'],
    ],
    'secondary' => [
        ['field' => 'ims_volte_json',            'label' => 'IMS / VoLTE',       'format' => 'json', 'jsonTitle' => 'IMS / VoLTE', 'icon' => 'fas fa-signal'],
        ['field' => 'data_roaming_json',         'label' => 'Data Roaming',      'format' => 'json', 'jsonTitle' => 'Data Roaming', 'icon' => 'fas fa-globe'],
        ['field' => 'ims_registration_state',    'label' => 'IMS Registration',  'format' => 'text', 'icon' => 'fas fa-check-circle'],
        ['field' => 'ims_registration_tech',     'label' => 'IMS Tech',          'format' => 'text', 'icon' => 'fas fa-microchip'],
        ['field' => 'volte_provisioned',         'label' => 'VoLTE Provisioned', 'format' => 'yesno', 'icon' => 'fas fa-phone-volume'],
        ['field' => 'vowifi_provisioned',        'label' => 'VoWiFi Provisioned','format' => 'yesno', 'icon' => 'fas fa-wifi'],
        ['field' => 'vowifi_enabled',            'label' => 'VoWiFi Enabled',    'format' => 'yesno', 'icon' => 'fas fa-wifi'],
        ['field' => 'wfc_mode_pref',             'label' => 'WFC Mode',          'format' => 'text', 'icon' => 'fas fa-cog'],
        ['field' => 'emergency_numbers',         'label' => 'Emergency Numbers', 'format' => 'json', 'jsonTitle' => 'Emergency Numbers', 'icon' => 'fas fa-phone-alt'],
        ['field' => 'voice_message_count',       'label' => 'Voicemail Count',   'format' => 'text', 'icon' => 'fas fa-voicemail'],
        ['field' => 'ussd_service_available',    'label' => 'USSD Available',    'format' => 'yesno', 'icon' => 'fas fa-terminal'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/telephony_network/delete'),
]) ?>
