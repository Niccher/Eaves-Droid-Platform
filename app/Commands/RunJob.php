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
        $jobId = (int)($params[0] ?? 0);
        if ($jobId <= 0) {
            CLI::error('Usage: spark anomalies:run-job <job_id>');
            return;
        }

        $model = new Mod_Anomalies();
        $job   = $model->getJob($jobId);
        if (!$job || $job['status'] !== 'running') {
            CLI::error("Job #{$jobId} is not in 'running' status.");
            return;
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

        try {
            $results = $model->runPhpDetection($selectedAlgs, $userId, $jobId);
            $model->saveResults($jobId, $userId, $results);
            $model->completeJob($jobId);

            CLI::write(' Job #' . $jobId . ' completed successfully.', 'green');
        } catch (\Throwable $e) {
            $model->completeJob($jobId, $e->getMessage());
            CLI::error(' Job #' . $jobId . ' failed: ' . $e->getMessage());
        }
    }
}
