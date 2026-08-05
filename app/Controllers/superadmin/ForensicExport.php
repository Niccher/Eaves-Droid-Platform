<?php

namespace App\Controllers\superadmin;

use App\Models\Mod_Export_Job;

class ForensicExport extends BaseSuperadminController
{
    public function index()
    {
        $db = $this->getDb();

        $this->logAdminAction('forensics_export_view', 'low', true);

        $users = $db->table('users')
            ->select('users.id, users.username, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->where('users.deleted_at IS NULL')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        $recentJobs = (new Mod_Export_Job())->recentFor($this->userId, 15);

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

        $jobId = (new Mod_Export_Job())->enqueue([
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

        return redirect()->to('superadmin/forensic-export')
            ->with('message', "Export job #{$jobId} queued. It will be ready shortly — the download link appears below when complete.");
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
}
