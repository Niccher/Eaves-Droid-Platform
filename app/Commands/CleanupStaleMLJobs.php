<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\Mod_Anomalies;

class CleanupStaleMLJobs extends BaseCommand
{
    protected $group       = 'Housekeeping';
    protected $name        = 'ml:cleanup';
    protected $description = 'Mark stale ML anomaly detection jobs as failed.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $startTime = microtime(true);
        $command = 'ml:cleanup';
        $output = '';

        $logId = $db->table('cron_execution_logs')->insert([
            'job_id' => 0,
            'command' => $command,
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'running',
        ]);
        $logId = $db->insertID();

        try {
            $settings = $this->getMLSettings();
            $timeoutHours = (int)($settings['ml_cleanup_timeout_hours'] ?? 1);
            $notifyUser = !empty($settings['ml_cleanup_notify_user']);

            CLI::write(" Checking for stale ML jobs (timeout: {$timeoutHours}h)...", 'yellow');
            $output .= "Checking for stale ML jobs (timeout: {$timeoutHours}h)..." . PHP_EOL;

            $cutoff = date('Y-m-d H:i:s', strtotime("-{$timeoutHours} hours"));

            $staleJobs = $db->table('ml_jobs')
                ->where('status', 'running')
                ->where('started_at IS NOT NULL')
                ->where('started_at <', $cutoff)
                ->get()
                ->getResultArray();

            if (empty($staleJobs)) {
                CLI::write(' No stale ML jobs found.', 'green');
                $output .= 'No stale ML jobs found.' . PHP_EOL;
            } else {
                $model = new Mod_Anomalies();
                $cleanedCount = 0;

                foreach ($staleJobs as $job) {
                    $jobId = (int)$job['id'];
                    $userId = (int)$job['user_id'];

                    $model->completeJob($jobId, "Job timed out after {$timeoutHours}h — marked as failed by automated cleanup cron");
                    $cleanedCount++;

                    CLI::write(" Job #{$jobId} (user #{$userId}) marked as failed — timeout after {$timeoutHours}h", 'red');
                    $output .= "Job #{$jobId} (user #{$userId}) marked as failed — timeout after {$timeoutHours}h" . PHP_EOL;

                    if ($notifyUser) {
                        $this->notifyUserTimeout($db, $userId, $jobId, $timeoutHours);
                    }
                }

                CLI::write(" Done. Cleaned up {$cleanedCount} stale job(s).", 'green');
                $output .= "Done. Cleaned up {$cleanedCount} stale job(s)." . PHP_EOL;
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

    private function notifyUserTimeout($db, int $userId, int $jobId, int $timeoutHours): void
    {
        $userRow = $db->table('auth_identities')
            ->select('auth_identities.secret AS email, users.username')
            ->join('users', 'auth_identities.user_id = users.id')
            ->where('auth_identities.type', 'email_password')
            ->where('auth_identities.user_id', $userId)
            ->get()
            ->getRowArray();

        if (!$userRow || empty($userRow['email'])) {
            return;
        }

        helper('email');
        send_templated_email(
            $userRow['email'],
            "Eaves Droid — ML Job #{$jobId} Timed Out",
            'email/admin/anomaly_high',
            [
                'jobId' => $jobId,
                'userId' => $userId,
                'timeoutHours' => $timeoutHours,
                'securityAction' => 'ML Job Timed Out',
                'securityDescription' => "ML anomaly detection job #{$jobId} was marked as failed due to exceeding the {$timeoutHours}-hour timeout threshold.",
                'securityStatus' => 'warning',
                'securityInitiatedBy' => 'System (ML Cleanup Cron)',
                'securityBrowser' => 'CLI (Scheduled Job)',
                'securityBrowserIp' => 'N/A',
                'securityExecutedAt' => date('Y-m-d H:i:s'),
            ]
        );
    }

    private function getMLSettings(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('settings')->where('class', 'ml')->get()->getResultArray();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return $settings;
    }
}