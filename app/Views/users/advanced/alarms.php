<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Alarms & Jobs',
    'subtitle' => 'Scheduled JobScheduler jobs and AlarmManager alarms',
    'tableId'  => 'alarmsTable',
    'columns'  => [
        ['field' => 'extracted_at',        'label' => 'Extracted',      'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'job_count',           'label' => 'Jobs',           'format' => 'text', 'icon' => 'fas fa-tasks'],
        ['field' => 'scheduled_jobs_json', 'label' => 'Scheduled Jobs', 'format' => 'json', 'jsonTitle' => 'Scheduled Jobs', 'icon' => 'fas fa-list'],
        ['field' => 'alarm_clocks_json',   'label' => 'Alarm Clocks',   'format' => 'json', 'jsonTitle' => 'Alarm Clocks', 'icon' => 'fas fa-alarm-clock'],
    ],
    'secondary' => [
        ['field' => 'device_id', 'label' => 'Device ID', 'format' => 'text', 'icon' => 'fas fa-mobile-alt'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/alarms/delete'),
]) ?>
