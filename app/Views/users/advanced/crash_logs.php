<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Crash Logs',
    'subtitle' => 'Application crashes, exceptions, stack traces, and ANR events',
    'tableId'  => 'crashLogsTable',
    'columns'  => [
        ['field' => 'crash_time',       'label' => 'Crash Time', 'format' => 'timestamp', 'icon' => 'fas fa-bomb'],
        ['field' => 'extracted_at',     'label' => 'Extracted',  'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'package_name',     'label' => 'Package',    'format' => 'text', 'icon' => 'fas fa-code'],
        ['field' => 'crash_type',       'label' => 'Type',       'format' => 'badge', 'map' => ['anr' => 'danger', 'native' => 'warning', 'crash' => 'danger', 'exception' => 'info'], 'default' => 'info', 'icon' => 'fas fa-exclamation-triangle'],
        ['field' => 'exception_class',  'label' => 'Exception',  'format' => 'text', 'icon' => 'fas fa-bug'],
        ['field' => 'process_name',     'label' => 'Process',    'format' => 'text', 'icon' => 'fas fa-tasks'],
        ['field' => 'pid',              'label' => 'PID',        'format' => 'text', 'icon' => 'fas fa-hashtag'],
    ],
    'secondary' => [
        ['field' => 'exception_message', 'label' => 'Exception Message', 'format' => 'text', 'truncate' => 60, 'icon' => 'fas fa-comment-alt'],
        ['field' => 'uid',               'label' => 'UID',               'format' => 'text', 'icon' => 'fas fa-id-badge'],
        ['field' => 'is_system_app',     'label' => 'System App',        'format' => 'yesno', 'icon' => 'fas fa-cog'],
        ['field' => 'is_silent',         'label' => 'Silent',            'format' => 'yesno', 'icon' => 'fas fa-volume-mute'],
        ['field' => 'build_fingerprint', 'label' => 'Build Fingerprint', 'format' => 'text', 'icon' => 'fas fa-fingerprint'],
        ['field' => 'android_version',   'label' => 'Android Version',   'format' => 'text', 'icon' => 'fas fa-android'],
        ['field' => 'device_model',      'label' => 'Device Model',      'format' => 'text', 'icon' => 'fas fa-mobile-alt'],
        ['field' => 'stack_trace',       'label' => 'Stack Trace',       'format' => 'json', 'jsonTitle' => 'Stack Trace', 'icon' => 'fas fa-code'],
        ['field' => 'logcat_tail',       'label' => 'Logcat',            'format' => 'json', 'jsonTitle' => 'Logcat', 'icon' => 'fas fa-terminal'],
        ['field' => 'dropbox_tag',       'label' => 'Dropbox Tag',       'format' => 'text', 'icon' => 'fas fa-tag'],
        ['field' => 'last_crash_time',   'label' => 'Last Crash',        'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/crash_logs/delete'),
]) ?>
