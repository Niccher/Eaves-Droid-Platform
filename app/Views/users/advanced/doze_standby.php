<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Doze & Standby',
    'subtitle' => 'Doze state, battery saver, adaptive battery, and standby buckets',
    'tableId'  => 'dozeStandbyTable',
    'columns'  => [
        ['field' => 'is_in_deep_doze',      'label' => 'Deep Doze',      'format' => 'yesno', 'icon' => 'fas fa-moon'],
        ['field' => 'is_in_light_doze',     'label' => 'Light Doze',     'format' => 'yesno', 'icon' => 'fas fa-cloud-moon'],
        ['field' => 'battery_saver_enabled','label' => 'Battery Saver',  'format' => 'yesno', 'icon' => 'fas fa-battery-half'],
        ['field' => 'adaptive_battery_enabled', 'label' => 'Adaptive Battery', 'format' => 'yesno', 'icon' => 'fas fa-brain'],
        ['field' => 'device_standby_bucket', 'label' => 'Standby Bucket', 'format' => 'badge', 'map' => ['active' => 'success', 'working_set' => 'success'], 'default' => 'info', 'icon' => 'fas fa-layer-group'],
        ['field' => 'extracted_at',          'label' => 'Extracted',     'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'secondary' => [
        ['field' => 'apps',                    'label' => 'Doze Standby Apps', 'format' => 'json', 'jsonTitle' => 'Doze Standby Apps', 'icon' => 'fas fa-list'],
        ['field' => 'power_save_mode',         'label' => 'Power Save Mode',   'format' => 'yesno', 'icon' => 'fas fa-battery-half'],
        ['field' => 'battery_saver_since',     'label' => 'Battery Saver Since', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'next_maintenance_window', 'label' => 'Next Maintenance',  'format' => 'timestamp', 'icon' => 'fas fa-calendar-alt'],
        ['field' => 'last_standby_transition', 'label' => 'Last Transition',   'format' => 'timestamp', 'icon' => 'fas fa-exchange-alt'],
        ['field' => 'adaptive_battery_learning', 'label' => 'Adaptive Learning', 'format' => 'yesno', 'icon' => 'fas fa-brain'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/doze_standby/delete'),
]) ?>
