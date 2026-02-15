<?php

namespace App\Controllers\clients;


use App\Models\Mod_Finder;
use App\Models\Mod_User;
use App\Models\Mod_Access_Logs;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Files\File;

class Account extends BaseClientController
{
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
            // Get enhanced user data
            $userData = $this->getEnhancedUserData();

            // Get user variables
            $userVars = $this->getUserVars();

            // Ensure user has a token
            $userToken = $this->ensureUserToken();

            // Check Android connection status
            $androidConnected = $this->isAndroidConnected();

            // Get user data counts from BaseClientController
            $dataCounts = $this->getUserDataCounts();

            // Get usage metrics
            $usageMetrics = $this->getUsageMetrics();

            $viewData = [
                'pag' => 'account_profile',
                'user_info' => $userData,
                'user_vars' => $userVars,
                'user_token' => $userToken,
                'android_connected' => $androidConnected,
                // Map BaseClientController keys (total_*) if Account expects them, or just merge
                // BaseClientController returns ['total_apps' => ..., 'total_files' => ...]
                // Account::home used $dataCounts['apps']. I will update to use the merged array directly or map it.
                // Best to simple merge and use the keys in view if view expects total_*.
                // BUT Account view 'card' might expect 'total_apps'.
                // Let's just merge $dataCounts.
                'total_tokens' => $usageMetrics['total_tokens'] ?? 0,
                'connected_devices' => $usageMetrics['connected_devices'] ?? 0,
                'csrf_token' => csrf_hash(),
            ];
            
            $viewData = array_merge($viewData, $dataCounts);

            return $this->renderView('profile', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Account home error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load account information');
            return redirect()->back();
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

            // Get user devices
            $userDevices = $this->getUserDevices();

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
                'user_devices' => $userDevices,
                'recent_files' => $this->getRecentFiles(),
                'activeSessions' => $this->modUser->get_active_sessions_count($this->userId),
                'securityEvents' => $this->modUser->get_security_events_count($this->userId),
                'total_tokens' => $usageMetrics['total_tokens'] ?? 0,
                'connected_devices' => $usageMetrics['connected_devices'] ?? 0,
                'tokenExpiry' => isset($userToken['expires_at']) ? date('M d, Y H:i', strtotime($userToken['expires_at'])) : 'Never',
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
                    'ip_address' => $device['device_ip_address'] ?? 'N/A',
                    'last_seen' => $lastSeenTime ? date('Y-m-d H:i:s', $lastSeenTime) : 'N/A',
                    'last_seen_formatted' => $lastSeenTime ? date('M d, Y H:i', $lastSeenTime) : 'Never'
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

            $allLogs[] = $log;

            // Count statuses
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }

        // Filter logs based on selected tab
        switch ($tab) {
            case 'web':
                $displayLogs = $webLogs;
                break;
            case 'android':
                $displayLogs = $androidLogs;
                break;
            default:
                $displayLogs = $allLogs;
                break;
        }

        // Sort logs by timestamp (newest first)
        usort($displayLogs, function($a, $b) {
            return ($b['Timestamps'] ?? 0) <=> ($a['Timestamps'] ?? 0);
        });

        usort($webLogs, function($a, $b) {
            return ($b['Timestamps'] ?? 0) <=> ($a['Timestamps'] ?? 0);
        });

        usort($androidLogs, function($a, $b) {
            return ($b['Timestamps'] ?? 0) <=> ($a['Timestamps'] ?? 0);
        });

        // Get last updated timestamp
        $lastUpdated = $this->getLastUpdated($displayLogs);

