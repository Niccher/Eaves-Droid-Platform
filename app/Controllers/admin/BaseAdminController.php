<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\LogUserActionModel;

class BaseAdminController extends BaseController
{
    protected const PRIVILEGED_GROUPS = ['superadmin', 'admin', 'developer'];

    protected $userData;
    protected $userId;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void
    {
        parent::initController($request, $response, $logger);

        if (!auth()->loggedIn()) {
            session()->setFlashdata('error', 'Please login to continue');
            throw new \RuntimeException('Authentication required');
        }

        $user = auth()->user();
        $this->userId = (int) $user->id;
        $this->userData = [
            'id' => $this->userId,
            'username' => $user->username,
            'email' => $user->getEmail(),
        ];
    }

    protected function getSystemVersionData(): array
    {
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('system_versions')) {
                $v = $db->table('system_versions')
                    ->where('is_current', 1)
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRowArray();
                if ($v) {
                    $changelogs = $db->table('system_changelogs')
                        ->where('version_id', (int) $v['id'])
                        ->get()
                        ->getResultArray();
                    return [
                        'platform_version'   => $v['version'],
                        'platform_build'     => $v['build_number'],
                        'platform_name'      => $v['release_name'],
                        'version_changelogs' => $changelogs,
                    ];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'getSystemVersionData exception: ' . $e->getMessage());
        }

        return [
            'platform_version'   => '2.4.0',
            'platform_build'     => '20400',
            'platform_name'      => 'Enterprise Telemetry & Real-Time Forensic Suite',
            'version_changelogs' => [
                [
                    'category'    => 'feature',
                    'title'       => 'Real-Time Forensic Exporting & SMTP Email Alerts',
                    'description' => 'On-demand synchronous archive generation with immediate download links and automated HTML email notifications.',
                    'component'   => 'webapp',
                ],
                [
                    'category'    => 'feature',
                    'title'       => 'Impersonation Flow & Top Warning Banner',
                    'description' => 'Direct target user dashboard redirection (/home) with persistent top warning bar and single-click Exit action.',
                    'component'   => 'webapp',
                ],
                [
                    'category'    => 'capability',
                    'title'       => 'Admin Subscription Bypass & Staff Filtering',
                    'description' => 'Unlimited plan access for admin roles and exclusion of staff accounts from analytics reports & subscriber metrics.',
                    'component'   => 'webapp',
                ],
                [
                    'category'    => 'security',
                    'title'       => 'Field-Level Data Encryption & Impersonation Protection',
                    'description' => 'AES-256-GCM crypto blueprint for telemetry storage and RBAC protection blocking impersonation of admin accounts.',
                    'component'   => 'platform',
                ],
                [
                    'category'    => 'refactor',
                    'title'       => 'Composite Telemetry & Obsolete Extractor Removal',
                    'description' => 'Streamlined telemetry into composite payloads (misc_hardware & misc_software) and removed 6 legacy extractors across Android client and WebApp.',
                    'component'   => 'platform',
                ],
                [
                    'category'    => 'fix',
                    'title'       => 'PDF Compilation & Date Column Reflection Fixes',
                    'description' => 'Resolved PDF report extracted_at array key exception and added dynamic date column inspection for export tables.',
                    'component'   => 'webapp',
                ],
            ],
        ];
    }

    protected function renderView(string $mainView, array $extraData = []): string
    {
        $impersonatedBy = session()->get('impersonated_by');
        $isImpersonating = $impersonatedBy !== null;

        $data = array_merge([
            'user_info' => $this->userData,
            'active_device_id' => null,
            'sidebar_user_devices' => [],
            'is_impersonating' => $isImpersonating,
            'impersonated_by' => $impersonatedBy,
        ], $this->getSystemVersionData(), $extraData);

        $sidebar = (auth()->user()->inGroup('superadmin') || $isImpersonating)
            ? 'headers_footers/sidebar_superadmin'
            : 'headers_footers/sidebar_admin';

        return view('headers_footers/head_users', $data)
            . view($sidebar, $data)
            . view($mainView, $data)
            . view('headers_footers/footer_users', $data);
    }

    protected function getDb(): \CodeIgniter\Database\BaseConnection
    {
        return \Config\Database::connect();
    }

    protected function logAdminAction(
        string $actionType,
        string $severity = 'medium',
        bool $success = true,
        array $extras = []
    ): void {
        $logModel = new LogUserActionModel();
        $request = service('request');

        $data = array_merge([
            'user_id' => $this->userId,
            'action_category' => 'admin',
            'action_type' => $actionType,
            'action_severity' => $severity,
            'ip_address' => $request->getIPAddress(),
            'request_url' => current_url(),
            'request_method' => $request->getMethod(),
            'success' => $success ? 1 : 0,
        ], $extras);

        $logModel->logAction($data);
    }

    protected function canManageRoles(): bool
    {
        return auth()->user()->can('users.manage-roles');
    }

    protected function getUserGroups(int $userId): array
    {
        $rows = $this->getDb()->table('auth_groups_users')
            ->select('`group`')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return array_values(array_unique(array_column($rows, 'group')));
    }

    protected function getHiddenSuperAdminIds(): array
    {
        if (in_array('superadmin', $this->getUserGroups($this->userId), true)) {
            return [];
        }

        $rows = $this->getDb()->table('auth_groups_users')
            ->select('user_id')
            ->where('group', 'superadmin')
            ->get()
            ->getResultArray();

        return array_map('intval', array_column($rows, 'user_id'));
    }
}
