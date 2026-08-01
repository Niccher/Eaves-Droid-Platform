<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CleanupOrphanedFiles extends BaseCommand
{
    protected $group       = 'Housekeeping';
    protected $name        = 'storage:pollution';
    protected $description = 'Delete orphaned uploaded files entries where physical file is missing.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $startTime = microtime(true);
        $command = 'storage:pollution';
        $output = '';

        $logId = $db->table('cron_execution_logs')->insert([
            'job_id' => 0,
            'command' => $command,
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'running',
        ]);
        $logId = $db->insertID();

        try {
            CLI::write(' Checking for orphaned uploaded file records...', 'yellow');
            $output .= 'Checking for orphaned uploaded file records...' . PHP_EOL;

            $rows = $db->table('uploaded_files')
                ->where('status !=', 'deleted')
                ->get()
                ->getResultArray();

            if (empty($rows)) {
                CLI::write(' No uploaded file records found.', 'green');
                $output .= 'No uploaded file records found.' . PHP_EOL;
            } else {
                $orphanedCount = 0;
                $totalReclaimed = 0;

                foreach ($rows as $row) {
                    $filePath = $row['file_path'] ?? $row['stored_path'] ?? null;

                    if ($filePath && !file_exists($filePath)) {
                        $fileSize = (int)($row['file_size'] ?? 0);

                        $db->table('uploaded_files')
                            ->where('id', (int)$row['id'])
                            ->update(['status' => 'deleted', 'deleted_at' => date('Y-m-d H:i:s')]);

                        $orphanedCount++;
                        $totalReclaimed += $fileSize;
                        $originalName = $row['original_name'] ?? 'unknown';
                        CLI::write(" Orphaned: #{$row['id']} ({$originalName}, " . $this->formatBytes($fileSize) . ')', 'red');
                        $output .= "Orphaned: #{$row['id']} ({$originalName}, " . $this->formatBytes($fileSize) . ')' . PHP_EOL;
                    }
                }

                CLI::write(" Done. Removed {$orphanedCount} orphaned record(s), reclaimed " . $this->formatBytes($totalReclaimed) . '.', 'green');
                $output .= "Done. Removed {$orphanedCount} orphaned record(s), reclaimed " . $this->formatBytes($totalReclaimed) . '.' . PHP_EOL;
            }

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