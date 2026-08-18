<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Screenshots',
    'subtitle' => 'Captured screenshots and screen recordings with metadata',
    'tableId'  => 'screenshotsTable',
    'columns'  => [
        ['field' => 'timestamp',  'label' => 'Timestamp', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'file_name',  'label' => 'File',      'format' => 'text', 'icon' => 'fas fa-file-image'],
        ['field' => 'file_size',  'label' => 'Size',      'format' => 'bytes', 'icon' => 'fas fa-hdd'],
        ['field' => 'mime_type',  'label' => 'Type',      'format' => 'code', 'icon' => 'fas fa-file-code'],
        ['field' => 'width',      'label' => 'Width',     'format' => 'text', 'icon' => 'fas fa-arrows-alt-h'],
        ['field' => 'height',     'label' => 'Height',    'format' => 'text', 'icon' => 'fas fa-arrows-alt-v'],
    ],
    'secondary' => [
        ['field' => 'source_package',   'label' => 'Source App',    'format' => 'text', 'icon' => 'fas fa-mobile-alt'],
        ['field' => 'is_screen_record', 'label' => 'Screen Record', 'format' => 'yesno', 'icon' => 'fas fa-video'],
        ['field' => 'duration_ms',      'label' => 'Duration',      'format' => 'ms', 'icon' => 'fas fa-stopwatch'],
        ['field' => 'video_width',      'label' => 'Video Width',   'format' => 'text', 'icon' => 'fas fa-arrows-alt-h'],
        ['field' => 'video_height',     'label' => 'Video Height',  'format' => 'text', 'icon' => 'fas fa-arrows-alt-v'],
        ['field' => 'video_duration_ms','label' => 'Video Duration','format' => 'ms', 'icon' => 'fas fa-stopwatch'],
        ['field' => 'video_frame_rate', 'label' => 'Frame Rate',    'format' => 'text', 'icon' => 'fas fa-film'],
        ['field' => 'video_bitrate',    'label' => 'Bitrate',       'format' => 'bytes', 'icon' => 'fas fa-tachometer-alt'],
        ['field' => 'is_edited',        'label' => 'Edited',        'format' => 'yesno', 'icon' => 'fas fa-edit'],
        ['field' => 'edit_timestamp',   'label' => 'Edit Timestamp','format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'edit_app_package', 'label' => 'Edit App',      'format' => 'text', 'icon' => 'fas fa-code'],
        ['field' => 'contains_pii',     'label' => 'Contains PII',  'format' => 'yesno', 'icon' => 'fas fa-user-secret'],
        ['field' => 'pii_types',        'label' => 'PII Types',     'format' => 'json', 'jsonTitle' => 'PII Types', 'icon' => 'fas fa-list'],
        ['field' => 'detection_confidence', 'label' => 'Confidence', 'format' => 'text', 'icon' => 'fas fa-percentage'],
        ['field' => 'extracted_at',     'label' => 'Extracted',     'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/screenshots/delete'),
]) ?>
