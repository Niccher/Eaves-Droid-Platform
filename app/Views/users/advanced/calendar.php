<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Calendar Events',
    'subtitle' => 'All calendar events and appointments found on device',
    'tableId'  => 'calendarTable',
    'columns'  => [
        ['field' => 'title',       'label' => 'Title',      'format' => 'text', 'icon' => 'fas fa-heading'],
        ['field' => 'start_time',  'label' => 'Start',      'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'end_time',    'label' => 'End',        'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'location',    'label' => 'Location',   'format' => 'maps', 'truncate' => 25, 'icon' => 'fas fa-map-marker-alt'],
        ['field' => 'all_day',     'label' => 'All Day',    'format' => 'yesno', 'icon' => 'fas fa-calendar-day'],
        ['field' => 'organizer',   'label' => 'Organizer',  'format' => 'text', 'icon' => 'fas fa-user'],
    ],
    'secondary' => [
        ['field' => 'description',    'label' => 'Description', 'format' => 'text', 'icon' => 'fas fa-align-left'],
        ['field' => 'calendar_name',  'label' => 'Calendar',    'format' => 'text', 'icon' => 'fas fa-calendar-alt'],
        ['field' => 'timezone',       'label' => 'Timezone',    'format' => 'text', 'icon' => 'fas fa-globe'],
        ['field' => 'event_status',   'label' => 'Status',      'format' => 'text', 'icon' => 'fas fa-info-circle'],
        ['field' => 'visibility',     'label' => 'Visibility',  'format' => 'text', 'icon' => 'fas fa-eye'],
        ['field' => 'rrule',          'label' => 'RRULE',       'format' => 'code', 'icon' => 'fas fa-code'],
        ['field' => 'attendees_json', 'label' => 'Attendees',   'format' => 'json', 'jsonTitle' => 'Event Attendees', 'icon' => 'fas fa-users'],
        ['field' => 'reminders_json', 'label' => 'Reminders',   'format' => 'json', 'jsonTitle' => 'Event Reminders', 'icon' => 'fas fa-bell'],
        ['field' => 'extracted_at',   'label' => 'Extracted',   'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/calendar/delete'),
]) ?>
