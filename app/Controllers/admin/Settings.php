<?php

namespace App\Controllers\admin;

class Settings extends BaseAdminController
{
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
            'ml' => ['ml_enabled', 'ml_anomaly_enabled', 'ml_schedule_interval', 'ml_phpml_kmeans_k', 'ml_phpml_dbscan_epsilon', 'ml_phpml_dbscan_minpoints', 'ml_phpml_isolationforest_trees', 'ml_phpml_isolationforest_samples', 'ml_python_enabled', 'ml_python_host', 'ml_python_port', 'ml_python_endpoint', 'ml_python_autoencoder_latent', 'ml_python_autoencoder_epochs', 'ml_python_autoencoder_threshold', 'ml_python_lstm_sequence', 'ml_python_lstm_units', 'ml_python_oneclass_nu', 'ml_python_oneclass_gamma', 'ml_python_iforest_trees', 'ml_python_iforest_samples', 'ml_python_iforest_contamination'],
        ];

        $allowed = $allowedMap[$section] ?? array_keys($post);
        $updated = 0;

        foreach ($post as $key => $value) {
            if (!in_array($key, $allowed)) continue;
            $existing = $db->table('settings')
                ->where('class', $section)
                ->where('key', $key)
                ->get()
                ->getRow();
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
        }

        $redirects = [
            'app' => 'admin/settings',
            'api' => 'admin/settings/api',
            'security' => 'admin/settings/security',
            'notification' => 'admin/settings/notifications',
            'ml' => 'admin/ml',
        ];

        $this->logAdminAction('admin_settings_update', 'medium', true, [
            'new_values' => json_encode(['section' => $section, 'updated_keys' => array_keys($post)]),
        ]);

        return redirect()->to($redirects[$section] ?? 'admin/settings')
            ->with('message', "{$updated} settings saved.");
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

        return $this->renderView('admin/settings/backup', [
            'pag' => 'admin-backup',
            'backups' => $backups,
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

        return redirect()->to('admin/settings/backup')
            ->with('message', "Backup created: {$filename}");
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
}
