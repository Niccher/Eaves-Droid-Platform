<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\Mod_Log_User_Action;

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
        ], $extraData);

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
        $logModel = new Mod_Log_User_Action();
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
