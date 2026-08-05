<?php

namespace App\Controllers\superadmin;

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

        return $this->renderView('superadmin/forensic_export', [
            'pag' => 'superadmin-forensics',
            'users' => $users,
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
        $format = $this->request->getPost('format') ?? 'zip';
        $dateFrom = $this->request->getPost('date_from') ?? '1970-01-01';
        $dateTo = $this->request->getPost('date_to') ?? date('Y-m-d');

        if (empty($userId)) {
            return redirect()->back()->with('error', 'Please select a user.');
        }

        if (empty($categories)) {
            return redirect()->back()->with('error', 'Please select at least one data category.');
        }

        // Verify user exists
        $user = $db->table('users')->where('id', $userId)->where('deleted_at IS NULL')->get()->getRowArray();
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $dataTypes = [
            'sms' => ['table' => 'tbl_sms', 'date_col' => 'created_at', 'label' => 'SMS'],
            'calls' => ['table' => 'tbl_logs', 'date_col' => 'created_at', 'label' => 'Call Logs'],
            'contacts' => ['table' => 'tbl_contacts', 'date_col' => 'created_at', 'label' => 'Contacts'],
            'apps' => ['table' => 'tbl_apps', 'date_col' => 'created_at', 'label' => 'Apps'],
            'files' => ['table' => 'tbl_device_files', 'date_col' => 'created_at', 'label' => 'Files'],
            'locations' => ['table' => 'tbl_location', 'date_col' => 'created_at', 'label' => 'Locations'],
            'activities' => ['table' => 'tbl_activity', 'date_col' => 'created_at', 'label' => 'Activities'],
            'accounts' => ['table' => 'tbl_accounts', 'date_col' => 'created_at', 'label' => 'Accounts'],
            'network' => ['table' => 'tbl_network_info', 'date_col' => 'created_at', 'label' => 'Network Info'],
            'device_context' => ['table' => 'tbl_device_context', 'date_col' => 'created_at', 'label' => 'Device Context'],
            'bluetooth' => ['table' => 'tbl_bluetooth', 'date_col' => 'created_at', 'label' => 'Bluetooth'],
            'sensors' => ['table' => 'tbl_sensor_profile', 'date_col' => 'created_at', 'label' => 'Sensors'],
            'security_audit' => ['table' => 'tbl_security_audit', 'date_col' => 'created_at', 'label' => 'Security Audit'],
            'notifications' => ['table' => 'tbl_notifications', 'date_col' => 'created_at', 'label' => 'Notifications'],
            'calendar' => ['table' => 'tbl_calendar_events', 'date_col' => 'created_at', 'label' => 'Calendar'],
            'app_usage' => ['table' => 'tbl_app_usage', 'date_col' => 'created_at', 'label' => 'App Usage'],
            'media' => ['table' => 'tbl_captured_media', 'date_col' => 'created_at', 'label' => 'Media'],
            'sim' => ['table' => 'tbl_sim_configs', 'date_col' => 'created_at', 'label' => 'SIM Configs'],
        ];

        $zip = new \ZipArchive();
        $tempDir = WRITEPATH . 'exports/forensic_' . $user['username'] . '_' . date('Ymd_His');
        
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '.zip';
        $manifest = [
            'exported_at' => date('Y-m-d H:i:s'),
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'] ?? '',
            ],
            'date_range' => $dateFrom . ' to ' . $dateTo,
            'categories' => [],
        ];

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            return redirect()->back()->with('error', 'Failed to create export archive.');
        }

        foreach ($categories as $cat) {
            if (!isset($dataTypes[$cat])) continue;

            $info = $dataTypes[$cat];
            $table = $info['table'];
            $dateCol = $info['date_col'];
            $label = $info['label'];

            $query = $db->table($table)
                ->where($dateCol . ' >=', $dateFrom)
                ->where($dateCol . ' <=', $dateTo)
                ->where('owner_id', $userId);

            $count = $query->countAllResults(false);
            $rows = $query->get()->getResultArray();

            if ($count > 0) {
                // Write CSV
                $csvPath = $tempDir . '/' . $cat . '.csv';
                $csvFile = fopen($csvPath, 'w');
                
                if ($rows) {
                    fputcsv($csvFile, array_keys($rows[0]));
                    foreach ($rows as $row) {
                        fputcsv($csvFile, $row);
                    }
                }
                fclose($csvFile);
                
                $zip->addFile($csvPath, $cat . '.csv');
                $manifest['categories'][] = [
                    'category' => $cat,
                    'label' => $label,
                    'records' => $count,
                ];
            }
        }

        // Add manifest
        $manifestPath = $tempDir . '/manifest.json';
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->addFile($manifestPath, 'manifest.json');

        // Add readme
        $readme = "Forensic Export for user: {$user['username']}\n";
        $readme .= "Export Date: " . date('Y-m-d H:i:s') . "\n";
        $readme .= "Date Range: $dateFrom to $dateTo\n";
        $readme .= "Categories: " . implode(', ', array_map(fn($c) => $dataTypes[$c]['label'] ?? $c, $categories)) . "\n";
        $readme .= "Exported by: " . ($this->userData['username'] ?? 'Unknown') . "\n";
        $zip->addFromString('README.txt', $readme);

        $zip->close();

        // Clean up temp directory
        $this->cleanupDir($tempDir);

        return $this->response->download($zipPath, null)->setFileName('forensic_export_' . $user['username'] . '_' . date('Ymd_His') . '.zip');
    }

    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
        rmdir($dir);
    }
}