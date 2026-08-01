<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CacheClear extends BaseCommand
{
    protected $group       = 'Housekeeping';
    protected $name        = 'cache:clear';
    protected $description = 'Clear stale cache files.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $startTime = microtime(true);
        $command = 'cache:clear';
        $output = '';

        $logId = $db->table('cron_execution_logs')->insert([
            'job_id' => 0,
            'command' => $command,
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'running',
        ]);
        $logId = $db->insertID();

        try {
            CLI::write(' Scanning cache directory...', 'yellow');
            $output .= 'Scanning cache directory...' . PHP_EOL;

            $cachePath = WRITEPATH . 'cache';
            if (!is_dir($cachePath)) {
                CLI::write(' Cache directory does not exist. Nothing to clean.', 'green');
                $output .= 'Cache directory does not exist. Nothing to clean.' . PHP_EOL;
                $duration = (int)((microtime(true) - $startTime) * 1000);
                $db->table('cron_execution_logs')->where('id', $logId)->update([
                    'finished_at' => date('Y-m-d H:i:s'),
                    'status' => 'success',
                    'output' => trim($output),
                    'duration_ms' => $duration,
                ]);
                return;
            }

            $files = glob($cachePath . '/*');
            $deletedCount = 0;
            $freedBytes = 0;

            foreach ($files as $file) {
                if (is_file($file)) {
                    $size = filesize($file);
                    if (unlink($file)) {
                        $deletedCount++;
                        $freedBytes += $size;
                    }
                }
            }

            // Also clear compiled view files
            $viewsPath = WRITEPATH . 'cache/view';
            if (is_dir($viewsPath)) {
                $viewFiles = glob($viewsPath . '/*');
                foreach ($viewFiles as $file) {
                    if (is_file($file)) {
                        $size = filesize($file);
                        if (unlink($file)) {
                            $deletedCount++;
                            $freedBytes += $size;
                        }
                    }
                }
            }

            CLI::write(" Done. Deleted {$deletedCount} cache file(s), freed " . $this->formatBytes($freedBytes) . ".", 'green');
            $output .= "Done. Deleted {$deletedCount} cache file(s), freed " . $this->formatBytes($freedBytes) . "." . PHP_EOL;

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