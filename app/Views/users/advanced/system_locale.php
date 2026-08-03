<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
// Extract locale and font details for display
foreach ($rows as &$row) {
    $locale = $row['locale_region'] ?? [];
    $fonts = $row['system_fonts'] ?? [];
    $row['font_count'] = is_array($fonts) ? count($fonts) : 0;
    $row['font_scale'] = $locale['font_scale'] ?? '—';
    $row['timezone'] = $locale['timezone'] ?? '—';
    $row['display_name'] = $locale['display_name'] ?? '—';
    $row['text_layout_direction'] = $locale['text_layout_direction'] ?? '—';
    $row['language'] = $locale['language'] ?? '—';
    $row['country'] = $locale['country'] ?? '—';
}
?>
<?= view('users/advanced/_card_table', [
    'title'    => 'System Locale',
    'subtitle' => 'Language, Region, Timezone, and Font Configuration',
    'tableId'  => 'systemLocaleTable',
    'columns'  => [
        ['field' => 'extracted_at',        'label' => 'Extracted',           'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'language',            'label' => 'Language',            'format' => 'text', 'icon' => 'fas fa-language'],
        ['field' => 'country',             'label' => 'Country',             'format' => 'text', 'icon' => 'fas fa-globe'],
        ['field' => 'timezone',            'label' => 'Timezone',            'format' => 'text', 'icon' => 'fas fa-clock'],
        ['field' => 'display_name',        'label' => 'Display Name',        'format' => 'text', 'icon' => 'fas fa-desktop'],
        ['field' => 'text_layout_direction','label' => 'Text Layout Dir.',   'format' => 'text', 'icon' => 'fas fa-arrows-alt-h'],
        ['field' => 'font_scale',          'label' => 'Font Scale',          'format' => 'text', 'icon' => 'fas fa-font'],
        ['field' => 'font_count',          'label' => 'Font Count',          'format' => 'text', 'icon' => 'fas fa-hashtag'],
    ],
    'secondary' => [
        ['field' => 'locale_region_json', 'label' => 'Locale Region', 'format' => 'json', 'jsonTitle' => 'Locale Region', 'icon' => 'fas fa-globe'],
        ['field' => 'system_fonts_json',  'label' => 'System Fonts',  'format' => 'json', 'jsonTitle' => 'System Fonts', 'icon' => 'fas fa-font'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/system_locale/delete'),
]) ?>
