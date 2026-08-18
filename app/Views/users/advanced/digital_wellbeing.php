<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Digital Wellbeing',
    'subtitle' => 'Focus/bedtime modes, screen time, and per-app usage limits',
    'tableId'  => 'digitalWellbeingTable',
    'columns'  => [
        ['field' => 'total_daily_usage_minutes', 'label' => 'Screen Time',   'format' => 'hours', 'icon' => 'fas fa-stopwatch'],
        ['field' => 'unlock_count',              'label' => 'Unlocks',       'format' => 'text', 'icon' => 'fas fa-unlock'],
        ['field' => 'notification_count',        'label' => 'Notifications', 'format' => 'text', 'icon' => 'fas fa-bell'],
        ['field' => 'focus_mode_enabled',        'label' => 'Focus Mode',    'format' => 'yesno', 'icon' => 'fas fa-brain'],
        ['field' => 'bedtime_mode_enabled',      'label' => 'Bedtime Mode',  'format' => 'yesno', 'icon' => 'fas fa-moon'],
    ],
    'secondary' => [
        ['field' => 'social_minutes',        'label' => 'Social',           'format' => 'text', 'icon' => 'fas fa-users'],
        ['field' => 'productivity_minutes',  'label' => 'Productivity',     'format' => 'text', 'icon' => 'fas fa-briefcase'],
        ['field' => 'entertainment_minutes', 'label' => 'Entertainment',    'format' => 'text', 'icon' => 'fas fa-gamepad'],
        ['field' => 'other_minutes',         'label' => 'Other',            'format' => 'text', 'icon' => 'fas fa-ellipsis-h'],
        ['field' => 'focus_mode_apps',       'label' => 'Focus Mode Apps',  'format' => 'json', 'jsonTitle' => 'Focus Mode Apps', 'icon' => 'fas fa-list'],
        ['field' => 'wind_down_enabled',     'label' => 'Wind Down',        'format' => 'yesno', 'icon' => 'fas fa-sun'],
        ['field' => 'wind_down_schedule',    'label' => 'Wind Down Schedule','format' => 'json', 'jsonTitle' => 'Wind Down Schedule', 'icon' => 'fas fa-calendar-alt'],
        ['field' => 'bedtime_schedule',      'label' => 'Bedtime Schedule', 'format' => 'json', 'jsonTitle' => 'Bedtime Schedule', 'icon' => 'fas fa-moon'],
        ['field' => 'extracted_at',          'label' => 'Extracted',        'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/digital_wellbeing/delete'),
]) ?>
