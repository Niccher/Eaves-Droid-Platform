<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?= view('users/advanced/_card_table', [
    'title'    => 'Keyguard Events',
    'subtitle' => 'Unlock/lock events, auth methods, and lock screen settings',
    'tableId'  => 'keyguardTable',
    'columns'  => [
        ['field' => 'timestamp',        'label' => 'Timestamp', 'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'event_type',       'label' => 'Event',     'format' => 'badge', 'map' => ['lock' => 'danger', 'unlock' => 'success'], 'default' => 'info', 'icon' => 'fas fa-lock'],
        ['field' => 'method',           'label' => 'Method',    'format' => 'badge', 'default' => 'info', 'icon' => 'fas fa-key'],
        ['field' => 'success',          'label' => 'Success',   'format' => 'yesno', 'icon' => 'fas fa-check'],
        ['field' => 'failed_attempts',  'label' => 'Failed Attempts', 'format' => 'text', 'icon' => 'fas fa-times-circle'],
        ['field' => 'is_secure',        'label' => 'Secure',    'format' => 'yesno', 'icon' => 'fas fa-shield-alt'],
    ],
    'secondary' => [
        ['field' => 'remaining_attempts',            'label' => 'Remaining Attempts', 'format' => 'text', 'icon' => 'fas fa-hashtag'],
        ['field' => 'lockout_until',                 'label' => 'Lockout Until',      'format' => 'timestamp', 'icon' => 'fas fa-clock'],
        ['field' => 'strong_auth_required_reason',   'label' => 'Strong Auth Reason', 'format' => 'text', 'icon' => 'fas fa-info-circle'],
        ['field' => 'biometric_error',               'label' => 'Biometric Error',    'format' => 'text', 'icon' => 'fas fa-exclamation-triangle'],
        ['field' => 'biometric_type',                'label' => 'Biometric Type',     'format' => 'text', 'icon' => 'fas fa-fingerprint'],
        ['field' => 'biometric_available',           'label' => 'Biometric Available', 'format' => 'yesno', 'icon' => 'fas fa-eye'],
        ['field' => 'notifications_on_lockscreen',   'label' => 'Notifs on Lockscreen', 'format' => 'text', 'icon' => 'fas fa-bell'],
        ['field' => 'sensitive_notifications_hidden','label' => 'Sensitive Notifs Hidden', 'format' => 'yesno', 'icon' => 'fas fa-eye-slash'],
        ['field' => 'storage_encryption_status',     'label' => 'Storage Encryption', 'format' => 'text', 'icon' => 'fas fa-lock'],
        ['field' => 'extracted_at',                  'label' => 'Extracted',          'format' => 'timestamp', 'icon' => 'fas fa-clock'],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'total'     => $total,
    'nav_urls'  => $nav_urls,
    'perPage'  => 25,
    'deleteUrl' => base_url('advanced/software/keyguard/delete'),
]) ?>
