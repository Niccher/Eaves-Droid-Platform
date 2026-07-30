<?php

namespace App\Controllers\admin;

use CodeIgniter\API\ResponseTrait;

class Settings extends BaseAdminController
{
    use ResponseTrait;
    public function index()
    {
        $db = $this->getDb();

        $saved = [];
        $rows = $db->table('settings')->where('class', 'app')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        return $this->renderView('admin/settings/general', [
            'pag' => 'admin-settings',
            'settings' => $saved,
        ]);
    }

    public function update()
    {
        $db = $this->getDb();
        $post = $this->request->getPost();

        $section = $post['section'] ?? 'app';
        unset($post['section'], $post[csrf_token()]);

$allowedMap = [
            'app' => ['app_name', 'app_description', 'maintenance_mode', 'maintenance_type', 'maintenance_start', 'maintenance_end', 'timezone', 'language'],
            'api' => ['rate_limit', 'allowed_origins', 'max_upload_size', 'token_expiry_days'],
            'security' => ['min_password_length', 'session_ttl', 'max_login_attempts', 'lockout_duration'],
            'notification' => ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name'],
            'email_triggers' => [
                'on_new_user', 'on_password_reset', 'on_password_changed', 'on_email_changed',
                'on_user_suspended', 'on_user_reactivated', 'on_maintenance_notice',
                'on_user_deleted', 'on_maintenance_toggle', 'on_backup_success', 'on_backup_failed',
                'on_anomaly_high', 'on_cron_failed', 'on_settings_changed',
                'on_storage_warning', 'on_storage_critical', 'on_queue_stalled', 'on_ssl_expiring',
            ],
            'backup' => ['schedule_cron', 'retention_days', 'storage_path', 'compress', 'notify_on_success', 'notify_on_failure'],
            'storage' => ['threshold_warning', 'threshold_critical', 'check_interval_minutes', 'notify_admins'],
            'ml' => ['ml_enabled', 'ml_anomaly_enabled', 'ml_schedule_interval', 'ml_phpml_kmeans_k', 'ml_phpml_dbscan_epsilon', 'ml_phpml_dbscan_minpoints', 'ml_phpml_isolationforest_trees', 'ml_phpml_isolationforest_samples', 'ml_python_enabled', 'ml_python_host', 'ml_python_port', 'ml_python_endpoint', 'ml_python_url', 'ml_python_autoencoder_latent', 'ml_python_autoencoder_epochs', 'ml_python_autoencoder_threshold', 'ml_python_lstm_sequence', 'ml_python_lstm_units', 'ml_python_oneclass_nu', 'ml_python_oneclass_gamma', 'ml_python_iforest_trees', 'ml_python_iforest_samples', 'ml_python_iforest_contamination'],
        ];

        $allowed = $allowedMap[$section] ?? array_keys($post);
        $updated = 0;

        // Track critical setting changes for email notification
        $criticalSettings = ['maintenance_mode', 'maintenance_type', 'maintenance_start', 'maintenance_end', 'smtp_host', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name', 'ml_enabled', 'ml_anomaly_enabled'];
        $changes = [];

        foreach ($post as $key => $value) {
            if (!in_array($key, $allowed)) continue;
            $existing = $db->table('settings')
                ->where('class', $section)
                ->where('key', $key)
                ->get()
                ->getRow();
            
            $oldValue = $existing ? $existing->value : null;
            
            if ($existing) {
                $db->table('settings')
                    ->where('id', $existing->id)
                    ->update(['value' => $value, 'updated_at' => date('Y-m-d H:i:s')]);
            } else {
                $db->table('settings')->insert([
                    'class' => $section,
                    'key' => $key,
                    'value' => $value,
                    'type' => 'string',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
            $updated++;

            // Track changes for critical settings
            if (in_array($key, $criticalSettings) && $oldValue !== $value) {
                $changes[] = [
                    'key' => $key,
                    'old' => $oldValue,
                    'new' => $value,
                ];
            }
        }

        // Handle unchecked checkboxes (missing from POST = disabled)
        $checkboxSections = [
            'email_triggers' => $allowed,
            'storage' => ['notify_admins'],
            'backup' => ['compress', 'notify_on_success', 'notify_on_failure'],
            'ml' => ['ml_enabled', 'ml_anomaly_enabled', 'ml_python_enabled'],
        ];
        if (isset($checkboxSections[$section])) {
            foreach ($checkboxSections[$section] as $key) {
                if (!isset($post[$key])) {
                    $existing = $db->table('settings')
                        ->where('class', $section)
                        ->where('key', $key)
                        ->get()
                        ->getRow();
                    $oldValue = $existing ? $existing->value : null;
                    if ($existing) {
                        $db->table('settings')
                            ->where('id', $existing->id)
                            ->update(['value' => '0', 'updated_at' => date('Y-m-d H:i:s')]);
                    } else {
                        $db->table('settings')->insert([
                            'class' => $section,
                            'key' => $key,
                            'value' => '0',
                            'type' => 'string',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                    $updated++;
                    if (in_array($key, $criticalSettings) && $oldValue !== '0') {
                        $changes[] = [
                            'key' => $key,
                            'old' => $oldValue,
                            'new' => '0',
                        ];
                    }
                }
            }
        }

        // Send maintenance toggled email if maintenance_mode changed
        if (isset($post['maintenance_mode']) && in_array('maintenance_mode', $criticalSettings)) {
            $this->sendMaintenanceToggledEmail($post['maintenance_mode'], $changes, $post);
        }

        // Send settings changed email for critical settings
        if (!empty($changes)) {
            $this->sendSettingsChangedEmail($changes);
        }

        // Redirect to the appropriate settings page
        $redirectMap = [
            'email_triggers' => 'admin/settings/email-triggers',
            'app' => 'admin/settings',
            'api' => 'admin/settings/api',
            'security' => 'admin/settings/security',
            'notification' => 'admin/settings/notifications',
            'storage' => 'admin/settings/storage',
            'backup' => 'admin/settings/backup',
            'ml' => 'admin/settings/ml',
        ];

        $redirectUrl = $redirectMap[$section] ?? 'admin/settings';
        return redirect()->to($redirectUrl)->with('message', "Updated {$updated} setting(s) for section '{$section}'.");
    }

    public function database()
    {
        $db = $this->getDb();

        $tableStats = [];
        $tables = $db->listTables();
        foreach ($tables as $table) {
            $status = $db->query("SHOW TABLE STATUS LIKE '{$table}'")->getRow();
            $tableStats[] = [
                'name' => $table,
                'engine' => $status->Engine ?? '-',
                'rows' => $status->Rows ?? 0,
                'data_size' => $status->Data_length ?? 0,
                'index_size' => $status->Index_length ?? 0,
                'size' => ($status->Data_length ?? 0) + ($status->Index_length ?? 0),
            ];
        }

        $logPath = WRITEPATH . 'logs';
        $logSize = 0;
        $logCount = 0;
        if (is_dir($logPath)) {
            foreach (glob($logPath . '/*') as $f) {
                if (is_file($f)) { $logSize += filesize($f); $logCount++; }
            }
        }

        $cachePath = WRITEPATH . 'cache';
        $cacheSize = 0;
        $cacheCount = 0;
        if (is_dir($cachePath)) {
            foreach (glob($cachePath . '/*') as $f) {
                if (is_file($f)) { $cacheSize += filesize($f); $cacheCount++; }
            }
        }

        return $this->renderView('admin/settings/database', [
            'pag' => 'admin-db-info',
            'db' => $db,
            'table_stats' => $tableStats,
            'total_tables' => count($tableStats),
            'total_db_size' => array_sum(array_column($tableStats, 'size')),
            'log_count' => $logCount,
            'log_size' => $logSize,
            'cache_count' => $cacheCount,
            'cache_size' => $cacheSize,
        ]);
    }

    public function api_settings()
    {
        $db = $this->getDb();

        $saved = [];
        $rows = $db->table('settings')->where('class', 'api')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        return $this->renderView('admin/settings/api', [
            'pag' => 'admin-settings-api',
            'settings' => $saved,
        ]);
    }

    public function security_settings()
    {
        $db = $this->getDb();

        $saved = [];
        $rows = $db->table('settings')->where('class', 'security')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        return $this->renderView('admin/settings/security', [
            'pag' => 'admin-settings-security',
            'settings' => $saved,
        ]);
    }

    public function notification_settings()
    {
        $db = $this->getDb();

        $saved = [];
        $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        return $this->renderView('admin/settings/notifications', [
            'pag' => 'admin-settings-notifications',
            'settings' => $saved,
        ]);
    }

    public function testEmail()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        // Use POSTed values first, fall back to saved settings
        $smtpHost = $this->request->getPost('smtp_host');
        $smtpPort = $this->request->getPost('smtp_port');
        $smtpUser = $this->request->getPost('smtp_user');
        $smtpPass = $this->request->getPost('smtp_pass');
        $smtpFromEmail = $this->request->getPost('smtp_from_email');
        $smtpFromName = $this->request->getPost('smtp_from_name');
        $recipient = $this->request->getPost('email');

        // Fall back to DB if not provided via POST
        if (!$smtpHost || !$smtpUser) {
            $db = $this->getDb();
            $settings = [];
            $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
            foreach ($rows as $r) {
                $settings[$r['key']] = $r['value'];
            }
            $smtpHost = $smtpHost ?: ($settings['smtp_host'] ?? '');
            $smtpPort = $smtpPort ?: ($settings['smtp_port'] ?? '587');
            $smtpUser = $smtpUser ?: ($settings['smtp_user'] ?? '');
            $smtpPass = $smtpPass ?: ($settings['smtp_pass'] ?? '');
            $smtpFromEmail = $smtpFromEmail ?: ($settings['smtp_from_email'] ?? '');
            $smtpFromName = $smtpFromName ?: ($settings['smtp_from_name'] ?? 'Eaves Droid');
        }

        if (empty($smtpHost)) {
            return $this->respond(["success" => false, "message" => "No SMTP host configured. Configure SMTP settings first."]);
        }

        // Use email helper with DB SMTP config
        helper("email");
        $sent = send_templated_email(
            $recipient,
            "Eaves Droid - SMTP Configuration Test",
            "email/admin/smtp_test",
            [
                "smtpHost" => $smtpHost,
                "smtpPort" => $smtpPort,
                "smtpUser" => $smtpUser,
                "smtpFromEmail" => $smtpFromEmail,
                "smtpFromName" => $smtpFromName,
            ]
        );

        if ($sent) {
            return $this->respond(["success" => true, "message" => "Test email sent successfully to " . $recipient]);
        } else {
            return $this->respond(["success" => false, "message" => "Failed to send test email. Check logs."]);
        }
    }

    public function maintenance()
    {
        $db = $this->getDb();

        $saved = [];
        $rows = $db->table('settings')->where('class', 'app')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        $tableStats = [];
        $tables = $db->listTables();
        foreach ($tables as $table) {
            $status = $db->query("SHOW TABLE STATUS LIKE '{$table}'")->getRow();
            $tableStats[] = [
                'name' => $table,
                'engine' => $status->Engine ?? '-',
                'rows' => $status->Rows ?? 0,
                'size' => ($status->Data_length ?? 0) + ($status->Index_length ?? 0),
            ];
        }

        $logPath = WRITEPATH . 'logs';
        $logSize = 0;
        $logCount = 0;
        if (is_dir($logPath)) {
            foreach (glob($logPath . '/*') as $f) {
                if (is_file($f)) { $logSize += filesize($f); $logCount++; }
            }
        }

        $cachePath = WRITEPATH . 'cache';
        $cacheSize = 0;
        $cacheCount = 0;
        if (is_dir($cachePath)) {
            foreach (glob($cachePath . '/*') as $f) {
                if (is_file($f)) { $cacheSize += filesize($f); $cacheCount++; }
            }
        }

        return $this->renderView('admin/settings/maintenance', [
            'pag' => 'admin-maintenance',
            'settings' => $saved,
            'table_stats' => $tableStats,
            'total_tables' => count($tableStats),
            'total_db_size' => array_sum(array_column($tableStats, 'size')),
            'log_count' => $logCount,
            'log_size' => $logSize,
            'cache_count' => $cacheCount,
            'cache_size' => $cacheSize,
        ]);
    }

    public function run_maintenance()
    {
        $db = $this->getDb();
        $action = $this->request->getPost('action');
        $messages = [];

        if ($action === 'optimize' || $action === 'all') {
            $tables = $db->listTables();
            foreach ($tables as $table) {
                $db->query("OPTIMIZE TABLE `{$table}`");
            }
            $messages[] = 'All tables optimized.';
        }

        if ($action === 'logs' || $action === 'all') {
            $logPath = WRITEPATH . 'logs';
            $deleted = 0;
            foreach (glob($logPath . '/log-*.log') as $f) {
                if (unlink($f)) $deleted++;
            }
            $messages[] = "{$deleted} log files deleted.";
        }

        if ($action === 'cache' || $action === 'all') {
            $cachePath = WRITEPATH . 'cache';
            $deleted = 0;
            foreach (glob($cachePath . '/*') as $f) {
                if (is_file($f) && unlink($f)) $deleted++;
            }
            $messages[] = "{$deleted} cache files cleared.";
        }

        $message = implode(' ', $messages);

        $this->logAdminAction('admin_maintenance_run', 'medium', true, [
            'new_values' => json_encode(['action' => $action, 'message' => $message]),
        ]);

        return redirect()->to('admin/settings/maintenance')->with('message', $message ?: 'No action performed.');
    }

    public function backup()
    {
        $db = $this->getDb();

        $backups = [];
        $backupPath = WRITEPATH . 'backups';
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        $files = glob($backupPath . '/db-backup-*.sql.gz');
        rsort($files);
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'modified' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        $saved = [];
        $rows = $db->table('settings')->where('class', 'backup')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        return $this->renderView('admin/settings/backup', [
            'pag' => 'admin-backup',
            'backups' => $backups,
            'settings' => $saved,
        ]);
    }

    public function create_backup()
    {
        $db = $this->getDb();
        $backupPath = WRITEPATH . 'backups';
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filename = 'db-backup-' . date('Y-m-d_H-i-s') . '.sql.gz';
        $filepath = $backupPath . '/' . $filename;

        $tables = $db->listTables();
        $sql = '';
        foreach ($tables as $table) {
            $create = $db->query("SHOW CREATE TABLE `{$table}`")->getRow()->{'Create Table'};
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n{$create};\n\n";
            $rows = $db->table($table)->get()->getResultArray();
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n";
                $vals = [];
                foreach ($rows as $row) {
                    $escaped = array_map(function ($v) use ($db) {
                        return $v === null ? 'NULL' : "'" . $db->escapeString($v) . "'";
                    }, array_values($row));
                    $vals[] = '(' . implode(', ', $escaped) . ')';
                }
                $sql .= implode(",\n", $vals) . ";\n\n";
            }
        }

        $gz = gzencode($sql, 9);
        file_put_contents($filepath, $gz);

        $this->logAdminAction('admin_backup_create', 'low', true, [
            'resource_id' => $filename,
            'new_values' => json_encode(['filename' => $filename, 'size' => filesize($filepath)]),
        ]);

        // Send notification email if enabled
        $this->sendBackupNotificationEmail('success', $filename, filesize($filepath));

        return redirect()->to('admin/settings/backup')
            ->with('message', "Backup created: {$filename}");
    }

    private function sendBackupNotificationEmail(string $status, string $filename, int $size): void
    {
        try {
            helper('email');
            $db = $this->getDb();

            // Get notification settings
            $settings = [];
            $rows = $db->table('settings')->where('class', 'backup')->get()->getResultArray();
            foreach ($rows as $r) {
                $settings[$r['key']] = $r['value'];
            }

            $notifyOnSuccess = !empty($settings['notify_on_success']);
            $notifyOnFailure = !empty($settings['notify_on_failure']);

            if (($status === 'success' && !$notifyOnSuccess) || ($status === 'failed' && !$notifyOnFailure)) {
                return;
            }

            // Get admins
            $admins = $db->table('auth_identities')
                ->select('auth_identities.secret AS email, users.username')
                ->join('users', 'auth_identities.user_id = users.id')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                ->where('auth_identities.type', 'email_password')
                ->where('auth_groups_users.group', 'admin')
                ->where('users.active', 1)
                ->get()
                ->getResultArray();

            $subject = $status === 'success' 
                ? 'Eaves Droid — Backup Completed' 
                : 'Eaves Droid — Backup Failed';

            $template = $status === 'success' 
                ? 'email/admin/backup_completed' 
                : 'email/admin/backup_failed';

            $sizeFormatted = $size > 1048576 
                ? number_format($size / 1048576, 2) . ' MB' 
                : number_format($size / 1024, 1) . ' KB';

            foreach ($admins as $admin) {
                send_templated_email(
                    $admin['email'],
                    $subject,
                    $template,
                    [
                        'filename' => $filename,
                        'size_formatted' => $sizeFormatted,
                        'completed_at' => date('Y-m-d H:i:s'),
                        'download_url' => base_url('admin/settings/backup/download/' . $filename),
                        'username' => $admin['username'],
                        'securityAction' => 'Backup ' . ucfirst($status),
                        'securityDescription' => 'Database backup ' . $status . '.',
                        'securityStatus' => $status === 'success' ? 'success' : 'failed',
                        'securityInitiatedBy' => auth()->user()->username ?? 'System',
                        'securityBrowser' => 'System (Scheduled)',
                        'securityBrowserIp' => 'N/A',
                        'securityExecutedAt' => date('Y-m-d H:i:s'),
                    ]
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'Backup notification email failed: ' . $e->getMessage());
        }
    }

    public function restore_backup()
    {
        $filename = $this->request->getPost('filename');
        if (!$filename) {
            return redirect()->to('admin/settings/backup')->with('error', 'No filename provided.');
        }

        $filepath = WRITEPATH . 'backups/' . basename($filename);
        if (!file_exists($filepath)) {
            return redirect()->to('admin/settings/backup')->with('error', 'Backup file not found.');
        }

        $gz = file_get_contents($filepath);
        $sql = gzdecode($gz);
        if (!$sql) {
            return redirect()->to('admin/settings/backup')->with('error', 'Invalid backup file.');
        }

        $db = $this->getDb();
        $statements = explode(";\n", $sql);
        $count = 0;
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (!empty($stmt)) {
                try {
                    $db->query($stmt);
                    $count++;
                } catch (\Exception $e) {
                    log_message('error', 'Restore error: ' . $e->getMessage());
                }
            }
        }

        $this->logAdminAction('admin_backup_restore', 'critical', true, [
            'resource_id' => $filename,
            'new_values' => json_encode(['filename' => $filename, 'statements_executed' => $count]),
        ]);

        return redirect()->to('admin/settings/backup')
            ->with('message', "Restore completed. {$count} statements executed.");
    }

    public function download_backup(string $filename)
    {
        $filepath = WRITEPATH . 'backups/' . basename($filename);
        if (!file_exists($filepath)) {
            return redirect()->to('admin/settings/backup')->with('error', 'File not found.');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/gzip')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string) filesize($filepath))
            ->setBody(file_get_contents($filepath));
    }

    public function delete_backup(string $filename)
    {
        $filepath = WRITEPATH . 'backups/' . basename($filename);
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $this->logAdminAction('admin_backup_delete', 'high', true, [
            'resource_id' => $filename,
        ]);

        return redirect()->to('admin/settings/backup')->with('message', 'Backup deleted.');
    }

    // =================================================================
    // NEW: Storage Monitor Settings
    // =================================================================
    public function storage()
    {
        $db = $this->getDb();

        $saved = [];
        $rows = $db->table('settings')->where('class', 'storage')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        $disks = $this->getDiskUsage();

        return $this->renderView('admin/settings/storage', [
            'pag' => 'admin-settings-storage',
            'settings' => $saved,
            'disks' => $disks,
        ]);
    }

    public function storage_check_now()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        $disks = $this->getDiskUsage();
        $settings = $this->getStorageSettings();

        $results = [];
        foreach ($disks as $disk) {
            $pct = $disk['usage_percent'];
            $results[] = [
                'mount' => $disk['mount'],
                'usage_percent' => $pct,
                'status' => $pct >= ($settings['threshold_critical'] ?? 90) ? 'critical' : ($pct >= ($settings['threshold_warning'] ?? 80) ? 'warning' : 'ok'),
            ];

            if (($settings['notify_admins'] ?? false) && $pct >= ($settings['threshold_warning'] ?? 80)) {
                helper('email');
                $this->sendStorageAlertEmail($disk, $settings);
            }
        }

        return $this->respond([
            'success' => true,
            'message' => 'Storage check completed',
            'disks' => $results,
            'usage_percent' => $disks[0]['usage_percent'] ?? 0,
        ]);
    }

    private function getDiskUsage(): array
    {
        $disks = [];
        $root = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $root - $free;
        $pct = $root > 0 ? round(($used / $root) * 100, 1) : 0;

        $disks[] = [
            'mount' => '/',
            'total' => $root,
            'used' => $used,
            'free' => $free,
            'usage_percent' => $pct,
            'total_formatted' => $this->formatBytes($root),
            'used_formatted' => $this->formatBytes($used),
            'free_formatted' => $this->formatBytes($free),
        ];

        return $disks;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        if ($bytes == 0) return '0 B';
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1024 ** $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function getStorageSettings(): array
    {
        $db = $this->getDb();
        $rows = $db->table('settings')->where('class', 'storage')->get()->getResultArray();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return $settings;
    }

    private function sendStorageAlertEmail(array $disk, array $settings): void
    {
        $db = $this->getDb();
        $admins = $db->table('auth_identities')
            ->select('auth_identities.secret AS email, users.username')
            ->join('users', 'auth_identities.user_id = users.id')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_identities.type', 'email_password')
            ->where('auth_groups_users.group', 'admin')
            ->where('users.active', 1)
            ->get()
            ->getResultArray();

        $pct = $disk['usage_percent'];
        $level = $pct >= ($settings['threshold_critical'] ?? 90) ? 'CRITICAL' : 'WARNING';

        foreach ($admins as $admin) {
            helper('email');
            send_templated_email(
                $admin['email'],
                "Eaves Droid — Storage {$level}: {$pct}% Used",
                'email/admin/storage_threshold',
                [
                    'disk' => $disk,
                    'level' => $level,
                    'threshold_warning' => $settings['threshold_warning'] ?? 80,
                    'threshold_critical' => $settings['threshold_critical'] ?? 90,
                ]
            );
        }
    }

    // =================================================================
    // NEW: Cron Jobs Management
    // =================================================================
    public function cron()
    {
        $db = $this->getDb();

        $cronJobs = $db->table('cron_jobs')
            ->orderBy('enabled', 'DESC')
            ->orderBy('command', 'ASC')
            ->get()
            ->getResultArray();

        $executionLogs = $db->table('cron_execution_logs')
            ->orderBy('started_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        return $this->renderView('admin/settings/cron', [
            'pag' => 'admin-settings-cron',
            'cronJobs' => $cronJobs,
            'executionLogs' => $executionLogs,
        ]);
    }

    public function cron_save()
    {
        if (!$this->request->isAJAX() && !$this->request->is('post')) {
            return $this->fail('Invalid request');
        }

        $db = $this->getDb();
        $id = $this->request->getPost('id');
        $command = $this->request->getPost('command');
        $customCommand = $this->request->getPost('custom_command');
        $schedule = $this->request->getPost('schedule');
        $description = $this->request->getPost('description');
        $enabled = $this->request->getPost('enabled') ? 1 : 0;
        $arguments = $this->request->getPost('arguments');
        if (empty($arguments) || $arguments === 'null') $arguments = '{}';
        if (json_decode($arguments) === null) $arguments = '{}';

        if ($command === 'custom') {
            $command = $customCommand;
        }

        if (!$command || !$schedule) {
            if ($this->request->isAJAX()) {
                return $this->respond(['success' => false, 'message' => 'Command and schedule are required']);
            }
            return redirect()->to('admin/settings/cron')->with('error', 'Command and schedule are required');
        }

        $data = [
            'command' => $command,
            'custom_command' => $command === $customCommand ? $customCommand : null,
            'schedule' => $schedule,
            'description' => $description,
            'enabled' => $enabled,
            'arguments' => $arguments,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $db->table('cron_jobs')->where('id', $id)->update($data);
            $this->logAdminAction('cron_updated', 'low', true, [
                'new_values' => json_encode(['id' => $id, 'command' => $command, 'schedule' => $schedule]),
            ]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->table('cron_jobs')->insert($data);
            $id = $db->insertID();
            $this->logAdminAction('cron_created', 'low', true, [
                'new_values' => json_encode(['id' => $id, 'command' => $command, 'schedule' => $schedule]),
            ]);

            helper('email');
            send_admin_notification(
                'Eaves Droid — New Cron Job Created',
                'email/admin/settings_changed',
                [
                    'changes' => [
                        ['key' => 'Command', 'old' => '', 'new' => $command],
                        ['key' => 'Schedule', 'old' => '', 'new' => $schedule],
                        ['key' => 'Description', 'old' => '', 'new' => $description ?? ''],
                        ['key' => 'Enabled', 'old' => '', 'new' => $enabled ? 'Yes' : 'No'],
                    ],
                    'securityAction' => 'Cron Job Created',
                    'securityDescription' => 'A new scheduled task has been created.',
                    'securityStatus' => 'info',
                    'securityInitiatedBy' => auth()->user()->username ?? 'System',
                ]
            );
        }

        if ($this->request->isAJAX()) {
            return $this->respond(['success' => true, 'id' => $id]);
        }

        return redirect()->to('admin/settings/cron')->with('success', $id ? 'Cron job updated successfully.' : 'Cron job created successfully.');
    }

    public function cron_toggle()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        $db = $this->getDb();
        $id = $this->request->getPost('id');
        $enabled = $this->request->getPost('enabled');

        $db->table('cron_jobs')->where('id', $id)->update(['enabled' => $enabled, 'updated_at' => date('Y-m-d H:i:s')]);

        $this->logAdminAction('cron_' . ($enabled ? 'enabled' : 'disabled'), 'low', true, [
            'new_values' => json_encode(['id' => $id]),
        ]);

        return $this->respond(['success' => true]);
    }

    public function cron_run(int $id)
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        $db = $this->getDb();
        $job = $db->table('cron_jobs')->where('id', $id)->get()->getRowArray();

        if (!$job) {
            return $this->fail('Cron job not found');
        }

        $command = $job['command'];
        $arguments = json_decode($job['arguments'] ?? '{}', true) ?? [];

        // Build command with arguments
        $args = '';
        foreach ($arguments as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $args .= " --{$key}={$value}";
        }

        $fullCommand = "spark {$command}{$args}";
        $logId = $this->logCronStart($job['id'], $fullCommand);

        $output = [];
        $returnVar = 0;
        exec($fullCommand, $output, $returnVar);
        $outputStr = implode("\n", $output);

        $this->logCronFinish($logId, $returnVar === 0 ? 'success' : 'failed', $outputStr);

        return $this->respond([
            'success' => $returnVar === 0,
            'message' => $returnVar === 0 ? 'Job executed successfully' : 'Job failed',
            'output' => $outputStr,
        ]);
    }

    public function cron_delete(int $id)
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        $db = $this->getDb();
        $db->table('cron_jobs')->where('id', $id)->delete();

        $this->logAdminAction('cron_deleted', 'low', true, [
            'new_values' => json_encode(['id' => $id]),
        ]);

        return $this->respond(['success' => true]);
    }

    public function cron_get(int $id)
    {
        $db = $this->getDb();
        $job = $db->table('cron_jobs')->where('id', $id)->get()->getRowArray();

        if (!$job) {
            return $this->fail('Not found');
        }

        return $this->respond(['success' => true, 'job' => $job]);
    }

    private function logCronStart(int $jobId, string $command): int
    {
        $db = $this->getDb();
        $db->table('cron_execution_logs')->insert([
            'job_id' => $jobId,
            'command' => $command,
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'running',
        ]);
        return $db->insertID();
    }

    private function logCronFinish(int $logId, string $status, string $output): void
    {
        $db = $this->getDb();
        $started = $db->table('cron_execution_logs')->where('id', $logId)->get()->getRowArray();
        $duration = 0;
        if ($started) {
            $startTs = strtotime($started['started_at']);
            $duration = (time() - $startTs) * 1000;
        }

        $db->table('cron_execution_logs')->where('id', $logId)->update([
            'finished_at' => date('Y-m-d H:i:s'),
            'status' => $status,
            'output' => $output,
            'duration_ms' => $duration,
        ]);

        // Update last_run on the cron job itself
        if ($started && isset($started['job_id'])) {
            $db->table('cron_jobs')->where('id', $started['job_id'])->update([
                'last_run' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // =================================================================
    // NEW: Email Triggers Data for View
    // =================================================================
    private function getEmailTriggerGroups(): array
    {
        return [
            'userTriggers' => [
                'on_new_user' => ['label' => 'New User Registered', 'description' => 'Send welcome email when a new user signs up.'],
                'on_password_reset' => ['label' => 'Password Reset Request', 'description' => 'Send password reset link when user requests it.'],
                'on_password_changed' => ['label' => 'Password Changed', 'description' => 'Notify user when their password is successfully changed.'],
                'on_email_changed' => ['label' => 'Email Changed', 'description' => 'Notify user when their email address is updated.'],
            ],
            'userSystemTriggers' => [
                'on_user_suspended' => ['label' => 'Account Suspended', 'description' => 'Notify user when their account is suspended by admin.'],
                'on_user_reactivated' => ['label' => 'Account Reactivated', 'description' => 'Notify user when their suspended account is reactivated.'],
                'on_maintenance_notice' => ['label' => 'Maintenance Notice', 'description' => 'Notify all users when maintenance mode is enabled.'],
            ],
            'adminTriggers' => [
                'on_user_deleted' => ['label' => 'User Deleted', 'description' => 'Notify admins when a user account is deleted.'],
                'on_maintenance_toggle' => ['label' => 'Maintenance Mode Changed', 'description' => 'Notify admins when maintenance mode is enabled/disabled.'],
                'on_backup_success' => ['label' => 'Backup Completed', 'description' => 'Notify admins when a scheduled backup finishes successfully.'],
                'on_backup_failed' => ['label' => 'Backup Failed', 'description' => 'Alert admins when a scheduled backup fails.'],
                'on_anomaly_high' => ['label' => 'High Severity Anomaly', 'description' => 'Alert admins when ML detects high-severity anomaly.'],
                'on_cron_failed' => ['label' => 'Cron Job Failed', 'description' => 'Alert admins when a scheduled cron job fails.'],
                'on_settings_changed' => ['label' => 'Critical Settings Changed', 'description' => 'Alert admins when critical system settings are modified.'],
            ],
            'systemTriggers' => [
                'on_storage_warning' => ['label' => 'Storage Warning', 'description' => 'Alert when disk usage exceeds warning threshold.'],
                'on_storage_critical' => ['label' => 'Storage Critical', 'description' => 'Alert when disk usage exceeds critical threshold.'],
                'on_queue_stalled' => ['label' => 'Upload Queue Stalled', 'description' => 'Alert when upload queue has too many pending items.'],
                'on_ssl_expiring' => ['label' => 'SSL Certificate Expiring', 'description' => 'Alert when SSL certificate expires within 30 days.'],
            ],
        ];
    }

    // =================================================================
    // EMAIL TRIGGERS SETTINGS
    // =================================================================
    public function email_triggers()
    {
        $db = $this->getDb();

        $triggerGroups = $this->getEmailTriggerGroups();

        // Get all trigger keys from all groups
        $allTriggerKeys = array_merge(
            array_keys($triggerGroups['userTriggers']),
            array_keys($triggerGroups['userSystemTriggers']),
            array_keys($triggerGroups['adminTriggers']),
            array_keys($triggerGroups['systemTriggers'])
        );

        $saved = [];
        $rows = $db->table('settings')->where('class', 'email_triggers')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        // Default all triggers to enabled (1) if not explicitly set
        foreach ($allTriggerKeys as $key) {
            if (!isset($saved[$key])) {
                $saved[$key] = '1';
            }
        }

        return $this->renderView('admin/settings/email_triggers', [
            'pag' => 'admin-settings-email-triggers',
            'settings' => $saved,
            'userTriggers' => $triggerGroups['userTriggers'],
            'userSystemTriggers' => $triggerGroups['userSystemTriggers'],
            'adminTriggers' => $triggerGroups['adminTriggers'],
            'systemTriggers' => $triggerGroups['systemTriggers'],
        ]);
    }

    // =================================================================
    // EMAIL HELPER METHODS
    // =================================================================

private function sendMaintenanceToggledEmail(string $mode, array $changes, array $post = []): void
    {
        try {
            helper('email');
            $db = $this->getDb();
            
            // Send to admins
            $admins = $db->table('auth_identities')
                ->select('auth_identities.secret AS email, users.username')
                ->join('users', 'auth_identities.user_id = users.id')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                ->where('auth_identities.type', 'email_password')
                ->where('auth_groups_users.group', 'admin')
                ->where('users.active', 1)
                ->get()
                ->getResultArray();

            $enabled = $mode === '1' || $mode === 'true';
            $subject = "Eaves Droid — Maintenance Mode " . ($enabled ? 'Enabled' : 'Disabled');
            
            foreach ($admins as $admin) {
                send_templated_email(
                    $admin['email'],
                    $subject,
                    'email/admin/maintenance_toggled',
                    [
                        'mode' => $enabled ? 'enabled' : 'disabled',
                        'initiated_by' => auth()->user()->username ?? 'Admin',
                        'window_start' => $post['maintenance_start'] ?? null,
                        'window_end' => $post['maintenance_end'] ?? null,
                        'message' => $post['maintenance_type'] ?? null,
                        'username' => $admin['username'],
                        'securityAction' => 'Maintenance Mode ' . ($enabled ? 'Enabled' : 'Disabled'),
                        'securityDescription' => 'Maintenance mode has been ' . ($enabled ? 'enabled' : 'disabled') . ' on the platform.',
                        'securityStatus' => 'success',
                        'securityInitiatedBy' => auth()->user()->username ?? 'Admin',
                        'securityBrowser' => $this->request->getUserAgent()->getAgentString() ?? 'Admin Panel',
                        'securityBrowserIp' => $this->request->getIPAddress(),
                        'securityExecutedAt' => date('Y-m-d H:i:s'),
                    ]
                );
            }

            // Send to all users if maintenance is being enabled
            if ($enabled) {
                $users = $db->table('auth_identities')
                    ->select('auth_identities.secret AS email, users.id, users.username')
                    ->join('users', 'auth_identities.user_id = users.id')
                    ->where('auth_identities.type', 'email_password')
                    ->where('users.active', 1)
                    ->get()
                    ->getResultArray();

                foreach ($users as $user) {
                    if (empty($user['email'])) continue;
                    
                    // Check user's email notification preference
                    $profile = $db->table('user_profiles')
                        ->select('email_notifications')
                        ->where('user_id', $user['id'])
                        ->get()
                        ->getRowArray();
                    if ($profile && isset($profile['email_notifications']) && !$profile['email_notifications']) continue;

                    send_templated_email(
                        $user['email'],
                        'Eaves Droid — Maintenance Mode Enabled',
                        'email/admin/maintenance_toggled',
                        [
                            'mode' => 'enabled',
                            'initiated_by' => auth()->user()->username ?? 'System',
                            'window_start' => $post['maintenance_start'] ?? null,
                            'window_end' => $post['maintenance_end'] ?? null,
                            'message' => $post['maintenance_type'] ?? 'Scheduled maintenance is in progress.',
                            'username' => $user['username'],
                            'securityAction' => 'Maintenance Mode Enabled',
                            'securityDescription' => 'The platform is entering maintenance mode. Services may be temporarily unavailable.',
                            'securityStatus' => 'warning',
                            'securityInitiatedBy' => auth()->user()->username ?? 'System',
                            'securityBrowser' => 'System (Scheduled)',
                            'securityBrowserIp' => 'N/A',
                            'securityExecutedAt' => date('Y-m-d H:i:s'),
                        ]
                    );
                }
            }

            // Send to all users if maintenance is being disabled (ended)
            if (!$enabled) {
                $users = $db->table('auth_identities')
                    ->select('auth_identities.secret AS email, users.id, users.username')
                    ->join('users', 'auth_identities.user_id = users.id')
                    ->where('auth_identities.type', 'email_password')
                    ->where('users.active', 1)
                    ->get()
                    ->getResultArray();

                foreach ($users as $user) {
                    if (empty($user['email'])) continue;
                    
                    // Check user's email notification preference
                    $profile = $db->table('user_profiles')
                        ->select('email_notifications')
                        ->where('user_id', $user['id'])
                        ->get()
                        ->getRowArray();
                    if ($profile && isset($profile['email_notifications']) && !$profile['email_notifications']) continue;

                    send_templated_email(
                        $user['email'],
                        'Eaves Droid — Maintenance Mode Ended',
                        'email/admin/maintenance_toggled',
                        [
                            'mode' => 'disabled',
                            'initiated_by' => auth()->user()->username ?? 'System',
                            'window_start' => $post['maintenance_start'] ?? null,
                            'window_end' => $post['maintenance_end'] ?? null,
                            'message' => 'Maintenance has been completed. All services are now operational.',
                            'username' => $user['username'],
                            'securityAction' => 'Maintenance Mode Disabled',
                            'securityDescription' => 'Maintenance mode has ended. All services are now fully operational.',
                            'securityStatus' => 'success',
                            'securityInitiatedBy' => auth()->user()->username ?? 'System',
                            'securityBrowser' => 'System (Scheduled)',
                            'securityBrowserIp' => 'N/A',
                            'securityExecutedAt' => date('Y-m-d H:i:s'),
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Maintenance toggled email failed: ' . $e->getMessage());
        }
    }

    private function sendSettingsChangedEmail(array $changes): void
    {
        try {
            helper('email');
            $db = $this->getDb();
            
            $admins = $db->table('auth_identities')
                ->select('auth_identities.secret AS email, users.username')
                ->join('users', 'auth_identities.user_id = users.id')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                ->where('auth_identities.type', 'email_password')
                ->where('auth_groups_users.group', 'admin')
                ->where('users.active', 1)
                ->get()
                ->getResultArray();

            foreach ($admins as $admin) {
                send_templated_email(
                    $admin['email'],
                    'Eaves Droid — Critical Settings Changed',
                    'email/admin/settings_changed',
                    [
                        'changes' => $changes,
                        'changed_by' => auth()->user()->username ?? 'Admin',
                        'changed_at' => date('Y-m-d H:i:s'),
                        'securityAction' => 'Critical Settings Changed',
                        'securityDescription' => 'One or more critical system settings have been modified.',
                        'securityStatus' => 'warning',
                        'securityInitiatedBy' => auth()->user()->username ?? 'Admin',
                        'securityBrowser' => $this->request->getUserAgent()->getAgentString() ?? 'Admin Panel',
                        'securityBrowserIp' => $this->request->getIPAddress(),
                        'securityExecutedAt' => date('Y-m-d H:i:s'),
                    ]
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'Settings changed email failed: ' . $e->getMessage());
        }
    }
}
