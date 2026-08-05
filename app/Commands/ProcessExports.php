<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\Mod_Export_Job;
use App\Services\ForensicExportService;

class ProcessExports extends BaseCommand
{
    protected $group       = 'Queue';
    protected $name        = 'export:process';
    protected $description = 'Process queued forensic export jobs and generate archives.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $startTime = microtime(true);
        $command = 'export:process';
        $output = '';

        $logId = $db->table('cron_execution_logs')->insert([
            'job_id' => 0,
            'command' => $command,
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'running',
        ]);
        $logId = $db->insertID();

        $jobModel = new Mod_Export_Job();
        $service = new ForensicExportService();

        try {
            $jobs = $jobModel->getQueuedBatch((int) ($params[0] ?? 5));

            if (empty($jobs)) {
                CLI::write(' No queued export jobs.', 'green');
                $output = 'No queued export jobs.';
            } else {
                foreach ($jobs as $job) {
                    $jobId = (int) $job['id'];
                    $jobModel->markProcessing($jobId);
                    CLI::write(" Processing job #{$jobId} (target user #{$job['target_user_id']})...", 'yellow');
                    $output .= "Processing job #{$jobId} (target user #{$job['target_user_id']})" . PHP_EOL;

                    $jobParams = json_decode($job['params'] ?? '[]', true);

                    $result = $service->build($jobParams);

                    $jobModel->markCompleted($jobId, $result['path'], $result['size']);
                    CLI::write(" Job #{$jobId} done (" . number_format($result['size']) . " bytes).", 'green');
                    $output .= "Job #{$jobId} done (" . number_format($result['size']) . " bytes)." . PHP_EOL;
                }
            }

            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $db->table('cron_execution_logs')->where('id', $logId)->update([
                'finished_at' => date('Y-m-d H:i:s'),
                'status' => 'success',
                'output' => trim($output),
                'duration_ms' => $duration,
            ]);
        } catch (\Throwable $e) {
            if (isset($jobId)) {
                $jobModel->markFailed($jobId, $e->getMessage());
            }
            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $db->table('cron_execution_logs')->where('id', $logId)->update([
                'finished_at' => date('Y-m-d H:i:s'),
                'status' => 'failed',
                'output' => trim($output) . PHP_EOL . $e->getMessage(),
                'duration_ms' => $duration,
            ]);
            CLI::error('Export processing failed: ' . $e->getMessage());
        }
    }
}
