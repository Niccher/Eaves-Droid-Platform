<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\Mod_Upload_Queue;

class CleanupUploadQueue extends BaseCommand
{
    protected $group       = 'Housekeeping';
    protected $name        = 'queue:cleanup';
    protected $description = 'Reset stuck upload queue items that have been processing for too long.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $startTime = microtime(true);
        $command = 'queue:cleanup';
        $output = '';

        $logId = $db->table('cron_execution_logs')->insert([
            'job_id' => 0,
            'command' => $command,
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'running',
        ]);
        $logId = $db->insertID();

        try {
            $settings = $this->getCleanupSettings();
            $timeoutHours = (int)($settings['queue_cleanup_timeout_hours'] ?? 2);
            $maxAttempts = (int)($settings['queue_cleanup_max_attempts'] ?? 3);

            CLI::write(" Checking for stuck queue items (timeout: {$timeoutHours}h, max attempts: {$maxAttempts})...", 'yellow');
            $output .= "Checking for stuck queue items (timeout: {$timeoutHours}h, max attempts: {$maxAttempts})..." . PHP_EOL;

            $queueModel = new Mod_Upload_Queue();
            $cutoff = date('Y-m-d H:i:s', strtotime("-{$timeoutHours} hours"));

            $stuckItems = $db->table('tbl_upload_queue')
                ->where('status', 'processing')
                ->where('updated_at <', $cutoff)
                ->get()
                ->getResultArray();

            if (empty($stuckItems)) {
                CLI::write(' No stuck queue items found.', 'green');
                $output .= 'No stuck queue items found.' . PHP_EOL;
            } else {
                $resetCount = 0;
                $failedCount = 0;

                foreach ($stuckItems as $item) {
                    $attempts = (int)$item['attempts'];

                    if ($attempts >= $maxAttempts) {
                        $queueModel->markFailed(
                            (int)$item['id'],
                            "Queue item timed out after {$timeoutHours}h and {$attempts} attempts — marked by cleanup cron"
                        );
                        $failedCount++;
                        CLI::write(" [{$item['id']}] Marked as failed ({$attempts} attempts, timeout {$timeoutHours}h)", 'red');
                        $output .= "[{$item['id']}] Marked as failed ({$attempts} attempts, timeout {$timeoutHours}h)" . PHP_EOL;
                    } else {
                        $db->table('tbl_upload_queue')
                            ->where('id', (int)$item['id'])
                            ->update([
                                'status' => 'pending',
                                'processing_started_at' => null,
                                'attempts' => $attempts + 1,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                        $resetCount++;
                        CLI::write(" [{$item['id']}] Reset to pending (attempt #{($attempts + 1)})", 'green');
                        $output .= "[{$item['id']}] Reset to pending (attempt #{($attempts + 1)})" . PHP_EOL;
                    }
                }

                CLI::write(" Done. Reset {$resetCount}, failed {$failedCount}.", 'green');
                $output .= "Done. Reset {$resetCount}, failed {$failedCount}." . PHP_EOL;
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

    private function getCleanupSettings(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('settings')->where('class', 'housekeeping')->get()->getResultArray();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return $settings;
    }
}