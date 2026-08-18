<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
foreach ($rows as &$r) {
    $r['account_unique_key'] = ($r['device_id'] ?? 'default') . '_' . ($r['account_name'] ?? 'unknown');
}
unset($r);

helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'account_unique_key',
    ['account_name', 'account_type', 'account_label', 'total_count', 'summary_json', 'is_syncable', 'last_sync_time', 'last_sync_result', 'auth_token_type', 'features']
);
?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Accounts',
    'subtitle' => 'User accounts, email addresses, and credentials',
    'tableId'  => 'accountsTable',
    'columns'  => [
        ['field' => 'extracted_at', 'label' => 'Extracted', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'account_name', 'label' => 'Account Name', 'format' => 'text', 'icon' => 'fas fa-user'],
        ['field' => 'account_type', 'label' => 'Type',      'format' => 'badge', 'map' => ['google' => 'info', 'com.google' => 'info'], 'default' => 'secondary', 'icon' => 'fas fa-tag'],
        ['field' => 'account_label', 'label' => 'Label',    'format' => 'text', 'icon' => 'fas fa-label'],
    ],
    'secondary' => [
        ['field' => 'owner_id',     'label' => 'Owner ID',   'format' => 'text', 'icon' => 'fas fa-id-badge'],
        ['field' => 'device_id',    'label' => 'Device ID',  'format' => 'text', 'icon' => 'fas fa-mobile-alt'],
        ['field' => 'total_count',  'label' => 'Total Count', 'format' => 'text', 'icon' => 'fas fa-hashtag'],
        ['field' => 'summary_json', 'label' => 'Summary',    'format' => 'json', 'jsonTitle' => 'Account Summary', 'icon' => 'fas fa-file-alt'],
        ['field' => 'is_syncable',  'label' => 'Syncable',   'format' => 'yesno', 'icon' => 'fas fa-sync'],
        ['field' => 'last_sync_time', 'label' => 'Last Sync', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'last_sync_result', 'label' => 'Sync Result', 'format' => 'text', 'icon' => 'fas fa-info-circle'],
        ['field' => 'auth_token_type', 'label' => 'Auth Token Type', 'format' => 'text', 'icon' => 'fas fa-key'],
        ['field' => 'features',     'label' => 'Features',   'format' => 'json', 'jsonTitle' => 'Account Features', 'icon' => 'fas fa-cogs'],
        ['field' => 'created_at',   'label' => 'Created',    'format' => 'timestamp', 'icon' => 'fas fa-calendar-plus'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
]) ?>