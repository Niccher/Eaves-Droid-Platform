<?php

namespace App\Controllers\superadmin;

use App\Controllers\superadmin\BaseSuperadminController;
use App\Models\SubscriptionModel;
use App\Models\PaymentModel;
use CodeIgniter\Database\BaseConnection;

class SubscriptionsController extends BaseSuperadminController
{
    protected SubscriptionModel $subscriptions;
    protected PaymentModel $payments;
    protected BaseConnection $db;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->subscriptions = new SubscriptionModel();
        $this->payments = new PaymentModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * List all users with their subscription status.
     */
    public function index()
    {
        $status = $this->request->getGet('status') ?? 'all';

        $builder = $this->db->table('users u')
            ->select('u.id, u.username, u.status AS account_status, u.active,
                      s.id AS subscription_id, s.plan, s.status AS sub_status,
                      s.billing_cycle, s.current_period_start, s.current_period_end,
                      s.canceled_at, s.trial_ends_at, s.payment_provider,
                      s.provider_subscription_id, s.payment_method,
                      ai.secret AS user_email,
                      (SELECT COUNT(*) FROM user_payments p WHERE p.user_id = u.id AND p.status = "succeeded") AS payment_count')
            ->join('user_subscriptions s', 's.user_id = u.id', 'left')
            ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
            ->whereNotIn('u.id', function (\CodeIgniter\Database\BaseBuilder $builder) {
                return $builder->select('user_id')->from('auth_groups_users')->whereIn('group', ['admin', 'superadmin']);
            })
            ->orderBy('s.current_period_end', 'DESC')
            ->orderBy('u.id', 'ASC');

        // Fetch all users but mark which have a paid plan
        $rows = $builder->get()->getResultArray();

        // Normalize: each user may appear once (latest sub via orderBy)
        $seen = [];
        $subscribers = [];
        foreach ($rows as $row) {
            if (!isset($seen[$row['id']])) {
                $seen[$row['id']] = true;
                $subscribers[] = $row;
            }
        }

        if ($status !== 'all') {
            $subscribers = array_values(array_filter($subscribers, fn($r) => ($r['sub_status'] ?? null) === $status));
        }

        $stats = $this->getSubscriberStats();

        return $this->renderView('superadmin/subscriptions/index', [
            'pag' => 'superadmin-subscriptions',
            'subscribers' => $subscribers,
            'status' => $status,
            'stats' => $stats,
        ]);
    }

    /**
     * Detail page for a single user: subscription + full payment history.
     */
    public function detail(int $userId)
    {
        $user = $this->db->table('users u')
            ->select('u.id, u.username, u.status AS account_status, u.active,
                      ai.secret AS user_email')
            ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
            ->where('u.id', $userId)
            ->get()
            ->getRowArray();

        if (!$user) {
            return redirect()->to('/superadmin/subscriptions')->with('error', 'User not found.');
        }

        $subscription = $this->db->table('user_subscriptions')
            ->where('user_id', $userId)
            ->orderBy('current_period_end', 'DESC')
            ->get()
            ->getRowArray();

        $payments = $this->payments->getForUser($userId);
        $stats = $this->payments->getStats();

        return $this->renderView('superadmin/subscriptions/detail', [
            'pag' => 'superadmin-subscriptions',
            'user' => $user,
            'subscription' => $subscription,
            'payments' => $payments,
            'stats' => $stats,
        ]);
    }

    /**
     * Payment history across all users (admin view).
     */
    public function payments()
    {
        $status = $this->request->getGet('status') ?? 'all';
        $payments = $this->payments->getWithUser($status);
        $stats = $this->payments->getStats();

        return $this->renderView('superadmin/subscriptions/payments', [
            'pag' => 'superadmin-payments',
            'payments' => $payments,
            'status' => $status,
            'stats' => $stats,
        ]);
    }

    /**
     * Manually upgrade/downgrade a user's plan (free|gold|platinum).
     * Used in place of a payment gateway for the current rollout.
     */
    public function changePlan(int $userId)
    {
        $plan = $this->request->getPost('plan');
        $days = (int)($this->request->getPost('days') ?? 365);
        $billing = $this->request->getPost('billing') ?? 'yearly';
        $status = $this->request->getPost('status') ?? 'active';

        if (!in_array($plan, ['free', 'gold', 'platinum'], true)) {
            return redirect()->back()->with('error', 'Invalid plan selected.');
        }
        if (!in_array($billing, ['monthly', 'yearly'], true)) {
            $billing = 'yearly';
        }
        if (!in_array($status, ['active', 'canceled', 'past_due', 'trialing'], true)) {
            $status = 'active';
        }
        if ($days < 1 || $days > 3650) {
            $days = 365;
        }

        $target = $this->db->table('users')
            ->select('users.id, users.username')
            ->where('users.id', $userId)
            ->where('users.deleted_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$target) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $oldSub = $this->db->table('user_subscriptions')
            ->where('user_id', $userId)
            ->orderBy('current_period_end', 'DESC')
            ->get()
            ->getRowArray();

        $oldPlan = $oldSub['plan'] ?? 'none';

        $db = $this->db;
        $db->transStart();

        try {
            $ok = $this->subscriptions->setPlan($userId, $plan, $days, $billing, $status);
            if (!$ok) {
                throw new \RuntimeException('Failed to save plan.');
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed');
            }

            $this->logAdminAction('subscription_plan_change', 'high', true, [
                'resource_id' => (string) $userId,
                'old_values' => json_encode([
                    'username' => $target['username'],
                    'plan' => $oldPlan,
                ]),
                'new_values' => json_encode([
                    'plan' => $plan,
                    'billing' => $billing,
                    'days' => $days,
                    'status' => $status,
                ]),
            ]);

            return redirect()->to("superadmin/subscriptions/{$userId}")
                ->with('success', "Plan set to {$plan} for {$target['username']}.");
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to change plan: ' . $e->getMessage());
        }
    }

    private function getSubscriberStats(): array
    {
        $db = $this->db;

        $now = date('Y-m-d H:i:s');

        $adminUserIdsSubquery = function (\CodeIgniter\Database\BaseBuilder $b) {
            return $b->select('user_id')->from('auth_groups_users')->whereIn('group', ['admin', 'superadmin']);
        };

        $paidPlans = $db->table('user_subscriptions s')
            ->where('s.status', 'active')
            ->where('s.plan !=', 'free')
            ->where('s.current_period_end >=', $now)
            ->whereNotIn('s.user_id', $adminUserIdsSubquery)
            ->countAllResults();

        $totalNonAdminUsers = $db->table('users u')
            ->whereNotIn('u.id', $adminUserIdsSubquery)
            ->countAllResults();

        $paidUserCount = $db->table('user_subscriptions s')
            ->select('s.user_id')
            ->where('s.status', 'active')
            ->where('s.plan !=', 'free')
            ->where('s.current_period_end >=', $now)
            ->whereNotIn('s.user_id', $adminUserIdsSubquery)
            ->distinct()
            ->countAllResults();

        $freeUsers = max(0, $totalNonAdminUsers - $paidUserCount);

        $activeSubs = $db->table('user_subscriptions s')
            ->where('s.status', 'active')
            ->whereNotIn('s.user_id', $adminUserIdsSubquery)
            ->countAllResults();

        $canceled = $db->table('user_subscriptions s')
            ->where('s.status', 'canceled')
            ->whereNotIn('s.user_id', $adminUserIdsSubquery)
            ->countAllResults();

        return [
            'paid_active' => $paidPlans,
            'free_users' => $freeUsers,
            'active_subs' => $activeSubs,
            'canceled' => $canceled,
        ];
    }
}
