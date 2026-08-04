<?php

namespace App\Controllers\superadmin;

class RoleMatrix extends BaseSuperadminController
{
    private const VALID_GROUPS = ['superadmin', 'admin', 'developer', 'user', 'beta'];

    public function index()
    {
        $db = $this->getDb();

        $perPage = 25;
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $totalUsers = (int) $db->table('users')->where('deleted_at IS NULL')->countAllResults();

        $users = $db->table('users')
            ->select('users.*, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->where('users.deleted_at IS NULL')
            ->orderBy('users.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $grouped = [];
        $groupRows = $db->table('auth_groups_users')
            ->select('user_id, `group`')
            ->get()
            ->getResultArray();
        foreach ($groupRows as $gr) {
            $uid = $gr['user_id'];
            if (!isset($grouped[$uid])) $grouped[$uid] = [];
            $grouped[$uid][] = $gr['group'];
        }

        $pager = \Config\Services::pager();
        $pager->makeLinks($page, $perPage, $totalUsers, 'bootstrap5_full');

        return $this->renderView('superadmin/role_matrix', [
            'pag' => 'superadmin-users',
            'users' => $users,
            'user_groups' => $grouped,
            'pager' => $pager,
            'total_users' => $totalUsers,
            'current_page' => $page,
            'per_page' => $perPage,
        ]);
    }

    public function changeRole(int $id)
    {
        $newGroup = $this->request->getPost('group');

        if (!in_array($newGroup, self::VALID_GROUPS, true)) {
            return redirect()->back()->with('error', 'Invalid role selected.');
        }

        if ($id === $this->userId && $newGroup !== 'superadmin') {
            return redirect()->back()->with('error', 'You cannot demote your own account from superadmin.');
        }

        $db = $this->getDb();
        $target = $db->table('users')
            ->select('users.id, users.username, auth_identities.secret AS email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->where('users.id', $id)
            ->where('users.deleted_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$target) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $currentGroups = $this->getUserGroups($id);
        $oldGroup = $currentGroups[0] ?? 'none';

        if ($oldGroup === $newGroup) {
            return redirect()->back()->with('message', 'No role change needed.');
        }

        $db->transStart();

        try {
            setUserGroup($id, $newGroup);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed');
            }

            $this->logAdminAction('admin_role_change', 'critical', true, [
                'resource_id' => (string) $id,
                'old_values' => json_encode([
                    'username' => $target['username'],
                    'group' => $oldGroup,
                ]),
                'new_values' => json_encode([
                    'group' => $newGroup,
                ]),
            ]);

            return redirect()->to('superadmin/users')
                ->with('message', "Role changed: {$target['username']} ({$oldGroup} → {$newGroup}).");

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to change role: ' . $e->getMessage());
        }
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
}
