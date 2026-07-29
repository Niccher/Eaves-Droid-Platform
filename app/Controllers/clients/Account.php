<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

use App\Models\Mod_Finder;
use App\Models\Mod_User;
use App\Models\Mod_Access_Logs;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Files\File;

class Account extends BaseClientController
{
    use ResponseTrait;

    /**
     * @var Mod_User
     */
    protected $modUser;

    /**
     * @var \App\Models\Mod_Crypt
     */
    protected $modCrypt;

    // Remove $modFinder, $userId as they are in BaseClientController
    // Keep $userData if needed or use parent's structure

    /**
     * @var array
     */
    protected $userData; // BaseClientController doesn't have userData property exposed maybe?

    /**
     * @var int
     */
    protected $perPage = 25;

    /**
     * Initialize controller.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request
     * @param \CodeIgniter\HTTP\ResponseInterface $response
     * @param \Psr\Log\LoggerInterface $logger
     * @return void
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        // Auth check and Mod_Finder init are handled in parent

        // Initialize specific models
        $this->modUser = new Mod_User();
        $this->modAccessLogs = new Mod_Access_Logs();
        $this->modCrypt = new \App\Models\Mod_Crypt();

        // Get authenticated user data (BaseClientController might have userId set, but let's keep this for consistency with Account's logic for now)
        $this->userData = $this->getAuthenticatedUserData();
        $this->userId = $this->userData['id'] ?? null;
        
        // Ensure userId is synced with parent if needed, though parent likely set it from auth
    }

    // ... home ...
    public function home(): string
    {
        try {
            $userData = $this->getEnhancedUserData();
            $userVars = $this->getUserVars();
            $userToken = $this->ensureUserToken();
            $androidConnected = $this->isAndroidConnected();
            $dataCounts = $this->getUserDataCounts();
            $usageMetrics = $this->getUsageMetrics();

            // Profile stats from user_profiles table
            $db = \Config\Database::connect();
            $profileStats = $db->table('user_profiles')
                ->select('export_count, last_exported_at, last_deleted_data_at')
                ->where('user_id', $this->userId)
                ->get()
                ->getRowArray();

            // Estimate storage per record count (rough bytes per type)
            $estimatedStorage = $this->estimateStorage($dataCounts);

            $viewData = [
                'pag' => 'account_profile',
                'user_info' => $userData,
                'user_vars' => $userVars,
                'user_token' => $userToken,
                'android_connected' => $androidConnected,
                'total_tokens' => $usageMetrics['total_tokens'] ?? 0,
                'connected_devices' => $usageMetrics['connected_devices'] ?? 0,
                'csrf_token' => csrf_hash(),
                'export_count' => $profileStats['export_count'] ?? 0,
                'last_exported_at' => $profileStats['last_exported_at'] ?? null,
                'last_deleted_data_at' => $profileStats['last_deleted_data_at'] ?? null,
                'estimated_storage' => $estimatedStorage,
            ];

            $viewData = array_merge($viewData, $dataCounts);

            return $this->renderView('profile', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Account home error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load account information');
            return redirect()->back();
        }
    }

    /**
     * POST /account/reset-device
     * Sends cmd_reset_app to the user's Android device via FCM.
     */
    public function sendDeviceReset(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        try {
            $device = $this->findFcmDevice();
            if (!$device || empty($device['fcm_token'])) {
                return $this->fail('No connected Android device found with FCM');
            }

            $firebase = new \App\Libraries\FirebaseLib();
            $result = $firebase->sendDataMessage($device['fcm_token'], [
                'command' => 'cmd_reset_app',
                'sent_at' => date('Y-m-d H:i:s'),
            ]);

            if ($result) {
                \App\Models\Mod_Log_User_Action::logAction([
                    'user_id' => $this->userId,
                    'action_type' => 'device_reset',
                    'new_values' => json_encode(['fcm_token' => substr($device['fcm_token'], 0, 20) . '...']),
                ]);
                return $this->respond(['success' => true, 'message' => 'Reset command sent to device']);
            }

            return $this->fail('Failed to send FCM command');
        } catch (\Exception $e) {
            log_message('error', 'sendDeviceReset error: ' . $e->getMessage());
            return $this->fail('Server error: ' . $e->getMessage());
        }
    }

    /**
     * Finds the user's registered device with an FCM token.
     */
    private function findFcmDevice(): ?array
    {
        $userModel = new \App\Models\Mod_User();
        $devices = $userModel->get_user_devices_from_profile($this->userId);
        if (empty($devices)) return null;

        $deviceIds = array_column($devices, 'device_id');
        $db = \Config\Database::connect();
        return $db->table('tbl_device_profile')
            ->whereIn('device_id', $deviceIds)
            ->where('fcm_token !=', '')
            ->where('fcm_token IS NOT NULL')
            ->orderBy('counter', 'DESC')
            ->get()
            ->getRowArray();
    }

    /**
     * Estimates storage usage in KB based on record counts.
     */
    private function estimateStorage(array $dataCounts): string
    {
        // Rough bytes per record for each type
        $weights = [
            'total_apps'     => 512,
            'total_contacts' => 256,
            'total_sms'      => 1024,
            'total_files'    => 1024,
            'total_calls'    => 512,
            'total_locations'=> 256,
            'total_activities'=> 512,
            'total_device'   => 1024,
            'total_network'  => 512,
            'total_accounts' => 512,
            'total_calendar' => 1024,
            'total_app_usage'=> 512,
            'total_notifications' => 256,
            'total_bluetooth'=> 256,
            'total_sensors'  => 1024,
            'total_media'    => 2048,
            'total_security_audit' => 512,
            'total_sim_configs'    => 256,
        ];

        $totalBytes = 0;
        foreach ($weights as $key => $bytes) {
            $count = $dataCounts[$key] ?? 0;
            $totalBytes += $count * $bytes;
        }

        if ($totalBytes < 1024) {
            return '~' . $totalBytes . ' B';
        } elseif ($totalBytes < 1048576) {
            return '~' . round($totalBytes / 1024, 1) . ' KB';
        } else {
            return '~' . round($totalBytes / 1048576, 2) . ' MB';
        }
    }

