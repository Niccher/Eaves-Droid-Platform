<?php

namespace App\Services;

use Config\Database;

class ForensicExportService
{
    private const DATA_TYPES = [
        'sms'               => ['table' => 'tbl_extracted_sms',             'date_col' => 'created_at',  'owner_col' => 'owner_id',       'label' => 'SMS Messages'],
        'calls'             => ['table' => 'tbl_extracted_call_logs',       'date_col' => 'created_at',  'owner_col' => 'owner_id',       'label' => 'Call Logs'],
        'contacts'          => ['table' => 'tbl_extracted_contacts',        'date_col' => 'created_at',  'owner_col' => 'owner_id',       'label' => 'Contacts'],
        'files'             => ['table' => 'tbl_extracted_device_files',    'date_col' => 'created_at',  'owner_col' => 'owner_id',       'label' => 'Device Files'],
        'locations'         => ['table' => 'tbl_extracted_locations',       'date_col' => 'created_at',  'owner_col' => 'owner_id',       'label' => 'Location & Activity'],
        'remote_data'       => ['table' => 'tbl_uploaded_files',            'date_col' => 'uploaded_at', 'owner_col' => 'token_owner_id', 'label' => 'Remote Data (Captured Files)'],
        'misc_hardware'     => ['table' => 'tbl_device_hardware_contexts',  'date_col' => 'created_at',  'owner_col' => 'owner_id',       'label' => 'Misc Hardware'],
        'misc_software'     => ['table' => 'tbl_system_app_security',       'date_col' => 'created_at',  'owner_col' => 'owner_id',       'label' => 'Misc Software'],
        'apps'              => ['table' => 'tbl_extracted_installed_apps', 'date_col' => 'created_at',  'owner_col' => 'owner_id',       'label' => 'Installed Apps'],
        'app_usage'         => ['table' => 'tbl_system_app_usage',          'date_col' => 'created_at',  'owner_col' => 'owner_id',       'label' => 'App Usage'],
        'app_notifications'  => ['table' => 'tbl_extracted_notifications',  'date_col' => 'created_at',  'owner_col' => 'owner_id',       'label' => 'App Notifications'],
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
            $ownerCol = $info['owner_col'] ?? 'owner_id';

            if (!$db->tableExists($table)) {
                continue;
            }

            $fields = $db->getFieldNames($table);
            $dateCol = $info['date_col'];
            if (!in_array($dateCol, $fields, true)) {
                if (in_array('created_at', $fields, true)) {
                    $dateCol = 'created_at';
                } elseif (in_array('uploaded_at', $fields, true)) {
                    $dateCol = 'uploaded_at';
                } elseif (in_array('updated_at', $fields, true)) {
                    $dateCol = 'updated_at';
                } else {
                    $dateCol = null;
                }
            }

            $query = $db->table($table)->where($ownerCol, $userId);

            if ($dateCol) {
                $query->where($dateCol . ' >=', $dateFrom . ' 00:00:00')
                      ->where($dateCol . ' <=', $dateTo . ' 23:59:59');
            }

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
