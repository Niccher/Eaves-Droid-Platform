<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;
use App\Models\Mod_Finder;
use App\Models\Mod_User;
use App\Models\Mod_Access_Logs;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Files\File;

class Account extends BaseController
{
    protected $modFinder;
    protected $modUser;
    protected $modAccessLogs;
    protected $userId;
    protected $userData;
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
    ) {
        parent::initController($request, $response, $logger);

        // Check authentication
        if (!auth()->loggedIn()) {
            session()->setFlashdata('error', 'Please login to continue');
            return redirect()->to('login')->send();
        }

        // Initialize models
        $this->modFinder = new Mod_Finder();
        $this->modUser = new Mod_User();
        $this->modAccessLogs = new Mod_Access_Logs();

        // Get user data
        $this->userData = $this->getAuthenticatedUserData();
        $this->userId = $this->userData['id'] ?? null;

        if (!$this->userId) {
            session()->setFlashdata('error', 'User data not found');
            return redirect()->to('login')->send();
        }
    }

    /**
     * Account profile page.
     *
     * @return string
     */
    public function home(): string
    {
        try {
            // Get user data with email from Shield
            $userEmail = auth()->user()->getEmail();
            $this->userData['email'] = $userEmail;

            // Get user variables
            $userVars = $this->getUserVars();
            $userToken = $this->ensureUserToken();

            // Get Android connection status
            $androidConnected = $this->isAndroidConnected();

            // Get data counts
            $dataCounts = $this->getUserDataCounts();

            // Get usage metrics
            $usageMetrics = $this->getUsageMetrics();

            $viewData = [
                'pag' => 'account_profile',
                'user_info' => $this->userData,
                'user_vars' => $userVars,
                'user_token' => $userToken,
                'android_connected' => $androidConnected,
                'total_apps' => $dataCounts['apps'] ?? 0,
                'total_contacts' => $dataCounts['contacts'] ?? 0,
                'total_sms' => $dataCounts['sms'] ?? 0,
                'total_calls' => $dataCounts['calls'] ?? 0,
                'total_tokens' => $usageMetrics['total_tokens'] ?? 0,
                'connected_devices' => $usageMetrics['connected_devices'] ?? 0,
                'csrf_token' => csrf_hash(),
            ];

            return $this->renderView('profile', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Account home error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load account information');
            return redirect()->back();
        }
    }

    /**
     * Account settings page.
     *
     * @return string
     */
    public function setting(): string
    {
        try {
            $userVars = $this->getUserVars();
            $userToken = $this->ensureUserToken();
            $userDevices = $this->getUserDevices();
            $userSessions = $this->getUserSessions();

            // Get usage metrics
            $usageMetrics = $this->getUsageMetrics();

            $viewData = [
                'pag' => 'account_setting',
                'user_info' => $this->userData,
                'user_vars' => $userVars,
                'user_token' => $userToken,
                'user_devices' => $userDevices,
                'user_sessions' => $userSessions,
                'activeSessions' => $this->modUser->get_active_sessions_count($this->userId),
                'securityEvents' => $this->modUser->get_security_events_count($this->userId),
                'total_tokens' => $usageMetrics['total_tokens'] ?? 0,
                'connected_devices' => $usageMetrics['connected_devices'] ?? 0,
                'tokenExpiry' => isset($userToken['Token_Expiry']) ? date('M d, Y H:i', strtotime($userToken['Token_Expiry'])) : 'Never',
                'csrf_token' => csrf_hash(),
            ];

            return $this->renderView('settings', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Account settings error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load settings');
            return redirect()->back();
        }
    }

    /**
     * Access logs page.
     *
     * @return string
     */
    public function access_logs(): string
    {
        try {
            // Get all access logs for the user
            $accessLogs = $this->modAccessLogs->get_access_logs($this->userId, 100);

            // Process the logs for the HTML dashboard
            $processedData = $this->processAccessLogs($accessLogs);

            // Get access statistics
            $accessStats = $this->modAccessLogs->get_access_stats($this->userId);

            // Prepare view data
            $viewData = [
                'pag' => 'account_logs',
                'user_info' => $this->userData,
                'csrf_token' => csrf_hash(),
                'access_head' => 'Access Logs',
            ];

            // Merge processed data
            $viewData = array_merge($viewData, $processedData, $accessStats);

            return $this->renderView('access_logs', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Access logs error: ' . $e->getMessage());

            // Return empty data structure
            return $this->renderView('access_logs', [
                'pag' => 'account_logs',
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
     * Export user data by type.
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
                    $data = $this->modFinder->get_apps($this->userId, 1000);
                    $filename = 'apps_export_' . date('Y-m-d_H-i-s') . '.json';
                    break;
                case 'calls':
                    $data = $this->modFinder->get_call_logs($this->userId, 1000);
                    $filename = 'calls_export_' . date('Y-m-d_H-i-s') . '.json';
                    break;
                case 'contacts':
                    $data = $this->modFinder->get_contacts($this->userId, 1000);
                    $filename = 'contacts_export_' . date('Y-m-d_H-i-s') . '.json';
                    break;
                case 'sms':
                    $data = $this->modFinder->get_sms($this->userId, 1000);
                    $filename = 'sms_export_' . date('Y-m-d_H-i-s') . '.json';
                    break;
                case 'all':
                    $data = [
                        'apps' => $this->modFinder->get_apps($this->userId, 1000),
                        'calls' => $this->modFinder->get_call_logs($this->userId, 1000),
                        'contacts' => $this->modFinder->get_contacts($this->userId, 1000),
                        'sms' => $this->modFinder->get_sms($this->userId, 1000),
                        'export_info' => [
                            'exported_at' => date('Y-m-d H:i:s'),
                            'user_id' => $this->userId,
                            'user_email' => auth()->user()->getEmail()
                        ]
                    ];
                    $filename = 'complete_export_' . date('Y-m-d_H-i-s') . '.json';
                    break;
                default:
                    session()->setFlashdata('error', 'Invalid export type');
                    return redirect()->to('account/home');
            }

            // Log export action
            $this->modAccessLogs->insert([
                'user_id' => $this->userId,
                'action_type' => 'data_export',
                'action_category' => 'system',
                'action_severity' => 'low',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'device_type' => 'desktop',
                'success' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Update export count in user profile
            $this->updateExportCount();

            // Return JSON download
            return $this->response
                ->setContentType('application/json')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody(json_encode($data, JSON_PRETTY_PRINT));

        } catch (\Exception $e) {
            log_message('error', 'Export data error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to export data: ' . $e->getMessage());
            return redirect()->to('account/home');
        }
    }

    /**
     * Delete user data by type.
     *
     * @param string $type
     * @return mixed
     */
    public function deleteData($type)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        // Check if this is a POST request (confirmation)
        if ($this->request->getMethod() !== 'post') {
            // Show confirmation view
            $viewData = [
                'pag' => 'account_profile',
                'user_info' => $this->userData,
                'delete_type' => $type,
                'csrf_token' => csrf_hash(),
            ];

            return $this->renderView('confirm_delete', $viewData);
        }

        // Validate CSRF token
        if (!csrf_val($this->request->getPost('csrf_token'))) {
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
                    $success = $this->modFinder->deleteAppsByUser($this->userId);
                    $message = 'All apps deleted successfully';
                    break;
                case 'call_logs':
                    $success = $this->modFinder->deleteCallsByUser($this->userId);
                    $message = 'All call logs deleted successfully';
                    break;
                case 'contacts':
                    $success = $this->modFinder->deleteContactsByUser($this->userId);
                    $message = 'All contacts deleted successfully';
                    break;
                case 'sms':
                    $success = $this->modFinder->deleteSmsByUser($this->userId);
                    $message = 'All SMS messages deleted successfully';
                    break;
                case 'all':
                    // Delete all data types
                    $apps = $this->modFinder->deleteAppsByUser($this->userId);
                    $calls = $this->modFinder->deleteCallsByUser($this->userId);
                    $contacts = $this->modFinder->deleteContactsByUser($this->userId);
                    $sms = $this->modFinder->deleteSmsByUser($this->userId);
                    $success = ($apps && $calls && $contacts && $sms);
                    $message = 'All data deleted successfully';
                    break;
                default:
                    session()->setFlashdata('error', 'Invalid delete type');
                    return redirect()->to('account/home');
            }

            if ($success) {
                // Log deletion action
                $this->modAccessLogs->insert([
                    'user_id' => $this->userId,
                    'action_type' => 'data_delete_' . $type,
                    'action_category' => 'system',
                    'action_severity' => 'medium',
                    'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getUserAgent()->getAgentString(),
                    'device_type' => 'desktop',
                    'success' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Update deletion timestamp
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

    /**
     * Update user profile.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function updateProfile()
    {
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
        }

        // Validate CSRF token
        if (!csrf_val($this->request->getPost('csrf_token'))) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid CSRF token'
            ]);
        }

        try {
            $data = [];

            // Update username if provided
            if ($username = $this->request->getPost('username')) {
                $data['username'] = $username;
            }

            // Update bio if provided
            if ($bio = $this->request->getPost('bio')) {
                $data['bio'] = $bio;
            }

            // Handle profile image upload
            if ($imageFile = $this->request->getFile('profile_image')) {
                if ($imageFile->isValid() && !$imageFile->hasMoved()) {
                    // Validate image
                    $validationRules = [
                        'profile_image' => [
                            'rules' => 'uploaded[profile_image]|max_size[profile_image,2048]|is_image[profile_image]',
                            'errors' => [
                                'uploaded' => 'Please select an image to upload',
                                'max_size' => 'Image size should not exceed 2MB',
                                'is_image' => 'Only image files are allowed'
                            ]
                        ]
                    ];

                    if (!$this->validate($validationRules)) {
                        return $this->response->setStatusCode(400)->setJSON([
                            'success' => false,
                            'errors' => $this->validator->getErrors()
                        ]);
                    }

                    // Generate unique filename
                    $newName = $imageFile->getRandomName();
                    $uploadPath = WRITEPATH . 'uploads/profiles/';

                    // Ensure directory exists
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    // Move file
                    if ($imageFile->move($uploadPath, $newName)) {
                        $data['profile_image'] = $newName;

                        // Delete old profile image if exists
                        $oldImage = $this->userData['profile_image'] ?? '';
                        if ($oldImage && file_exists($uploadPath . $oldImage) && $oldImage != $newName) {
                            unlink($uploadPath . $oldImage);
                        }
                    }
                }
            }

            // Update user profile
            $db = \Config\Database::connect();
            $builder = $db->table('user_profiles');

            // Check if profile exists
            $profileExists = $builder->where('user_id', $this->userId)->countAllResults() > 0;

            if ($profileExists) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $builder->where('user_id', $this->userId)->update($data);
            } else {
                $data['user_id'] = $this->userId;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                $builder->insert($data);
            }

            // Log profile update
            $this->modAccessLogs->insert([
                'user_id' => $this->userId,
                'action_type' => 'profile_update',
                'action_category' => 'profile',
                'action_severity' => 'low',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'device_type' => 'desktop',
                'success' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Profile updated successfully',
                'redirect' => base_url('account/home')
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
     * Regenerate token.
     *
     * @return RedirectResponse
     */
    public function regenerateToken()
    {
        if (!$this->request->is('post')) {
            return redirect()->back()->with('error', 'Invalid request method');
        }

        // Validate CSRF token
        if (!csrf_val($this->request->getPost('csrf_token'))) {
            session()->setFlashdata('error', 'Invalid security token');
            return redirect()->back();
        }

        try {
            $newToken = bin2hex(random_bytes(32));

            // Get user email for token naming
            $userEmail = auth()->user()->getEmail();
            $username = auth()->user()->username ?? explode('@', $userEmail)[0];

            // Mark old tokens as inactive
            $db = \Config\Database::connect();
            $db->table('tbl_Tokens')
                ->where('Token_Owner', $this->userId)
                ->where('Token_Status', '00')
                ->set('Token_Status', '11')
                ->update();

            // Create new token
            $tokenData = [
                'Token_Created' => date('Y-m-d H:i:s'),
                'Token_Owner' => $this->userId,
                'Token' => $newToken,
                'Token_Status' => '00',
                'Token_Initiator' => $this->request->getIPAddress(),
                'Token_Expiry' => date('Y-m-d H:i:s', strtotime('+30 days')),
                'Token_Name' => $username . '_' . date('Ymd_His')
            ];

            $db->table('tbl_Tokens')->insert($tokenData);

            // Log token regeneration
            $this->modAccessLogs->insert([
                'user_id' => $this->userId,
                'action_type' => 'token_regenerate',
                'action_category' => 'security',
                'action_severity' => 'medium',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'device_type' => 'desktop',
                'success' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            session()->setFlashdata('success', 'Token regenerated successfully. Update your Android app with the new token.');

        } catch (\Exception $e) {
            log_message('error', 'Token regeneration failed: ' . $e->getMessage());
            session()->setFlashdata('error', 'Token regeneration failed: ' . $e->getMessage());
        }

        return redirect()->to('account/setting');
    }

    /**
     * Check if Android device is connected.
     *
     * @return bool
     */
    private function isAndroidConnected(): bool
    {
        try {
            $token = $this->modUser->get_token($this->userId);
            return !empty($token) && isset($token['Token_Status']) && $token['Token_Status'] == '00';
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
            $builder = $db->table('tbl_Tokens');
            return $builder->where('Token_Owner', $this->userId)->countAllResults();
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
            // Get unique devices from access logs
            $db = \Config\Database::connect();
            return $db->table('tbl_user_actions')
                    ->select('COUNT(DISTINCT CONCAT(device_name, ip_address)) as device_count')
                    ->where('user_id', $this->userId)
                    ->where('device_name IS NOT NULL')
                    ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                    ->get()
                    ->getRow()->device_count ?? 0;
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
     * Process access logs for HTML dashboard.
     *
     * @param array $accessLogs
     * @return array
     */
    private function processAccessLogs(array $accessLogs): array
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

        // Icons mapping for categories
        $categoryIcons = [
            'authentication' => 'fa-key',
            'file' => 'fa-file',
            'profile' => 'fa-user',
            'admin' => 'fa-cog',
            'system' => 'fa-server',
            'security' => 'fa-shield-alt'
        ];

        // Icons mapping for severity
        $severityIcons = [
            'low' => 'fa-circle text-success',
            'medium' => 'fa-exclamation-circle text-warning',
            'high' => 'fa-exclamation-triangle text-danger',
            'critical' => 'fa-skull-crossbones text-danger'
        ];

        // Icons mapping for device types
        $deviceIcons = [
            'desktop' => 'fa-desktop text-primary',
            'mobile' => 'fa-mobile-alt text-success',
            'tablet' => 'fa-tablet-alt text-info',
            'bot' => 'fa-robot text-secondary',
            'unknown' => 'fa-question-circle text-muted'
        ];

        foreach ($accessLogs as $log) {
            // Ensure created_at exists
            if (!isset($log['created_at']) || empty($log['created_at'])) {
                $log['created_at'] = date('Y-m-d H:i:s');
            }

            // Convert timestamp
            $timestamp = strtotime($log['created_at']);
            $log['Timestamps'] = $timestamp ?: time();

            // Determine platform from device_type
            $platform = $this->determinePlatformFromLog($log);
            $log['Platform'] = $platform;

            // Determine status from success and severity
            $status = $this->determineStatusFromLog($log);
            $log['Status'] = $status;

            // Add Action
            if (!isset($log['Action'])) {
                $log['Action'] = $this->determineActionFromLog($log);
            }

            // Add IP
            $log['IP'] = $log['ip_address'] ?? 'N/A';

            // Add location
            $log['Location'] = $this->determineLocationFromLog($log);

            // Add icons
            $log['CategoryIcon'] = $categoryIcons[$log['action_category'] ?? 'system'] ?? 'fa-question-circle';
            $log['SeverityIcon'] = $severityIcons[$log['action_severity'] ?? 'low'] ?? 'fa-circle text-secondary';
            $log['DeviceIcon'] = $deviceIcons[$log['device_type'] ?? 'unknown'] ?? 'fa-question-circle text-muted';

            // Add platform-specific fields for HTML
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

            // Count statuses
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }

            $allLogs[] = $log;
        }

        // Sort logs by timestamp (newest first)
        usort($allLogs, function($a, $b) {
            return ($b['Timestamps'] ?? 0) <=> ($a['Timestamps'] ?? 0);
        });

        usort($webLogs, function($a, $b) {
            return ($b['Timestamps'] ?? 0) <=> ($a['Timestamps'] ?? 0);
        });

        usort($androidLogs, function($a, $b) {
            return ($b['Timestamps'] ?? 0) <=> ($a['Timestamps'] ?? 0);
        });

        // Get last updated timestamp
        $lastUpdated = $this->getLastUpdated($allLogs);

        return [
            'user_logs' => $allLogs,
            'webLogs' => $webLogs,
            'androidLogs' => $androidLogs,
            'webLogsCount' => count($webLogs),
            'androidLogsCount' => count($androidLogs),
            'totalLogs' => count($allLogs),
            'successfulLogins' => $statusCounts['success'],
            'failedAttempts' => $statusCounts['failed'],
            'suspiciousActivities' => $statusCounts['suspicious'],
            'lastUpdated' => $lastUpdated,
            'categoryIcons' => $categoryIcons,
            'severityIcons' => $severityIcons,
            'deviceIcons' => $deviceIcons
        ];
    }

    /**
     * Determine platform from log data.
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
     * Determine status from log data.
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
     * Determine action from log data.
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
     * Determine location from log data.
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
     * Get last updated timestamp.
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
     * Empty logs structure for fallback.
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

    /**
     * Get authenticated user data.
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
        $userArray['email'] = $user->getEmail(); // Get email from Shield

        // Get additional profile data from user_profiles table
        try {
            $db = \Config\Database::connect();
            $profile = $db->table('user_profiles')
                ->where('user_id', $user->id)
                ->get()
                ->getRowArray();

            if ($profile) {
                $userArray = array_merge($userArray, $profile);
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch user profile: ' . $e->getMessage());
        }

        return $userArray;
    }

    /**
     * Get user variables.
     *
     * @return array
     */
    private function getUserVars(): array
    {
        $vars = $this->modUser->get_vars($this->userId);
        return is_array($vars) ? $vars : [];
    }

    /**
     * Ensure user has a token, create if not exists.
     *
     * @return array
     */
    private function ensureUserToken(): array
    {
        $tokenData = $this->modUser->get_token($this->userId);

        if (empty($tokenData) || !isset($tokenData['Token'])) {
            $newToken = bin2hex(random_bytes(32));

            // Get user email for token naming
            $userEmail = auth()->user()->getEmail();
            $username = auth()->user()->username ?? explode('@', $userEmail)[0];

            // Create token with IP address
            $this->modUser->create_token(
                $this->userId,
                $newToken,
                $this->request->getIPAddress()
            );

            $tokenData = $this->modUser->get_token($this->userId);
        }

        return is_array($tokenData) ? $tokenData : [];
    }

    /**
     * Get user devices.
     *
     * @return array
     */
    private function getUserDevices(): array
    {
        $devices = $this->modUser->get_devices($this->userId);
        return is_array($devices) ? $devices : [];
    }

    /**
     * Get user sessions.
     *
     * @return array
     */
    private function getUserSessions(): array
    {
        $sessions = $this->modUser->get_sessions($this->userId);
        return is_array($sessions) ? $sessions : [];
    }

    /**
     * Get all user data counts.
     *
     * @return array
     */
    private function getUserDataCounts(): array
    {
        try {
            return [
                'apps' => $this->modFinder->get_count_Apps($this->userId),
                'contacts' => $this->modFinder->get_count_Contacts($this->userId),
                'sms' => $this->modFinder->get_count_Sms($this->userId),
                'calls' => $this->modFinder->get_count_Calls($this->userId),
            ];
        } catch (\Exception $e) {
            log_message('error', 'Failed to get user data counts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update export count in user profile.
     *
     * @return bool
     */
    private function updateExportCount(): bool
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('user_profiles');

            // Check if profile exists
            $exists = $builder->where('user_id', $this->userId)->countAllResults() > 0;

            if ($exists) {
                // Increment export count
                $builder->where('user_id', $this->userId)
                    ->set('export_count', 'export_count + 1', false)
                    ->set('last_exported_at', date('Y-m-d H:i:s'))
                    ->set('updated_at', date('Y-m-d H:i:s'))
                    ->update();
            } else {
                // Create profile with export count
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
     * Update last deleted timestamp.
     *
     * @return bool
     */
    private function updateLastDeletedTimestamp(): bool
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('user_profiles');

            // Check if profile exists
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
     * Render view with common layout.
     *
     * @param string $page
     * @param array $data
     * @return string
     */
    private function renderView(string $page, array $data = []): string
    {
        $viewPath = 'users/account/' . $page;

        // Ensure view exists
        $viewFile = APPPATH . 'Views/' . str_replace('/', DIRECTORY_SEPARATOR, $viewPath) . '.php';
        if (!file_exists($viewFile)) {
            log_message('error', "View not found: {$viewPath}");
            throw new \RuntimeException("View not found: {$viewPath}");
        }

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($viewPath, $data)
            . view('headers_footers/footer_users');
    }
}