    // ... setting ...
    public function setting(): string
    {
        try {
            // Get enhanced user data
            $userData = $this->getEnhancedUserData();

            // Get user variables and token
            $userVars = $this->getUserVars();
            $userToken = $this->ensureUserToken();

            // Get user devices (limit to last 10)
            $userDevices = array_slice($this->getUserDevices(), 0, 10);

            // Get used tokens
            $usedTokens = $this->modUser->get_used_tokens($this->userId);

            // Get usage metrics
            $usageMetrics = $this->getUsageMetrics();
            
            // Stats
            $dataCounts = $this->getUserDataCounts();

            $viewData = [
                'pag' => 'account_setting',
                'user_info' => $userData,
                'user_vars' => $userVars,
                'user_token' => $userToken,
                'user_devices' => $userDevices,
                'used_tokens' => $usedTokens,
                'recent_files' => $this->getRecentFiles(),
                'activeSessions' => $this->modUser->get_active_sessions_count($this->userId),
                'securityEvents' => $this->modUser->get_security_events_count($this->userId),
                'total_tokens' => $usageMetrics['total_tokens'] ?? 0,
                'connected_devices' => $usageMetrics['connected_devices'] ?? 0,
                'tokenExpiry' => isset($userToken['expires_at']) ? date('M d, Y, l H:i', strtotime($userToken['expires_at'])) : 'Never',
                'currentTokenDisplay' => $userToken['token'] ?? 'No token found',
                'qrCodeData' => $this->generateQRCodeData($userToken['token'] ?? ''),
                'csrf_token' => csrf_hash(),
            ];
            
            $viewData = array_merge($viewData, $dataCounts);

            return $this->renderView('settings', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Account settings error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load settings');
            return redirect()->back();
        }
    }
    
    // ... access_logs ...
    public function access_logs($tab = 'all'): string
    {
        try {
            // Get access logs for the user
            $accessLogs = $this->getAccessLogs();

            // Process logs with tab filtering
            $processedData = $this->processAccessLogsTabbed($accessLogs, $tab);

            // Get access statistics
            $accessStats = $this->getAccessStats();

            // Get enhanced user data
            $userData = $this->getEnhancedUserData();
            
            // Stats
            $dataCounts = $this->getUserDataCounts();

            $viewData = [
                'pag' => 'account_logs',
                'activeTab' => $tab,
                'user_info' => $userData,
                'csrf_token' => csrf_hash(),
                'access_head' => 'Access Logs',
            ];

            // Merge processed data
            $viewData = array_merge($viewData, $processedData, $accessStats, $dataCounts);

            return $this->renderView('access_logs', $viewData);

        } catch (\Exception $e) {
            // ... (keep catch block but ensure stats are missing or default)
             // For error view, maybe just empty stats
            log_message('error', 'Access logs error: ' . $e->getMessage());

            return $this->renderView('access_logs', [
                'pag' => 'account_logs',
                'activeTab' => $tab,
                'user_info' => $this->userData,
                'access_head' => 'Access Logs',
                'user_logs' => [],
                'grouped_logs' => [],
                'webLogs' => [],
                'androidLogs' => [],
                'webLogsCount' => 0,
                'androidLogsCount' => 0,
                'totalLogs' => 0,
                'successfulLogins' => 0,
                'failedAttempts' => 0,
                'suspiciousActivities' => 0,
                'lastUpdated' => 'Never',
                'by_status' => [],
                'by_category' => [],
                'by_device' => [],
                'csrf_token' => csrf_hash(),
            ]);
        }
    }

    /**
     * POST /account/clear_logs
     * Clears all access logs for the current user.
     */
    public function clearLogs(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $this->modAccessLogs->where('user_id', $this->userId)->delete();

            $this->logUserAction('access_logs_clear', 'system', 'medium', 1);

            return $this->response->setJSON(['success' => true, 'message' => 'Logs cleared']);
        } catch (\Exception $e) {
            log_message('error', 'clearLogs: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /account/add_log_note
     * Adds an administrative note as a log entry.
     */
    public function addLogNote(): \CodeIgniter\HTTP\ResponseInterface
    {
        try {
            $note = $this->request->getPost('note');
            $category = $this->request->getPost('category') ?? 'general';
            if (empty($note)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Note text is required']);
            }
            $this->modAccessLogs->logAction([
                'user_id' => $this->userId,
                'action_category' => 'admin_note',
                'action_type' => 'admin_note_' . $category,
                'action_severity' => 'low',
                'success' => 1,
                'new_values' => json_encode(['note' => $note]),
            ]);
            return $this->response->setJSON(['success' => true, 'message' => 'Note added']);
        } catch (\Exception $e) {
            log_message('error', 'addLogNote: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Gets enhanced user data with profile information.
     *
     * @return array
     */
    private function getEnhancedUserData(): array
    {
        $userData = $this->getAuthenticatedUserData();

        // Get additional profile data from database
        try {
            $db = \Config\Database::connect();
            $profile = $db->table('user_profiles')
                ->where('user_id', $this->userId)
                ->get()
                ->getRowArray();

            if ($profile) {
            // Decrypt bio if it exists
            if (!empty($profile['bio'])) {
                try {
                    $decodedBio = base64_decode($profile['bio'], true);
                    if ($decodedBio !== false) {
                        $decrypted = $this->modCrypt->Dec_String($decodedBio);
                        if ($decrypted) {
                            $profile['bio'] = $decrypted;
                        }
                    }
                } catch (\Exception $e) {
                    log_message('debug', 'Bio decryption failed, it might be plain text: ' . $e->getMessage());
                }
            }
            $userData = array_merge($userData, $profile);
        }

            // Get last login timestamp from access logs
            $lastLogin = $db->table('tbl_user_actions')
                ->select('created_at')
                ->where('user_id', $this->userId)
                ->where('action_category', 'authentication')
                ->where('success', 1)
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($lastLogin) {
                $userData['last_login'] = $lastLogin['created_at'];
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to get enhanced user data: ' . $e->getMessage());
        }

        return $userData;
    }

    /**
     * Gets authenticated user data from Shield.
     *
     * @return array
     */
    private function getAuthenticatedUserData(): array
    {
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        $userArray = $user->toArray();
        $userArray['email'] = $user->getEmail();
        $userArray['id'] = $user->id;

        // Get profile data for avatar
        if ($this->userId || $user->id) {
            $profile = (new Mod_User())->get_data_tbl_users($this->userId ?? $user->id);
            if ($profile) {
                $userArray['profile_image'] = $profile['profile_image'] ?? null;
            }
        }

        return $userArray;
    }

    /**
     * Gets user variables from custom user table.
     *
     * @return array
     */
    private function getUserVars(): array
    {
        try {
            $vars = $this->modUser->get_vars($this->userId);
            return is_array($vars) ? $vars : [];
        } catch (\Exception $e) {
            log_message('error', 'Failed to get user vars: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates user profile.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function updateProfile()
    {
        // Only allow POST requests
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
        }

        // Validate CSRF token
        $csrfToken = $this->request->getPost('csrf_token');
        if (!$csrfToken || !csrf_hash($csrfToken)) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid or expired CSRF token. Please refresh the page.'
            ]);
        }

        try {
            $data = [];

            // Update username if provided
            $username = $this->request->getPost('username');
            if ($username && trim($username) !== '') {
                // Update in Shield users table
                $user = auth()->user();
                $user->fill(['username' => trim($username)]);
                auth()->updateUser($user);
            }

            // Update bio if provided
        $bio = $this->request->getPost('bio');
        if ($bio !== null) {
            $trimmedBio = trim($bio);
            if ($trimmedBio !== '') {
                // Encrypt bio to stay consistent with legacy code
                $data['bio'] = base64_encode($this->modCrypt->Enc_String($trimmedBio));
            }
        }

        // Handle profile image upload
            $imageFile = $this->request->getFile('profile_image');
            if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
                $validationRules = [
                    'profile_image' => [
                        'uploaded[profile_image]',
                        'mime_in[profile_image,image/jpg,image/jpeg,image/png,image/gif,image/webp]',
                        'max_size[profile_image,2048]',
                    ]
                ];

                if (!$this->validate($validationRules)) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => 'Invalid image. Please upload JPG, PNG, WEBP or GIF files under 2MB.',
                        'errors' => $this->validator->getErrors()
                    ]);
                }

                $newName = $imageFile->getRandomName();
            // Store in public/uploads/profiles so it's accessible via base_url()
            $uploadPath = FCPATH . 'uploads/profiles/';

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                if ($imageFile->move($uploadPath, $newName)) {
                    $data['profile_image'] = $newName;

                    // Delete old profile image
                    if (!empty($this->userData['profile_image'])) {
                        $oldImagePath = $uploadPath . $this->userData['profile_image'];
                        if (file_exists($oldImagePath) && $oldImagePath !== $newName) {
                            @unlink($oldImagePath);
                        }
                    }
                }
            }

            // Update other profile fields
            $fields = ['language', 'timezone', 'theme'];
            foreach ($fields as $field) {
                $value = $this->request->getPost($field);
                if ($value !== null) {
                    $data[$field] = trim($value);
                }
            }

            // Update or insert profile data
            $db = \Config\Database::connect();
            $builder = $db->table('user_profiles');

            $profileExists = $builder->where('user_id', $this->userId)->countAllResults() > 0;

            if ($profileExists) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $result = $builder->where('user_id', $this->userId)->update($data);
            } else {
                $data['user_id'] = $this->userId;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                $result = $builder->insert($data);
            }

            if (!$result) {
                throw new \Exception('Database update failed');
            }

            // Log the action
            $this->logUserAction('profile_update', 'profile', 'low', 1);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'redirect' => base_url('account/home'),
                'data' => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Profile update failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ]);
        }
    }

    // =================================================================
    // SETTINGS METHODS
    // =================================================================



    /**
     * Gets only Android devices that have connected using tokens.
     *
     * @return array
     */
    private function getUserDevices(): array
    {
        try {
            // Use the model method that specifically finds devices connected via tokens/uploads
            $devices = $this->modUser->get_user_devices_from_profile($this->userId);

            if (empty($devices)) {
                return [];
            }

            $formattedDevices = [];
            foreach ($devices as $device) {
                // Determine the best way to parse the "last seen" timestamp
                $rawTimestamp = $device['extraction_timestamp'] ?? null;
                $lastSeenTime = null;

                if ($rawTimestamp) {
                    if (is_numeric($rawTimestamp)) {
                        // Handle millisecond or second timestamp
                        $lastSeenTime = strlen($rawTimestamp) > 11 ? (int)($rawTimestamp / 1000) : (int)$rawTimestamp;
                    } else {
                        // Handle date string
                        $lastSeenTime = strtotime($rawTimestamp);
                    }
                }

                $formattedDevices[] = [
                    'device_type' => 'mobile',
                    'device_name' => ($device['device_brand'] ?? 'Unknown') . ' ' . ($device['device_model'] ?? 'Device'),
                    'os' => 'Android ' . ($device['android_version'] ?? 'Unknown'),
                    'browser' => 'FGM Extractor',
                    'ip_address' => $device['device_ip_address'] ?? 'Unknown',
                    'last_seen' => $lastSeenTime ? date('Y-m-d H:i:s', $lastSeenTime) : 'N/A',
                    'last_seen_formatted' => $lastSeenTime ? date('M d, Y, l H:i', $lastSeenTime) : 'Never'
                ];
            }

            return $formattedDevices;
        } catch (\Exception $e) {
            log_message('error', 'Failed to get user devices: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets recent files for the user.
     *
     * @param int $limit
     * @return array
     */
    private function getRecentFiles(int $limit = 10): array
    {
        try {
            $db = \Config\Database::connect();
            return $db->table('uploaded_files')
                ->select('original_filename as name, file_size_bytes as size_bytes, file_extension as extension, file_category as category, uploaded_at as created_at')
                ->where('token_owner_id', $this->userId)
                ->orderBy('uploaded_at', 'DESC')
                ->limit($limit)
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Failed to get recent files: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generates QR code data for token.
     *
     * @param string $token
     * @return array
     */
    private function generateQRCodeData(string $token): array
    {
        return [
            'text' => $token,
            'size' => 200,
            'color' => '#000000',
            'bgColor' => '#ffffff',
            'level' => 'M'
        ];
    }

    /**
     * Regenerates user token.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function regenerateToken()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
        }

        // Validate CSRF token
        $csrfToken = $this->request->getPost('csrf_token');
        if (!$csrfToken || !csrf_hash($csrfToken)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid or expired CSRF token. Please refresh the page.'
            ]);
        }

        try {
            $db = \Config\Database::connect();

            // Mark old active tokens as inactive
            $db->table('tbl_tokens')
                ->where('owner_id', $this->userId)
                ->where('status', '00')
                ->set('status', '11')
                ->set('last_used_at', date('Y-m-d H:i:s'))
                ->update();

            // Generate new token
            $newToken = bin2hex(random_bytes(4));

            // Get user info
            $userEmail = auth()->user()->getEmail();
            $username = auth()->user()->username ?? explode('@', $userEmail)[0];

            // Create new token using the model method
            $this->modUser->create_token(
                $this->userId,
                $newToken,
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString()
            );

            // Log the action
            $this->logUserAction('token_regenerate', 'security', 'medium', 1);

            // Check if it's an AJAX request
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Token regenerated successfully!',
                    'token' => $newToken,
                    'qrCodeData' => $this->generateQRCodeData($newToken),
                    'expiry' => date('M d, Y H:i', strtotime('+30 days'))
                ]);
            } else {
                // For non-AJAX requests, redirect with flash message
                session()->setFlashdata('success', 'Token regenerated successfully!');
                return redirect()->to('account/setting');
            }

        } catch (\Exception $e) {
            log_message('error', 'Token regeneration failed: ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Token regeneration failed: ' . $e->getMessage()
                ]);
            } else {
                session()->setFlashdata('error', 'Token regeneration failed: ' . $e->getMessage());
                return redirect()->to('account/setting');
            }
        }
    }
    /**
     * POST /account/createToken
     * Creates a new named token.
     */
    public function createToken()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
        }

        $tokenName = trim($this->request->getPost('token_name') ?? '');

        try {
            $newToken = bin2hex(random_bytes(4));

            $this->modUser->create_token(
                $this->userId,
                $newToken,
                $this->request->getIPAddress(),
                $tokenName ?: null
            );

            $this->logUserAction('token_create', 'security', 'medium', 1);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Token created successfully!',
                'token' => $newToken,
                'token_name' => $tokenName,
                'qrCodeData' => $this->generateQRCodeData($newToken),
                'expiry' => date('M d, Y H:i', strtotime('+30 days'))
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Token creation failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Token creation failed: ' . $e->getMessage()
            ]);
        }
    }

    // =================================================================
    // ACCESS LOGS METHODS
    // =================================================================



    /**
     * Gets access logs for the current user.
     *
     * @param int $limit
     * @return array
     */
    private function getAccessLogs(int $limit = 200): array
    {
        try {
            return $this->modAccessLogs->get_access_logs($this->userId, $limit);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get access logs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets access statistics for the current user.
     *
     * @return array
     */
    private function getAccessStats(): array
    {
        try {
            return $this->modAccessLogs->get_access_stats($this->userId);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get access stats: ' . $e->getMessage());
            return ['total' => 0, 'by_status' => [], 'by_category' => [], 'by_device' => []];
        }
    }

    /**
     * Processes access logs with tab filtering.
     *
     * @param array $accessLogs
     * @param string $tab
     * @return array
     */
    private function processAccessLogsTabbed(array $accessLogs, string $tab): array
    {
        if (empty($accessLogs)) {
            return $this->getEmptyLogsStructure();
        }

        $webLogs = [];
        $androidLogs = [];
        $fileLogs = [];
        $allLogs = [];

        $statusCounts = [
            'success' => 0,
            'failed' => 0,
            'warning' => 0,
            'suspicious' => 0,
            'info' => 0
        ];

        foreach ($accessLogs as $log) {
            // Ensure created_at exists
            if (!isset($log['created_at']) || empty($log['created_at'])) {
                $log['created_at'] = date('Y-m-d H:i:s');
            }

            // Convert timestamp
            $timestamp = strtotime($log['created_at']);
            $log['Timestamps'] = $timestamp ?: time();

            // Determine platform
            $platform = $this->determinePlatformFromLog($log);
            $log['Platform'] = $platform;

            // Determine status
            $status = $this->determineStatusFromLog($log);
            $log['Status'] = $status;

            // Add Action if not present
            if (!isset($log['Action'])) {
                $log['Action'] = $this->determineActionFromLog($log);
            }

            // Add IP
            $log['IP'] = $log['ip_address'] ?? 'N/A';

            // Add location
            $log['Location'] = $this->determineLocationFromLog($log);

            // Add icons
            $log['CategoryIcon'] = $this->getCategoryIcon($log['action_category'] ?? 'system');
            $log['SeverityIcon'] = $this->getSeverityIcon($log['action_severity'] ?? 'low');
            $log['DeviceIcon'] = $this->getDeviceIcon($log['device_type'] ?? 'unknown');

            // Extract file upload metadata (file_category, file_size) from JSON values
            $log['file_category'] = '';
            $log['file_size_formatted'] = '';
            if (in_array($log['action_category'] ?? '', ['file', 'upload']) || ($log['action_type'] ?? '') === 'file_upload') {
                $meta = null;
                if (!empty($log['new_values'])) {
                    $meta = json_decode($log['new_values'], true);
                }
                if (!$meta && !empty($log['old_values'])) {
                    $meta = json_decode($log['old_values'], true);
                }
                if (is_array($meta)) {
                    $rawSize = $meta['file_size'] ?? $meta['size'] ?? $meta['fileSize'] ?? null;
                    $log['file_size_formatted'] = formatFileSize($rawSize);
                    $log['file_category'] = $meta['file_category'] ?? $meta['category'] ?? '';
                }
            }

            // Categorize by platform
            if ($platform === 'web') {
                $log['Browser'] = $log['browser'] ?? ($log['user_agent'] ?? 'Unknown Browser');
                $log['Device'] = $log['device_name'] ?? ($log['device_type'] ?? 'Unknown Device');
                $webLogs[] = $log;
            } elseif ($platform === 'android') {
                $log['Device'] = $log['device_name'] ?? 'Unknown Android Device';
                $log['OS_Version'] = $log['operating_system'] ?? 'Unknown';
                $log['Browser'] = $log['browser'] ?? 'Android App';
                $androidLogs[] = $log;
            }

            // Categorize by category for file uploads
            if (($log['action_category'] ?? '') === 'file' || ($log['action_type'] ?? '') === 'file_upload') {
                $fileLogs[] = $log;
            }

            $allLogs[] = $log;

            // Count statuses
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }

        // Sort all lists by timestamp (newest first)
        $sortByTimestamp = function($a, $b) {
            return ($b['Timestamps'] ?? 0) <=> ($a['Timestamps'] ?? 0);
        };
        usort($allLogs, $sortByTimestamp);
        usort($webLogs, $sortByTimestamp);
        usort($androidLogs, $sortByTimestamp);
        usort($fileLogs, $sortByTimestamp);

        // Keep raw counts for the UI badges before slicing to 10
        $totalLogsCount = count($allLogs);
        $webLogsCount = count($webLogs);
        $androidLogsCount = count($androidLogs);
        $fileLogsCount = count($fileLogs);

        // Limit all lists to the last 10 entries (per requirements)
        $allLogs = array_slice($allLogs, 0, 10);
        $webLogs = array_slice($webLogs, 0, 10);
        $androidLogs = array_slice($androidLogs, 0, 10);
        $fileLogs = array_slice($fileLogs, 0, 10);

        // Filter logs based on selected tab
        switch ($tab) {
            case 'web':
                $displayLogs = $webLogs;
                break;
            case 'android':
                $displayLogs = $androidLogs;
                break;
            case 'uploads':
            case 'file':
                $displayLogs = $fileLogs;
                break;
            default:
                $displayLogs = $allLogs;
                break;
        }

        // Get grouped logs for summary view (All Activities tab)
        $groupedLogs = $this->modAccessLogs->get_grouped_access_logs($this->userId, 50);

        // Get last updated timestamp
        $lastUpdated = $this->getLastUpdated($displayLogs);

        return [
            'user_logs' => $displayLogs,
            'grouped_logs' => $groupedLogs,
            'webLogs' => $webLogs,
            'androidLogs' => $androidLogs,
            'fileLogs' => $fileLogs,
            'webLogsCount' => $webLogsCount,
            'androidLogsCount' => $androidLogsCount,
            'fileLogsCount' => $fileLogsCount,
            'totalLogs' => $totalLogsCount,
            'successfulLogins' => $statusCounts['success'],
            'failedAttempts' => $statusCounts['failed'],
            'suspiciousActivities' => $statusCounts['suspicious'],
            'lastUpdated' => $lastUpdated,
            'totalByDevice' => [
                'web' => $webLogsCount,
                'android' => $androidLogsCount,
                'unknown' => $totalLogsCount - $webLogsCount - $androidLogsCount
            ],
            'activeTab' => $tab
        ];
    }

    /**
     * Determines platform from log data.
     *
     * @param array $log
     * @return string
     */
    private function determinePlatformFromLog(array $log): string
    {
        $deviceType = strtolower($log['device_type'] ?? '');
        $userAgent = strtolower($log['user_agent'] ?? '');

        // Check for Android in user agent
        if (strpos($userAgent, 'android') !== false || strpos($userAgent, 'okhttp') !== false) {
            return 'android';
        }

        // Check device type
        if (in_array($deviceType, ['desktop', 'tablet'])) {
            return 'web';
        } elseif ($deviceType === 'mobile') {
            return strpos($userAgent, 'mobile') !== false ? 'android' : 'web';
        }

        // Default based on user agent
        if (strpos($userAgent, 'mozilla') !== false || strpos($userAgent, 'chrome') !== false) {
            return 'web';
        }

        return 'unknown';
    }

    /**
     * Determines status from log data.
     *
     * @param array $log
     * @return string
     */
    private function determineStatusFromLog(array $log): string
    {
        $success = $log['success'] ?? 1;
        $severity = strtolower($log['action_severity'] ?? 'low');

        if (!$success) {
            return 'failed';
        }

        switch ($severity) {
            case 'medium':
                return 'warning';
            case 'high':
            case 'critical':
                return 'suspicious';
            default:
                return 'success';
        }
    }

    /**
     * Determines action from log data.
     *
     * @param array $log
     * @return string
     */
    private function determineActionFromLog(array $log): string
    {
        $actionType = $log['action_type'] ?? '';
        $actionCategory = $log['action_category'] ?? '';

        if (!empty($actionType)) {
            return ucwords(str_replace('_', ' ', $actionType));
        }

        if (!empty($actionCategory)) {
            return ucfirst($actionCategory) . ' Action';
        }

        return 'Unknown Action';
    }

    /**
     * Determines location from log data.
     *
     * @param array $log
     * @return string
     */
    private function determineLocationFromLog(array $log): string
    {
        $city = $log['city'] ?? '';
        $countryCode = $log['country_code'] ?? '';

        if ($city && $countryCode) {
            return $city . ', ' . strtoupper($countryCode);
        } elseif ($city) {
            return $city;
        } elseif ($countryCode) {
            return strtoupper($countryCode);
        }

        return 'Unknown Location';
    }

    /**
     * Gets category icon.
     *
     * @param string $category
     * @return string
     */
    private function getCategoryIcon(string $category): string
    {
        $icons = [
            'authentication' => 'fa-key',
            'file' => 'fa-file',
            'profile' => 'fa-user',
            'admin' => 'fa-cog',
            'system' => 'fa-server',
            'security' => 'fa-shield-alt'
        ];

        return $icons[$category] ?? 'fa-question-circle';
    }

    /**
     * Gets severity icon.
     *
     * @param string $severity
     * @return string
     */
    private function getSeverityIcon(string $severity): string
    {
        $icons = [
            'low' => 'fa-circle text-success',
            'medium' => 'fa-exclamation-circle text-warning',
            'high' => 'fa-exclamation-triangle text-danger',
            'critical' => 'fa-skull-crossbones text-danger'
        ];

        return $icons[$severity] ?? 'fa-circle text-secondary';
    }

    /**
     * Gets device icon.
     *
     * @param string $deviceType
     * @return string
     */
    private function getDeviceIcon(string $deviceType): string
    {
        $icons = [
            'desktop' => 'fa-desktop text-primary',
            'mobile' => 'fa-mobile-alt text-success',
            'tablet' => 'fa-tablet-alt text-info',
            'bot' => 'fa-robot text-secondary',
            'unknown' => 'fa-question-circle text-muted'
        ];

        return $icons[$deviceType] ?? 'fa-question-circle text-muted';
    }

    /**
     * Gets last updated timestamp.
     *
     * @param array $logs
     * @return string
     */
    private function getLastUpdated(array $logs): string
    {
        if (empty($logs)) {
            return 'Never';
        }

        $timestamps = array_column($logs, 'Timestamps');
        if (empty($timestamps)) {
            return 'Unknown';
        }

        $latest = max($timestamps);
        return date('M d, Y, l H:i:s', $latest);
    }

    /**
     * Returns empty logs structure.
     *
     * @return array
     */
    private function getEmptyLogsStructure(): array
    {
        return [
            'user_logs' => [],
            'webLogs' => [],
            'androidLogs' => [],
            'webLogsCount' => 0,
            'androidLogsCount' => 0,
            'totalLogs' => 0,
            'successfulLogins' => 0,
            'failedAttempts' => 0,
            'suspiciousActivities' => 0,
            'lastUpdated' => 'Never'
        ];
    }

    // =================================================================
    // DATA MANAGEMENT METHODS
    // =================================================================

    /**
     * Exports user data by type.
     *
     * @param string $type
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function exportData($type)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $format = $this->request->getGet('format') ?? 'json';
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        if (!in_array($format, ['json', 'csv'])) {
            $format = 'json';
        }

        try {
            $data = [];
            $filename = '';

            switch ($type) {
                case 'apps':
                    $data = $this->finderModel->get_apps($this->userId, 10000);
                    $filename = 'apps_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'calls':
                    $data = $this->finderModel->get_call_logs($this->userId, 10000);
                    $filename = 'calls_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'contacts':
                    $data = $this->finderModel->get_contacts($this->userId, 10000);
                    $filename = 'contacts_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'sms':
                    $data = $this->finderModel->get_sms($this->userId, 10000);
                    $filename = 'sms_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'files':
                    $data = $this->finderModel->export_device_files($this->userId, 10000);
                    $filename = 'files_metadata_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'locations':
                    $data = [
                        'locations' => $this->finderModel->get_locations($this->userId, 10000),
                        'activities' => $this->finderModel->get_activities($this->userId, 10000)
                    ];
                    $filename = 'location_history_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'misc_software':
                    $data = [
                        'device_context' => $this->finderModel->export_device_context($this->userId),
                        'network_info' => $this->finderModel->export_network_info($this->userId),
                        'accounts' => $this->finderModel->export_accounts($this->userId),
                        'calendar' => $this->finderModel->export_calendar_events($this->userId),
                        'app_usage' => $this->finderModel->export_app_usage($this->userId),
                        'notifications' => $this->finderModel->export_notifications($this->userId),
                        'accessibility' => $this->finderModel->export_accessibility($this->userId),
                        'input_methods' => $this->finderModel->export_input_methods($this->userId),
                        'security_audit' => $this->finderModel->export_security_audit($this->userId),
                        'proc_info' => $this->finderModel->export_proc_info($this->userId),
                        'data_usage' => $this->finderModel->export_data_usage($this->userId),
                        'saved_wifi' => $this->finderModel->export_saved_wifi($this->userId),
                        'default_apps' => $this->finderModel->export_default_apps($this->userId),
                        'alarms' => $this->finderModel->export_alarms($this->userId),
                        'app_security' => $this->finderModel->export_app_security($this->userId),
                        'network_security' => $this->finderModel->export_network_security($this->userId),
                        'telephony_network' => $this->finderModel->export_telephony_network($this->userId),
                        'system_locale' => $this->finderModel->export_system_locale($this->userId),
                    ];
                    $filename = 'misc_software_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'misc_hardware':
                    $data = [
                        'hardware_graphics' => $this->finderModel->export_hardware_graphics($this->userId),
                        'hardware_network' => $this->finderModel->export_hardware_network($this->userId),
                        'camera_info' => $this->finderModel->export_camera_info($this->userId),
                        'battery_stats' => $this->finderModel->export_battery_stats($this->userId),
                        'sensors' => $this->finderModel->export_sensors($this->userId),
                        'bluetooth' => $this->finderModel->export_bluetooth($this->userId),
                        'cell_towers' => $this->finderModel->export_cell_towers($this->userId),
                        'display_info' => $this->finderModel->export_display_info($this->userId),
                        'storage' => $this->finderModel->export_storage($this->userId),
                        'thermal' => $this->finderModel->export_thermal($this->userId),
                        'nfc' => $this->finderModel->export_nfc($this->userId),
                        'processes' => $this->finderModel->export_processes($this->userId),
                    ];
                    $filename = 'misc_hardware_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'advanced':
                    $data = [
                        'device_context' => $this->finderModel->export_device_context($this->userId),
                        'network_info' => $this->finderModel->export_network_info($this->userId),
                        'accounts' => $this->finderModel->export_accounts($this->userId),
                        'calendar' => $this->finderModel->export_calendar_events($this->userId),
                        'app_usage' => $this->finderModel->export_app_usage($this->userId),
                        'notifications' => $this->finderModel->export_notifications($this->userId),
                        'bluetooth' => $this->finderModel->export_bluetooth($this->userId),
                        'sensors' => $this->finderModel->export_sensors($this->userId),
                    ];
                    $filename = 'advanced_data_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'all':
                    $data = [
                        'apps' => $this->finderModel->get_apps($this->userId, 10000),
                        'calls' => $this->finderModel->get_call_logs($this->userId, 10000),
                        'contacts' => $this->finderModel->get_contacts($this->userId, 10000),
                        'sms' => $this->finderModel->get_sms($this->userId, 10000),
                        'files' => $this->finderModel->export_device_files($this->userId, 10000),
                        'location' => [
                            'locations' => $this->finderModel->get_locations($this->userId, 10000),
                            'activities' => $this->finderModel->get_activities($this->userId, 10000)
                        ],
                        'advanced' => [
                            'device_context' => $this->finderModel->export_device_context($this->userId),
                            'network_info' => $this->finderModel->export_network_info($this->userId),
                            'accounts' => $this->finderModel->export_accounts($this->userId),
                            'calendar' => $this->finderModel->export_calendar_events($this->userId),
                            'app_usage' => $this->finderModel->export_app_usage($this->userId),
                            'notifications' => $this->finderModel->export_notifications($this->userId),
                            'bluetooth' => $this->finderModel->export_bluetooth($this->userId),
                            'sensors' => $this->finderModel->export_sensors($this->userId),
                        ],
                        'misc_software' => [
                            'device_context' => $this->finderModel->export_device_context($this->userId),
                            'network_info' => $this->finderModel->export_network_info($this->userId),
                            'accounts' => $this->finderModel->export_accounts($this->userId),
                            'calendar' => $this->finderModel->export_calendar_events($this->userId),
                            'app_usage' => $this->finderModel->export_app_usage($this->userId),
                            'notifications' => $this->finderModel->export_notifications($this->userId),
                            'accessibility' => $this->finderModel->export_accessibility($this->userId),
                            'input_methods' => $this->finderModel->export_input_methods($this->userId),
                            'security_audit' => $this->finderModel->export_security_audit($this->userId),
                            'proc_info' => $this->finderModel->export_proc_info($this->userId),
                            'data_usage' => $this->finderModel->export_data_usage($this->userId),
                            'saved_wifi' => $this->finderModel->export_saved_wifi($this->userId),
                            'default_apps' => $this->finderModel->export_default_apps($this->userId),
                            'alarms' => $this->finderModel->export_alarms($this->userId),
                            'app_security' => $this->finderModel->export_app_security($this->userId),
                            'network_security' => $this->finderModel->export_network_security($this->userId),
                            'telephony_network' => $this->finderModel->export_telephony_network($this->userId),
                            'system_locale' => $this->finderModel->export_system_locale($this->userId),
                        ],
                        'misc_hardware' => [
                            'hardware_graphics' => $this->finderModel->export_hardware_graphics($this->userId),
                            'hardware_network' => $this->finderModel->export_hardware_network($this->userId),
                            'camera_info' => $this->finderModel->export_camera_info($this->userId),
                            'battery_stats' => $this->finderModel->export_battery_stats($this->userId),
                            'sensors' => $this->finderModel->export_sensors($this->userId),
                            'bluetooth' => $this->finderModel->export_bluetooth($this->userId),
                            'cell_towers' => $this->finderModel->export_cell_towers($this->userId),
                            'display_info' => $this->finderModel->export_display_info($this->userId),
                            'storage' => $this->finderModel->export_storage($this->userId),
                            'thermal' => $this->finderModel->export_thermal($this->userId),
                            'nfc' => $this->finderModel->export_nfc($this->userId),
                            'processes' => $this->finderModel->export_processes($this->userId),
                        ],
                        'export_info' => [
                            'exported_at' => date('Y-m-d H:i:s'),
                            'user_id' => $this->userId,
                            'user_email' => auth()->user()->getEmail(),
                        ],
                    ];
                    $filename = 'complete_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                default:
                    session()->setFlashdata('error', 'Invalid export type');
                    return redirect()->to('account/home');
            }

            if (empty($data) || (isset($data['error']) && $data['error'])) {
                 session()->setFlashdata('error', 'No data found to export or error occurred.');
                 return redirect()->to('account/home');
            }

            $exportLabel = ['apps'=>'Applications','calls'=>'Call Logs','contacts'=>'Contacts','sms'=>'SMS Messages','files'=>'File Metadata','locations'=>'Location History','advanced'=>'Advanced Data','all'=>'All Data'];
            $label = $exportLabel[$type] ?? ucfirst($type);
            $this->logUserAction('exported_' . $type . '_via_download', 'system', 'low', 1,
                ['new_values' => json_encode(['export_type' => $label, 'format' => $format])]
            );
            $this->updateExportCount();

            // Send notification email
            $userEmail = auth()->user()->getEmail();
            if ($userEmail) {
                $sizeEstimate = $this->estimateDataSize($data);
                $breakdownHtml = '';
                if ($type === 'misc_software' && isset($data['accounts'])) {
                    $subItems = [
                        'fa-user' => ['Accounts', $data['accounts']],
                        'fa-calendar-alt' => ['Calendar', $data['calendar']],
                        'fa-chart-bar' => ['App Usage', $data['app_usage']],
                        'fa-bell' => ['Notifications', $data['notifications']],
                        'fa-info-circle' => ['Device Context', $data['device_context']],
                        'fa-network-wired' => ['Network Info', $data['network_info']],
                        'fa-universal-access' => ['Accessibility', $data['accessibility']],
                        'fa-keyboard' => ['Input Methods', $data['input_methods']],
                        'fa-shield-alt' => ['Security Audit', $data['security_audit']],
                        'fa-microchip' => ['Proc Info', $data['proc_info']],
                        'fa-chart-line' => ['Data Usage', $data['data_usage']],
                        'fa-wifi' => ['Saved WiFi', $data['saved_wifi']],
                        'fa-th-list' => ['Default Apps', $data['default_apps']],
                        'fa-clock' => ['Alarms', $data['alarms']],
                        'fa-lock' => ['App Security', $data['app_security']],
                        'fa-shield-virus' => ['Network Security', $data['network_security']],
                        'fa-sim-card' => ['Telephony Network', $data['telephony_network']],
                        'fa-language' => ['System Locale', $data['system_locale']],
                    ];
                    $breakdownHtml = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Data Breakdown</h4><table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                    foreach ($subItems as $icon => $info) {
                        $count = is_array($info[1]) ? count($info[1]) : (is_numeric($info[1]) ? (int)$info[1] : 0);
                        $breakdownHtml .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $info[0] . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($count) . ' records</td></tr>';
                    }
                    $breakdownHtml .= '</table>';
                } elseif ($type === 'misc_hardware' && isset($data['hardware_graphics'])) {
                    $subItems = [
                        'fa-palette' => ['Hardware Graphics', $data['hardware_graphics']],
                        'fa-network-wired' => ['Hardware Network', $data['hardware_network']],
                        'fa-camera' => ['Camera Info', $data['camera_info']],
                        'fa-battery-full' => ['Battery Stats', $data['battery_stats']],
                        'fa-ruler' => ['Sensors', $data['sensors']],
                        'fa-bluetooth-b' => ['Bluetooth', $data['bluetooth']],
                        'fa-broadcast-tower' => ['Cell Towers', $data['cell_towers']],
                        'fa-tv' => ['Display Info', $data['display_info']],
                        'fa-hdd' => ['Storage', $data['storage']],
                        'fa-thermometer-half' => ['Thermal', $data['thermal']],
                        'fa-credit-card' => ['NFC', $data['nfc']],
                        'fa-tasks' => ['Processes', $data['processes']],
                    ];
                    $breakdownHtml = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Data Breakdown</h4><table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                    foreach ($subItems as $icon => $info) {
                        $count = is_array($info[1]) ? count($info[1]) : (is_numeric($info[1]) ? (int)$info[1] : 0);
                        $breakdownHtml .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $info[0] . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($count) . ' records</td></tr>';
                    }
                    $breakdownHtml .= '</table>';
                }

                $this->sendNotificationEmail(
                    $userEmail,
                    'Eaves Droid — Export Initiated: ' . $label,
                    '
<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
<div style="background:#28a745;padding:20px;text-align:center;"><h1 style="color:#fff;margin:0;font-size:22px;">📦 Export Initiated</h1></div>
<div style="padding:25px;">
<p style="color:#333;font-size:15px;">Hello,</p>
<p style="color:#333;font-size:15px;">A data export has been initiated from your <strong>Eaves Droid</strong> account. The file is being downloaded to your browser.</p>
<table style="width:100%;border-collapse:collapse;margin:20px 0;background:#f8f9fa;border-radius:6px;">
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Data Type</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . $label . '</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Format</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . strtoupper($format) . '</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Estimated Size</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . $sizeEstimate . '</td></tr>
<tr><td style="padding:10px 15px;font-weight:bold;color:#495057;">Exported At</td><td style="padding:10px 15px;">' . date('F j, Y, g:i A') . '</td></tr>
</table>' . $breakdownHtml . '
<div style="background:#e8fde8;border-left:4px solid #28a745;padding:12px 15px;margin:15px 0;border-radius:4px;">
<p style="margin:0;color:#333;font-size:13px;"><strong>🔒 Important:</strong> This file contains sensitive data. Keep it secure.</p>
</div>
<p style="color:#333;font-size:15px;">If you did not request this export, please contact support immediately.</p>
<p style="color:#333;font-size:15px;">Thank you,<br><strong>Eaves Droid Team</strong></p>
<div style="margin-top:20px;padding:12px 15px;background:#e9ecef;border-radius:6px;font-size:11px;color:#555;">
<table style="width:100%;border-collapse:collapse;">
<tr><td style="padding:2px 5px;"><strong>Action:</strong> Data Export</td></tr>
<tr><td style="padding:2px 5px;"><strong>Status:</strong> <span style="color:#28a745;font-weight:bold;">Success</span></td></tr>
<tr><td style="padding:2px 5px;"><strong>Browser:</strong> ' . htmlspecialchars($this->request->getUserAgent()->getAgentString() ?: '') . '</td></tr>
<tr><td style="padding:2px 5px;"><strong>Browser IP:</strong> ' . $this->request->getIPAddress() . '</td></tr>
<tr><td style="padding:2px 5px;"><strong>Executed At:</strong> ' . date('Y-m-d H:i:s') . '</td></tr>
</table>
</div>
</div>
<div style="background:#f1f1f1;padding:12px;text-align:center;font-size:11px;color:#888;">Eaves Droid — Advanced Mobile Forensic &amp; Data Intelligence Platform</div>
</div></body></html>'
                );
            }

            if ($format === 'csv') {
                return $this->exportAsCsv($data, $type, $filename);
            }

            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            if ($jsonData === false) {
                throw new \Exception('JSON encoding failed: ' . json_last_error_msg());
            }

            return $this->response
                ->setContentType('application/json')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($jsonData);

        } catch (\Exception $e) {
            log_message('error', 'Export data error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to export data: ' . $e->getMessage());
            return redirect()->to('account/home');
        }
    }

    /**
     * POST /account/export-email
     * Generates an export and sends it via email.
     */
    public function exportEmail()
    {
        ini_set('memory_limit', '512M');

        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        $type = $this->request->getPost('type');
        $format = $this->request->getPost('format') ?? 'json';
        $recipient = $this->request->getPost('email');
        $dateFrom = $this->request->getPost('date_from');
        $dateTo = $this->request->getPost('date_to');

        if (!$type || !$recipient) {
            return $this->fail('Type and email are required.');
        }

        try {
            $data = [];
            switch ($type) {
                case 'apps': $data = $this->finderModel->get_apps($this->userId, 10000); break;
                case 'calls': $data = $this->finderModel->get_call_logs($this->userId, 10000); break;
                case 'contacts': $data = $this->finderModel->get_contacts($this->userId, 10000); break;
                case 'sms': $data = $this->finderModel->get_sms($this->userId, 10000); break;
                case 'files': $data = $this->finderModel->export_device_files($this->userId, 10000); break;
                case 'locations':
                    $data = ['locations' => $this->finderModel->get_locations($this->userId, 10000), 'activities' => $this->finderModel->get_activities($this->userId, 10000)];
                    break;
                case 'misc_software':
                    $data = [
                        'device_context' => $this->finderModel->export_device_context($this->userId),
                        'network_info' => $this->finderModel->export_network_info($this->userId),
                        'accounts' => $this->finderModel->export_accounts($this->userId),
                        'calendar' => $this->finderModel->export_calendar_events($this->userId),
                        'app_usage' => $this->finderModel->export_app_usage($this->userId),
                        'notifications' => $this->finderModel->export_notifications($this->userId),
                        'accessibility' => $this->finderModel->export_accessibility($this->userId),
                        'input_methods' => $this->finderModel->export_input_methods($this->userId),
                        'security_audit' => $this->finderModel->export_security_audit($this->userId),
                        'proc_info' => $this->finderModel->export_proc_info($this->userId),
                        'data_usage' => $this->finderModel->export_data_usage($this->userId),
                        'saved_wifi' => $this->finderModel->export_saved_wifi($this->userId),
                        'default_apps' => $this->finderModel->export_default_apps($this->userId),
                        'alarms' => $this->finderModel->export_alarms($this->userId),
                        'app_security' => $this->finderModel->export_app_security($this->userId),
                        'network_security' => $this->finderModel->export_network_security($this->userId),
                        'telephony_network' => $this->finderModel->export_telephony_network($this->userId),
                        'system_locale' => $this->finderModel->export_system_locale($this->userId),
                    ];
                    break;
                case 'misc_hardware':
                    $data = [
                        'hardware_graphics' => $this->finderModel->export_hardware_graphics($this->userId),
                        'hardware_network' => $this->finderModel->export_hardware_network($this->userId),
                        'camera_info' => $this->finderModel->export_camera_info($this->userId),
                        'battery_stats' => $this->finderModel->export_battery_stats($this->userId),
                        'sensors' => $this->finderModel->export_sensors($this->userId),
                        'bluetooth' => $this->finderModel->export_bluetooth($this->userId),
                        'cell_towers' => $this->finderModel->export_cell_towers($this->userId),
                        'display_info' => $this->finderModel->export_display_info($this->userId),
                        'storage' => $this->finderModel->export_storage($this->userId),
                        'thermal' => $this->finderModel->export_thermal($this->userId),
                        'nfc' => $this->finderModel->export_nfc($this->userId),
                        'processes' => $this->finderModel->export_processes($this->userId),
                    ];
                    break;
                case 'advanced':
                    $data = ['device_context' => $this->finderModel->export_device_context($this->userId), 'network_info' => $this->finderModel->export_network_info($this->userId), 'accounts' => $this->finderModel->export_accounts($this->userId), 'calendar' => $this->finderModel->export_calendar_events($this->userId), 'app_usage' => $this->finderModel->export_app_usage($this->userId), 'notifications' => $this->finderModel->export_notifications($this->userId), 'bluetooth' => $this->finderModel->export_bluetooth($this->userId), 'sensors' => $this->finderModel->export_sensors($this->userId)];
                    break;
                case 'all':
                    $data = ['apps' => $this->finderModel->get_apps($this->userId, 10000), 'calls' => $this->finderModel->get_call_logs($this->userId, 10000), 'contacts' => $this->finderModel->get_contacts($this->userId, 10000), 'sms' => $this->finderModel->get_sms($this->userId, 10000), 'files' => $this->finderModel->export_device_files($this->userId, 10000), 'location' => ['locations' => $this->finderModel->get_locations($this->userId, 10000), 'activities' => $this->finderModel->get_activities($this->userId, 10000)], 'advanced' => ['device_context' => $this->finderModel->export_device_context($this->userId), 'network_info' => $this->finderModel->export_network_info($this->userId), 'accounts' => $this->finderModel->export_accounts($this->userId), 'calendar' => $this->finderModel->export_calendar_events($this->userId), 'app_usage' => $this->finderModel->export_app_usage($this->userId), 'notifications' => $this->finderModel->export_notifications($this->userId), 'bluetooth' => $this->finderModel->export_bluetooth($this->userId), 'sensors' => $this->finderModel->export_sensors($this->userId)], 'misc_software' => ['device_context' => $this->finderModel->export_device_context($this->userId), 'network_info' => $this->finderModel->export_network_info($this->userId), 'accounts' => $this->finderModel->export_accounts($this->userId), 'calendar' => $this->finderModel->export_calendar_events($this->userId), 'app_usage' => $this->finderModel->export_app_usage($this->userId), 'notifications' => $this->finderModel->export_notifications($this->userId), 'accessibility' => $this->finderModel->export_accessibility($this->userId), 'input_methods' => $this->finderModel->export_input_methods($this->userId), 'security_audit' => $this->finderModel->export_security_audit($this->userId), 'proc_info' => $this->finderModel->export_proc_info($this->userId), 'data_usage' => $this->finderModel->export_data_usage($this->userId), 'saved_wifi' => $this->finderModel->export_saved_wifi($this->userId), 'default_apps' => $this->finderModel->export_default_apps($this->userId), 'alarms' => $this->finderModel->export_alarms($this->userId), 'app_security' => $this->finderModel->export_app_security($this->userId), 'network_security' => $this->finderModel->export_network_security($this->userId), 'telephony_network' => $this->finderModel->export_telephony_network($this->userId), 'system_locale' => $this->finderModel->export_system_locale($this->userId)], 'misc_hardware' => ['hardware_graphics' => $this->finderModel->export_hardware_graphics($this->userId), 'hardware_network' => $this->finderModel->export_hardware_network($this->userId), 'camera_info' => $this->finderModel->export_camera_info($this->userId), 'battery_stats' => $this->finderModel->export_battery_stats($this->userId), 'sensors' => $this->finderModel->export_sensors($this->userId), 'bluetooth' => $this->finderModel->export_bluetooth($this->userId), 'cell_towers' => $this->finderModel->export_cell_towers($this->userId), 'display_info' => $this->finderModel->export_display_info($this->userId), 'storage' => $this->finderModel->export_storage($this->userId), 'thermal' => $this->finderModel->export_thermal($this->userId), 'nfc' => $this->finderModel->export_nfc($this->userId), 'processes' => $this->finderModel->export_processes($this->userId)]];
                    break;
                default: return $this->fail('Invalid type.');
            }

            $content = json_encode($data, JSON_PRETTY_PRINT);
            $filename = $type . '_export_' . date('Y-m-d_H-i-s') . '.json';
            $tmpPath = WRITEPATH . 'uploads/' . $filename;
            file_put_contents($tmpPath, $content);

            // Build breakdown HTML for misc types before data is unset
            $exportBreakdownHtml = '';
            if ($type === 'misc_software' && isset($data['accounts'])) {
                $subItems = [
                    'fa-user' => 'Accounts', 'fa-calendar-alt' => 'Calendar', 'fa-chart-bar' => 'App Usage',
                    'fa-bell' => 'Notifications', 'fa-info-circle' => 'Device Context', 'fa-network-wired' => 'Network Info',
                    'fa-universal-access' => 'Accessibility', 'fa-keyboard' => 'Input Methods', 'fa-shield-alt' => 'Security Audit',
                    'fa-microchip' => 'Proc Info', 'fa-chart-line' => 'Data Usage', 'fa-wifi' => 'Saved WiFi',
                    'fa-th-list' => 'Default Apps', 'fa-clock' => 'Alarms', 'fa-lock' => 'App Security',
                    'fa-shield-virus' => 'Network Security', 'fa-sim-card' => 'Telephony Network', 'fa-language' => 'System Locale',
                ];
                $exportBreakdownHtml = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Data Breakdown</h4>
                <table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                foreach ($subItems as $icon => $label) {
                    $key = strtolower(str_replace([' ', '-'], '_', $label));
                    $keys = ['accounts','calendar','app_usage','notifications','device_context','network_info','accessibility','input_methods','security_audit','proc_info','data_usage','saved_wifi','default_apps','alarms','app_security','network_security','telephony_network','system_locale'];
                    $idx = array_search($key, $keys);
                    $val = array_values(array_slice($data, 0, 18))[$idx] ?? [];
                    $count = is_array($val) ? count($val) : 0;
                    $exportBreakdownHtml .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $label . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($count) . ' records</td></tr>';
                }
                $exportBreakdownHtml .= '</table>';
            } elseif ($type === 'misc_hardware' && isset($data['hardware_graphics'])) {
                $subItems = [
                    'fa-palette' => 'Hardware Graphics', 'fa-network-wired' => 'Hardware Network',
                    'fa-camera' => 'Camera Info', 'fa-battery-full' => 'Battery Stats',
                    'fa-ruler' => 'Sensors', 'fa-bluetooth-b' => 'Bluetooth',
                    'fa-broadcast-tower' => 'Cell Towers', 'fa-tv' => 'Display Info',
                    'fa-hdd' => 'Storage', 'fa-thermometer-half' => 'Thermal',
                    'fa-credit-card' => 'NFC', 'fa-tasks' => 'Processes',
                ];
                $exportBreakdownHtml = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Data Breakdown</h4>
                <table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                $idx = 0;
                foreach ($subItems as $icon => $label) {
                    $val = array_values($data)[$idx] ?? [];
                    $count = is_array($val) ? count($val) : 0;
                    $exportBreakdownHtml .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $label . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($count) . ' records</td></tr>';
                    $idx++;
                }
                $exportBreakdownHtml .= '</table>';
            }

            unset($data);
            unset($content);

            $db = \Config\Database::connect();
            $smtpSettings = [];

            // Use POSTed SMTP config first, fall back to DB
            $smtpHost = $this->request->getPost('smtp_host');
            $smtpPort = $this->request->getPost('smtp_port');
            $smtpUser = $this->request->getPost('smtp_user');
            $smtpPass = $this->request->getPost('smtp_pass');
            $smtpFromEmail = $this->request->getPost('smtp_from_email');
            $smtpFromName = $this->request->getPost('smtp_from_name');

            if (!$smtpHost) {
                $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
                foreach ($rows as $r) {
                    $smtpSettings[$r['key']] = $r['value'];
                }
                $smtpHost = $smtpSettings['smtp_host'] ?? '';
                $smtpPort = $smtpSettings['smtp_port'] ?? '587';
                $smtpUser = $smtpSettings['smtp_user'] ?? '';
                $smtpPass = $smtpSettings['smtp_pass'] ?? '';
                $smtpFromEmail = $smtpSettings['smtp_from_email'] ?? '';
                $smtpFromName = $smtpSettings['smtp_from_name'] ?? 'Eaves Droid';
            }

            $email = \Config\Services::email();
            $email->initialize([
                'protocol'   => 'smtp',
                'SMTPHost'   => $smtpHost,
                'SMTPPort'   => $smtpPort,
                'SMTPUser'   => $smtpUser,
                'SMTPPass'   => $smtpPass,
                'SMTPCrypto' => 'tls',
                'mailType'   => 'html',
                'wordWrap'   => true,
            ]);
            $email->setFrom($smtpFromEmail, $smtpFromName);
            $email->setTo($recipient);
            $typeLabels = [
                'apps' => 'Installed Applications',
                'calls' => 'Call Logs',
                'contacts' => 'Contacts',
                'sms' => 'SMS Messages',
                'files' => 'File Metadata',
                'locations' => 'Location History',
                'advanced' => 'Advanced Device Data',
                'all' => 'Complete Data Archive',
            ];
            $label = $typeLabels[$type] ?? ucfirst($type);
            $fileSize = filesize($tmpPath);
            $sizeStr = $fileSize > 1048576 ? number_format($fileSize / 1048576, 2) . ' MB' : number_format($fileSize / 1024, 1) . ' KB';
            $downloadUrl = base_url('downloads/export/' . $filename);

            $email->setSubject('Eaves Droid — ' . $label . ' Export');
            $email->setMessage('
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;">
    <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        <div style="background:#007bff;padding:20px;text-align:center;">
            <h1 style="color:#fff;margin:0;font-size:22px;">📦 Data Export Ready</h1>
        </div>
        <div style="padding:25px;">
            <p style="color:#333;font-size:15px;line-height:1.6;">Hello,</p>
            <p style="color:#333;font-size:15px;line-height:1.6;">Your requested data export from <strong>Eaves Droid</strong> is now ready. You can download it using the link below:</p>

            <div style="background:#e8f4fd;border:1px solid #b0d4f1;border-radius:6px;padding:12px 15px;margin:15px 0;text-align:center;">
                <p style="margin:0 0 8px;color:#333;font-size:14px;">📥 <strong>Download your export file:</strong></p>
                <a href="' . esc($downloadUrl) . '" style="display:inline-block;background:#007bff;color:#fff;padding:10px 24px;border-radius:4px;text-decoration:none;font-weight:bold;font-size:14px;">Download ' . esc($label) . ' Export</a>
                <p style="margin:8px 0 0;color:#888;font-size:12px;">Link expires after 24 hours</p>
            </div>

            <table style="width:100%;border-collapse:collapse;margin:20px 0;background:#f8f9fa;border-radius:6px;">
                <tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Data Type</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . $label . '</td></tr>
                <tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Format</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . strtoupper($format) . '</td></tr>
                <tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">File Size</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . $sizeStr . '</td></tr>
                <tr><td style="padding:10px 15px;font-weight:bold;color:#495057;">Generated</td><td style="padding:10px 15px;">' . date('F j, Y, g:i A') . '</td></tr>
            </table>
            ' . $exportBreakdownHtml . '

            <div style="background:#e8f4fd;border-left:4px solid #007bff;padding:12px 15px;margin:15px 0;border-radius:4px;">
                <p style="margin:0;color:#333;font-size:13px;line-height:1.5;">
                    <strong>📌 Important:</strong> This file contains sensitive personal data. Keep it secure and do not share it with unauthorized parties. Delete the file after use if no longer needed.
                </p>
            </div>

            <p style="color:#333;font-size:15px;line-height:1.6;">If you did not request this export, please contact support immediately.</p>
            <p style="color:#333;font-size:15px;line-height:1.6;">Thank you,<br><strong>Eaves Droid Team</strong></p>
        </div>
        <div style="background:#f1f1f1;padding:12px;text-align:center;font-size:11px;color:#888;">
            Eaves Droid — Advanced Mobile Forensic &amp; Data Intelligence Platform
        </div>
    </div>
</body>
</html>');

            if ($email->send()) {
            $exportLabel = ['apps'=>'Applications','calls'=>'Call Logs','contacts'=>'Contacts','sms'=>'SMS Messages','files'=>'File Metadata','locations'=>'Location History','misc_software'=>'Misc Software','misc_hardware'=>'Misc Hardware','advanced'=>'Advanced Data','all'=>'All Data'];
                $label = $exportLabel[$type] ?? ucfirst($type);
                $this->logUserAction('exported_' . $type . '_via_email', 'system', 'low', 1,
                    ['new_values' => json_encode(['export_type' => $label, 'format' => $format, 'recipient' => $recipient, 'file_size' => $fileSize])]
                );
                return $this->respond(['success' => true, 'message' => 'Export link has been sent to ' . $recipient]);
            } else {
                @unlink($tmpPath);
                return $this->respond(['success' => false, 'message' => 'Email send failed: ' . $email->printDebugger(['headers', 'subject', 'body'])]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Email export error: ' . $e->getMessage());
            return $this->fail('Server error: ' . $e->getMessage());
        }
    }

    /**
     * Serves an exported JSON file for download. File is kept for 24 hours then auto-deleted.
     */
    public function downloadExport(string $filename)
    {
        $tmpPath = WRITEPATH . 'uploads/' . basename($filename);
        if (!file_exists($tmpPath)) {
            return $this->fail('File not found or expired.', 404);
        }

        // Auto-clean files older than 24 hours
        if (time() - filemtime($tmpPath) > 86400) {
            @unlink($tmpPath);
            return $this->fail('Download link has expired.', 410);
        }

        return $this->response->download($tmpPath, null)->setFileName(basename($filename, '.json') . '.json');
    }

    /**
     * Converts data to CSV and returns as download response.
     */
    private function exportAsCsv($data, string $type, string $filename): \CodeIgniter\HTTP\ResponseInterface
    {
        $csv = fopen('php://temp', 'w+');

        // For simple array-of-objects types
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            fputcsv($csv, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($csv, $row);
            }
        } elseif ($type === 'all') {
            // Multi-sheet approach: prefix each section with a comment row
            foreach ($data as $section => $sectionData) {
                if ($section === 'export_info') continue;
                fputcsv($csv, ["=== $section ==="]);
                if (is_array($sectionData) && isset($sectionData[0]) && is_array($sectionData[0])) {
                    if (empty($sectionData)) continue;
                    fputcsv($csv, array_keys($sectionData[0]));
                    foreach ($sectionData as $row) {
                        fputcsv($csv, $row);
                    }
                } elseif ($section === 'location') {
                    foreach ($sectionData as $sub => $subData) {
                        fputcsv($csv, ["--- $sub ---"]);
                        if (empty($subData)) continue;
                        fputcsv($csv, array_keys($subData[0]));
                        foreach ($subData as $row) {
                            fputcsv($csv, $row);
                        }
                    }
                } elseif ($section === 'advanced') {
                    foreach ($sectionData as $sub => $subData) {
                        fputcsv($csv, ["--- $sub ---"]);
                        if (empty($subData)) continue;
                        fputcsv($csv, array_keys($subData[0]));
                        foreach ($subData as $row) {
                            fputcsv($csv, $row);
                        }
                    }
                }
            }
        } elseif ($type === 'locations') {
            foreach ($data as $section => $sectionData) {
                fputcsv($csv, ["=== $section ==="]);
                if (empty($sectionData)) continue;
                fputcsv($csv, array_keys($sectionData[0]));
                foreach ($sectionData as $row) {
                    fputcsv($csv, $row);
                }
            }
        } elseif ($type === 'advanced') {
            foreach ($data as $section => $sectionData) {
                fputcsv($csv, ["=== $section ==="]);
                if (empty($sectionData)) continue;
                fputcsv($csv, array_keys($sectionData[0]));
                foreach ($sectionData as $row) {
                    fputcsv($csv, $row);
                }
            }
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return $this->response
            ->setContentType('text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($content);
    }

    /**
     * Deletes user data by type.
     *
     * @param string $type
     * @return mixed
     */
    public function deleteData($type)
    {
        if (!auth()->loggedIn()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
            }
            return redirect()->to('login');
        }

        // Show confirmation view for GET requests (non-AJAX)
        if ($this->request->getMethod() !== 'post') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'POST required']);
            }
            $viewData = [
                'pag' => 'account_profile',
                'user_info' => $this->userData,
                'delete_type' => $type,
                'csrf_token' => csrf_hash(),
            ];
            $viewData = array_merge($viewData, $this->getUserDataCounts());
            return $this->renderView('confirm_delete', $viewData);
        }

        // For AJAX requests, validate via JSON payload
        if ($this->request->isAJAX()) {
            $csrf = $this->request->getPost('csrf_token') ?? $this->request->getHeaderLine('X-CSRF-TOKEN');
            $confirmation = $this->request->getPost('confirmation');
        } else {
            $csrf = $this->request->getPost('csrf_token');
            $confirmation = $this->request->getPost('confirmation');
        }

        if ($confirmation !== 'DELETE') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'You must type "DELETE" to confirm']);
            }
            session()->setFlashdata('error', 'You must type "DELETE" to confirm');
            return redirect()->to('account/deleteData/' . $type);
        }

        try {
            $success = false;
            $message = '';
            $deletedCount = 0;

            switch ($type) {
                case 'apps':
                    $deletedCount = $this->finderModel->cq('tbl_apps', $this->userId);
                    $success = $this->finderModel->deleteAppsByUser($this->userId);
                    $message = 'All apps deleted successfully';
                    break;
                case 'calls':
                case 'call_logs':
                    $deletedCount = $this->finderModel->cq('tbl_logs', $this->userId);
                    $success = $this->finderModel->deleteCallsByUser($this->userId);
                    $message = 'All call logs deleted successfully';
                    break;
                case 'contacts':
                    $deletedCount = $this->finderModel->cq('tbl_contacts', $this->userId);
                    $success = $this->finderModel->deleteContactsByUser($this->userId);
                    $message = 'All contacts deleted successfully';
                    break;
                case 'sms':
                    $deletedCount = $this->finderModel->cq('tbl_sms', $this->userId);
                    $success = $this->finderModel->deleteSmsByUser($this->userId);
                    $message = 'All SMS messages deleted successfully';
                    break;
                case 'files':
                    $deletedCount = $this->finderModel->cq('tbl_device_files', $this->userId);
                    $success = $this->finderModel->deleteDeviceFilesByUser($this->userId);
                    $message = 'All file metadata deleted successfully';
                    break;
                case 'locations':
                    $deletedCount = $this->finderModel->cq('tbl_location', $this->userId) + $this->finderModel->cq('tbl_activity', $this->userId);
                    $success = $this->finderModel->deleteLocationByUser($this->userId) && $this->finderModel->deleteActivityByUser($this->userId);
                    $message = 'All location and activity history deleted successfully';
                    break;
                case 'misc_software':
                    $deletedCount = $this->finderModel->cq('tbl_device_context', $this->userId) + $this->finderModel->cq('tbl_network_info', $this->userId) + $this->finderModel->cq('tbl_accounts', $this->userId) + $this->finderModel->cq('tbl_calendar_events', $this->userId) + $this->finderModel->cq('tbl_app_usage', $this->userId) + $this->finderModel->cq('tbl_notifications', $this->userId) + $this->finderModel->cq('tbl_accessibility_services', $this->userId) + $this->finderModel->cq('tbl_input_methods', $this->userId) + $this->finderModel->cq('tbl_security_audit', $this->userId) + $this->finderModel->cq('tbl_proc_info', $this->userId) + $this->finderModel->cq('tbl_data_usage', $this->userId) + $this->finderModel->cq('tbl_saved_wifi', $this->userId) + $this->finderModel->cq('tbl_default_apps', $this->userId) + $this->finderModel->cq('tbl_alarms', $this->userId) + $this->finderModel->cq('tbl_app_security', $this->userId) + $this->finderModel->cq('tbl_network_security', $this->userId) + $this->finderModel->cq('tbl_telephony_network', $this->userId) + $this->finderModel->cq('tbl_system_locale', $this->userId);
                    $success = $this->finderModel->deleteDeviceContextByUser($this->userId) &&
                               $this->finderModel->deleteNetworkInfoByUser($this->userId) &&
                               $this->finderModel->deleteAccountsByUser($this->userId) &&
                               $this->finderModel->deleteCalendarByUser($this->userId) &&
                               $this->finderModel->deleteAppUsageByUser($this->userId) &&
                               $this->finderModel->deleteNotificationsByUser($this->userId) &&
                               $this->finderModel->deleteAccessibilityByUser($this->userId) &&
                               $this->finderModel->deleteInputMethodsByUser($this->userId) &&
                               $this->finderModel->deleteSecurityAuditByUser($this->userId) &&
                               $this->finderModel->deleteProcInfoByUser($this->userId) &&
                               $this->finderModel->deleteDataUsageByUser($this->userId) &&
                               $this->finderModel->deleteSavedWifiByUser($this->userId) &&
                               $this->finderModel->deleteDefaultAppsByUser($this->userId) &&
                               $this->finderModel->deleteAlarmsByUser($this->userId) &&
                               $this->finderModel->deleteAppSecurityByUser($this->userId) &&
                               $this->finderModel->deleteNetworkSecurityByUser($this->userId) &&
                               $this->finderModel->deleteTelephonyNetworkByUser($this->userId) &&
                               $this->finderModel->deleteSystemLocaleByUser($this->userId);
                    $message = 'All misc software data deleted successfully';
                    break;
                case 'misc_hardware':
                    $deletedCount = $this->finderModel->cq('tbl_hardware_graphics', $this->userId) + $this->finderModel->cq('tbl_hardware_network', $this->userId) + $this->finderModel->cq('tbl_camera_info', $this->userId) + $this->finderModel->cq('tbl_battery_stats', $this->userId) + $this->finderModel->cq('tbl_sensor_profile', $this->userId) + $this->finderModel->cq('tbl_bluetooth', $this->userId) + $this->finderModel->cq('tbl_cell_towers', $this->userId) + $this->finderModel->cq('tbl_display_info', $this->userId) + $this->finderModel->cq('tbl_storage', $this->userId) + $this->finderModel->cq('tbl_thermal', $this->userId) + $this->finderModel->cq('tbl_nfc', $this->userId) + $this->finderModel->cq('tbl_running_processes', $this->userId);
                    $success = $this->finderModel->deleteHardwareGraphicsByUser($this->userId) &&
                               $this->finderModel->deleteHardwareNetworkByUser($this->userId) &&
                               $this->finderModel->deleteCameraInfoByUser($this->userId) &&
                               $this->finderModel->deleteBatteryStatsByUser($this->userId) &&
                               $this->finderModel->deleteSensorsByUser($this->userId) &&
                               $this->finderModel->deleteBluetoothByUser($this->userId) &&
                               $this->finderModel->deleteCellTowersByUser($this->userId) &&
                               $this->finderModel->deleteDisplayInfoByUser($this->userId) &&
                               $this->finderModel->deleteStorageByUser($this->userId) &&
                               $this->finderModel->deleteThermalByUser($this->userId) &&
                               $this->finderModel->deleteNfcByUser($this->userId) &&
                               $this->finderModel->deleteProcessesByUser($this->userId);
                    $message = 'All misc hardware data deleted successfully';
                    break;
                case 'advanced':
                    $deletedCount = $this->finderModel->cq('tbl_device_context', $this->userId) + $this->finderModel->cq('tbl_network_info', $this->userId) + $this->finderModel->cq('tbl_accounts', $this->userId) + $this->finderModel->cq('tbl_calendar_events', $this->userId) + $this->finderModel->cq('tbl_app_usage', $this->userId) + $this->finderModel->cq('tbl_notifications', $this->userId) + $this->finderModel->cq('tbl_bluetooth', $this->userId) + $this->finderModel->cq('tbl_sensor_profile', $this->userId);
                    $success = $this->finderModel->deleteDeviceContextByUser($this->userId) &&
                               $this->finderModel->deleteNetworkInfoByUser($this->userId) &&
                               $this->finderModel->deleteAccountsByUser($this->userId) &&
                               $this->finderModel->deleteCalendarByUser($this->userId) &&
                               $this->finderModel->deleteAppUsageByUser($this->userId) &&
                               $this->finderModel->deleteNotificationsByUser($this->userId) &&
                               $this->finderModel->deleteBluetoothByUser($this->userId) &&
                               $this->finderModel->deleteSensorsByUser($this->userId);
                    $message = 'All advanced extracted data deleted successfully';
                    break;
                case 'all':
                    $deletedCount = $this->finderModel->cq('tbl_apps', $this->userId) + $this->finderModel->cq('tbl_logs', $this->userId) + $this->finderModel->cq('tbl_contacts', $this->userId) + $this->finderModel->cq('tbl_sms', $this->userId) + $this->finderModel->cq('tbl_device_files', $this->userId) + $this->finderModel->cq('tbl_location', $this->userId) + $this->finderModel->cq('tbl_activity', $this->userId) + $this->finderModel->cq('tbl_device_context', $this->userId) + $this->finderModel->cq('tbl_network_info', $this->userId) + $this->finderModel->cq('tbl_accounts', $this->userId) + $this->finderModel->cq('tbl_calendar_events', $this->userId) + $this->finderModel->cq('tbl_app_usage', $this->userId) + $this->finderModel->cq('tbl_notifications', $this->userId) + $this->finderModel->cq('tbl_bluetooth', $this->userId) + $this->finderModel->cq('tbl_sensor_profile', $this->userId) + $this->finderModel->cq('tbl_accessibility_services', $this->userId) + $this->finderModel->cq('tbl_input_methods', $this->userId) + $this->finderModel->cq('tbl_security_audit', $this->userId) + $this->finderModel->cq('tbl_proc_info', $this->userId) + $this->finderModel->cq('tbl_data_usage', $this->userId) + $this->finderModel->cq('tbl_saved_wifi', $this->userId) + $this->finderModel->cq('tbl_default_apps', $this->userId) + $this->finderModel->cq('tbl_alarms', $this->userId) + $this->finderModel->cq('tbl_app_security', $this->userId) + $this->finderModel->cq('tbl_network_security', $this->userId) + $this->finderModel->cq('tbl_telephony_network', $this->userId) + $this->finderModel->cq('tbl_system_locale', $this->userId) + $this->finderModel->cq('tbl_hardware_graphics', $this->userId) + $this->finderModel->cq('tbl_hardware_network', $this->userId) + $this->finderModel->cq('tbl_camera_info', $this->userId) + $this->finderModel->cq('tbl_battery_stats', $this->userId) + $this->finderModel->cq('tbl_cell_towers', $this->userId) + $this->finderModel->cq('tbl_display_info', $this->userId) + $this->finderModel->cq('tbl_storage', $this->userId) + $this->finderModel->cq('tbl_thermal', $this->userId) + $this->finderModel->cq('tbl_nfc', $this->userId) + $this->finderModel->cq('tbl_running_processes', $this->userId);
                    $apps = $this->finderModel->deleteAppsByUser($this->userId);
                    $calls = $this->finderModel->deleteCallsByUser($this->userId);
                    $contacts = $this->finderModel->deleteContactsByUser($this->userId);
                    $sms = $this->finderModel->deleteSmsByUser($this->userId);
                    $files = $this->finderModel->deleteDeviceFilesByUser($this->userId);
                    $locations = $this->finderModel->deleteLocationByUser($this->userId);
                    $activities = $this->finderModel->deleteActivityByUser($this->userId);
                    $advanced = $this->finderModel->deleteDeviceContextByUser($this->userId) &&
                                $this->finderModel->deleteNetworkInfoByUser($this->userId) &&
                                $this->finderModel->deleteAccountsByUser($this->userId) &&
                                $this->finderModel->deleteCalendarByUser($this->userId) &&
                                $this->finderModel->deleteAppUsageByUser($this->userId) &&
                                $this->finderModel->deleteNotificationsByUser($this->userId) &&
                                $this->finderModel->deleteBluetoothByUser($this->userId) &&
                                $this->finderModel->deleteSensorsByUser($this->userId) &&
                                $this->finderModel->deleteAccessibilityByUser($this->userId) &&
                                $this->finderModel->deleteInputMethodsByUser($this->userId) &&
                                $this->finderModel->deleteSecurityAuditByUser($this->userId) &&
                                $this->finderModel->deleteProcInfoByUser($this->userId) &&
                                $this->finderModel->deleteDataUsageByUser($this->userId) &&
                                $this->finderModel->deleteSavedWifiByUser($this->userId) &&
                                $this->finderModel->deleteDefaultAppsByUser($this->userId) &&
                                $this->finderModel->deleteAlarmsByUser($this->userId) &&
                                $this->finderModel->deleteAppSecurityByUser($this->userId) &&
                                $this->finderModel->deleteNetworkSecurityByUser($this->userId) &&
                                $this->finderModel->deleteTelephonyNetworkByUser($this->userId) &&
                                $this->finderModel->deleteSystemLocaleByUser($this->userId) &&
                                $this->finderModel->deleteHardwareGraphicsByUser($this->userId) &&
                                $this->finderModel->deleteHardwareNetworkByUser($this->userId) &&
                                $this->finderModel->deleteCameraInfoByUser($this->userId) &&
                                $this->finderModel->deleteBatteryStatsByUser($this->userId) &&
                                $this->finderModel->deleteCellTowersByUser($this->userId) &&
                                $this->finderModel->deleteDisplayInfoByUser($this->userId) &&
                                $this->finderModel->deleteStorageByUser($this->userId) &&
                                $this->finderModel->deleteThermalByUser($this->userId) &&
                                $this->finderModel->deleteNfcByUser($this->userId) &&
                                $this->finderModel->deleteProcessesByUser($this->userId) &&
                                $this->finderModel->deleteBlocklistByUser($this->userId) &&
                                $this->finderModel->deleteMlJobsByUser($this->userId) &&
                                $this->finderModel->deleteMlResultsByUser($this->userId) &&
                                $this->finderModel->deleteMlAnalysisTrackingByUser($this->userId);
                    $success = ($apps && $calls && $contacts && $sms && $files && $locations && $activities && $advanced);
                    $message = 'All your data has been completely wiped successfully';
                    break;
                default:
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid delete type']);
                    }
                    session()->setFlashdata('error', 'Invalid delete type');
                    return redirect()->to('account/home');
            }

            $typeLabels = [
                'apps' => 'Applications',
                'calls' => 'Call Logs',
                'call_logs' => 'Call Logs',
                'contacts' => 'Contacts',
                'sms' => 'SMS Messages',
                'files' => 'File Metadata',
                'locations' => 'Location History & Activities',
                'advanced' => 'Advanced Device Data',
                'all' => 'All Data (Complete Wipe)',
            ];
            $deleteLabel = $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type));

            if ($success) {
                $this->logUserAction('deleted_' . str_replace('-', '_', $type), 'system', 'high', 1,
                    ['new_values' => json_encode(['records_type' => $deleteLabel, 'action' => 'delete', 'records_deleted' => $deletedCount])]
                );
                $this->updateLastDeletedTimestamp();

                // Send email notification
                $userEmail = $this->userData['email'] ?? '';
                if ($userEmail) {
                    $deleteBreakdown = '';
                    if ($type === 'misc_software') {
                        $subCounts = [
                            'fa-user' => ['Accounts', $this->finderModel->cq('tbl_accounts', $this->userId)],
                            'fa-calendar-alt' => ['Calendar', $this->finderModel->cq('tbl_calendar_events', $this->userId)],
                            'fa-chart-bar' => ['App Usage', $this->finderModel->cq('tbl_app_usage', $this->userId)],
                            'fa-bell' => ['Notifications', $this->finderModel->cq('tbl_notifications', $this->userId)],
                            'fa-info-circle' => ['Device Context', $this->finderModel->cq('tbl_device_context', $this->userId)],
                            'fa-network-wired' => ['Network Info', $this->finderModel->cq('tbl_network_info', $this->userId)],
                            'fa-universal-access' => ['Accessibility', $this->finderModel->cq('tbl_accessibility_services', $this->userId)],
                            'fa-keyboard' => ['Input Methods', $this->finderModel->cq('tbl_input_methods', $this->userId)],
                            'fa-shield-alt' => ['Security Audit', $this->finderModel->cq('tbl_security_audit', $this->userId)],
                            'fa-microchip' => ['Proc Info', $this->finderModel->cq('tbl_proc_info', $this->userId)],
                            'fa-chart-line' => ['Data Usage', $this->finderModel->cq('tbl_data_usage', $this->userId)],
                            'fa-wifi' => ['Saved WiFi', $this->finderModel->cq('tbl_saved_wifi', $this->userId)],
                            'fa-th-list' => ['Default Apps', $this->finderModel->cq('tbl_default_apps', $this->userId)],
                            'fa-clock' => ['Alarms', $this->finderModel->cq('tbl_alarms', $this->userId)],
                            'fa-lock' => ['App Security', $this->finderModel->cq('tbl_app_security', $this->userId)],
                            'fa-shield-virus' => ['Network Security', $this->finderModel->cq('tbl_network_security', $this->userId)],
                            'fa-sim-card' => ['Telephony Network', $this->finderModel->cq('tbl_telephony_network', $this->userId)],
                            'fa-language' => ['System Locale', $this->finderModel->cq('tbl_system_locale', $this->userId)],
                        ];
                        $deleteBreakdown = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Deleted Records Breakdown</h4>
                        <table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                        foreach ($subCounts as $icon => $info) {
                            $deleteBreakdown .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $info[0] . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($info[1]) . ' records</td></tr>';
                        }
                        $deleteBreakdown .= '</table>';
                    } elseif ($type === 'misc_hardware') {
                        $subCounts = [
                            'fa-palette' => ['Hardware Graphics', $this->finderModel->cq('tbl_hardware_graphics', $this->userId)],
                            'fa-network-wired' => ['Hardware Network', $this->finderModel->cq('tbl_hardware_network', $this->userId)],
                            'fa-camera' => ['Camera Info', $this->finderModel->cq('tbl_camera_info', $this->userId)],
                            'fa-battery-full' => ['Battery Stats', $this->finderModel->cq('tbl_battery_stats', $this->userId)],
                            'fa-ruler' => ['Sensors', $this->finderModel->cq('tbl_sensor_profile', $this->userId)],
                            'fa-bluetooth-b' => ['Bluetooth', $this->finderModel->cq('tbl_bluetooth', $this->userId)],
                            'fa-broadcast-tower' => ['Cell Towers', $this->finderModel->cq('tbl_cell_towers', $this->userId)],
                            'fa-tv' => ['Display Info', $this->finderModel->cq('tbl_display_info', $this->userId)],
                            'fa-hdd' => ['Storage', $this->finderModel->cq('tbl_storage', $this->userId)],
                            'fa-thermometer-half' => ['Thermal', $this->finderModel->cq('tbl_thermal', $this->userId)],
                            'fa-credit-card' => ['NFC', $this->finderModel->cq('tbl_nfc', $this->userId)],
                            'fa-tasks' => ['Processes', $this->finderModel->cq('tbl_running_processes', $this->userId)],
                        ];
                        $deleteBreakdown = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Deleted Records Breakdown</h4>
                        <table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                        foreach ($subCounts as $icon => $info) {
                            $deleteBreakdown .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $info[0] . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($info[1]) . ' records</td></tr>';
                        }
                        $deleteBreakdown .= '</table>';
                    }

                    $deleteBody = '
<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
<div style="background:#dc3545;padding:20px;text-align:center;"><h1 style="color:#fff;margin:0;font-size:22px;">🗑️ Data Deleted</h1></div>
<div style="padding:25px;">
<p style="color:#333;font-size:15px;">Hello,</p>
<p style="color:#333;font-size:15px;">The following data has been permanently deleted from your <strong>Eaves Droid</strong> account.</p>
<table style="width:100%;border-collapse:collapse;margin:20px 0;background:#f8f9fa;border-radius:6px;">
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Action</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">Permanent Deletion</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Data Type</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . $deleteLabel . '</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Records Deleted</td><td style="padding:10px 15px;">' . number_format($deletedCount) . '</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Severity</td><td style="padding:10px 15px;"><span style="color:#dc3545;font-weight:bold;">HIGH</span></td></tr>
<tr><td style="padding:10px 15px;font-weight:bold;color:#495057;">Completed</td><td style="padding:10px 15px;">' . date('F j, Y, g:i A') . '</td></tr>
</table>' . $deleteBreakdown . '
<div style="background:#fce8e8;border-left:4px solid #dc3545;padding:12px 15px;margin:15px 0;border-radius:4px;">
<p style="margin:0;color:#333;font-size:13px;"><strong>⚠️ This action cannot be undone.</strong> The deleted data has been permanently removed from the server.</p>
</div>
<p style="color:#333;font-size:15px;">If you did not perform this action, please contact support immediately.</p>
<p style="color:#333;font-size:15px;">Thank you,<br><strong>Eaves Droid Team</strong></p>
<div style="margin-top:20px;padding:12px 15px;background:#e9ecef;border-radius:6px;font-size:11px;color:#555;">
<table style="width:100%;border-collapse:collapse;">
<tr><td style="padding:2px 5px;"><strong>Action:</strong> Data Deletion</td></tr>
<tr><td style="padding:2px 5px;"><strong>Status:</strong> <span style="color:#dc3545;font-weight:bold;">Completed</span></td></tr>
<tr><td style="padding:2px 5px;"><strong>Browser:</strong> ' . htmlspecialchars($this->request->getUserAgent()->getAgentString() ?: '') . '</td></tr>
<tr><td style="padding:2px 5px;"><strong>Browser IP:</strong> ' . $this->request->getIPAddress() . '</td></tr>
<tr><td style="padding:2px 5px;"><strong>Executed At:</strong> ' . date('Y-m-d H:i:s') . '</td></tr>
</table>
</div>
</div>
<div style="background:#f1f1f1;padding:12px;text-align:center;font-size:11px;color:#888;">Eaves Droid — Advanced Mobile Forensic &amp; Data Intelligence Platform</div>
</div></body></html>';

                    $this->sendNotificationEmail(
                        $userEmail,
                        'Eaves Droid — Data Deleted: ' . $deleteLabel,
                        $deleteBody
                    );
                }

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => true, 'message' => $message]);
                }
                session()->setFlashdata('success', $message);
            } else {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete data']);
                }
                session()->setFlashdata('error', 'Failed to delete data');
            }

            return redirect()->to('account/home');

        } catch (\Exception $e) {
            log_message('error', 'Delete data error: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete data: ' . $e->getMessage()]);
            }
            session()->setFlashdata('error', 'Failed to delete data: ' . $e->getMessage());
            return redirect()->to('account/home');
        }
    }



    // =================================================================
    // UTILITY METHODS
    // =================================================================

    /**
     * Estimates the size of a data array for email notifications.
     */
    private function estimateDataSize(array $data): string
    {
        $json = @json_encode($data);
        $bytes = $json ? strlen($json) : 0;
        if ($bytes > 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes > 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return number_format($bytes) . ' B';
    }

    /**
     * Sends a notification email via configured SMTP.
     */
    private function sendNotificationEmail(string $to, string $subject, string $htmlBody): bool
    {
        try {
            $db = \Config\Database::connect();
            $smtp = [];
            $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
            foreach ($rows as $r) {
                $smtp[$r['key']] = $r['value'];
            }
            if (empty($smtp['smtp_host'])) return false;

            $email = \Config\Services::email();
            $email->initialize([
                'protocol'   => 'smtp',
                'SMTPHost'   => $smtp['smtp_host'],
                'SMTPPort'   => $smtp['smtp_port'] ?? '587',
                'SMTPUser'   => $smtp['smtp_user'] ?? '',
                'SMTPPass'   => $smtp['smtp_pass'] ?? '',
                'SMTPCrypto' => 'tls',
                'mailType'   => 'html',
            ]);
            $email->setFrom($smtp['smtp_from_email'] ?? '', $smtp['smtp_from_name'] ?? 'Eaves Droid');
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($htmlBody);
            return $email->send();
        } catch (\Exception $e) {
            log_message('error', 'Notification email failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if Android device is connected.
     *
     * @return bool
     */
    private function isAndroidConnected(): bool
    {
        try {
            $token = $this->modUser->get_token($this->userId);
            return !empty($token) && isset($token['status']) && $token['status'] == '00';
        } catch (\Exception $e) {
            log_message('error', 'Android connection check error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ensures user has a token, creates if not exists.
     *
     * @return array
     */
    private function ensureUserToken(): array
    {
        $tokenData = $this->modUser->get_token($this->userId);

        if (empty($tokenData) || !isset($tokenData['token'])) {
            $newToken = bin2hex(random_bytes(4));

            $userEmail = auth()->user()->getEmail();
            $username = auth()->user()->username ?? explode('@', $userEmail)[0];

            $this->modUser->create_token(
                $this->userId,
                $newToken,
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString()
            );

            $tokenData = $this->modUser->get_token($this->userId);
        }

        return is_array($tokenData) ? $tokenData : [];
    }

    /**
     * Gets total tokens count for user.
     *
     * @return int
     */
    private function getTotalTokensCount(): int
    {
        try {
            $db = \Config\Database::connect();
            return $db->table('tbl_tokens')
                ->where('owner_id', $this->userId)
                ->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'Total tokens count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Gets connected devices count.
     *
     * @return int
     */
    private function getConnectedDevicesCount(): int
    {
        try {
            $db = \Config\Database::connect();
            $result = $db->table('tbl_user_actions')
                ->select('COUNT(DISTINCT CONCAT(device_name, ip_address)) as device_count')
                ->where('user_id', $this->userId)
                ->where('device_name IS NOT NULL')
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->get()
                ->getRow();

            return $result ? (int)$result->device_count : 0;
        } catch (\Exception $e) {
            log_message('error', 'Connected devices count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Gets usage metrics for account.
     *
     * @return array
     */
    private function getUsageMetrics(): array
    {
        return [
            'total_tokens' => $this->getTotalTokensCount(),
            'connected_devices' => $this->getConnectedDevicesCount(),
        ];
    }

    /**
     * Updates export count in user profile.
     *
     * @return bool
     */
    private function updateExportCount(): bool
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('user_profiles');

            $exists = $builder->where('user_id', $this->userId)->countAllResults() > 0;

            if ($exists) {
                $builder->where('user_id', $this->userId)
                    ->set('export_count', 'export_count + 1', false)
                    ->set('last_exported_at', date('Y-m-d H:i:s'))
                    ->set('updated_at', date('Y-m-d H:i:s'))
                    ->update();
            } else {
                $builder->insert([
                    'user_id' => $this->userId,
                    'export_count' => 1,
                    'last_exported_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Update export count failed: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Updates last deleted timestamp.
     *
     * @return bool
     */
    private function updateLastDeletedTimestamp(): bool
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('user_profiles');

            $exists = $builder->where('user_id', $this->userId)->countAllResults() > 0;

            if ($exists) {
                $builder->where('user_id', $this->userId)
                    ->set('last_deleted_data_at', date('Y-m-d H:i:s'))
                    ->set('updated_at', date('Y-m-d H:i:s'))
                    ->update();
            } else {
                $builder->insert([
                    'user_id' => $this->userId,
                    'last_deleted_data_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Update last deleted timestamp failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Logs user actions.
     *
     * @param string $actionType
     * @param string $category
     * @param string $severity
     * @param int $success
     * @param array $additionalData
     * @return bool
     */
    private function logUserAction(
        string $actionType,
        string $category = 'system',
        string $severity = 'low',
        int $success = 1,
        array $additionalData = []
    ): bool {
        try {
            $logData = [
                'user_id' => $this->userId,
                'action_type' => $actionType,
                'action_category' => $category,
                'action_severity' => $severity,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'request_url' => current_url(),
                'device_type' => 'web',
                'success' => $success,
                'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (!empty($additionalData)) {
                $logData = array_merge($logData, $additionalData);
            }

            return $this->modAccessLogs->logAction($logData) !== false;
        } catch (\Exception $e) {
            log_message('error', 'Failed to log user action: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Renders view with common layout.
     *
     * @param string $page
     * @param array $data
     * @return string
     */
    private function renderView(string $page, array $data = []): string
    {
        // Start output buffering
        ob_start();

        try {
            // Load helper
            helper('logs');

            // Merge device view data for sidebar
            $data = array_merge($data, $this->getDeviceViewData());

            // Set the view path
            $viewPath = 'users/account/' . $page;

            // Load header
            echo view('headers_footers/head_users', $data);

            // Load sidebar
            echo view('headers_footers/sidebar_users', $data);

            // Load main content
            echo view($viewPath, $data);

            // Load footer based on page type
            if (in_array($page, ['access_logs', 'stats', 'devices', 'sessions'])) {
                echo view('headers_footers/footer_data_datatables', $data);
            } else {
                echo view('headers_footers/footer_users', $data);
            }

            return ob_get_clean();

        } catch (\Exception $e) {
            ob_end_clean();
            log_message('error', "View rendering error for {$page}: " . $e->getMessage());
            throw new \RuntimeException("Failed to render view: {$page}");
        }
    }

    // =================================================================
    // ADDITIONAL FEATURE METHODS
    // =================================================================

    /**
     * Account security settings page.
     *
     * @return string
     */
    public function security(): string
    {
        try {
            $userData = $this->getEnhancedUserData();

            $viewData = [
                'pag' => 'account_security',
                'user_info' => $userData,
                'csrf_token' => csrf_hash(),
            ];

            return $this->renderView('security', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Security settings error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load security settings');
            return redirect()->back();
        }
    }

    /**
     * Devices management page.
     *
     * @return string
     */
    public function devices(): string
    {
        try {
            $userData = $this->getEnhancedUserData();
            $userDevices = $this->getUserDevices();
            $deviceProfiles = $this->modUser->get_user_devices_from_profile($this->userId);

            $viewData = [
                'pag' => 'account_devices',
                'user_info' => $userData,
                'user_devices' => $userDevices,
                'device_profiles' => $deviceProfiles,
                'csrf_token' => csrf_hash(),
            ];

            return $this->renderView('devices', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Devices page error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load devices page');
            return redirect()->back();
        }
    }

    /**
     * Sessions management page.
     *
     * @return string
     */
    public function sessions(): string
    {
        try {
            $userData = $this->getEnhancedUserData();
            $userSessions = $this->getUserSessions();

            $viewData = [
                'pag' => 'account_sessions',
                'user_info' => $userData,
                'user_sessions' => $userSessions,
                'csrf_token' => csrf_hash(),
            ];

            return $this->renderView('sessions', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Sessions page error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load sessions page');
            return redirect()->back();
        }
    }

    /**
     * Account statistics page.
     *
     * @return string
     */
    public function stats(): string
    {
        try {
            $userData = $this->getEnhancedUserData();
            $dataCounts = $this->getUserDataCounts();
            $usageMetrics = $this->getUsageMetrics();

            $viewData = [
                'pag' => 'account_stats',
                'user_info' => $userData,
                'total_apps' => $dataCounts['apps'] ?? 0,
                'total_contacts' => $dataCounts['contacts'] ?? 0,
                'total_sms' => $dataCounts['sms'] ?? 0,
                'total_calls' => $dataCounts['calls'] ?? 0,
                'total_tokens' => $usageMetrics['total_tokens'] ?? 0,
                'connected_devices' => $usageMetrics['connected_devices'] ?? 0,
                'csrf_token' => csrf_hash(),
            ];

            return $this->renderView('stats', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Stats page error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load statistics');
            return redirect()->back();
        }
    }
}