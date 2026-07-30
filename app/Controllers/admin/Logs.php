<?php

namespace App\Controllers\admin;

use App\Models\Mod_Anomalies;

class Logs extends BaseAdminController
{
    public function index(string $tab = 'all')
    {
        $db = $this->getDb();
        $data = ['pag' => 'admin-logs', 'active_tab' => $tab];

        // All actions
        $data['logs'] = $db->table('tbl_user_actions')
            ->select("'action' as source, tbl_user_actions.id, tbl_user_actions.user_id, users.username,
                      tbl_user_actions.action_type, tbl_user_actions.action_category as category,
                      tbl_user_actions.action_severity as severity, tbl_user_actions.ip_address,
                      tbl_user_actions.user_agent, tbl_user_actions.created_at as date,
                      tbl_user_actions.success, tbl_user_actions.error_message,
                      tbl_user_actions.new_values, tbl_user_actions.request_url,
                      NULL as identifier, NULL as response_code, NULL as execution_time_ms")
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        // Access logs
        $data['accessLogs'] = $db->table('tbl_user_actions')
            ->select("'action' as source, tbl_user_actions.id, tbl_user_actions.user_id, users.username,
                      tbl_user_actions.action_type, tbl_user_actions.ip_address,
                      tbl_user_actions.user_agent as user_agent, tbl_user_actions.created_at as date,
                      tbl_user_actions.success, NULL as identifier")
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->where('tbl_user_actions.action_category', 'authentication')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        // Error logs
        $data['errorLogs'] = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->where('tbl_user_actions.success', 0)
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        // PHP error logs
        $errorFiles = [];
        $logPath = WRITEPATH . 'logs';
        if (is_dir($logPath)) {
            $files = glob($logPath . '/log-*.log');
            rsort($files);
            foreach (array_slice($files, 0, 50) as $file) {
                $basename = basename($file);
                $size = filesize($file);
                $lines = $size > 0 ? count(file($file)) : 0;
                $errorFiles[] = [
                    'filename' => $basename,
                    'size' => $size,
                    'lines' => $lines,
                    'modified' => date('Y-m-d H:i:s', filemtime($file)),
                ];
            }
        }
        $data['phpErrorFiles'] = $errorFiles;

        // API logs
        $data['apiLogs'] = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->whereIn('tbl_user_actions.action_category', ['file', 'system', 'security'])
            ->where('tbl_user_actions.request_url IS NOT NULL')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        // FCM logs
        $data['fcmLogs'] = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->like('tbl_user_actions.action_type', 'remote_cmd_')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        // Maintenance logs
        $data['maintenanceLogs'] = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->where('tbl_user_actions.action_type', 'maintenance_blocked')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        // Engine logs
        $model = new Mod_Anomalies();
        $data['engineHistory'] = $model->getJobHistory(100);

        // Tab counts
        $data['count_all'] = $db->table('tbl_user_actions')->countAllResults();
        $data['count_access'] = $db->table('tbl_user_actions')->where('action_category', 'authentication')->countAllResults();
        $data['count_errors'] = $db->table('tbl_user_actions')->where('success', 0)->countAllResults();
        $data['count_php_errors'] = count($errorFiles);
        $data['count_api'] = $db->table('tbl_user_actions')->whereIn('action_category', ['file', 'system', 'security'])->where('request_url IS NOT NULL')->countAllResults();
        $data['count_fcm'] = $db->table('tbl_user_actions')->like('action_type', 'remote_cmd_')->countAllResults();
        $data['count_maintenance'] = $db->table('tbl_user_actions')->where('action_type', 'maintenance_blocked')->countAllResults();
        $data['count_engine'] = count($data['engineHistory']);

        return $this->renderView('admin/logs/index', $data);
    }

    public function access_logs()
    {
        return $this->index('access');
    }

    public function error_logs()
    {
        return $this->index('errors');
    }

    public function php_error_logs()
    {
        return $this->index('php-errors');
    }

    public function api_logs()
    {
        return $this->index('api');
    }

    public function fcm_logs()
    {
        return $this->index('fcm');
    }

    public function maintenance_logs()
    {
        return $this->index('maintenance');
    }

    public function engine_logs()
    {
        return $this->index('engine');
    }

    public function engineAlgoDetails(int $jobId)
    {
        $model = new Mod_Anomalies();
        $logs = $model->getAlgorithmLogs($jobId);
        return $this->response->setJSON($logs);
    }

    public function view_error_file(string $filename)
    {
        $db = $this->getDb();
        $logPath = WRITEPATH . 'logs/' . $filename;
        if (!file_exists($logPath) || !preg_match('/^log-\d{4}-\d{2}-\d{2}\.log$/', $filename)) {
            return redirect()->to('admin/logs')->with('error', 'Log file not found.');
        }

        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -500);

        return $this->renderView('admin/logs/index', [
            'pag' => 'admin-logs',
            'active_tab' => 'php-errors',
            'logs' => [],
            'accessLogs' => [],
            'accessTotal' => 0,
            'errorLogs' => [],
            'phpErrorFiles' => [],
            'apiLogs' => [],
            'fcmLogs' => [],
            'maintenanceLogs' => [],
            'engineHistory' => [],
            'errorFilename' => $filename,
            'errorLines' => $lines,
            'count_all' => $db->table('tbl_user_actions')->countAllResults(),
            'count_access' => 0,
            'count_errors' => 0,
            'count_php_errors' => 0,
            'count_api' => 0,
            'count_fcm' => 0,
            'count_maintenance' => 0,
            'count_engine' => 0,
        ]);
    }

    public function clear_logs()
    {
        $this->logAdminAction('admin_logs_clear', 'critical', true);

        $db = $this->getDb();
        $db->table('tbl_user_actions')->truncate();
        return redirect()->to('admin/logs')->with('message', 'All system logs cleared.');
    }

    public function clear_error_files()
    {
        $logPath = WRITEPATH . 'logs';
        $files = glob($logPath . '/log-*.log');
        $deleted = 0;
        foreach ($files as $file) {
            if (unlink($file)) $deleted++;
        }
        $this->logAdminAction('admin_error_files_clear', 'medium', true, [
            'new_values' => json_encode(['deleted_count' => $deleted]),
        ]);

        return redirect()->to('admin/logs/php-errors')->with('message', "Deleted {$deleted} log files.");
    }

    public function export_logs()
    {
        $db = $this->getDb();
        $logs = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $this->logAdminAction('admin_logs_export', 'low', true, [
            'new_values' => json_encode(['record_count' => count($logs)]),
        ]);

        $csv = "ID,User,Action,Category,Severity,IP,Success,Error,Timestamp\n";
        foreach ($logs as $log) {
            $csv .= '"' . $log['id'] . '",'
                . '"' . addslashes($log['username'] ?? 'Unknown') . '",'
                . '"' . addslashes($log['action_type'] ?? '') . '",'
                . '"' . addslashes($log['action_category'] ?? '') . '",'
                . '"' . addslashes($log['action_severity'] ?? '') . '",'
                . '"' . ($log['ip_address'] ?? '') . '",'
                . ($log['success'] ?? '') . ','
                . '"' . addslashes($log['error_message'] ?? '') . '",'
                . '"' . $log['created_at'] . '"'
                . "\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="system_logs_' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }
}
