<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class Apps extends BaseClientController
{
    use ResponseTrait;

    /**
     * Display all apps with pagination.
     *
     * @return string
     */
    public function index(): string
    {
        return $this->view('all');
    }

    /**
     * Display only system apps.
     *
     * @return string
     */
    public function system(): string
    {
        return $this->view('system');
    }

    /**
     * Display only user-installed apps.
     *
     * @return string
     */
    public function user(): string
    {
        return $this->view('user');
    }

    /**
     * Single method with parameter for all app types.
     *
     * @param string $type
     * @return string
     */
    public function view(string $type = 'all'): string
    {
        // Validate type parameter
        $validTypes = ['all', 'system', 'user', 'recent', 'disabled'];
        if (!in_array($type, $validTypes)) {
            return redirect()->to('apps');
        }

        // Get app data based on type
        $appData = [];
        switch ($type) {
            case 'system':
                $appData = $this->getSystemApps();
                break;
            case 'user':
                $appData = $this->getUserApps();
                break;
            default: // 'all'
                $appData = $this->getAllApps();
                break;
        }

        // Get common data for App views
        $commonData = $this->getAppCommonData($type);

        // Prepare data for the view
        $data = array_merge($commonData, [
            'apps_dump' => $appData['data'] ?? [],
            'pager' => $appData['pager'] ?? null,
        ]);

        return $this->renderAppView('users/apps/apps_with_type', $data);
    }

    /**
     * Get all apps with complete details.
     *
     * @return array
     */
    private function getAllApps(): array
    {
        try {
            $db = \Config\Database::connect();

            $query = $db->table('tbl_apps')
                ->select('
                    counter,
                    app_name as Name,
                    package_name as Package,
                    version_name,
                    version_code as Code,
                    permission_count,
                    app_size,
                    is_system_app,
                    target_sdk,
                    min_sdk,
                    permissions,
                    first_install_time,
                    last_update_time,
                    created_at
                ')
                ->where('owner_id', $this->userId)
                ->orderBy('app_name', 'ASC');

            // Get total count for pagination
            $total = $query->countAllResults(false);

            // Apply pagination
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->perPage;
            $offset = ($page - 1) * $perPage;

            $results = $query->limit($perPage, $offset)->get()->getResultArray();

            // Set up pagination
            $pager = \Config\Services::pager();
            $pager->makeLinks($page, $perPage, $total, 'bootstrap4');

            return [
                'data' => $results,
                'pager' => $pager,
                'total' => $total
            ];

        } catch (\Exception $e) {
            log_message('error', 'getAllApps error: ' . $e->getMessage());
            return ['data' => [], 'pager' => null, 'total' => 0];
        }
    }

    /**
     * Get system apps.
     *
     * @return array
     */
    private function getSystemApps(): array
    {
        try {
            $db = \Config\Database::connect();

            $query = $db->table('tbl_apps')
                ->select('
                    counter,
                    app_name as Name,
                    package_name as Package,
                    version_name,
                    version_code as Code,
                    permission_count,
                    app_size,
                    is_system_app,
                    target_sdk,
                    min_sdk,
                    permissions,
                    first_install_time,
                    last_update_time
                ')
                ->where('owner_id', $this->userId)
                ->where('is_system_app', 1)
                ->orderBy('app_name', 'ASC');

            // Get total count for pagination
            $total = $query->countAllResults(false);

            // Apply pagination
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->perPage;
            $offset = ($page - 1) * $perPage;

            $results = $query->limit($perPage, $offset)->get()->getResultArray();

            // Set up pagination
            $pager = \Config\Services::pager();
            $pager->makeLinks($page, $perPage, $total, 'bootstrap4');

            return [
                'data' => $results,
                'pager' => $pager,
                'total' => $total
            ];

        } catch (\Exception $e) {
            log_message('error', 'getSystemApps error: ' . $e->getMessage());
            return ['data' => [], 'pager' => null, 'total' => 0];
        }
    }

    /**
     * Get user-installed apps.
     *
     * @return array
     */
    private function getUserApps(): array
    {
        try {
            $db = \Config\Database::connect();

            $query = $db->table('tbl_apps')
                ->select('
                    counter,
                    app_name as Name,
                    package_name as Package,
                    version_name,
                    version_code as Code,
                    permission_count,
                    app_size,
                    is_system_app,
                    target_sdk,
                    min_sdk,
                    permissions,
                    first_install_time,
                    last_update_time
                ')
                ->where('owner_id', $this->userId)
                ->where('is_system_app', 0)
                ->orderBy('app_name', 'ASC');

            // Get total count for pagination
            $total = $query->countAllResults(false);

            // Apply pagination
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->perPage;
            $offset = ($page - 1) * $perPage;

            $results = $query->limit($perPage, $offset)->get()->getResultArray();

            // Set up pagination
            $pager = \Config\Services::pager();
            $pager->makeLinks($page, $perPage, $total, 'bootstrap4');

            return [
                'data' => $results,
                'pager' => $pager,
                'total' => $total
            ];

        } catch (\Exception $e) {
            log_message('error', 'getUserApps error: ' . $e->getMessage());
            return ['data' => [], 'pager' => null, 'total' => 0];
        }
    }

    /**
     * Alternative method for backward compatibility.
     *
     * @return string
     */
    public function apps(): string
    {
        return $this->index();
    }

    /**
     * Alternative method for backward compatibility.
     *
     * @return string
     */
    public function apps_system(): string
    {
        return $this->system();
    }

    /**
     * Alternative method for backward compatibility.
     *
     * @return string
     */
    public function apps_user(): string
    {
        return $this->user();
    }

}