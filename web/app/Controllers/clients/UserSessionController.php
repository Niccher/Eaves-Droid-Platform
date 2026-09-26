<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\AccessLogsModel;

class UserSessionController extends BaseClientController
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
     * @var array
     */
    protected $userData;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->modUser = new UserModel();
        $this->modAccessLogs = new AccessLogsModel();

        // Load logs helper for formatFileSize
        helper('logs');

        $this->userData = $this->getAuthenticatedUserData();
        $this->userId = $this->userData['id'] ?? null;
    }

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
                'access_head' => 'Access LogsController',
            ];

            // Merge processed data
            $viewData = array_merge($viewData, $processedData, $accessStats, $dataCounts);

            return $this->renderView('access_logs', $viewData);

        } catch (\Exception $e) {
            // For error view, maybe just empty stats
            log_message('error', 'Access logs error: ' . $e->getMessage());

            return $this->renderView('access_logs', [
                'pag' => 'account_logs',
                'activeTab' => $tab,
                'user_info' => $this->userData,
                'access_head' => 'Access LogsController',
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

            return $this->response->setJSON(['success' => true, 'message' => 'LogsController cleared']);
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
            $log['LocationController'] = $this->determineLocationFromLog($log);

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

        return 'Unknown LocationController';
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
     * Helper to get user sessions from model.
     *
     * @return array
     */
    private function getUserSessions(): array
    {
        try {
            return $this->modUser->get_sessions($this->userId);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get user sessions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * AccountController statistics page.
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

    // =================================================================
    // HELPER METHODS
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
                // Decrypt bio if it exists (not decrypted in sessions/logs but keeping for consistency)
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
}
