<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\RetentionService;

class RetentionPurge extends BaseCommand
{
    protected $group       = 'Housekeeping';
    protected $name        = 'retention:purge';
    protected $description = 'Purge data older than configured retention periods for enabled categories.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $startTime = microtime(true);
        $command = 'retention:purge';
        $output = '';

        $logId = $db->table('cron_execution_logs')->insert([
            'job_id' => 0,
            'command' => $command,
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'running',
        ]);
        $logId = $db->insertID();

        try {
            CLI::write(' Running retention purge for enabled categories...', 'yellow');

            $results = (new RetentionService())->purge();

            $totalDeleted = 0;
            foreach ($results as $cat => $r) {
                $deleted = (int) ($r['deleted'] ?? 0);
                $totalDeleted += $deleted;
                $status = $deleted > 0 ? 'green' : 'white';
                CLI::write(" {$cat}: {$deleted} deleted", $status);
                $output .= "{$cat}: {$deleted} deleted" . PHP_EOL;
            }

            CLI::write(" Done. Total records deleted: {$totalDeleted}.", 'green');
            $output .= "Done. Total records deleted: {$totalDeleted}." . PHP_EOL;

            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $db->table('cron_execution_logs')->where('id', $logId)->update([
                'finished_at' => date('Y-m-d H:i:s'),
                'status' => 'success',
                'output' => trim($output),
                'duration_ms' => $duration,
            ]);
        } catch (\Throwable $e) {
            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $db->table('cron_execution_logs')->where('id', $logId)->update([
                'finished_at' => date('Y-m-d H:i:s'),
                'status' => 'failed',
                'output' => trim($output) . PHP_EOL . $e->getMessage(),
                'duration_ms' => $duration,
            ]);
            CLI::error('Retention purge failed: ' . $e->getMessage());
        }
    }
}
