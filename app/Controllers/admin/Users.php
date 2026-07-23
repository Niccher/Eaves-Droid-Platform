<?php

namespace App\Controllers\admin;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class Users extends BaseAdminController
{
    public function index()
    {
        $db = $this->getDb();

        $users = $db->table('users')
            ->select('users.*, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->where('users.deleted_at IS NULL')
            ->orderBy('users.created_at', 'DESC')
            ->limit(15)
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

        return $this->renderView('admin/users/index', [
            'pag' => 'admin-users',
            'users' => $users,
            'user_groups' => $grouped,
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
            ]);

            if (!$users->save($user)) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('errors', $users->errors());
            }

            $userId = $users->getInsertID();
            $savedUser = $users->findById($userId);
            $group = $this->request->getPost('group');
            $savedUser->addGroup($group);

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
            $db->table('auth_groups_users')
                ->where('user_id', $id)
                ->delete();
            $db->table('auth_groups_users')->insert([
                'user_id' => $id,
                'group' => $newGroup,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

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

        $db = $this->getDb();
        $db->table('users')->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('admin/users')->with('message', 'User deleted successfully.');
    }

    public function suspend(int $id)
    {
        if ($id === $this->userId) {
            return redirect()->to('admin/users')->with('error', 'You cannot suspend your own account.');
        }

        $db = $this->getDb();
        $db->table('users')
            ->where('id', $id)
            ->update(['active' => 0, 'status' => 'suspended']);

        return redirect()->to('admin/users')->with('message', 'User suspended successfully.');
    }

    public function activate(int $id)
    {
        $db = $this->getDb();
        $db->table('users')
            ->where('id', $id)
            ->update(['active' => 1, 'status' => null]);

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

        $tables = [
            'tbl_sms', 'tbl_logs', 'tbl_contacts', 'tbl_apps',
            'tbl_location', 'tbl_activity', 'tbl_device_files',
            'tbl_device_context', 'tbl_network_info', 'tbl_accounts',
            'tbl_calendar_events', 'tbl_app_usage', 'tbl_notifications',
            'tbl_bluetooth', 'tbl_sensor_profile', 'tbl_security_audit',
            'tbl_captured_media', 'tbl_sim_configs', 'uploaded_files',
        ];

        $db = $this->getDb();
        $totalDeleted = 0;

        foreach ($tables as $table) {
            $count = $db->table($table)->where('owner_id', $id)->countAllResults();
            if ($count > 0) {
                $db->table($table)->where('owner_id', $id)->delete();
                $totalDeleted += $count;
            }
        }

        $db->table('tbl_tokens')->where('owner_id', $id)->delete();
        $db->table('tbl_user_actions')->where('user_id', $id)->delete();

        return redirect()->to('admin/users/data/' . $id)
            ->with('message', "Cleared {$totalDeleted} records for user.");
    }

    public function delete_data_type(int $id, string $type)
    {
        $tableMap = [
            'sms' => 'tbl_sms', 'calls' => 'tbl_logs', 'contacts' => 'tbl_contacts',
            'apps' => 'tbl_apps', 'locations' => 'tbl_location', 'activities' => 'tbl_activity',
            'files' => 'tbl_device_files', 'uploads' => 'uploaded_files',
            'device' => 'tbl_device_context', 'network' => 'tbl_network_info',
            'accounts' => 'tbl_accounts', 'calendar' => 'tbl_calendar_events',
            'app_usage' => 'tbl_app_usage', 'notifications' => 'tbl_notifications',
            'bluetooth' => 'tbl_bluetooth', 'sensors' => 'tbl_sensor_profile',
            'security' => 'tbl_security_audit', 'media' => 'tbl_captured_media',
            'sim' => 'tbl_sim_configs',
        ];

        if (!isset($tableMap[$type])) {
            return redirect()->back()->with('error', 'Unknown data type: ' . $type);
        }

        $db = $this->getDb();
        $count = $db->table($tableMap[$type])->where('owner_id', $id)->countAllResults();
        if ($count > 0) {
            $db->table($tableMap[$type])->where('owner_id', $id)->delete();
        }

        return redirect()->to('admin/users/data/' . $id)
            ->with('message', "Deleted {$count} {$type} records.");
    }

    private function getUserDataCounts(int $userId): array
    {
        $db = $this->getDb();
        $tables = [
            'sms' => ['table' => 'tbl_sms', 'label' => 'SMS Messages'],
            'calls' => ['table' => 'tbl_logs', 'label' => 'Call Logs'],
            'contacts' => ['table' => 'tbl_contacts', 'label' => 'Contacts'],
            'apps' => ['table' => 'tbl_apps', 'label' => 'Installed Apps'],
            'locations' => ['table' => 'tbl_location', 'label' => 'Locations'],
            'activities' => ['table' => 'tbl_activity', 'label' => 'Activities'],
            'files' => ['table' => 'tbl_device_files', 'label' => 'Device Files'],
            'uploads' => ['table' => 'uploaded_files', 'label' => 'Uploaded Files'],
            'device' => ['table' => 'tbl_device_context', 'label' => 'Device Context'],
            'network' => ['table' => 'tbl_network_info', 'label' => 'Network Info'],
            'accounts' => ['table' => 'tbl_accounts', 'label' => 'Accounts'],
            'calendar' => ['table' => 'tbl_calendar_events', 'label' => 'Calendar Events'],
            'app_usage' => ['table' => 'tbl_app_usage', 'label' => 'App Usage'],
            'notifications' => ['table' => 'tbl_notifications', 'label' => 'Notifications'],
            'bluetooth' => ['table' => 'tbl_bluetooth', 'label' => 'Bluetooth'],
            'sensors' => ['table' => 'tbl_sensor_profile', 'label' => 'Sensor Profiles'],
            'security' => ['table' => 'tbl_security_audit', 'label' => 'Security Audit'],
            'media' => ['table' => 'tbl_captured_media', 'label' => 'Captured Media'],
            'sim' => ['table' => 'tbl_sim_configs', 'label' => 'SIM Configs'],
        ];

        $counts = [];
        foreach ($tables as $key => $info) {
            $count = $db->table($info['table'])->where('owner_id', $userId)->countAllResults();
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
