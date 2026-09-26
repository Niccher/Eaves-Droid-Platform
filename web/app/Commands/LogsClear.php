<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class LogsClear extends BaseCommand
{
    protected $group       = 'Housekeeping';
    protected $name        = 'logs:clear';
    protected $description = 'Clear or rotate old log files.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $startTime = microtime(true);
        $command = 'logs:clear';
        $output = '';

        $logId = $db->table('cron_execution_logs')->insert([
            'job_id' => 0,
            'command' => $command,
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'running',
        ]);
        $logId = $db->insertID();

        try {
            $settings = $this->getLogSettings();
            $logPath = WRITEPATH . 'logs';
            $retentionDays = (int)($settings['logs_retention_days'] ?? 30);

            CLI::write(" Scanning log directory (retention: {$retentionDays} days)...", 'yellow');
            $output .= "Scanning log directory (retention: {$retentionDays} days)..." . PHP_EOL;

            if (!is_dir($logPath)) {
                CLI::write(' Log directory does not exist. Nothing to clean.', 'green');
                $output .= 'Log directory does not exist. Nothing to clean.' . PHP_EOL;
                $duration = (int)((microtime(true) - $startTime) * 1000);
                $db->table('cron_execution_logs')->where('id', $logId)->update([
                    'finished_at' => date('Y-m-d H:i:s'),
                    'status' => 'success',
                    'output' => trim($output),
                    'duration_ms' => $duration,
                ]);
                return;
            }

            $files = glob($logPath . '/*.log');
            $cutoff = time() - ($retentionDays * 86400);
            $deletedCount = 0;
            $freedBytes = 0;

            foreach ($files as $file) {
                $mtime = filemtime($file);
                if ($mtime < $cutoff) {
                    $size = filesize($file);
                    if (unlink($file)) {
                        $deletedCount++;
                        $freedBytes += $size;
                        CLI::write(" Deleted: " . basename($file) . " (" . $this->formatBytes($size) . ")", 'green');
                        $output .= "Deleted: " . basename($file) . " (" . $this->formatBytes($size) . ")" . PHP_EOL;
                    } else {
                        CLI::error(" Failed to delete: " . basename($file));
                        $output .= "Failed to delete: " . basename($file) . PHP_EOL;
                    }
                }
            }

            CLI::write(" Done. Deleted {$deletedCount} log file(s), freed " . $this->formatBytes($freedBytes) . ".", 'green');
            $output .= "Done. Deleted {$deletedCount} log file(s), freed " . $this->formatBytes($freedBytes) . "." . PHP_EOL;

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

    private function getLogSettings(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('settings')->where('class', 'housekeeping')->get()->getResultArray();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return $settings;
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