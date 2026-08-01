<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\Mod_Anomalies;

class RunJob extends BaseCommand
{
    protected $group       = 'Anomalies';
    protected $name        = 'anomalies:run-job';
    protected $description = 'Process an ML detection job in the background.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $startTime = microtime(true);
        $command = 'anomalies:run-job';
        $output = '';

        $logId = $db->table('cron_execution_logs')->insert([
            'job_id' => 0,
            'command' => $command,
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'running',
        ]);
        $logId = $db->insertID();

        try {
            $jobId = (int)($params[0] ?? 0);
            if ($jobId <= 0) {
                CLI::error('Usage: spark anomalies:run-job <job_id>');
                $output .= 'Usage: spark anomalies:run-job <job_id>' . PHP_EOL;
                throw new \InvalidArgumentException('Invalid job ID');
            }

            $model = new Mod_Anomalies();
            $job   = $model->getJob($jobId);
            if (!$job || $job['status'] !== 'running') {
                CLI::error("Job #{$jobId} is not in 'running' status.");
                $output .= "Job #{$jobId} is not in 'running' status." . PHP_EOL;
                throw new \RuntimeException("Job #{$jobId} is not in 'running' status.");
            }

            $flatAlgIds = json_decode($job['algorithms'] ?? '[]', true) ?: [];
            $userId       = (int)$job['user_id'];

            // Reconstruct category-keyed format expected by runPhpDetection()
            $selectedAlgs = [];
            $categories = $model->getAlgorithmCategories();
            foreach ($flatAlgIds as $aid) {
                foreach ($categories as $catKey => $cat) {
                    $algIds = array_column($cat['algorithms'], 'id');
                    if (in_array($aid, $algIds, true)) {
                        $selectedAlgs[$catKey][] = $aid;
                        break;
                    }
                }
            }

            CLI::write(" Processing job #{$jobId} for user #{$userId}...", 'yellow');
            $output .= "Processing job #{$jobId} for user #{$userId}..." . PHP_EOL;

            $results = $model->runPhpDetection($selectedAlgs, $userId, $jobId);
            $model->saveResults($jobId, $userId, $results);
            $model->completeJob($jobId);

            CLI::write(' Job #' . $jobId . ' completed successfully.', 'green');
            $output .= 'Job #' . $jobId . ' completed successfully.' . PHP_EOL;

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
}
