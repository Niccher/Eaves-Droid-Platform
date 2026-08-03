<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Input Methods (IMEs)',
    'subtitle' => 'Enabled keyboards, subtypes and locale configurations',
    'tableId'  => 'inputMethodsTable',
    'columns'  => [
        ['field' => 'extracted_at', 'label' => 'Extracted', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'ime_id',       'label' => 'IME ID',    'format' => 'code', 'icon' => 'fas fa-id-badge'],
        ['field' => 'package_name', 'label' => 'Package',   'format' => 'text', 'icon' => 'fas fa-code'],
        ['field' => 'label',        'label' => 'Label',     'format' => 'text', 'icon' => 'fas fa-tag'],
        ['field' => 'is_system',    'label' => 'System',    'format' => 'yesno', 'icon' => 'fas fa-cog'],
    ],
    'secondary' => [
        ['field' => 'service_name', 'label' => 'Service Name', 'format' => 'code', 'icon' => 'fas fa-cogs'],
        ['field' => 'is_auxiliary', 'label' => 'Auxiliary',    'format' => 'yesno', 'icon' => 'fas fa-plus'],
        ['field' => 'subtypes',     'label' => 'Subtypes',     'format' => 'json', 'jsonTitle' => 'Subtypes', 'icon' => 'fas fa-list'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/input_methods/delete'),
]) ?>
