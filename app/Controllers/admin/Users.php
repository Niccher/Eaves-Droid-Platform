<?php

namespace App\Controllers\admin;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class Users extends BaseAdminController
{
    public function index()
    {
        $db = $this->getDb();

        $perPage = 25;
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $userBuilder = $db->table('users')
            ->select('users.*, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->where('users.deleted_at IS NULL');

        $groupFilter = $this->request->getGet('group');
        if ($groupFilter !== null && $groupFilter !== '') {
            $validGroups = ['superadmin', 'admin', 'developer', 'beta', 'user'];
            if (in_array($groupFilter, $validGroups, true)) {
                $userBuilder->whereIn('users.id', static function ($builder) use ($groupFilter) {
                    return $builder->select('user_id')
                        ->from('auth_groups_users')
                        ->where('`group`', $groupFilter);
                });
            }
        }

        if (!$this->canManageRoles()) {
            $superAdminIds = $db->table('auth_groups_users')
                ->select('user_id')
                ->where('group', 'superadmin')
                ->get()
                ->getResultArray();
            $ids = array_column($superAdminIds, 'user_id');
            if ($ids !== []) {
                $userBuilder->whereNotIn('users.id', $ids);
            }
        }

        $totalUsers = (int) $userBuilder->countAllResults(false);

        $users = $userBuilder->orderBy('users.created_at', 'DESC')
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

        return $this->renderView('admin/users/index', [
            'pag' => 'admin-users',
            'users' => $users,
            'user_groups' => $grouped,
            'pager' => $pager,
            'total_users' => $totalUsers,
            'current_page' => $page,
            'per_page' => $perPage,
            'group_filter' => $groupFilter,
        ]);
    }

    public function create()
    {
        return $this->renderView('admin/users/create', [
            'pag' => 'admin-users',
        ]);
    }

    public function store()
    {
        $group = $this->request->getPost('group');

        if (in_array($group, self::PRIVILEGED_GROUPS, true) && !$this->canManageRoles()) {
            return redirect()->back()->withInput()->with('error', 'You do not have permission to assign that role.');
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]',
            'group' => 'required|in_list[user,beta,admin,developer,superadmin]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = $this->getDb();
        $db->transStart();

        try {
            $users = model(UserModel::class);
            $user = new User([
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'active' => 1,
            ]);

            if (!$users->save($user)) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('errors', $users->errors());
            }

            $userId = $users->getInsertID();
            $savedUser = $users->findById($userId);
            $group = $this->request->getPost('group');
            setUserGroup((int) $userId, $group);

            $profileData = [
                'user_id' => $userId,
                'language' => 'en',
                'timezone' => null,
                'theme' => 'system',
                'account_status' => 'active',
                'notifications_enabled' => true,
                'email_notifications' => true,
                'push_notifications' => true,
                'onboarding_completed' => false,
                'profile_completed' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $db->table('user_profiles')->insert($profileData);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed');
            }

            $this->logAdminAction('admin_user_create', 'medium', true, [
                'new_values' => json_encode([
                    'username' => $this->request->getPost('username'),
                    'email' => $this->request->getPost('email'),
                    'group' => $this->request->getPost('group'),
                ]),
                'resource_id' => (string) $userId,
            ]);

            return redirect()->to('admin/users')->with('message', 'User created successfully.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function edit(int $id)
    {
        $db = $this->getDb();

        $user = $db->table('users')
            ->select('users.*, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->where('users.id', $id)
            ->where('users.deleted_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$user) {
            return redirect()->to('admin/users')->with('error', 'User not found.');
        }

        $groups = $db->table('auth_groups_users')
            ->select('`group`')
            ->where('user_id', $id)
            ->get()
            ->getResultArray();
        $user['groups'] = array_column($groups, 'group');

        return $this->renderView('admin/users/edit', [
            'pag' => 'admin-users',
            'edit_user' => $user,
        ]);
    }

    public function update(int $id)
    {
        $db = $this->getDb();

        $existing = $db->table('users')
            ->where('id', $id)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$existing) {
            return redirect()->to('admin/users')->with('error', 'User not found.');
        }

        $targetGroups = $this->getUserGroups($id);
        $newGroup = $this->request->getPost('group');
        $roleChange = $targetGroups !== [] && !in_array($newGroup, $targetGroups, true);

        if (!$this->canManageRoles()) {
            if ($targetGroups !== [] && array_intersect($targetGroups, self::PRIVILEGED_GROUPS) !== []) {
                return redirect()->to('admin/users')->with('error', 'You do not have permission to modify a privileged account.');
            }

            if ($roleChange && in_array($newGroup, self::PRIVILEGED_GROUPS, true)) {
                return redirect()->back()->withInput()->with('error', 'You do not have permission to assign that role.');
            }
        }

        $rules = [
            'username' => "required|min_length[3]|max_length[30]|is_unique[users.username,id,{$id}]",
            'email' => "required|valid_email|is_unique[auth_identities.secret,user_id,{$id}]",
            'group' => 'required|in_list[user,beta,admin,developer,superadmin]',
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $rules['password'] = 'min_length[8]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db->transStart();

        try {
            $db->table('users')
                ->where('id', $id)
                ->update([
                    'username' => $this->request->getPost('username'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $email = $this->request->getPost('email');
            $db->table('auth_identities')
                ->where('user_id', $id)
                ->where('type', 'email_password')
                ->update(['secret' => $email]);

            if (!empty($password)) {
                $users = model(UserModel::class);
                $user = $users->findById($id);
                $user->setPassword($password);
                $users->save($user);
            }

            $newGroup = $this->request->getPost('group');
            setUserGroup((int) $id, $newGroup);

            $status = $this->request->getPost('status');
            if (in_array($status, ['active', 'suspended'])) {
                $db->table('users')
                    ->where('id', $id)
                    ->update([
                        'active' => $status === 'active' ? 1 : 0,
                        'status' => $status === 'active' ? null : 'suspended',
                    ]);
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed');
            }

            $newGroup = $this->request->getPost('group');
            $this->logAdminAction('admin_user_update', 'medium', true, [
                'resource_id' => (string) $id,
                'old_values' => json_encode([
                    'username' => $existing['username'],
                    'group' => $targetGroups[0] ?? null,
                    'status' => $existing['status'] ?? null,
                ]),
                'new_values' => json_encode([
                    'username' => $this->request->getPost('username'),
                    'email' => $this->request->getPost('email'),
                    'group' => $newGroup,
                    'status' => $this->request->getPost('status'),
                ]),
            ]);

            return redirect()->to('admin/users')->with('message', 'User updated successfully.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function delete(int $id)
    {
        if ($id === $this->userId) {
            return redirect()->to('admin/users')->with('error', 'You cannot delete your own account.');
        }

        $targetGroups = $this->getUserGroups($id);
        if (!$this->canManageRoles() && array_intersect($targetGroups, self::PRIVILEGED_GROUPS) !== []) {
            return redirect()->to('admin/users')->with('error', 'You do not have permission to delete a privileged account.');
        }

        $db = $this->getDb();
        $db->transStart();

        try {
            // Get user info before deletion for logging
            $user = $db->table('auth_identities')
                ->select('auth_identities.secret AS email, users.username')
                ->join('users', 'auth_identities.user_id = users.id')
                ->where('auth_identities.type', 'email_password')
                ->where('users.id', $id)
                ->get()
                ->getRowArray();

            // Delete from auth_identities (Shield auth - email/password, etc.)
            $db->table('auth_identities')->where('user_id', $id)->delete();

            // Delete from auth_groups_users (Shield auth - group memberships)
            $db->table('auth_groups_users')->where('user_id', $id)->delete();

            // Delete from auth_permissions_users (Shield auth - direct permissions)
            $db->table('auth_permissions_users')->where('user_id', $id)->delete();

            // Delete from auth_remember_tokens (Shield auth - remember me tokens)
            $db->table('auth_remember_tokens')->where('user_id', $id)->delete();

            // Delete from auth_logins (Shield auth - login history)
            $db->table('auth_logins')->where('user_id', $id)->delete();

            // Delete from auth_token_logins (Shield auth - token-based logins)
            $db->table('auth_token_logins')->where('user_id', $id)->delete();

            // Delete user profiles
            $db->table('user_profiles')->where('user_id', $id)->delete();

            // Delete user's data from all data tables
            $dataTables = [
                ['table' => 'tbl_sms', 'column' => 'owner_id'],
                ['table' => 'tbl_logs', 'column' => 'owner_id'],
                ['table' => 'tbl_contacts', 'column' => 'owner_id'],
                ['table' => 'tbl_apps', 'column' => 'owner_id'],
                ['table' => 'tbl_location', 'column' => 'owner_id'],
                ['table' => 'tbl_activity', 'column' => 'owner_id'],
                ['table' => 'tbl_device_files', 'column' => 'owner_id'],
                ['table' => 'tbl_device_context', 'column' => 'owner_id'],
                ['table' => 'tbl_network_info', 'column' => 'owner_id'],
                ['table' => 'tbl_accounts', 'column' => 'owner_id'],
                ['table' => 'tbl_calendar_events', 'column' => 'owner_id'],
                ['table' => 'tbl_app_usage', 'column' => 'owner_id'],
                ['table' => 'tbl_notifications', 'column' => 'owner_id'],
                ['table' => 'tbl_bluetooth', 'column' => 'owner_id'],
                ['table' => 'tbl_sensor_profile', 'column' => 'owner_id'],
                ['table' => 'tbl_security_audit', 'column' => 'owner_id'],
                ['table' => 'tbl_captured_media', 'column' => 'owner_id'],
                ['table' => 'tbl_sim_configs', 'column' => 'owner_id'],
                ['table' => 'uploaded_files', 'column' => 'token_owner_id'],
                // Advanced Hardware tables
                ['table' => 'tbl_device_profile', 'column' => 'owner_id'],
                ['table' => 'tbl_proc_info', 'column' => 'owner_id'],
                ['table' => 'tbl_running_processes', 'column' => 'owner_id'],
                ['table' => 'tbl_running_process_details', 'column' => 'owner_id'],
                ['table' => 'tbl_running_services', 'column' => 'owner_id'],
                ['table' => 'tbl_camera_info', 'column' => 'owner_id'],
                ['table' => 'tbl_battery_stats', 'column' => 'owner_id'],
                ['table' => 'tbl_accessibility_services', 'column' => 'owner_id'],
                ['table' => 'tbl_input_methods', 'column' => 'owner_id'],
                ['table' => 'tbl_input_method_subtypes', 'column' => 'owner_id'],
                ['table' => 'tbl_cell_towers', 'column' => 'owner_id'],
                ['table' => 'tbl_display_info', 'column' => 'owner_id'],
                ['table' => 'tbl_storage', 'column' => 'owner_id'],
                ['table' => 'tbl_thermal', 'column' => 'owner_id'],
                ['table' => 'tbl_nfc', 'column' => 'owner_id'],
                ['table' => 'tbl_hardware_graphics', 'column' => 'owner_id'],
                ['table' => 'tbl_hardware_network', 'column' => 'owner_id'],
                ['table' => 'tbl_app_security', 'column' => 'owner_id'],
                ['table' => 'tbl_network_security', 'column' => 'owner_id'],
                ['table' => 'tbl_telephony_network', 'column' => 'owner_id'],
                ['table' => 'tbl_system_locale', 'column' => 'owner_id'],
                ['table' => 'tbl_app_usage_sessions', 'column' => 'owner_id'],
                ['table' => 'tbl_data_usage', 'column' => 'owner_id'],
                ['table' => 'tbl_saved_wifi', 'column' => 'owner_id'],
                ['table' => 'tbl_default_apps', 'column' => 'owner_id'],
                ['table' => 'tbl_alarms', 'column' => 'owner_id'],
                ['table' => 'tbl_nearby_wifi', 'column' => 'owner_id'],
                ['table' => 'tbl_bluetooth_paired', 'column' => 'owner_id'],
            ];

            foreach ($dataTables as $info) {
                $db->table($info['table'])->where($info['column'], $id)->delete();
            }

            // Delete token-related data
            $db->table('tbl_tokens')->where('owner_id', $id)->delete();
            $db->table('tbl_user_actions')->where('user_id', $id)->delete();

            // Finally, soft delete the user record
            $db->table('users')->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed');
            }

            $this->logAdminAction('admin_user_delete', 'high', true, [
                'resource_id' => (string) $id,
                'new_values' => json_encode([
                    'username' => $user['username'] ?? 'unknown',
                    'email' => $user['email'] ?? 'unknown',
                ]),
            ]);

            return redirect()->to('admin/users')->with('message', 'User and all associated data deleted successfully.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('admin/users')->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    public function suspend(int $id)
    {
        if ($id === $this->userId) {
            return redirect()->to('admin/users')->with('error', 'You cannot suspend your own account.');
        }

        if (!$this->canManageRoles() && array_intersect($this->getUserGroups($id), self::PRIVILEGED_GROUPS) !== []) {
            return redirect()->to('admin/users')->with('error', 'You do not have permission to suspend a privileged account.');
        }

        $db = $this->getDb();
        $db->table('users')
            ->where('id', $id)
            ->update(['active' => 0, 'status' => 'suspended']);

        $this->logAdminAction('admin_user_suspend', 'high', true, [
            'resource_id' => (string) $id,
        ]);

        return redirect()->to('admin/users')->with('message', 'User suspended successfully.');
    }

    public function activate(int $id)
    {
        if (!$this->canManageRoles() && array_intersect($this->getUserGroups($id), self::PRIVILEGED_GROUPS) !== []) {
            return redirect()->to('admin/users')->with('error', 'You do not have permission to activate a privileged account.');
        }

        $db = $this->getDb();
        $db->table('users')
            ->where('id', $id)
            ->update(['active' => 1, 'status' => null]);

        $this->logAdminAction('admin_user_activate', 'medium', true, [
            'resource_id' => (string) $id,
        ]);

        return redirect()->to('admin/users')->with('message', 'User activated successfully.');
    }

    public function user_data(int $id)
    {
        $db = $this->getDb();

        $user = $db->table('users')
            ->select('users.*, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->where('users.id', $id)
            ->where('users.deleted_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$user) {
            return redirect()->to('admin/users')->with('error', 'User not found.');
        }

        $dataCounts = $this->getUserDataCounts($id);

        return $this->renderView('admin/users/view_data', [
            'pag' => 'admin-users',
            'view_user' => $user,
            'data_counts' => $dataCounts,
        ]);
    }

    public function clear_user_data(int $id)
    {
        if ($id === $this->userId) {
            return redirect()->to('admin/users')->with('error', 'You cannot clear your own data.');
        }

        if (!$this->canManageRoles() && array_intersect($this->getUserGroups($id), self::PRIVILEGED_GROUPS) !== []) {
            return redirect()->to('admin/users')->with('error', 'You do not have permission to clear data for a privileged account.');
        }

        $tables = [
            ['table' => 'tbl_sms', 'column' => 'owner_id'],
            ['table' => 'tbl_logs', 'column' => 'owner_id'],
            ['table' => 'tbl_contacts', 'column' => 'owner_id'],
            ['table' => 'tbl_apps', 'column' => 'owner_id'],
            ['table' => 'tbl_location', 'column' => 'owner_id'],
            ['table' => 'tbl_activity', 'column' => 'owner_id'],
            ['table' => 'tbl_device_files', 'column' => 'owner_id'],
            ['table' => 'tbl_device_context', 'column' => 'owner_id'],
            ['table' => 'tbl_network_info', 'column' => 'owner_id'],
            ['table' => 'tbl_accounts', 'column' => 'owner_id'],
            ['table' => 'tbl_calendar_events', 'column' => 'owner_id'],
            ['table' => 'tbl_app_usage', 'column' => 'owner_id'],
            ['table' => 'tbl_notifications', 'column' => 'owner_id'],
            ['table' => 'tbl_bluetooth', 'column' => 'owner_id'],
            ['table' => 'tbl_sensor_profile', 'column' => 'owner_id'],
            ['table' => 'tbl_security_audit', 'column' => 'owner_id'],
            ['table' => 'tbl_captured_media', 'column' => 'owner_id'],
            ['table' => 'tbl_sim_configs', 'column' => 'owner_id'],
            ['table' => 'uploaded_files', 'column' => 'token_owner_id'],
        ];

        $db = $this->getDb();
        $totalDeleted = 0;

        foreach ($tables as $info) {
            $count = $db->table($info['table'])->where($info['column'], $id)->countAllResults();
            if ($count > 0) {
                $db->table($info['table'])->where($info['column'], $id)->delete();
                $totalDeleted += $count;
            }
        }

        $db->table('tbl_tokens')->where('owner_id', $id)->delete();
        $db->table('tbl_user_actions')->where('user_id', $id)->delete();

        $this->logAdminAction('admin_clear_user_data', 'critical', true, [
            'resource_id' => (string) $id,
            'new_values' => json_encode(['record_count' => $totalDeleted]),
        ]);

        return redirect()->to('admin/users/data/' . $id)
            ->with('message', "Cleared {$totalDeleted} records for user.");
    }

    public function delete_data_type(int $id, string $type)
    {
        $tableMap = [
            'sms' => ['table' => 'tbl_sms', 'column' => 'owner_id'],
            'calls' => ['table' => 'tbl_logs', 'column' => 'owner_id'],
            'contacts' => ['table' => 'tbl_contacts', 'column' => 'owner_id'],
            'apps' => ['table' => 'tbl_apps', 'column' => 'owner_id'],
            'locations' => ['table' => 'tbl_location', 'column' => 'owner_id'],
            'activities' => ['table' => 'tbl_activity', 'column' => 'owner_id'],
            'files' => ['table' => 'tbl_device_files', 'column' => 'owner_id'],
            'uploads' => ['table' => 'uploaded_files', 'column' => 'token_owner_id'],
            'device' => ['table' => 'tbl_device_context', 'column' => 'owner_id'],
            'network' => ['table' => 'tbl_network_info', 'column' => 'owner_id'],
            'accounts' => ['table' => 'tbl_accounts', 'column' => 'owner_id'],
            'calendar' => ['table' => 'tbl_calendar_events', 'column' => 'owner_id'],
            'app_usage' => ['table' => 'tbl_app_usage', 'column' => 'owner_id'],
            'notifications' => ['table' => 'tbl_notifications', 'column' => 'owner_id'],
            'bluetooth' => ['table' => 'tbl_bluetooth', 'column' => 'owner_id'],
            'sensors' => ['table' => 'tbl_sensor_profile', 'column' => 'owner_id'],
            'security' => ['table' => 'tbl_security_audit', 'column' => 'owner_id'],
            'media' => ['table' => 'tbl_captured_media', 'column' => 'owner_id'],
            'sim' => ['table' => 'tbl_sim_configs', 'column' => 'owner_id'],
        ];

        if (!isset($tableMap[$type])) {
            return redirect()->back()->with('error', 'Unknown data type: ' . $type);
        }

        if (!$this->canManageRoles() && array_intersect($this->getUserGroups($id), self::PRIVILEGED_GROUPS) !== []) {
            return redirect()->back()->with('error', 'You do not have permission to modify data for a privileged account.');
        }

        $db = $this->getDb();
        $info = $tableMap[$type];
        $count = $db->table($info['table'])->where($info['column'], $id)->countAllResults();
        if ($count > 0) {
            $db->table($info['table'])->where($info['column'], $id)->delete();
        }

        $this->logAdminAction('admin_delete_data_type', 'high', true, [
            'resource_id' => (string) $id,
            'new_values' => json_encode(['type' => $type, 'count' => $count]),
        ]);

        return redirect()->to('admin/users/data/' . $id)
            ->with('message', "Deleted {$count} {$type} records.");
    }

    private function getUserDataCounts(int $userId): array
    {
        $db = $this->getDb();
        $tables = [
            'sms' => ['table' => 'tbl_sms', 'label' => 'SMS Messages', 'column' => 'owner_id'],
            'calls' => ['table' => 'tbl_logs', 'label' => 'Call Logs', 'column' => 'owner_id'],
            'contacts' => ['table' => 'tbl_contacts', 'label' => 'Contacts', 'column' => 'owner_id'],
            'apps' => ['table' => 'tbl_apps', 'label' => 'Installed Apps', 'column' => 'owner_id'],
            'locations' => ['table' => 'tbl_location', 'label' => 'Locations', 'column' => 'owner_id'],
            'activities' => ['table' => 'tbl_activity', 'label' => 'Activities', 'column' => 'owner_id'],
            'files' => ['table' => 'tbl_device_files', 'label' => 'Device Files', 'column' => 'owner_id'],
            'uploads' => ['table' => 'uploaded_files', 'label' => 'Uploaded Files', 'column' => 'token_owner_id'],
            'device' => ['table' => 'tbl_device_context', 'label' => 'Device Context', 'column' => 'owner_id'],
            'network' => ['table' => 'tbl_network_info', 'label' => 'Network Info', 'column' => 'owner_id'],
            'accounts' => ['table' => 'tbl_accounts', 'label' => 'Accounts', 'column' => 'owner_id'],
            'calendar' => ['table' => 'tbl_calendar_events', 'label' => 'Calendar Events', 'column' => 'owner_id'],
            'app_usage' => ['table' => 'tbl_app_usage', 'label' => 'App Usage', 'column' => 'owner_id'],
            'notifications' => ['table' => 'tbl_notifications', 'label' => 'Notifications', 'column' => 'owner_id'],
            'bluetooth' => ['table' => 'tbl_bluetooth', 'label' => 'Bluetooth', 'column' => 'owner_id'],
            'sensors' => ['table' => 'tbl_sensor_profile', 'label' => 'Sensor Profiles', 'column' => 'owner_id'],
            'security' => ['table' => 'tbl_security_audit', 'label' => 'Security Audit', 'column' => 'owner_id'],
            'media' => ['table' => 'tbl_captured_media', 'label' => 'Captured Media', 'column' => 'owner_id'],
            'sim' => ['table' => 'tbl_sim_configs', 'label' => 'SIM Configs', 'column' => 'owner_id'],
        ];

        $counts = [];
        foreach ($tables as $key => $info) {
            $column = $info['column'] ?? 'owner_id';
            $count = $db->table($info['table'])->where($column, $userId)->countAllResults();
            $counts[] = [
                'key' => $key,
                'table' => $info['table'],
                'label' => $info['label'],
                'count' => $count,
            ];
        }

        return $counts;
    }
}
