<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
// Extract app names from JSON for display
foreach ($rows as &$row) {
    $fields = ['default_browser_json', 'default_dialer_json', 'default_sms_json', 
               'default_launcher_json', 'default_email_json', 'default_maps_json',
               'default_music_json', 'default_gallery_json'];
    foreach ($fields as $field) {
        $data = json_decode($row[$field] ?? '{}', true);
        $key = str_replace('_json', '', $field);
        $row[$key] = $data['app_name'] ?? $data['package_name'] ?? '—';
    }
}
?>
<?= view('users/advanced/_card_table', [
    'title'    => 'Default Apps',
    'subtitle' => 'Default browser, dialer, SMS, launcher, and other intent handlers',
    'tableId'  => 'defaultAppsTable',
    'columns'  => [
        ['field' => 'extracted_at', 'label' => 'Extracted', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'default_browser', 'label' => 'Browser', 'format' => 'text', 'icon' => 'fas fa-globe'],
        ['field' => 'default_dialer', 'label' => 'Dialer', 'format' => 'text', 'icon' => 'fas fa-phone'],
        ['field' => 'default_sms', 'label' => 'SMS', 'format' => 'text', 'icon' => 'fas fa-sms'],
        ['field' => 'default_launcher', 'label' => 'Launcher', 'format' => 'text', 'icon' => 'fas fa-rocket'],
        ['field' => 'default_email', 'label' => 'Email', 'format' => 'text', 'icon' => 'fas fa-envelope'],
        ['field' => 'default_maps', 'label' => 'Maps', 'format' => 'text', 'icon' => 'fas fa-map-marked-alt'],
        ['field' => 'default_music', 'label' => 'Music', 'format' => 'text', 'icon' => 'fas fa-music'],
        ['field' => 'default_gallery', 'label' => 'Gallery', 'format' => 'text', 'icon' => 'fas fa-images'],
    ],
    'secondary' => [
        ['field' => 'default_browser_json',  'label' => 'Browser',    'format' => 'json', 'jsonTitle' => 'Browser', 'icon' => 'fas fa-globe'],
        ['field' => 'default_dialer_json',   'label' => 'Dialer',     'format' => 'json', 'jsonTitle' => 'Dialer', 'icon' => 'fas fa-phone'],
        ['field' => 'default_sms_json',      'label' => 'SMS',        'format' => 'json', 'jsonTitle' => 'SMS', 'icon' => 'fas fa-sms'],
        ['field' => 'default_launcher_json', 'label' => 'Launcher',   'format' => 'json', 'jsonTitle' => 'Launcher', 'icon' => 'fas fa-rocket'],
        ['field' => 'default_email_json',    'label' => 'Email',      'format' => 'json', 'jsonTitle' => 'Email', 'icon' => 'fas fa-envelope'],
        ['field' => 'default_maps_json',     'label' => 'Maps',       'format' => 'json', 'jsonTitle' => 'Maps', 'icon' => 'fas fa-map-marked-alt'],
        ['field' => 'default_music_json',    'label' => 'Music',      'format' => 'json', 'jsonTitle' => 'Music', 'icon' => 'fas fa-music'],
        ['field' => 'default_gallery_json',  'label' => 'Gallery',    'format' => 'json', 'jsonTitle' => 'Gallery', 'icon' => 'fas fa-images'],
        ['field' => 'default_sms_package',   'label' => 'SMS Package','format' => 'text', 'icon' => 'fas fa-code'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/default_apps/delete'),
]) ?>
