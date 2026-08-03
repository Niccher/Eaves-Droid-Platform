<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
// Helper function to safely decode and truncate JSON
function decode_and_truncate($json_str, $max_items = 20) {
    if (empty($json_str)) return [];
    $decoded = json_decode($json_str, true);
    if (!is_array($decoded)) return [];
    return array_slice($decoded, 0, $max_items);
}

// Decode JSON for display summaries - limit size to prevent memory issues
foreach ($rows as &$row) {
    $adminApps = decode_and_truncate($row['device_admin_apps_json'] ?? '');
    $permMap = decode_and_truncate($row['app_permissions_map_json'] ?? '');
    $runningSvcs = decode_and_truncate($row['running_services_json'] ?? '');
    $row['admin_apps_count'] = count($adminApps);
    $row['perm_apps_count'] = count($permMap);
    $row['running_svcs_count'] = count($runningSvcs);
    
    // Store truncated JSON for secondary display (max 20 items each to prevent memory exhaustion)
    $row['device_admin_apps_json_truncated'] = json_encode($adminApps);
    $row['app_permissions_map_json_truncated'] = json_encode($permMap);
    $row['running_services_json_truncated'] = json_encode($runningSvcs);
}
?>
<?= view('users/advanced/_card_table', [
    'title'    => 'App Security',
    'subtitle' => 'Device Admin Apps, App Permissions, and Running Services',
    'tableId'  => 'appSecurityTable',
    'columns'  => [
        ['field' => 'extracted_at',        'label' => 'Extracted',       'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'admin_apps_count',    'label' => 'Device Admin Apps', 'format' => 'text', 'icon' => 'fas fa-user-shield'],
        ['field' => 'perm_apps_count',     'label' => 'Apps w/ Permissions', 'format' => 'text', 'icon' => 'fas fa-lock'],
        ['field' => 'running_svcs_count',  'label' => 'Running Services',  'format' => 'text', 'icon' => 'fas fa-cogs'],
    ],
    'secondary' => [
        ['field' => 'device_admin_apps_json_truncated', 'label' => 'Device Admin Apps (First 20)', 'format' => 'json', 'jsonTitle' => 'Device Admin Apps', 'icon' => 'fas fa-user-shield'],
        ['field' => 'app_permissions_map_json_truncated', 'label' => 'Permissions Map (First 20)', 'format' => 'json', 'jsonTitle' => 'Permissions Map', 'icon' => 'fas fa-lock'],
        ['field' => 'running_services_json_truncated', 'label' => 'Running Services (First 20)', 'format' => 'json', 'jsonTitle' => 'Running Services', 'icon' => 'fas fa-cogs'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/app_security/delete'),
]) ?>
