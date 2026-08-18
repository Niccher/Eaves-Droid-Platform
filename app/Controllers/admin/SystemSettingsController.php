<?php

namespace App\Controllers\admin;

use CodeIgniter\API\ResponseTrait;

class SystemSettingsController extends BaseAdminController
{
    use ResponseTrait;

    private function requirePermission(string $permission)
    {
        if (!auth()->user()->can($permission)) {
            $this->logAdminAction('permission_denied', 'medium', false, [
                'new_values' => json_encode(['uri' => current_url()]),
            ]);
            return redirect()->to('admin/dashboard')->with('error', 'You do not have permission to access this page.');
        }
        return null;
    }

    public function index()
    {
        $db = $this->getDb();

        $this->logAdminAction('settings_view', 'low', true, [
            'section' => 'general',
        ]);

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

        $maintenanceKeys = ['maintenance_mode', 'maintenance_type', 'maintenance_start', 'maintenance_end'];
        if ($section === 'app' && array_intersect(array_keys($post), $maintenanceKeys)) {
            if ($denied = $this->requirePermission('system.maintenance')) {
                return $denied;
            }
        }

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
                'on_brute_force',
            ],
            'backup' => ['schedule_cron', 'retention_days', 'storage_path', 'compress', 'notify_on_success', 'notify_on_failure'],
            'storage' => ['threshold_warning', 'threshold_critical', 'notify_admins', 'logs_retention_days', 'queue_cleanup_timeout_hours', 'queue_cleanup_max_attempts', 'ml_cleanup_timeout_hours', 'cleanup_backups_days', 'cleanup_cache_days', 'cleanup_exports_days'],
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
            $mailSettingsController = new MailSettingsController();
            $mailSettingsController->initController($this->request, $this->response, $this->logger);
            $mailSettingsController->sendMaintenanceToggledEmail($post['maintenance_mode'], $changes, $post);
        }

        // Send settings changed email for critical settings
        if (!empty($changes)) {
            $mailSettingsController = new MailSettingsController();
            $mailSettingsController->initController($this->request, $this->response, $this->logger);
            $mailSettingsController->sendSettingsChangedEmail($changes);
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
        $returnUrl = $this->request->getPost('return_url');
        if ($returnUrl && filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            $redirectUrl = parse_url($returnUrl, PHP_URL_PATH);
        }
        cache()->delete('maintenance_settings');
        return redirect()->to($redirectUrl)->with('message', "Updated {$updated} setting(s) for section '{$section}'.");
    }

    public function database()
    {
        $db = $this->getDb();

        $this->logAdminAction('settings_view', 'low', true, [
            'section' => 'database',
        ]);

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

    public function maintenance()
    {
        if ($denied = $this->requirePermission('system.maintenance')) {
            return $denied;
        }

        $db = $this->getDb();

        $this->logAdminAction('settings_view', 'low', true, [
            'section' => 'maintenance',
        ]);

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
        if ($denied = $this->requirePermission('system.maintenance')) {
            return $denied;
        }

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

        $this->logAdminAction('settings_view', 'low', true, [
            'section' => 'backup',
        ]);

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

    public function storage()
    {
        $db = $this->getDb();

        $this->logAdminAction('settings_view', 'low', true, [
            'section' => 'storage',
        ]);

        $saved = [];
        $rows = $db->table('settings')->where('class', 'storage')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        $disks = $this->getDiskUsage();

        $allFilesSize = 0;
        $fileRows = $db->table('tbl_uploaded_files')->select('SUM(file_size_bytes) AS total_size')->get()->getRowArray();
        $allFilesSize = (int)($fileRows['total_size'] ?? 0);

        $dbSize = 0;
        foreach ($db->listTables() as $table) {
            $status = $db->query("SHOW TABLE STATUS LIKE '{$table}'")->getRow();
            $dbSize += ($status->Data_length ?? 0) + ($status->Index_length ?? 0);
        }

        return $this->renderView('admin/settings/storage', [
            'pag' => 'admin-settings-storage',
            'settings' => $saved,
            'disks' => $disks,
            'all_files_size' => $allFilesSize,
            'all_files_size_formatted' => $this->formatBytes($allFilesSize),
            'db_size' => $dbSize,
            'db_size_formatted' => $this->formatBytes($dbSize),
            'total_used_size' => $allFilesSize + $dbSize,
            'total_used_size_formatted' => $this->formatBytes($allFilesSize + $dbSize),
            'total_disk_size' => array_sum(array_column($disks, 'total')),
            'total_disk_size_formatted' => $this->formatBytes(array_sum(array_column($disks, 'total'))),
        ]);
    }

    public function storage_cleanup()
    {
        $db = $this->getDb();

        $saved = [];
        $rows = $db->table('settings')->where('class', 'storage')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        $disks = $this->getDiskUsage();

        $allFilesSize = 0;
        $fileRows = $db->table('tbl_uploaded_files')->select('SUM(file_size_bytes) AS total_size')->get()->getRowArray();
        $allFilesSize = (int)($fileRows['total_size'] ?? 0);

        $dbSize = 0;
        foreach ($db->listTables() as $table) {
            $status = $db->query("SHOW TABLE STATUS LIKE '{$table}'")->getRow();
            $dbSize += ($status->Data_length ?? 0) + ($status->Index_length ?? 0);
        }

        return $this->renderView('admin/settings/storage_cleanup', [
            'pag' => 'admin-settings-storage-cleanup',
            'settings' => $saved,
            'disks' => $disks,
            'all_files_size' => $allFilesSize,
            'all_files_size_formatted' => $this->formatBytes($allFilesSize),
            'db_size' => $dbSize,
            'db_size_formatted' => $this->formatBytes($dbSize),
            'total_used_size' => $allFilesSize + $dbSize,
            'total_used_size_formatted' => $this->formatBytes($allFilesSize + $dbSize),
            'total_disk_size' => array_sum(array_column($disks, 'total')),
            'total_disk_size_formatted' => $this->formatBytes(array_sum(array_column($disks, 'total'))),
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

    public function cron()
    {
        $db = $this->getDb();

        $this->logAdminAction('settings_view', 'low', true, [
            'section' => 'cron',
        ]);

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

        $sparkPath = defined('ROOTPATH') ? ROOTPATH . 'spark' : FCPATH . '../spark';
        $fullCommand = "php {$sparkPath} {$command}{$args}";
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

    public function retention()
    {
        if ($denied = $this->requirePermission('data.retention')) {
            return $denied;
        }

        $db = $this->getDb();

        $this->logAdminAction('settings_view', 'low', true, [
            'section' => 'retention',
        ]);

        $saved = (new \App\Services\RetentionService())->getSettings();

        $categories = \App\Services\RetentionService::CATEGORIES_MAP;

        // Row counts are expensive (~18 COUNT(*) scans); cache briefly and
        // refresh after a purge. Retention days/enabled stay live from settings.
        $counts = cache('retention_stats_counts');
        if (!is_array($counts)) {
            $counts = [];
            foreach ($categories as $label => $table) {
                $counts[$label] = $db->table($table)->countAllResults();
            }
            cache()->save('retention_stats_counts', $counts, 300);
        }

        $stats = [];
        foreach ($categories as $label => $table) {
            $stats[$label] = [
                'total' => $counts[$label] ?? 0,
                'retention_days' => (int) ($saved["retention_{$label}_days"] ?? 365),
                'enabled' => (bool) ($saved["retention_{$label}_enabled"] ?? false),
            ];
        }

        return $this->renderView('admin/settings/retention', [
            'pag' => 'admin-retention',
            'settings' => $saved,
            'stats' => $stats,
            'can_reset' => auth()->user()->can('system.reset'),
        ]);
    }

    public function run_purge()
    {
        if ($denied = $this->requirePermission('data.retention')) {
            return $denied;
        }

        if (!$this->request->is('post')) {
            return redirect()->to('admin/settings/retention')->with('error', 'Invalid request method.');
        }

        $this->logAdminAction('data_purge_run', 'critical', true, [
            'new_values' => json_encode($this->request->getPost()),
        ]);

        $categories = $this->request->getPost('categories') ?? [];

        if (empty($categories)) {
            return redirect()->back()->with('error', 'Please select at least one category to purge.');
        }

        $results = (new \App\Services\RetentionService())->purge($categories);

        $totalDeleted = 0;
        foreach ($results as $r) {
            $totalDeleted += (int) ($r['deleted'] ?? 0);
        }

        cache()->delete('retention_stats_counts');

        $this->logAdminAction('data_purge_run', 'critical', true, [
            'new_values' => json_encode([
                'total_deleted' => $totalDeleted,
                'results' => $results,
            ]),
        ]);

        return redirect()->to('admin/settings/retention')->with('message', "Purge completed. Total records deleted: {$totalDeleted}.");
    }

    public function save_retention()
    {
        if ($denied = $this->requirePermission('data.retention')) {
            return $denied;
        }

        if (!$this->request->is('post')) {
            return redirect()->to('admin/settings/retention')->with('error', 'Invalid request method.');
        }

        $post = $this->request->getPost();
        unset($post[csrf_token()]);

        $updated = (new \App\Services\RetentionService())->saveSettings($post);

        $this->logAdminAction('retention_settings_save', 'medium', true, [
            'new_values' => json_encode(['updated' => $updated]),
        ]);

        cache()->delete('retention_stats_counts');

        return redirect()->to('admin/settings/retention')->with('message', "Retention configuration saved ({$updated} setting(s)).");
    }

    public function factory_reset()
    {
        if ($denied = $this->requirePermission('system.reset')) {
            return $denied;
        }

        if (!auth()->user()->inGroup('superadmin')) {
            $this->logAdminAction('factory_reset_denied', 'critical', false, [
                'new_values' => json_encode(['reason' => 'not_superadmin']),
            ]);
            return redirect()->to('admin/settings/retention')->with('error', 'Only super administrators can perform a factory reset.');
        }

        if (!$this->request->is('post')) {
            return redirect()->to('admin/settings/retention')->with('error', 'Invalid request method.');
        }

        $confirm = strtoupper(trim((string) $this->request->getPost('confirm')));
        if ($confirm !== 'RESET') {
            return redirect()->to('admin/settings/retention')->with('error', 'Factory reset aborted: confirmation phrase did not match.');
        }

        $this->logAdminAction('factory_reset_run', 'critical', true, [
            'new_values' => json_encode(['confirm' => $confirm]),
        ]);

        $stats = (new \App\Services\SystemResetService())->reset();

        cache()->clean();

        return redirect()->to('admin/settings/retention')->with('message', "Factory reset complete. Wiped {$stats['tables_wiped']} data table(s) and deleted {$stats['files_deleted']} file(s). All user accounts were retained but all of their data (device data, uploads, reports, backups, billing) was permanently removed.");
    }
}
