<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Screen State',
    'subtitle' => 'Screen on/off events, unlock attempts, and brightness levels',
    'tableId'  => 'screenStateTable',
    'columns'  => [
        ['field' => 'event_type',        'label' => 'Event',        'format' => 'badge', 'map' => ['off' => 'danger', 'screen_off' => 'danger', 'OFF' => 'danger', 'SCREEN_OFF' => 'danger', 'power_off' => 'danger'], 'default' => 'success', 'icon' => 'fas fa-power-off'],
        ['field' => 'timestamp',         'label' => 'Timestamp',    'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'battery_level',     'label' => 'Battery',      'format' => 'percent', 'icon' => 'fas fa-battery-three-quarters'],
        ['field' => 'unlock_method',     'label' => 'Unlock Method','format' => 'badge', 'default' => 'info', 'icon' => 'fas fa-key'],
        ['field' => 'unlock_success',    'label' => 'Success',      'format' => 'yesno', 'icon' => 'fas fa-check'],
        ['field' => 'screen_brightness', 'label' => 'Brightness',   'format' => 'text', 'icon' => 'fas fa-sun'],
    ],
    'secondary' => [
        ['field' => 'failed_attempts',     'label' => 'Failed Attempts', 'format' => 'text', 'icon' => 'fas fa-times-circle'],
        ['field' => 'strong_auth_required','label' => 'Strong Auth Required', 'format' => 'yesno', 'icon' => 'fas fa-shield-alt'],
        ['field' => 'auto_brightness',     'label' => 'Auto Brightness', 'format' => 'yesno', 'icon' => 'fas fa-adjust'],
        ['field' => 'doze_state',          'label' => 'Doze State',      'format' => 'text', 'icon' => 'fas fa-moon'],
        ['field' => 'keyguard_state',      'label' => 'Keyguard State',  'format' => 'text', 'icon' => 'fas fa-lock'],
        ['field' => 'extracted_at',        'label' => 'Extracted',       'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/screen_state/delete'),
]) ?>
