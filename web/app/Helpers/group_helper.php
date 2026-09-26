<?php

if (!function_exists('setUserGroup')) {
    /**
     * Assign a single (exclusive) Shield group to a user.
     * Clears any existing group memberships first, so each user
     * belongs to exactly one privilege group.
     *
     * @param int    $userId
     * @param string $group  One of: superadmin, admin, developer, user, beta
     * @return bool
     */
    function setUserGroup(int $userId, string $group): bool
    {
        $db = \Config\Database::connect();

        $groups = setting('AuthGroups.groups') ?: (config('AuthGroups')->groups ?? []);
        $validGroups = is_array($groups) ? array_keys($groups) : [];
        if (!in_array($group, $validGroups, true)) {
            return false;
        }

        try {
            $users = model(\CodeIgniter\Shield\Models\UserModel::class);
            $user = $users->findById($userId);
            if ($user) {
                $user->syncGroups($group);
                return true;
            }
        } catch (\Throwable $e) {
            // Fall back to direct DB table operation
        }

        $db->table('auth_groups_users')->where('user_id', $userId)->delete();
        $db->table('auth_groups_users')->insert([
            'user_id'    => $userId,
            'group'      => $group,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }
}
