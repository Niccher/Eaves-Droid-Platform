<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\AccessLogsModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\FilesController\File;

class ClientProfileController extends BaseClientController
{
    use ResponseTrait;

    /**
     * @var UserModel
     */
    protected $modUser;

    /**
     * @var AccessLogsModel
     */
    protected $modAccessLogs;

    /**
     * @var \App\Models\CryptModel
     */
    protected $modCrypt;

    /**
     * @var array
     */
    protected $userData;

    /**
     * @var int
     */
    protected $perPage = 25;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->modUser = new UserModel();
        $this->modAccessLogs = new AccessLogsModel();
        $this->modCrypt = new \App\Models\CryptModel();

        $this->userData = $this->getAuthenticatedUserData();
        $this->userId = $this->userData['id'] ?? null;
    }

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

            // Get user's active subscription (if any)
            $subscriptionModel = new \App\Models\SubscriptionModel();
            $subscription = $subscriptionModel->getActivePlan($this->userId);

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
                'subscription' => $subscription,
            ];

            $viewData = array_merge($viewData, $dataCounts);

            return $this->renderView('profile', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'AccountController home error: ' . $e->getMessage());
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
                \App\Models\LogUserActionModel::logAction([
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
        $userModel = new \App\Models\UserModel();
        $devices = $userModel->get_user_devices_from_profile($this->userId);
        if (empty($devices)) return null;

        $deviceIds = array_column($devices, 'device_id');
        $db = \Config\Database::connect();
        return $db->table('tbl_device_profiles')
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
            'total_camera_info'   => 1024,
            'total_battery_stats' => 512,
            'total_accessibility' => 512,
            'total_input_methods' => 256,
            'total_processes'     => 512,
            'total_proc_info'     => 1024,
            'total_cell_towers'   => 256,
            'total_display_info'  => 512,
            'total_storage'       => 1024,
            'total_thermal'       => 512,
            'total_nfc'           => 256,
            'total_hardware_graphics' => 1024,
            'total_hardware_network'  => 1024,
            'total_data_usage'    => 1024,
            'total_saved_wifi'    => 512,
            'total_default_apps'  => 512,
            'total_alarms'        => 256,
            'total_app_security'  => 512,
            'total_network_security' => 512,
            'total_telephony_network' => 512,
            'total_system_locale' => 256,
            'total_app_permissions' => 256,
            'total_browser_history' => 512,
            'total_clipboard'     => 1024,
            'total_content_providers' => 512,
            'total_crash_logs'    => 2048,
            'total_digital_wellbeing' => 512,
            'total_doze_standby'  => 512,
            'total_email_accounts' => 1024,
            'total_health_data'   => 1024,
            'total_keyboard_input' => 256,
            'total_keyguard_events' => 256,
            'total_screenshots'   => 512,
            'total_screen_state'  => 256,
            'total_vpn_config'    => 512,
            'total_running_processes_detailed' => 512,
            'total_audio_devices' => 512,
            'total_biometric'     => 512,
            'total_gnss_hardware' => 1024,
            'total_power_rails'   => 512,
            'total_usb_devices'   => 512,
            'total_vibration'     => 512,
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
            log_message('error', 'AccountController settings error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load settings');
            return redirect()->back();
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

                // Check for valid timestamp (not null, not empty, not zero)
                if ($rawTimestamp !== null && $rawTimestamp !== '' && $rawTimestamp !== 0 && $rawTimestamp !== '0') {
                    if (is_numeric($rawTimestamp)) {
                        // Handle millisecond or second timestamp
                        $lastSeenTime = strlen((string)$rawTimestamp) > 11 ? (int)($rawTimestamp / 1000) : (int)$rawTimestamp;
                    } else {
                        // Handle date string
                        $lastSeenTime = strtotime($rawTimestamp);
                    }
                }

                // Fallback: try to get the most recent file upload time for this device
                if (!$lastSeenTime) {
                    $lastUpload = $this->getLastFileUploadTime($device['device_id'] ?? null);
                    if ($lastUpload) {
                        $lastSeenTime = $lastUpload;
                    }
                }

                // Get first contact date (when device was first registered)
                $firstContactTime = null;
                if (!empty($device['created_at']) && strpos($device['created_at'], '0000-00-00') !== 0) {
                    $parsed = strtotime($device['created_at']);
                    if ($parsed !== false && $parsed > 0) {
                        $firstContactTime = $parsed;
                    }
                }

                $formattedDevices[] = [
                    'device_type' => 'mobile',
                    'device_name' => ($device['device_brand'] ?? 'Unknown') . ' ' . ($device['device_model'] ?? 'Device'),
                    'os' => 'Android ' . ($device['android_version'] ?? 'Unknown'),
                    'browser' => 'FGM Extractor',
                    'ip_address' => $device['device_ip_address'] ?? 'Unknown',
                    'last_seen' => $lastSeenTime ? date('Y-m-d H:i:s', $lastSeenTime) : 'N/A',
                    'last_seen_formatted' => $lastSeenTime ? date('M d, Y, l H:i', $lastSeenTime) : 'Never',
                    'first_contact' => $firstContactTime ? date('Y-m-d H:i:s', $firstContactTime) : 'N/A',
                    'first_contact_formatted' => $firstContactTime ? date('M d, Y, l H:i', $firstContactTime) : 'Never'
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
            return $db->table('tbl_uploaded_files')
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
     * Gets the most recent file upload timestamp for a specific device.
     *
     * @param string|null $deviceId
     * @return int|null Unix timestamp or null if no uploads found
     */
    private function getLastFileUploadTime(?string $deviceId): ?int
    {
        if (!$deviceId) {
            return null;
        }

        try {
            $db = \Config\Database::connect();
            $row = $db->table('tbl_uploaded_files')
                ->select('MAX(uploaded_at) as last_upload')
                ->where('token_owner_id', $this->userId)
                ->where('device_checksum', $deviceId)
                ->get()
                ->getRow();

            if ($row && $row->last_upload) {
                $ts = (int)$row->last_upload;
                // Convert milliseconds to seconds if needed
                return strlen((string)$ts) > 11 ? (int)($ts / 1000) : $ts;
            }
            return null;
        } catch (\Exception $e) {
            log_message('error', 'Failed to get last file upload time: ' . $e->getMessage());
            return null;
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
     * AccountController security settings page.
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
     * POST /account/updateSecurity
     * Updates user security settings (password, email, 2FA).
     */
    public function updateSecurity()
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
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid or expired CSRF token. Please refresh the page.'
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $user = auth()->user();
            $oldEmail = $user->getEmail();
            $username = $user->username ?? '';

            // Handle password change
            if ($this->request->getPost('current_password') && $this->request->getPost('new_password')) {
                $currentPassword = $this->request->getPost('current_password');
                $newPassword = $this->request->getPost('new_password');
                $confirmPassword = $this->request->getPost('confirm_password');

                if ($newPassword !== $confirmPassword) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'New passwords do not match.'
                    ]);
                }

                // Verify current password
                $identities = model(\CodeIgniter\Shield\Models\UserIdentityModel::class);
                $emailIdentity = $identities->where('user_id', $this->userId)
                    ->where('type', 'email_password')
                    ->first();

                if (!$emailIdentity || !service('passwords')->verify($currentPassword, $emailIdentity->secret2)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Current password is incorrect.'
                    ]);
                }

                // Update password
                $newHash = service('passwords')->hash($newPassword);
                $identities->update($emailIdentity->id, ['secret2' => $newHash]);

                // Invalidate all session tokens (force logout everywhere)
                $identities->where('user_id', $this->userId)
                    ->where('type', 'session')
                    ->delete();

                // Send password changed email
                $this->sendPasswordChangedEmail();

                $this->logUserAction('password_change', 'security', 'high', 1);
            }

            // Handle email change
            if ($this->request->getPost('new_email') && $this->request->getPost('new_email') !== $oldEmail) {
                $newEmail = $this->request->getPost('new_email');

                // Check if email already exists
                $existing = $db->table('auth_identities')
                    ->where('secret', $newEmail)
                    ->where('type', 'email_password')
                    ->get()
                    ->getRow();
                if ($existing) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'This email is already registered.'
                    ]);
                }

                // Update email in auth_identities
                $identities = model(\CodeIgniter\Shield\Models\UserIdentityModel::class);
                $emailIdentity = $identities->where('user_id', $this->userId)
                    ->where('type', 'email_password')
                    ->first();
                if ($emailIdentity) {
                    $identities->update($emailIdentity->id, ['secret' => $newEmail]);
                }

                // Also update in user_profiles if present
                $db->table('user_profiles')
                    ->where('user_id', $this->userId)
                    ->update(['email' => $newEmail]);

                // Send email changed notification to both old and new email
                $this->sendEmailChangedEmail($oldEmail, $newEmail);

                $this->logUserAction('email_change', 'security', 'high', 1);
            }

            // Handle 2FA enable/disable
            if ($this->request->getPost('totp_action')) {
                $action = $this->request->getPost('totp_action'); // 'enable' or 'disable'
                $totpCode = $this->request->getPost('totp_code');

                $user = auth()->user();

                if ($action === 'enable') {
                    if (!$totpCode) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'TOTP code is required to enable 2FA.'
                        ]);
                    }
                    // Verify TOTP code
                    if (!$user->verifyTOTP($totpCode)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Invalid authenticator code.'
                        ]);
                    }
                    $user->enableTOTP();
                    $backupCodes = $user->getBackupCodes();
                    $this->send2faEnabledEmail($backupCodes);
                    $this->logUserAction('2fa_enable', 'security', 'high', 1);
                } elseif ($action === 'disable') {
                    if (!$totpCode) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'TOTP code is required to disable 2FA.'
                        ]);
                    }
                    // Verify TOTP code
                    if (!$user->verifyTOTP($totpCode)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Invalid authenticator code.'
                        ]);
                    }
                    $user->disableTOTP();
                    $this->send2faDisabledEmail();
                    $this->logUserAction('2fa_disable', 'security', 'high', 1);
                }
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Security settings updated successfully!'
                ]);
            } else {
                session()->setFlashdata('success', 'Security settings updated successfully!');
                return redirect()->to('account/security');
            }

        } catch (\Exception $e) {
            log_message('error', 'Security update failed: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update security settings: ' . $e->getMessage()
                ]);
            } else {
                session()->setFlashdata('error', 'Failed to update security settings: ' . $e->getMessage());
                return redirect()->back();
            }
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

    // =================================================================
    // HELPERS & REPLICATED ACCOUNT METHODS
    // =================================================================

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
            $profile = (new UserModel())->get_data_tbl_users($this->userId ?? $user->id);
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
     * Gets total tokens count for user.
     *
     * @return int
     */
    private function getTotalTokensCount(): int
    {
        try {
            $db = \Config\Database::connect();
            return $db->table('tbl_user_api_tokens')
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
     * LogsController user actions.
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

            // Merge device view data, version data, and sidebar counts
            $data = array_merge($data, $this->getDeviceViewData(), $this->getSystemVersionData(), $this->getUserDataCounts());

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
    // EMAIL TRIGGER HELPER METHODS
    // =================================================================

    /**
     * Send email for password change
     */
    private function sendPasswordChangedEmail(): void
    {
        if (!$this->isTriggerEnabled('on_password_changed')) return;
        $this->sendUserTriggerEmail('password_changed', [
            'changedAt' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Send email for email change
     */
    private function sendEmailChangedEmail(string $oldEmail, string $newEmail): void
    {
        if (!$this->isTriggerEnabled('on_email_changed')) return;
        $this->sendUserTriggerEmail('email_changed', [
            'oldEmail' => $oldEmail,
            'newEmail' => $newEmail,
            'changedAt' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Send email for 2FA enabled
     */
    private function send2faEnabledEmail(array $backupCodes = []): void
    {
        if (!$this->isTriggerEnabled('on_2fa_enabled')) return;
        $this->sendUserTriggerEmail('2fa_enabled', [
            'backupCodes' => $backupCodes,
            'enabledAt' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Send email for 2FA disabled
     */
    private function send2faDisabledEmail(): void
    {
        if (!$this->isTriggerEnabled('on_2fa_disabled')) return;
        $this->sendUserTriggerEmail('2fa_disabled', [
            'disabledAt' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Check if an email trigger is enabled
     */
    private function isTriggerEnabled(string $key): bool
    {
        $db = \Config\Database::connect();
        $row = $db->table('settings')
            ->where('class', 'email_triggers')
            ->where('key', $key)
            ->get()
            ->getRowArray();
        return $row && $row['value'] === '1';
    }

    /**
     * Send user-facing trigger email
     */
    private function sendUserTriggerEmail(string $template, array $data = []): void
    {
        try {
            $userEmail = auth()->user()->getEmail();
            $username = auth()->user()->username ?? 'User';

            if (!$userEmail) return;

            // Check user email notifications preference
            $db = \Config\Database::connect();
            $profile = $db->table('user_profiles')
                ->select('email_notifications')
                ->where('user_id', $this->userId)
                ->get()
                ->getRowArray();
            if ($profile && isset($profile['email_notifications']) && !$profile['email_notifications']) {
                return;
            }

            $securityData = [
                'securityAction' => ucfirst(str_replace('_', ' ', $template)),
                'securityDescription' => $this->getTriggerDescription($template),
                'securityStatus' => 'success',
                'securityInitiatedBy' => $username,
                'securityBrowser' => $this->request->getUserAgent()->getAgentString() ?: 'Unknown',
                'securityBrowserIp' => $this->request->getIPAddress(),
                'securityExecutedAt' => date('Y-m-d H:i:s'),
            ];

            helper('email');
            send_templated_email(
                $userEmail,
                'Eaves Droid — ' . ucfirst(str_replace('_', ' ', $template)),
                'email/user/' . $template,
                array_merge($data, $securityData)
            );
        } catch (\Throwable $e) {
            log_message('error', "User trigger email failed ($template): " . $e->getMessage());
        }
    }

    private function getTriggerDescription(string $template): string
    {
        $descriptions = [
            'password_changed' => 'Your account password was successfully changed.',
            'email_changed' => 'Your account email address was updated.',
            '2fa_enabled' => 'Two-factor authentication was enabled on your account.',
            '2fa_disabled' => 'Two-factor authentication was disabled on your account.',
            'device_paired' => 'A new Android device was paired with your account.',
            'device_unpaired' => 'A device was removed from your account.',
        ];
        return $descriptions[$template] ?? 'An action was performed on your account.';
    }

    /**
     * Send device unpaired email
     */
    private function sendDeviceUnpairedEmail(string $deviceName): void
    {
        $this->sendUserTriggerEmail('device_unpaired', [
            'device_name' => $deviceName,
            'unpaired_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
