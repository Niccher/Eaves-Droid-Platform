<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackupDatabase extends BaseCommand
{
    protected $group       = 'Backup';
    protected $name        = 'backup:create';
    protected $description = 'Create a full database backup with gzip compression.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $startTime = microtime(true);
        $command = 'backup:create';
        $output = '';

        $logId = $db->table('cron_execution_logs')->insert([
            'job_id' => 0,
            'command' => $command,
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'running',
        ]);
        $logId = $db->insertID();

        try {
            CLI::write(' Starting database backup...', 'yellow');
            $output .= 'Starting database backup...' . PHP_EOL;

            $settings = $this->getBackupSettings();
            $backupPath = $settings['storage_path'] ?? WRITEPATH . 'backups';

            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $dbConn = \Config\Database::connect();
            $tables = $dbConn->listTables();

            $filename = 'db-backup-' . date('Y-m-d_H-i-s') . '.sql';
            if ($settings['compress']) {
                $filename .= '.gz';
            }
            $filepath = $backupPath . '/' . $filename;

            $sql = '';
            foreach ($tables as $table) {
                $create = $dbConn->query("SHOW CREATE TABLE `{$table}`")->getRow()->{'Create Table'};
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n{$create};\n\n";

                $rows = $dbConn->table($table)->get()->getResultArray();
                if (!empty($rows)) {
                    $columns = array_keys($rows[0]);
                    $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n";
                    $vals = [];
                    foreach ($rows as $row) {
                        $escaped = array_map(function ($v) use ($dbConn) {
                            return $v === null ? 'NULL' : "'" . $dbConn->escapeString($v) . "'";
                        }, array_values($row));
                        $vals[] = '(' . implode(', ', $escaped) . ')';
                    }
                    $sql .= implode(",\n", $vals) . ";\n\n";
                }
            }

            if ($settings['compress']) {
                $gz = gzencode($sql, 9);
                file_put_contents($filepath, $gz);
            } else {
                file_put_contents($filepath, $sql);
            }

            $size = filesize($filepath);
            CLI::write(" Backup created: {$filename} (" . $this->formatBytes($size) . ")", 'green');
            $output .= "Backup created: {$filename} (" . $this->formatBytes($size) . ")" . PHP_EOL;

            // Cleanup old backups
            $retention = $settings['retention_days'] ?? 30;
            if ($retention > 0) {
                $this->cleanupOldBackups($backupPath, $retention);
            }

            // Send notification email
            if ($settings['notify_on_success']) {
                $this->sendNotificationEmail('success', $filename, $size, $settings);
            }

            CLI::write(' Backup completed successfully.', 'green');
            $output .= 'Backup completed successfully.' . PHP_EOL;

            $duration = (int)((microtime(true) - $startTime) * 1000);
            $db->table('cron_execution_logs')->where('id', $logId)->update([
                'finished_at' => date('Y-m-d H:i:s'),
                'status' => 'success',
                'output' => trim($output),
                'duration_ms' => $duration,
            ]);
        } catch (\Throwable $e) {
            $duration = (int)((microtime(true) - $startTime) * 1000);
            $db->table('cron_execution_logs')->where('id', $logId)->update([
                'finished_at' => date('Y-m-d H:i:s'),
                'status' => 'failed',
                'output' => trim($output) . PHP_EOL . $e->getMessage(),
                'duration_ms' => $duration,
            ]);
        }
    }

    private function getBackupSettings(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('settings')->where('class', 'backup')->get()->getResultArray();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return $settings;
    }

    private function cleanupOldBackups(string $path, int $retentionDays): void
    {
        $files = glob($path . '/db-backup-*.sql*');
        $deleted = 0;

        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ((time() - $mtime) > ($retentionDays * 86400)) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            CLI::write(" Cleaned up {$deleted} old backup(s) older than {$retentionDays} days.", 'yellow');
        }
    }

    private function sendNotificationEmail(string $status, string $filename, int $size, array $settings): void
    {
        $db = \Config\Database::connect();
        $admins = $db->table('auth_identities')
            ->select('auth_identities.secret AS email')
            ->join('users', 'auth_identities.user_id = users.id')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_identities.type', 'email_password')
            ->where('auth_groups_users.group', 'admin')
            ->where('users.active', 1)
            ->get()
            ->getResultArray();

        $subject = "Eaves Droid — Backup " . ($status === 'success' ? 'Completed' : 'Failed');
        $template = $status === 'success' ? 'email/admin/backup_completed' : 'email/admin/backup_failed';

        foreach ($admins as $admin) {
            helper('email');
            send_templated_email(
                $admin['email'],
                $subject,
                $template,
                [
                    'filename' => $filename,
                    'size' => $this->formatBytes($size),
                    'size_raw' => $size,
                    'completed_at' => date('Y-m-d H:i:s'),
                ]
            );
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        if ($bytes == 0) return '0 B';
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1024 ** $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}