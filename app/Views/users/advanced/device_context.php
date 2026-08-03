<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Device Context',
    'subtitle' => 'Battery state, clipboard content and locale settings',
    'tableId'  => 'deviceContextTable',
    'columns'  => [
        ['field' => 'extracted_at',            'label' => 'Extracted', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'battery_level_percent',   'label' => 'Battery',   'format' => 'text', 'icon' => 'fas fa-battery-three-quarters'],
        ['field' => 'battery_is_charging',     'label' => 'Charging',  'format' => 'yesno', 'icon' => 'fas fa-bolt'],
        ['field' => 'battery_health',          'label' => 'Battery Health', 'format' => 'text', 'icon' => 'fas fa-heartbeat'],
        ['field' => 'locale_display_language', 'label' => 'Language',  'format' => 'text', 'icon' => 'fas fa-language'],
        ['field' => 'locale_display_country',  'label' => 'Country',   'format' => 'text', 'icon' => 'fas fa-globe'],
        ['field' => 'locale_timezone',         'label' => 'Timezone',  'format' => 'code', 'icon' => 'fas fa-clock'],
    ],
    'secondary' => [
        ['field' => 'battery_plugged_usb',        'label' => 'Plugged USB', 'format' => 'yesno', 'icon' => 'fas fa-usb'],
        ['field' => 'battery_plugged_ac',         'label' => 'Plugged AC',  'format' => 'yesno', 'icon' => 'fas fa-plug'],
        ['field' => 'battery_temperature_celsius','label' => 'Temperature', 'format' => 'text', 'icon' => 'fas fa-thermometer-half'],
        ['field' => 'battery_voltage_mv',         'label' => 'Voltage (mV)', 'format' => 'text', 'icon' => 'fas fa-bolt'],
        ['field' => 'clipboard_text',             'label' => 'Clipboard',   'format' => 'text', 'truncate' => 80, 'icon' => 'fas fa-clipboard'],
        ['field' => 'locale_country',             'label' => 'Locale Country', 'format' => 'text', 'icon' => 'fas fa-globe'],
        ['field' => 'locale_language',            'label' => 'Locale Language', 'format' => 'text', 'icon' => 'fas fa-language'],
        ['field' => 'locale_timezone_offset_ms',  'label' => 'TZ Offset (ms)', 'format' => 'text', 'icon' => 'fas fa-clock'],
        ['field' => 'created_at',                 'label' => 'Created',     'format' => 'timestamp', 'icon' => 'fas fa-calendar-plus'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'deleteUrl' => base_url('advanced/hardware/device/delete'),
]) ?>