        return [
            'user_logs' => $displayLogs,
            'webLogs' => $webLogs,
            'androidLogs' => $androidLogs,
            'webLogsCount' => count($webLogs),
            'androidLogsCount' => count($androidLogs),
            'totalLogs' => count($allLogs),
            'successfulLogins' => $statusCounts['success'],
            'failedAttempts' => $statusCounts['failed'],
            'suspiciousActivities' => $statusCounts['suspicious'],
            'lastUpdated' => $lastUpdated,
            'totalByDevice' => [
                'web' => count($webLogs),
                'android' => count($androidLogs),
                'unknown' => count($allLogs) - count($webLogs) - count($androidLogs)
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
        return date('Y-m-d H:i:s', $latest);
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

        try {
            switch ($type) {
                case 'apps':
                    $data = $this->finderModel->get_apps($this->userId, 1000);
                    $filename = 'apps_export_' . date('Y-m-d_H-i-s') . '.json';
                    break;
                case 'calls':
                    $data = $this->finderModel->get_call_logs($this->userId, 1000);
                    $filename = 'calls_export_' . date('Y-m-d_H-i-s') . '.json';
                    break;
                case 'contacts':
                    $data = $this->finderModel->get_contacts($this->userId, 1000);
                    $filename = 'contacts_export_' . date('Y-m-d_H-i-s') . '.json';
                    break;
                case 'sms':
                    $data = $this->finderModel->get_sms($this->userId, 1000);
                    $filename = 'sms_export_' . date('Y-m-d_H-i-s') . '.json';
                    break;
                case 'all':
                    $data = [
                        'apps' => $this->finderModel->get_apps($this->userId, 1000),
                        'calls' => $this->finderModel->get_call_logs($this->userId, 1000),
                        'contacts' => $this->finderModel->get_contacts($this->userId, 1000),
                        'sms' => $this->finderModel->get_sms($this->userId, 1000),
                        'export_info' => [
                            'exported_at' => date('Y-m-d H:i:s'),
                            'user_id' => $this->userId,
                            'user_email' => auth()->user()->getEmail(),
                        ],
                    ];
                    $filename = 'complete_export_' . date('Y-m-d_H-i-s') . '.json';
                    break;
                default:
                    session()->setFlashdata('error', 'Invalid export type');
                    return redirect()->to('account/home');
            }

            if (empty($data) || (isset($data['error']) && $data['error'])) {
                 session()->setFlashdata('error', 'No data found to export or error occurred.');
                 return redirect()->to('account/home');
            }

            // Log export action
            $this->logUserAction('data_export_' . $type, 'system', 'low', 1);

            // Update export count
            $this->updateExportCount();
            
            // Encode JSON with error checking
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
     * Deletes user data by type.
     *
     * @param string $type
     * @return mixed
     */
    public function deleteData($type)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        // Show confirmation view for GET requests
        if ($this->request->getMethod() !== 'post') {
            return $this->renderView('confirm_delete', [
                'pag' => 'account_profile',
                'user_info' => $this->userData,
                'delete_type' => $type,
                'csrf_token' => csrf_hash(),
            ]);
        }

        // Validate CSRF token
        if (!csrf_hash($this->request->getPost('csrf_token'))) {
            session()->setFlashdata('error', 'Invalid security token');
            return redirect()->to('account/home');
        }

        // Validate confirmation text
        if ($this->request->getPost('confirmation') !== 'DELETE') {
            session()->setFlashdata('error', 'You must type "DELETE" to confirm');
            return redirect()->to('account/deleteData/' . $type);
        }

        try {
            $success = false;
            $message = '';

            switch ($type) {
                case 'apps':
                    $success = $this->finderModel->deleteAppsByUser($this->userId);
                    $message = 'All apps deleted successfully';
                    break;
                case 'calls':
                case 'call_logs':
                    $success = $this->finderModel->deleteCallsByUser($this->userId);
                    $message = 'All call logs deleted successfully';
                    break;
                case 'contacts':
                    $success = $this->finderModel->deleteContactsByUser($this->userId);
                    $message = 'All contacts deleted successfully';
                    break;
                case 'sms':
                    $success = $this->finderModel->deleteSmsByUser($this->userId);
                    $message = 'All SMS messages deleted successfully';
                    break;
                case 'all':
                    $apps = $this->finderModel->deleteAppsByUser($this->userId);
                    $calls = $this->finderModel->deleteCallsByUser($this->userId);
                    $contacts = $this->finderModel->deleteContactsByUser($this->userId);
                    $sms = $this->finderModel->deleteSmsByUser($this->userId);
                    $success = ($apps && $calls && $contacts && $sms);
                    $message = 'All data deleted successfully';
                    break;
                default:
                    session()->setFlashdata('error', 'Invalid delete type');
                    return redirect()->to('account/home');
            }

            if ($success) {
                $this->logUserAction('data_delete_' . $type, 'system', 'medium', 1);
                $this->updateLastDeletedTimestamp();
                session()->setFlashdata('success', $message);
            } else {
                session()->setFlashdata('error', 'Failed to delete data');
            }

            return redirect()->to('account/home');

        } catch (\Exception $e) {
            log_message('error', 'Delete data error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to delete data: ' . $e->getMessage());
            return redirect()->to('account/home');
        }
    }



    // =================================================================
    // UTILITY METHODS
    // =================================================================

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

            $viewData = [
                'pag' => 'account_devices',
                'user_info' => $userData,
                'user_devices' => $userDevices,
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