<?php

namespace App\Controllers\superadmin;

class Dashboard extends BaseSuperadminController
{
    public function index()
    {
        $db = $this->getDb();

        $groupCounts = [];
        $groupRows = $db->table('auth_groups_users')
            ->select('`group`, COUNT(*) AS total')
            ->groupBy('`group`')
            ->get()
            ->getResultArray();
        foreach ($groupRows as $gr) {
            $groupCounts[$gr['group']] = (int) $gr['total'];
        }

        $totalUsers = (int) ($db->table('users')->where('deleted_at IS NULL')->countAllResults());
        $adminActions = (int) $db->table('tbl_user_actions')
            ->where('action_category', 'admin')
            ->countAllResults();
        $securitySnapshots = (int) $db->table('tbl_security_audit')->countAllResults();
        $criticalActions = (int) $db->table('tbl_user_actions')
            ->whereIn('action_severity', ['high', 'critical'])
            ->countAllResults();

        $recentAdminActions = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $privilegedAccounts = $db->table('auth_groups_users')
            ->select('users.id, users.username, auth_identities.secret AS email, auth_groups_users.group')
            ->join('users', 'users.id = auth_groups_users.user_id', 'inner')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->whereIn('auth_groups_users.group', ['superadmin', 'admin', 'developer'])
            ->orderBy('auth_groups_users.group', 'ASC')
            ->get()
            ->getResultArray();

        return $this->renderView('superadmin/home', [
            'pag' => 'superadmin-home',
            'total_users' => $totalUsers,
            'group_counts' => $groupCounts,
            'admin_actions' => $adminActions,
            'security_snapshots' => $securitySnapshots,
            'critical_actions' => $criticalActions,
            'recent_admin_actions' => $recentAdminActions,
            'privileged_accounts' => $privilegedAccounts,
        ]);
    }
}
