<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Content Providers',
    'subtitle' => 'Registered content provider authorities, permissions, and exported flags',
    'tableId'  => 'contentProvidersTable',
    'columns'  => [
        ['field' => 'extracted_at',    'label' => 'Extracted',    'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'authority',       'label' => 'Authority',    'format' => 'code', 'icon' => 'fas fa-database'],
        ['field' => 'name',            'sub_field' => 'package_name', 'label' => 'Name', 'format' => 'stacked', 'icon' => 'fas fa-tag'],
        ['field' => 'is_exported',     'label' => 'Exported',     'format' => 'yesno', 'icon' => 'fas fa-globe'],
        ['field' => 'read_permission', 'label' => 'Read Perm',    'format' => 'code', 'icon' => 'fas fa-lock'],
    ],
    'secondary' => [
        ['field' => 'write_permission',     'label' => 'Write Perm',      'format' => 'code', 'icon' => 'fas fa-lock'],
        ['field' => 'grant_uri_permissions','label' => 'Grant URI Perms', 'format' => 'yesno', 'icon' => 'fas fa-key'],
        ['field' => 'is_syncable',          'label' => 'Syncable',        'format' => 'yesno', 'icon' => 'fas fa-sync'],
        ['field' => 'is_multiprocess',      'label' => 'Multiprocess',    'format' => 'yesno', 'icon' => 'fas fa-cogs'],
        ['field' => 'init_order',           'label' => 'Init Order',      'format' => 'text', 'icon' => 'fas fa-sort-numeric-up'],
        ['field' => 'authorities',          'label' => 'Authorities',     'format' => 'json', 'jsonTitle' => 'Authorities', 'icon' => 'fas fa-list'],
        ['field' => 'flags',                'label' => 'Flags',           'format' => 'text', 'icon' => 'fas fa-flag'],
        ['field' => 'path_permissions',     'label' => 'Path Permissions','format' => 'json', 'jsonTitle' => 'Path Permissions', 'icon' => 'fas fa-folder'],
        ['field' => 'types',                'label' => 'Types',           'format' => 'json', 'jsonTitle' => 'Types', 'icon' => 'fas fa-tags'],
        ['field' => 'stream_types',         'label' => 'Stream Types',    'format' => 'json', 'jsonTitle' => 'Stream Types', 'icon' => 'fas fa-stream'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/content_providers/delete'),
]) ?>
