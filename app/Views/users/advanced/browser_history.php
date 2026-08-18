<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Browser History',
    'subtitle' => 'Browsing history, bookmarks, and search activity',
    'tableId'  => 'browserHistoryTable',
    'columns'  => [
        ['field' => 'title',            'label' => 'Title',       'format' => 'text', 'icon' => 'fas fa-heading'],
        ['field' => 'url',              'label' => 'URL',         'format' => 'text', 'truncate' => 60, 'icon' => 'fas fa-link'],
        ['field' => 'domain',           'label' => 'Domain',      'format' => 'badge', 'default' => 'info', 'icon' => 'fas fa-globe'],
        ['field' => 'browser_package',  'label' => 'Browser',     'format' => 'text', 'icon' => 'fas fa-chrome'],
        ['field' => 'visit_count',      'label' => 'Visits',      'format' => 'text', 'icon' => 'fas fa-eye'],
        ['field' => 'last_visit_time',  'label' => 'Last Visit',  'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'secondary' => [
        ['field' => 'is_bookmark',       'label' => 'Bookmark',     'format' => 'yesno', 'icon' => 'fas fa-bookmark'],
        ['field' => 'is_incognito',      'label' => 'Incognito',    'format' => 'yesno', 'icon' => 'fas fa-user-secret'],
        ['field' => 'typed_count',       'label' => 'Typed Count',  'format' => 'text', 'icon' => 'fas fa-keyboard'],
        ['field' => 'transition_type',   'label' => 'Transition',   'format' => 'text', 'icon' => 'fas fa-exchange-alt'],
        ['field' => 'referrer_url',      'label' => 'Referrer',     'format' => 'text', 'icon' => 'fas fa-share'],
        ['field' => 'visit_duration_ms', 'label' => 'Visit Duration','format' => 'ms', 'icon' => 'fas fa-stopwatch'],
        ['field' => 'search_terms',      'label' => 'Search Terms', 'format' => 'text', 'icon' => 'fas fa-search'],
        ['field' => 'scheme',            'label' => 'Scheme',       'format' => 'text', 'icon' => 'fas fa-code'],
        ['field' => 'extracted_at',      'label' => 'Extracted',    'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/browser_history/delete'),
]) ?>
