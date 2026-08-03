<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'App Permissions',
    'subtitle' => 'Requested, granted, and runtime permissions per package',
    'tableId'  => 'appPermissionsTable',
    'columns'  => [
        ['field' => 'extracted_at',    'label' => 'Extracted', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'package_name',    'label' => 'Package',   'format' => 'text', 'icon' => 'fas fa-code'],
        ['field' => 'permission_name', 'label' => 'Permission', 'format' => 'code', 'icon' => 'fas fa-shield-alt'],
        ['field' => 'is_granted',      'label' => 'Granted',   'format' => 'yesno', 'icon' => 'fas fa-check'],
        ['field' => 'is_runtime',      'label' => 'Runtime',   'format' => 'yesno', 'icon' => 'fas fa-clock'],
        ['field' => 'is_revoked',      'label' => 'Revoked',   'format' => 'yesno', 'icon' => 'fas fa-ban'],
    ],
    'secondary' => [
        ['field' => 'is_requested',               'label' => 'Requested',  'format' => 'yesno', 'icon' => 'fas fa-question'],
        ['field' => 'is_system_fixed',            'label' => 'System Fixed', 'format' => 'yesno', 'icon' => 'fas fa-lock'],
        ['field' => 'grant_time',                 'label' => 'Grant Time', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'last_used_time',             'label' => 'Last Used',  'format' => 'timestamp', 'icon' => 'fas fa-history'],
        ['field' => 'flags',                      'label' => 'Flags',      'format' => 'text', 'icon' => 'fas fa-flag'],
        ['field' => 'is_one_time',                'label' => 'One Time',   'format' => 'yesno', 'icon' => 'fas fa-redo'],
        ['field' => 'is_auto_revoke_whitelisted', 'label' => 'Auto-Revoke Whitelisted', 'format' => 'yesno', 'icon' => 'fas fa-user-shield'],
        ['field' => 'user_set',                   'label' => 'User Set',   'format' => 'yesno', 'icon' => 'fas fa-user'],
        ['field' => 'fixed_policy',               'label' => 'Fixed Policy', 'format' => 'text', 'icon' => 'fas fa-gavel'],
        ['field' => 'is_hard_restricted',         'label' => 'Hard Restricted', 'format' => 'yesno', 'icon' => 'fas fa-lock'],
        ['field' => 'is_soft_restricted',         'label' => 'Soft Restricted', 'format' => 'yesno', 'icon' => 'fas fa-unlock'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/app_permissions/delete'),
]) ?>
