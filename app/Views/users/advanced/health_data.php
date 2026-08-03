<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Health Data',
    'subtitle' => 'Steps, heart rate, sleep, workouts, and health records',
    'tableId'  => 'healthDataTable',
    'columns'  => [
        ['field' => 'data_type', 'label' => 'Type',    'format' => 'badge', 'default' => 'info', 'icon' => 'fas fa-heartbeat'],
        ['field' => 'value',     'label' => 'Value',   'format' => 'steps', 'icon' => 'fas fa-hashtag'],
        ['field' => 'unit',      'label' => 'Unit',    'format' => 'code', 'icon' => 'fas fa-ruler'],
        ['field' => 'end_time',  'label' => 'End Time','format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'secondary' => [
        ['field' => 'start_time',       'label' => 'Start Time', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'step_count',       'label' => 'Steps',      'format' => 'text', 'icon' => 'fas fa-walking'],
        ['field' => 'distance_meters',  'label' => 'Distance',   'format' => 'text', 'icon' => 'fas fa-route'],
        ['field' => 'calories_kcal',    'label' => 'Calories',   'format' => 'text', 'icon' => 'fas fa-fire'],
        ['field' => 'heart_rate_bpm',   'label' => 'Heart Rate', 'format' => 'text', 'icon' => 'fas fa-heart'],
        ['field' => 'data_source',      'label' => 'Source',     'format' => 'text', 'icon' => 'fas fa-database'],
        ['field' => 'data_source_name', 'label' => 'Source Name','format' => 'text', 'icon' => 'fas fa-tag'],
        ['field' => 'session_name',     'label' => 'Session',    'format' => 'text', 'icon' => 'fas fa-running'],
        ['field' => 'workout_type',     'label' => 'Workout',    'format' => 'text', 'icon' => 'fas fa-dumbbell'],
        ['field' => 'sleep_stage',      'label' => 'Sleep Stage','format' => 'text', 'icon' => 'fas fa-bed'],
        ['field' => 'extracted_at',     'label' => 'Extracted',  'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/health_data/delete'),
]) ?>
