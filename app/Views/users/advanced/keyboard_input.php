<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Keyboard Input',
    'subtitle' => 'Installed input methods, default keyboard, and IME subtypes',
    'tableId'  => 'keyboardInputTable',
    'columns'  => [
        ['field' => 'extracted_at', 'label' => 'Extracted', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'ime_label',    'label' => 'Keyboard',  'format' => 'text', 'icon' => 'fas fa-keyboard'],
        ['field' => 'ime_package',  'label' => 'Package',   'format' => 'text', 'icon' => 'fas fa-code'],
        ['field' => 'is_enabled',   'label' => 'Enabled',   'format' => 'yesno', 'icon' => 'fas fa-toggle-on'],
        ['field' => 'is_default',   'label' => 'Default',   'format' => 'yesno', 'icon' => 'fas fa-star'],
        ['field' => 'is_system_ime','label' => 'System IME','format' => 'yesno', 'icon' => 'fas fa-cog'],
    ],
    'secondary' => [
        ['field' => 'is_auxiliary',                            'label' => 'Auxiliary', 'format' => 'yesno', 'icon' => 'fas fa-plus'],
        ['field' => 'supports_switching_to_next_input_method', 'label' => 'Can Switch IME', 'format' => 'yesno', 'icon' => 'fas fa-exchange-alt'],
        ['field' => 'subtypes',                                'label' => 'Subtypes',  'format' => 'json', 'jsonTitle' => 'Subtypes', 'icon' => 'fas fa-list'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/keyboard_input/delete'),
]) ?>
