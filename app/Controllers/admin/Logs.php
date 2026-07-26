<?php

namespace App\Controllers\admin;

use App\Models\Mod_Anomalies;

class Logs extends BaseAdminController
{
    public function index()
    {
        $db = $this->getDb();

        $actions = $db->table('tbl_user_actions')
            ->select("'action' as source, tbl_user_actions.id, tbl_user_actions.user_id, users.username,
                      tbl_user_actions.action_type, tbl_user_actions.action_category as category,
                      tbl_user_actions.action_severity as severity, tbl_user_actions.ip_address,
                      tbl_user_actions.user_agent, tbl_user_actions.created_at as date,
                      tbl_user_actions.success, tbl_user_actions.error_message,
                      tbl_user_actions.new_values,
                      NULL as identifier, NULL as response_code, NULL as execution_time_ms")
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(15)
            ->get()
            ->getResultArray();

        return $this->renderView('admin/logs/index', [
            'pag' => 'admin-logs',
            'logs' => $actions,
        ]);
    }

    public function access_logs()
    {
        $db = $this->getDb();

        $actionTotal = $db->table('tbl_user_actions')
            ->where('action_category', 'authentication')
            ->countAllResults();

        $actions = $db->table('tbl_user_actions')
            ->select("'action' as source, tbl_user_actions.id, tbl_user_actions.user_id, users.username,
                      tbl_user_actions.action_type, tbl_user_actions.ip_address,
                      tbl_user_actions.user_agent as user_agent, tbl_user_actions.created_at as date,
                      tbl_user_actions.success, NULL as identifier")
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->where('tbl_user_actions.action_category', 'authentication')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(15)
            ->get()
            ->getResultArray();

        return $this->renderView('admin/logs/access_logs', [
            'pag' => 'admin-access-logs',
            'actions' => $actions,
            'actionTotal' => $actionTotal,
        ]);
    }

    public function error_logs()
    {
        $db = $this->getDb();

        $logs = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->where('tbl_user_actions.success', 0)
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(15)
            ->get()
            ->getResultArray();

        return $this->renderView('admin/logs/error_logs', [
            'pag' => 'admin-error-logs',
            'logs' => $logs,
            'total' => count($logs),
        ]);
    }

    public function php_error_logs()
    {
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

        return $this->renderView('admin/logs/php_error_logs', [
            'pag' => 'admin-php-error-logs',
            'error_files' => $errorFiles,
            'total' => count($errorFiles),
        ]);
    }

    public function maintenance_logs()
    {
        $db = $this->getDb();

        $total = $db->table('tbl_user_actions')
            ->where('action_type', 'maintenance_blocked')
            ->countAllResults();

        $logs = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->where('tbl_user_actions.action_type', 'maintenance_blocked')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        return $this->renderView('admin/logs/maintenance_logs', [
            'pag' => 'admin-maintenance-logs',
            'logs' => $logs,
            'total' => $total,
        ]);
    }

    public function api_logs()
    {
        $db = $this->getDb();

        $logs = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->whereIn('tbl_user_actions.action_category', ['file', 'system', 'security'])
            ->where('tbl_user_actions.request_url IS NOT NULL')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(15)
            ->get()
            ->getResultArray();

        return $this->renderView('admin/logs/api_logs', [
            'pag' => 'admin-api-logs',
            'logs' => $logs,
            'total' => count($logs),
        ]);
    }

    public function engine_logs()
    {
        $model = new Mod_Anomalies();
        $history = $model->getJobHistory(100);

        return $this->renderView('admin/logs/engine_logs', [
            'pag' => 'admin-engine-logs',
            'history' => $history,
        ]);
    }

    public function fcm_logs()
    {
        $db = $this->getDb();

        $total = $db->table('tbl_user_actions')
            ->like('action_type', 'remote_cmd_')
            ->countAllResults();

        $logs = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->like('tbl_user_actions.action_type', 'remote_cmd_')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        return $this->renderView('admin/logs/fcm_logs', [
            'pag' => 'admin-fcm-logs',
            'logs' => $logs,
            'total' => $total,
        ]);
    }

    public function view_error_file(string $filename)
    {
        $logPath = WRITEPATH . 'logs/' . $filename;
        if (!file_exists($logPath) || !preg_match('/^log-\d{4}-\d{2}-\d{2}\.log$/', $filename)) {
            return redirect()->to('admin/logs/errors')->with('error', 'Log file not found.');
        }

        $content = file_get_contents($logPath);
        $lines = explode("\n", $content);

        return $this->renderView('admin/logs/view_error_file', [
            'pag' => 'admin-error-logs',
            'filename' => $filename,
            'lines' => $lines,
            'total_lines' => count($lines),
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
