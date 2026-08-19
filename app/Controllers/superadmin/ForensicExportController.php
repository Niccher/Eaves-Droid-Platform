<?php

namespace App\Controllers\superadmin;

use App\Models\ExportJobModel;

class ForensicExportController extends BaseSuperadminController
{
    public function index()
    {
        $db = $this->getDb();

        $this->logAdminAction('forensics_export_view', 'low', true);

        $users = $db->table('users')
            ->select('users.id, users.username, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->where('users.deleted_at IS NULL')
            ->whereNotIn('users.id', function (\CodeIgniter\Database\BaseBuilder $builder) {
                return $builder->select('user_id')->from('auth_groups_users')->whereIn('group', ['admin', 'superadmin']);
            })
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        $jobModel = new ExportJobModel();
        $stuckJobs = $jobModel->getQueuedBatch(10);
        if (!empty($stuckJobs)) {
            $service = new \App\Services\ForensicExportService();
            foreach ($stuckJobs as $sj) {
                try {
                    $jobModel->markProcessing((int)$sj['id']);
                    $sjParams = json_decode($sj['params'] ?? '[]', true);
                    $res = $service->build($sjParams);
                    $jobModel->markCompleted((int)$sj['id'], $res['path'], $res['size']);
                } catch (\Throwable $ex) {
                    log_message('error', 'Auto-flush export error: ' . $ex->getMessage());
                    $jobModel->markFailed((int)$sj['id'], $ex->getMessage());
                }
            }
        }

        $recentJobs = $jobModel->recentFor($this->userId, 15);

        return $this->renderView('superadmin/forensic_export', [
            'pag' => 'superadmin-forensics',
            'users' => $users,
            'recentJobs' => $recentJobs,
        ]);
    }

    public function export()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('superadmin/forensic-export')->with('error', 'Invalid request method.');
        }

        $this->logAdminAction('forensics_export', 'high', true, [
            'resource_id' => $this->request->getPost('user_id'),
            'new_values' => json_encode([
                'user_id' => $this->request->getPost('user_id'),
                'categories' => $this->request->getPost('categories'),
                'format' => $this->request->getPost('format'),
                'date_from' => $this->request->getPost('date_from'),
                'date_to' => $this->request->getPost('date_to'),
            ]),
        ]);

        $db = $this->getDb();
        $userId = $this->request->getPost('user_id');
        $categories = $this->request->getPost('categories') ?? [];
        $dateFrom = $this->request->getPost('date_from') ?? '1970-01-01';
        $dateTo = $this->request->getPost('date_to') ?? date('Y-m-d');

        if (empty($userId)) {
            return redirect()->back()->with('error', 'Please select a user.');
        }

        if (empty($categories)) {
            return redirect()->back()->with('error', 'Please select at least one data category.');
        }

        $user = $db->table('users')->where('id', $userId)->where('deleted_at IS NULL')->get()->getRowArray();
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $jobModel = new ExportJobModel();
        $jobId = $jobModel->enqueue([
            'job_type'       => 'forensics',
            'requester_id'   => $this->userId,
            'target_user_id' => (int) $userId,
            'params'         => [
                'user_id'    => (int) $userId,
                'username'   => $user['username'],
                'email'      => $user['email'] ?? '',
                'categories' => $categories,
                'date_from'  => $dateFrom,
                'date_to'    => $dateTo,
                'exporter'   => $this->userData['username'] ?? 'Unknown',
            ],
        ]);

        if (!$jobId) {
            return redirect()->back()->with('error', 'Failed to enqueue export job. Please try again.');
        }

        // Synchronously process the export so it generates immediately without relying on external cron workers
        try {
            $jobModel->markProcessing($jobId);
            $service = new \App\Services\ForensicExportService();
            $result = $service->build([
                'user_id'    => (int) $userId,
                'username'   => $user['username'],
                'email'      => $user['email'] ?? '',
                'categories' => $categories,
                'date_from'  => $dateFrom,
                'date_to'    => $dateTo,
                'exporter'   => $this->userData['username'] ?? 'Unknown',
            ]);
            $jobModel->markCompleted($jobId, $result['path'], $result['size']);
            $this->sendExportReadyEmail($user, $jobId, $result['size']);
        } catch (\Throwable $e) {
            log_message('error', 'Forensic export execution error: ' . $e->getMessage());
            $jobModel->markFailed($jobId, $e->getMessage());
            return redirect()->to('superadmin/forensic-export')
                ->with('error', "Export job #{$jobId} failed: " . $e->getMessage());
        }

