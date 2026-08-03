<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Accessibility Services',
    'subtitle' => 'Enabled accessibility services and their capabilities',
    'tableId'  => 'accessibilityTable',
    'columns'  => [
        ['field' => 'extracted_at',              'label' => 'Extracted', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'package_name',              'label' => 'Package',   'format' => 'text', 'icon' => 'fas fa-code'],
        ['field' => 'service_id',                'label' => 'Service ID', 'format' => 'code', 'icon' => 'fas fa-id-badge'],
        ['field' => 'description',               'label' => 'Description', 'format' => 'text', 'truncate' => 60, 'icon' => 'fas fa-align-left'],
        ['field' => 'can_retrieve_window_content','label' => 'Window Content', 'format' => 'yesno', 'icon' => 'fas fa-window-maximize'],
    ],
    'secondary' => [
        ['field' => 'capabilities',           'label' => 'Capabilities',   'format' => 'json', 'jsonTitle' => 'Capabilities', 'icon' => 'fas fa-list'],
        ['field' => 'flags',                  'label' => 'Flags',          'format' => 'text', 'icon' => 'fas fa-flag'],
        ['field' => 'feedback_type',          'label' => 'Feedback Type',  'format' => 'text', 'icon' => 'fas fa-volume-up'],
        ['field' => 'notification_timeout',   'label' => 'Notification Timeout', 'format' => 'text', 'icon' => 'fas fa-clock'],
        ['field' => 'settings_activity_name', 'label' => 'Settings Activity', 'format' => 'code', 'icon' => 'fas fa-cog'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/accessibility/delete'),
]) ?>
