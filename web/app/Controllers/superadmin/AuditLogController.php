<?php

namespace App\Controllers\superadmin;

class AuditLogController extends BaseSuperadminController
{
    public function index()
    {
        $db = $this->getDb();

        $severity = $this->request->getGet('severity');
        $category = $this->request->getGet('category');
        $outcome = $this->request->getGet('outcome');

        $builder = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left');

        if (in_array($severity, ['low', 'medium', 'high', 'critical'], true)) {
            $builder->where('tbl_user_actions.action_severity', $severity);
        }
        if ($category !== null && $category !== '') {
            $builder->where('tbl_user_actions.action_category', $category);
        }
        if (in_array($outcome, ['success', 'failed'], true)) {
            $builder->where('tbl_user_actions.success', $outcome === 'success' ? 1 : 0);
        }

        $totalLogs = $builder->countAllResults(false);
        $perPage = 25;
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $logs = $builder->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $pager = \Config\Services::pager();
        $pager->makeLinks($page, $perPage, $totalLogs, 'bootstrap5_full');

        $categories = $db->table('tbl_user_actions')
            ->select('action_category')
            ->distinct()
            ->orderBy('action_category', 'ASC')
            ->get()
            ->getResultArray();

        $securitySnapshots = $db->table('tbl_security_audit')
            ->select('tbl_security_audit.*, users.username')
            ->join('users', 'users.id = tbl_security_audit.owner_id', 'left')
            ->orderBy('tbl_security_audit.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return $this->renderView('superadmin/audit', [
            'pag' => 'superadmin-audit',
            'logs' => $logs,
            'categories' => array_column($categories, 'action_category'),
            'filters' => [
                'severity' => $severity,
                'category' => $category,
                'outcome' => $outcome,
            ],
            'security_snapshots' => $securitySnapshots,
            'pager' => $pager,
            'total_logs' => $totalLogs,
            'current_page' => $page,
            'per_page' => $perPage,
        ]);
    }
}