        return redirect()->to('superadmin/forensic-export')
            ->with('message', "Export job #{$jobId} generated successfully. Your download link is ready below.");
    }

    /**
     * JSON status for one or more job ids (used by the poller).
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function jobsStatus()
    {
        $ids = $this->request->getGet('ids');
        $ids = $ids ? array_filter(array_map('intval', explode(',', $ids))) : [];

        $statuses = [];
        if ($ids) {
            $rows = $this->getDb()->table('export_jobs')
                ->where('requester_id', $this->userId)
                ->whereIn('id', $ids)
                ->get()
                ->getResultArray();
            foreach ($rows as $r) {
                $statuses[(int) $r['id']] = [
                    'status' => $r['status'],
                    'result_size' => (int) $r['result_size'],
                    'error_message' => $r['error_message'],
                    'download_url' => $r['status'] === 'done'
                        ? base_url('superadmin/forensic-export/download/' . $r['id'])
                        : null,
                ];
            }
        }

        return $this->response->setJSON(['jobs' => $statuses]);
    }

    /**
     * Streams a completed export file. Only the requester can download.
     *
     * @param int $id Job ID
     * @return \CodeIgniter\HTTP\ResponseInterface|\CodeIgniter\HTTP\RedirectResponse
     */
    public function download($id)
    {
        $job = $this->getDb()->table('export_jobs')
            ->where('id', (int) $id)
            ->where('requester_id', $this->userId)
            ->get()
            ->getRowArray();

        if (!$job) {
            return redirect()->to('superadmin/forensic-export')->with('error', 'Export job not found.');
        }

        if ($job['status'] !== 'done' || !$job['result_path'] || !is_file($job['result_path'])) {
            return redirect()->to('superadmin/forensic-export')->with('error', 'Export is not ready or file is missing.');
        }

        $username = 'export';
        $params = json_decode($job['params'] ?? '[]', true);
        if (is_array($params) && !empty($params['username'])) {
            $username = $params['username'];
        }
        $filename = 'forensic_export_' . $username . '_' . date('Ymd_His', strtotime($job['completed_at'] ?? 'now')) . '.zip';

        return $this->response->download($job['result_path'], null)->setFileName($filename);
    }

    /**
     * Send email notification to user with download link upon export completion.
     */
    private function sendExportReadyEmail(array $user, int $jobId, int $fileSizeBytes): bool
    {
        $email = $user['email'] ?? null;
        if (empty($email)) {
            return false;
        }

        try {
            $db = \Config\Database::connect();
            $smtpSettings = [];
            $settingsRows = $db->table('system_settings')
                ->whereIn('key', ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name'])
                ->get()
                ->getResultArray();
            foreach ($settingsRows as $r) {
                $smtpSettings[$r['key']] = $r['value'];
            }

            $emailService = \Config\Services::email();

            if (!empty($smtpSettings['smtp_host'])) {
                $emailService->initialize([
                    'protocol'   => 'smtp',
                    'SMTPHost'   => $smtpSettings['smtp_host'] ?? '',
                    'SMTPPort'   => $smtpSettings['smtp_port'] ?? 587,
                    'SMTPUser'   => $smtpSettings['smtp_user'] ?? '',
                    'SMTPPass'   => $smtpSettings['smtp_pass'] ?? '',
                    'SMTPCrypto' => 'tls',
                    'mailType'   => 'html',
                    'wordWrap'   => true,
                ]);
                $fromEmail = $smtpSettings['smtp_from_email'] ?? $emailService->getFromEmail();
                $fromName  = $smtpSettings['smtp_from_name'] ?? 'Eaves Droid System';
                $emailService->setFrom($fromEmail, $fromName);
            }

            $downloadUrl = base_url("superadmin/forensic-export/download/{$jobId}");
            $formattedSize = number_format($fileSizeBytes / 1024, 2) . ' KB';
            $username = esc($user['username'] ?? 'User');

            $htmlMessage = "
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2 style='color: #2c3e50;'>Eaves Droid — Forensic Export Ready</h2>
                    <p>Hello <strong>{$username}</strong>,</p>
                    <p>Your requested forensic telemetry export (Job #<strong>{$jobId}</strong>) has been generated successfully.</p>
                    <div style='background: #f8f9fa; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;'>
                        <p style='margin: 0;'><strong>Archive Size:</strong> {$formattedSize}</p>
                        <p style='margin: 5px 0 0 0;'><strong>Status:</strong> Completed & Verified</p>
                    </div>
                    <p>Click the button below to download your export archive:</p>
                    <p style='margin-top: 25px;'>
                        <a href='{$downloadUrl}' style='background-color: #007bff; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;'>Download Forensic Export</a>
                    </p>
                    <p style='margin-top: 30px; font-size: 12px; color: #777;'>
                        Direct link: <a href='{$downloadUrl}'>{$downloadUrl}</a>
                    </p>
                </div>
            ";

            $emailService->setTo($email);
            $emailService->setSubject("Forensic Export Archive Ready (Job #{$jobId}) — Eaves Droid");
            $emailService->setMessage($htmlMessage);

            return $emailService->send();
        } catch (\Throwable $e) {
            log_message('error', 'sendExportReadyEmail exception: ' . $e->getMessage());
            return false;
        }
    }
}
