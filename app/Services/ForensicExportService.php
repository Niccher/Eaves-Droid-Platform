<?php

namespace App\Services;

use Config\Database;

class ForensicExportService
{
    private const DATA_TYPES = [
        'sms' => ['table' => 'tbl_extracted_sms', 'date_col' => 'created_at', 'label' => 'SMS'],
        'calls' => ['table' => 'tbl_extracted_call_logs', 'date_col' => 'created_at', 'label' => 'Call Logs'],
        'contacts' => ['table' => 'tbl_extracted_contacts', 'date_col' => 'created_at', 'label' => 'Contacts'],
        'apps' => ['table' => 'tbl_extracted_installed_apps', 'date_col' => 'created_at', 'label' => 'Apps'],
        'files' => ['table' => 'tbl_extracted_device_files', 'date_col' => 'created_at', 'label' => 'Files'],
        'locations' => ['table' => 'tbl_extracted_locations', 'date_col' => 'created_at', 'label' => 'Locations'],
        'activities' => ['table' => 'tbl_extracted_activities', 'date_col' => 'created_at', 'label' => 'Activities'],
        'accounts' => ['table' => 'tbl_accounts', 'date_col' => 'created_at', 'label' => 'Accounts'],
        'network' => ['table' => 'tbl_system_network_info', 'date_col' => 'created_at', 'label' => 'Network Info'],
        'device_context' => ['table' => 'tbl_device_hardware_contexts', 'date_col' => 'created_at', 'label' => 'Device Context'],
        'bluetooth' => ['table' => 'tbl_telemetry_bluetooth_devices', 'date_col' => 'created_at', 'label' => 'Bluetooth'],
        'sensors' => ['table' => 'tbl_telemetry_sensors', 'date_col' => 'created_at', 'label' => 'Sensors'],
        'security_audit' => ['table' => 'tbl_security_audit', 'date_col' => 'created_at', 'label' => 'Security Audit'],
        'notifications' => ['table' => 'tbl_extracted_notifications', 'date_col' => 'created_at', 'label' => 'Notifications'],
        'calendar' => ['table' => 'tbl_extracted_calendar_events', 'date_col' => 'created_at', 'label' => 'Calendar'],
        'app_usage' => ['table' => 'tbl_system_app_usage', 'date_col' => 'created_at', 'label' => 'App Usage'],
        'media' => ['table' => 'tbl_extracted_media_files', 'date_col' => 'created_at', 'label' => 'Media'],
        'sim' => ['table' => 'tbl_sim_configs', 'date_col' => 'created_at', 'label' => 'SIM Configs'],
    ];

    /**
     * Builds a forensic ZIP archive for the given user and returns the path.
     *
     * @param array $params user_id, username, email, categories, date_from, date_to, exporter
     * @return array{path:string, size:int}
     * @throws \RuntimeException
     */
    public function build(array $params): array
    {
        $db = Database::connect();
        $userId = (int) ($params['user_id'] ?? 0);
        $username = $params['username'] ?? 'user' . $userId;
        $email = $params['email'] ?? '';
        $categories = $params['categories'] ?? [];
        $dateFrom = $params['date_from'] ?? '1970-01-01';
        $dateTo = $params['date_to'] ?? date('Y-m-d');
        $exporter = $params['exporter'] ?? 'Unknown';

        $tempDir = WRITEPATH . 'exports/forensic_' . $username . '_' . date('Ymd_His');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '.zip';
        $manifest = [
            'exported_at' => date('Y-m-d H:i:s'),
            'user' => [
                'id' => $userId,
                'username' => $username,
                'email' => $email,
            ],
            'exporter' => $exporter,
            'date_range' => $dateFrom . ' to ' . $dateTo,
            'categories' => [],
        ];

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            $this->cleanupDir($tempDir);
            throw new \RuntimeException('Failed to create export archive.');
        }

        foreach ($categories as $cat) {
            if (!isset(self::DATA_TYPES[$cat])) {
                continue;
            }

            $info = self::DATA_TYPES[$cat];
            $table = $info['table'];
            $dateCol = $info['date_col'];

            $query = $db->table($table)
                ->where($dateCol . ' >=', $dateFrom)
                ->where($dateCol . ' <=', $dateTo)
                ->where('owner_id', $userId);

            $count = $query->countAllResults(false);
            $rows = $query->get()->getResultArray();

            if ($count > 0) {
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
                    'label' => $info['label'],
                    'records' => $count,
                ];
            }
        }

        $manifestPath = $tempDir . '/manifest.json';
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->addFile($manifestPath, 'manifest.json');

        $readme = "Forensic Export for user: {$username}\n";
        $readme .= "Export Date: " . date('Y-m-d H:i:s') . "\n";
        $readme .= "Date Range: {$dateFrom} to {$dateTo}\n";
        $readme .= "Categories: " . implode(', ', array_map(fn($c) => self::DATA_TYPES[$c]['label'] ?? $c, $categories)) . "\n";
        $readme .= "Exported by: {$exporter}\n";
        $zip->addFromString('README.txt', $readme);

        $zip->close();
        $this->cleanupDir($tempDir);

        return [
            'path' => $zipPath,
            'size' => (int) filesize($zipPath),
        ];
    }

    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}
