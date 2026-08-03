<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Email Accounts',
    'subtitle' => 'Configured email accounts, providers, and sync status',
    'tableId'  => 'emailTable',
    'columns'  => [
        ['field' => 'account_email',  'label' => 'Email',     'format' => 'text', 'icon' => 'fas fa-envelope'],
        ['field' => 'account_type',   'label' => 'Type',      'format' => 'badge', 'map' => ['google' => 'info'], 'default' => 'info', 'icon' => 'fas fa-tag'],
        ['field' => 'provider',       'label' => 'Provider',  'format' => 'text', 'icon' => 'fas fa-server'],
        ['field' => 'is_primary',     'label' => 'Primary',   'format' => 'yesno', 'icon' => 'fas fa-star'],
        ['field' => 'last_sync_time', 'label' => 'Last Sync', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'secondary' => [
        ['field' => 'folder',       'label' => 'Folder',    'format' => 'text', 'icon' => 'fas fa-folder'],
        ['field' => 'extracted_at', 'label' => 'Extracted', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'created_at',   'label' => 'Created',   'format' => 'timestamp', 'icon' => 'fas fa-calendar-plus'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/email/delete'),
]) ?>
