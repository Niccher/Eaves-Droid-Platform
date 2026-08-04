<?php

namespace App\Controllers\admin;

class Tokens extends BaseAdminController
{
    public function index(string $tab = 'all')
    {
        $db = $this->getDb();

        $perPage = 25;
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $allBuilder = $db->table('tbl_tokens')
            ->select('tbl_tokens.*, users.username')
            ->join('users', 'users.id = tbl_tokens.owner_id', 'left')
            ->where('tbl_tokens.status !=', '99');
        $totalAll = (int) $allBuilder->countAllResults(false);
        $tokens = $allBuilder->orderBy('tbl_tokens.counter', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $expiredBuilder = $db->table('tbl_tokens')
            ->select('tbl_tokens.*, users.username')
            ->join('users', 'users.id = tbl_tokens.owner_id', 'left')
            ->where('tbl_tokens.expires_at <', date('Y-m-d H:i:s'))
            ->where('tbl_tokens.expires_at IS NOT NULL')
            ->where('tbl_tokens.status !=', '99');
        $totalExpired = (int) $expiredBuilder->countAllResults(false);
        $expiredTokens = $expiredBuilder->orderBy('tbl_tokens.expires_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $total = $db->table('tbl_tokens')->countAllResults();
        $active = $db->table('tbl_tokens')->where('status', '00')->countAllResults();
        $used = $db->table('tbl_tokens')->where('status', '11')->countAllResults();
        $expiredCount = $db->table('tbl_tokens')
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->where('status !=', '99')
            ->countAllResults();
        $deleted = $db->table('tbl_tokens')->where('status', '99')->countAllResults();

        $perUser = $db->table('tbl_tokens')
            ->select('tbl_tokens.owner_id, users.username, COUNT(*) as token_count')
            ->join('users', 'users.id = tbl_tokens.owner_id', 'left')
            ->where('tbl_tokens.status !=', '99')
            ->groupBy('tbl_tokens.owner_id')
            ->orderBy('token_count', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $usageByDay = $db->table('tbl_tokens')
            ->select("DATE(created_at) as date, COUNT(*) as count")
            ->where('created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')
            ->groupBy('DATE(created_at)')
            ->get()
            ->getResultArray();

        $pagerAll = \Config\Services::pager();
        $pagerAll->makeLinks($page, $perPage, $totalAll, 'bootstrap5_full');
        $pagerExpired = \Config\Services::pager();
        $pagerExpired->makeLinks($page, $perPage, $totalExpired, 'bootstrap5_full');

        return $this->renderView('admin/tokens/index', [
            'pag' => 'admin-tokens',
            'active_tab' => $tab,
            'tokens' => $tokens,
            'expired_tokens' => $expiredTokens,
            'total_all' => $totalAll,
            'total_expired' => $totalExpired,
            'pager_all' => $pagerAll,
            'pager_expired' => $pagerExpired,
            'current_page' => $page,
            'per_page' => $perPage,
            'count_all' => $totalAll,
            'total' => $total,
            'active' => $active,
            'used' => $used,
            'expired_count' => $expiredCount,
            'deleted' => $deleted,
            'per_user' => $perUser,
            'usage_by_day' => $usageByDay,
        ]);
    }

    public function expired()
    {
        return $this->index('expired');
    }

    public function analytics()
    {
        return $this->index('analytics');
    }

    public function revoke(int $tokenId)
    {
        $db = $this->getDb();
        $db->table('tbl_tokens')
            ->where('counter', $tokenId)
            ->update(['status' => '11']);

        $this->logAdminAction('admin_token_revoke', 'medium', true, [
            'resource_id' => (string) $tokenId,
        ]);

        return redirect()->to('admin/tokens')->with('message', 'Token revoked.');
    }

    public function regenerate(int $tokenId)
    {
        $db = $this->getDb();
        $token = $db->table('tbl_tokens')
            ->where('counter', $tokenId)
            ->get()
            ->getRowArray();

        if (!$token) {
            return redirect()->to('admin/tokens')->with('error', 'Token not found.');
        }

        $newToken = bin2hex(random_bytes(32));
        $db->table('tbl_tokens')
            ->where('counter', $tokenId)
            ->update([
                'token' => $newToken,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
                'status' => '00',
                'last_used_at' => null,
            ]);

        $this->logAdminAction('admin_token_regenerate', 'medium', true, [
            'resource_id' => (string) $tokenId,
        ]);

        return redirect()->to('admin/tokens')->with('message', 'Token regenerated.');
    }

    public function delete(int $tokenId)
    {
        $db = $this->getDb();
        $db->table('tbl_tokens')
            ->where('counter', $tokenId)
            ->update(['status' => '99']);

        $this->logAdminAction('admin_token_delete', 'high', true, [
            'resource_id' => (string) $tokenId,
        ]);

        return redirect()->to('admin/tokens')->with('message', 'Token deleted.');
    }
}
