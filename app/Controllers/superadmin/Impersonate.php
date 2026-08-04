<?php

namespace App\Controllers\superadmin;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class Impersonate extends BaseSuperadminController
{
    public function index()
    {
        $db = $this->getDb();
        $userModel = model(UserModel::class);

        $superAdminIds = $db->table('auth_groups_users')
            ->select('user_id')
            ->where('group', 'superadmin')
            ->get()
            ->getResultArray();
        $superAdminIds = array_map('intval', array_column($superAdminIds, 'user_id'));

        $users = $userModel->where('deleted_at', null)
            ->whereNotIn('id', $superAdminIds)
            ->orderBy('username', 'asc')
            ->findAll();

        $grouped = [];
        $groupRows = $db->table('auth_groups_users')
            ->select('user_id, `group`')
            ->get()
            ->getResultArray();
        foreach ($groupRows as $gr) {
            $uid = (int) $gr['user_id'];
            if (!isset($grouped[$uid])) {
                $grouped[$uid] = [];
            }
            $grouped[$uid][] = $gr['group'];
        }

        $data = [
            'pag' => 'superadmin-impersonate',
            'users' => $users,
            'user_groups' => $grouped,
            'impersonated_by' => session()->get('impersonated_by'),
        ];

        return $this->renderView('superadmin/impersonate', $data);
    }

    public function actAs(int $userId)
    {
        if ($userId === $this->userId) {
            return redirect()->back()->with('error', 'You cannot impersonate yourself.');
        }

        if (session()->has('impersonated_by')) {
            return redirect()->back()->with('error', 'You are already impersonating a user. Stop first.');
        }

        $targetGroups = $this->getUserGroups($userId);
        if (in_array('superadmin', $targetGroups, true)) {
            return redirect()->back()->with('error', 'You cannot impersonate a superadmin account.');
        }

        $userModel = model(UserModel::class);
        $targetUser = $userModel->find($userId);

        if ($targetUser === null) {
            return redirect()->back()->with('error', 'User not found.');
        }

        session()->set('impersonated_by', $this->userId);
        session()->set('impersonated_username', $targetUser->username);

        auth()->loginById($userId);

        $this->logAdminAction('impersonate_start', 'high', true, [
            'resource_id' => (string) $userId,
            'new_values' => json_encode([
                'impersonated_user_id' => $userId,
                'impersonated_username' => $targetUser->username,
            ]),
        ]);

        return redirect()->to('admin/dashboard')->with('message', 'Now impersonating ' . $targetUser->username);
    }

    public function stop()
    {
        $impersonatedBy = session()->get('impersonated_by');

        if ($impersonatedBy === null) {
            return redirect()->to('superadmin/impersonate')->with('error', 'You are not currently impersonating anyone.');
        }

        $impersonatedUserId = $this->userId;

        auth()->loginById((int) $impersonatedBy);

        session()->remove('impersonated_by');
        session()->remove('impersonated_username');

        $this->logAdminAction('impersonate_stop', 'high', true, [
            'resource_id' => (string) $impersonatedUserId,
            'new_values' => json_encode([
                'impersonated_user_id' => $impersonatedUserId,
                'restored_user_id' => (int) $impersonatedBy,
            ]),
        ]);

        return redirect()->to('superadmin/impersonate')->with('message', 'Impersonation stopped. You are now acting as yourself.');
    }
}