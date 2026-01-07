<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class Apps extends BaseClientController
{
    use ResponseTrait;

    /**
     * Display all apps with pagination
     * Route: /apps
     */
    public function index()
    {
        return $this->view('all');
    }

    /**
     * Display only system apps
     * Route: /apps/system
     */
    public function system()
    {
        return $this->view('system');
    }

    /**
     * Display only user-installed apps
     * Route: /apps/user
     */
    public function user()
    {
        return $this->view('user');
    }

    /**
     * Display only recently installed apps
     * Route: /apps/recent
     */
    public function recent()
    {
        return $this->view('recent');
    }

    /**
     * Display only disabled apps
     * Route: /apps/disabled
     */
    public function disabled()
    {
        return $this->view('disabled');
    }

    /**
     * Single method with parameter for all app types
     * Route: /apps/(all|system|user|recent|disabled)
     */
    public function view($type = 'all')
    {
        // Validate type parameter
        $validTypes = ['all', 'system', 'user', 'recent', 'disabled'];
        if (!in_array($type, $validTypes)) {
            return redirect()->to('apps');
        }

        // Get app data based on type
        switch ($type) {
            case 'system':
                $appData = $this->getSystemApps();
                $viewFile = 'users/apps/apps_with_type';
                break;
            case 'user':
                $appData = $this->getUserApps();
                $viewFile = 'users/apps/apps_with_type';
                break;
            case 'recent':
                $appData = $this->getRecentApps();
                $viewFile = 'users/apps/apps_with_type';
                break;
            case 'disabled':
                $appData = $this->getDisabledApps();
                $viewFile = 'users/apps/apps_with_type';
                break;
            default: // 'all'
                $appData = $this->finderModel->get_apps($this->userId, $this->perPage);
                $viewFile = 'users/apps/apps_all';
                break;
        }

        // Get common data for App views
        $commonData = $this->getAppCommonData($type);

        // Prepare data for the view
        $data = array_merge($commonData, [
            'apps_dump' => $appData,
        ]);

        return $this->renderAppView($viewFile, $data);
    }

    /**
     * Get system apps (apps with android package)
     */
    private function getSystemApps(): array
    {
        try {
            // Filter apps that are likely system apps
            $allApps = $this->finderModel->get_apps($this->userId, PHP_INT_MAX);
            $systemApps = [];

            foreach ($allApps as $app) {
                $packageName = strtolower($app['Package'] ?? '');
                // Common system app package patterns
                if (strpos($packageName, 'com.android') === 0 ||
                    strpos($packageName, 'com.google.android') === 0 ||
                    strpos($packageName, 'android') !== false) {
                    $systemApps[] = $app;
                }
            }

            return $systemApps;
        } catch (\Exception $e) {
            log_message('error', 'getSystemApps error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user-installed apps (non-system)
     */
    private function getUserApps(): array
    {
        try {
            $allApps = $this->finderModel->get_apps($this->userId, PHP_INT_MAX);
            $userApps = [];

            foreach ($allApps as $app) {
                $packageName = strtolower($app['Package'] ?? '');
                // Exclude common system app patterns
                if (strpos($packageName, 'com.android') !== 0 &&
                    strpos($packageName, 'com.google.android') !== 0 &&
                    !preg_match('/^android\./', $packageName)) {
                    $userApps[] = $app;
                }
            }

            return $userApps;
        } catch (\Exception $e) {
            log_message('error', 'getUserApps error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recently installed apps (based on first_install_time)
     */
    private function getRecentApps(): array
    {
        try {
            // Get all apps sorted by first_install_time
            $this->finderModel->table = 'tbl_apps';
            $recentApps = $this->finderModel->select("package_name as Package, app_name as Name, 
                                                     version_code as Code, version_name, 
                                                     first_install_time, last_update_time, is_system_app")
                ->where('meta_Owner', $this->userId)
                ->orderBy('first_install_time', 'DESC')
                ->limit(10)
                ->findAll();

            return $recentApps;
        } catch (\Exception $e) {
            log_message('error', 'getRecentApps error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get disabled apps (check is_system_app field or other criteria)
     */
    private function getDisabledApps(): array
    {
        try {
            // In a real implementation, you would need a status field
            // For now, return apps that are not system apps but not recently updated
            $allApps = $this->finderModel->get_apps($this->userId, PHP_INT_MAX);
            $disabledApps = [];

            foreach ($allApps as $app) {
                // Example criteria: apps not updated in last 30 days
                if (isset($app['last_update_time'])) {
                    $thirtyDaysAgo = time() - (30 * 24 * 60 * 60);
                    if ($app['last_update_time'] < $thirtyDaysAgo * 1000) {
                        $disabledApps[] = $app;
                    }
                }
            }

            return $disabledApps;
        } catch (\Exception $e) {
            log_message('error', 'getDisabledApps error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Alternative method for backward compatibility
     * Route: /apps (maps to index)
     */
    public function apps()
    {
        return $this->index();
    }

    /**
     * Alternative method for backward compatibility
     * Route: /apps/system (maps to system)
     */
    public function apps_system()
    {
        return $this->system();
    }

    /**
     * Alternative method for backward compatibility
     * Route: /apps/user (maps to user)
     */
    public function apps_user()
    {
        return $this->user();
    }

    /**
     * Alternative method for backward compatibility
     * Route: /apps/recent (maps to recent)
     */
    public function apps_recent()
    {
        return $this->recent();
    }

    /**
     * Alternative method for backward compatibility
     * Route: /apps/disabled (maps to disabled)
     */
    public function apps_disabled()
    {
        return $this->disabled();
    }

    /**
     * Get navigation URLs for App views
     */
    protected function getAppNavigationUrls(string $activeView = 'all'): string
    {
        $buttons = [
            'all'      => ($activeView === 'all') ? 'btn-primary' : 'btn-outline-primary',
            'system'   => ($activeView === 'system') ? 'btn-primary' : 'btn-outline-primary',
            'user'     => ($activeView === 'user') ? 'btn-primary' : 'btn-outline-primary',
            'recent'   => ($activeView === 'recent') ? 'btn-primary' : 'btn-outline-primary',
            'disabled' => ($activeView === 'disabled') ? 'btn-primary' : 'btn-outline-primary'
        ];

        return '
            <a class="btn ' . $buttons['all'] . '" href="' . base_url("apps") . '">All</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['system'] . '" href="' . base_url("apps/system") . '">System</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['user'] . '" href="' . base_url("apps/user") . '">User</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['recent'] . '" href="' . base_url("apps/recent") . '">Recent</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['disabled'] . '" href="' . base_url("apps/disabled") . '">Disabled</a>
            &nbsp;&nbsp;';
    }

    /**
     * Get page titles for different App views
     */
    protected function getAppPageTitle(string $viewType): string
    {
        $titles = [
            'all'      => 'All Apps',
            'system'   => 'System Apps',
            'user'     => 'User Apps',
            'recent'   => 'Recently Installed',
            'disabled' => 'Disabled Apps'
        ];

        return $titles[$viewType] ?? 'Apps';
    }

    /**
     * Get common data for App views
     */
    protected function getAppCommonData(string $viewType = 'all'): array
    {
        $paginationData = $this->getPaginationData();

        // Get counts for each category
        $allApps = $this->finderModel->get_apps($this->userId, PHP_INT_MAX);
        $totalApps = count($allApps);
        $systemAppsCount = count($this->getSystemApps());
        $userAppsCount = count($this->getUserApps());

        return array_merge([
            'pag' => 'apps',
            'apps_head' => $this->getAppPageTitle($viewType),
            'apps_urls' => $this->getAppNavigationUrls($viewType),
            'totalApps' => $totalApps,
            'systemAppsCount' => $systemAppsCount,
            'userAppsCount' => $userAppsCount,
            'recentAppsCount' => min(10, $totalApps),
            'disabledAppsCount' => count($this->getDisabledApps()),
        ], $paginationData);
    }

    /**
     * Render App-specific view
     */
    protected function renderAppView(string $mainView, array $extraData = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
        ], $this->getUserDataCounts(), $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_data_datatables', $data);
    }
}