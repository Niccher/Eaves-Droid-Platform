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

        $validGroups = array_keys(setting('AuthGroups.groups'));
        if (!in_array($group, $validGroups, true)) {
            return false;
        }

        $db->transStart();

        $db->table('auth_groups_users')->where('user_id', $userId)->delete();
        $db->table('auth_groups_users')->insert([
            'user_id'    => $userId,
            'group'      => $group,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        return $db->transStatus();
    }
}
