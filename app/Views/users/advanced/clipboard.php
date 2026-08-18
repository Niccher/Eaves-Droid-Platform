<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Clipboard',
    'subtitle' => 'Clipboard contents, copied text, and source applications',
    'tableId'  => 'clipboardTable',
    'columns'  => [
        ['field' => 'clip_data_type',  'label' => 'Type',      'format' => 'badge', 'default' => 'info', 'icon' => 'fas fa-tag'],
        ['field' => 'clip_text',       'label' => 'Content',   'format' => 'text', 'truncate' => 80, 'icon' => 'fas fa-file-alt'],
        ['field' => 'source_package',  'label' => 'Source App', 'format' => 'text', 'icon' => 'fas fa-mobile-alt'],
        ['field' => 'is_sensitive',    'label' => 'Sensitive', 'format' => 'yesno', 'icon' => 'fas fa-lock'],
        ['field' => 'timestamp',       'label' => 'Timestamp', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'secondary' => [
        ['field' => 'clip_html',                  'label' => 'HTML',              'format' => 'text', 'truncate' => 200, 'icon' => 'fas fa-code'],
        ['field' => 'clip_uri',                   'label' => 'URI',               'format' => 'code', 'icon' => 'fas fa-link'],
        ['field' => 'clip_intent_action',         'label' => 'Intent Action',     'format' => 'text', 'icon' => 'fas fa-bolt'],
        ['field' => 'clip_intent_package',        'label' => 'Intent Package',    'format' => 'text', 'icon' => 'fas fa-code'],
        ['field' => 'label',                      'label' => 'Label',             'format' => 'text', 'icon' => 'fas fa-label'],
        ['field' => 'item_count',                 'label' => 'Item Count',        'format' => 'text', 'icon' => 'fas fa-hashtag'],
        ['field' => 'primary_clip_description',   'label' => 'Primary Description', 'format' => 'text', 'icon' => 'fas fa-info-circle'],
        ['field' => 'extracted_at',               'label' => 'Extracted',         'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/clipboard/delete'),
]) ?>
