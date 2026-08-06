<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table = 'user_subscriptions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'plan', 'status', 'billing_cycle',
        'current_period_start', 'current_period_end',
        'canceled_at', 'trial_ends_at',
        'payment_provider', 'provider_subscription_id', 'payment_method', 'metadata',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getActivePlan(int $userId): ?array
    {
        $row = $this->where('user_id', $userId)
            ->where('status', 'active')
            ->where('current_period_end >=', date('Y-m-d H:i:s'))
            ->orderBy('current_period_end', 'DESC')
            ->first();
        
        if (!$row) return null;
        
        $planModel = new PlanModel();
        $version = $planModel->getCurrentVersion($row['plan']);
        
        if (!$version) return null;
        
        return array_merge($row, $version);
    }

    public function getPlanLimits(int $userId): array
    {
        $plan = $this->getActivePlan($userId);
        if (!$plan) return $this->getFreeLimits();
        return $plan;
    }

    public function getFreeLimits(): array
    {
        return [
            'max_devices' => 1,
            'history_days' => 10,
            'features' => [
                'risk_score' => false,
                'geofencing' => false,
                'push_notifications' => false,
                'forensic_export' => true,
                'wellbeing_summary_days' => 0,
                'smart_timeline' => true,
            ],
            'ml_algorithms' => ['core'],
        ];
    }

    public function getDeviceLimit(int $userId): int
    {
        return $this->getPlanLimits($userId)['max_devices'] ?? 1;
    }

    public function getHistoryDays(int $userId): int
    {
        return $this->getPlanLimits($userId)['history_days'] ?? 10;
    }

    public function hasFeature(int $userId, string $feature): bool
    {
        $limits = $this->getPlanLimits($userId);
        return ($limits['features'][$feature] ?? false) === true;
    }

    public function getAllowedAlgorithms(int $userId): array
    {
        return $this->getPlanLimits($userId)['ml_algorithms'] ?? ['core'];
    }

    public function canAddDevice(int $userId, int $currentCount): bool
    {
        return $currentCount < $this->getDeviceLimit($userId);
    }

    /**
     * Manually set (upsert) a user's subscription plan.
     * Used by superadmin to grant/revoke access without a payment gateway.
     *
     * @param string $plan    free|gold|platinum
     * @param int    $days    period length in days (default 365)
     * @param string $billing monthly|yearly
     * @return bool
     */
    public function setPlan(int $userId, string $plan, int $days = 365, string $billing = 'yearly', string $status = 'active'): bool
    {
        $existing = $this->where('user_id', $userId)->orderBy('current_period_end', 'DESC')->first();

        $now = date('Y-m-d H:i:s');
        $periodStart = $now;
        $periodEnd = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        $data = [
            'user_id' => $userId,
            'plan' => $plan,
            'status' => $status,
            'billing_cycle' => $billing,
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
            'canceled_at' => null,
            'payment_provider' => 'manual',
            'payment_method' => 'superadmin',
        ];

        $ok = false;
        if ($existing) {
            $ok = $this->update($existing['id'], $data);
        } else {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            $ok = (bool)$this->insert($data);
        }

        // Record a matching payment row for paid plans so the payment
        // history stays in sync with subscriber onboarding.
        $this->recordManualPayment($userId, $plan, $billing);

        return $ok;
    }

    /**
     * Insert a payment row reflecting a manual plan grant (paid plans only).
     */
    private function recordManualPayment(int $userId, string $plan, string $billing): void
    {
        if (!in_array($plan, ['gold', 'platinum'], true)) {
            return;
        }

        $planModel = new \App\Models\PlanModel();
        $version = $planModel->getCurrentVersion($plan);
        if (!$version) {
            return;
        }

        $amountCents = $billing === 'monthly'
            ? (int)$version['price_monthly_cents']
            : (int)$version['price_yearly_cents'];

        if ($amountCents <= 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $paymentModel = new \App\Models\PaymentModel();
        $paymentModel->insert([
            'user_id' => $userId,
            'plan' => $plan,
            'billing_cycle' => $billing,
            'amount_cents' => $amountCents,
            'currency' => $version['currency'] ?? 'USD',
            'status' => 'succeeded',
            'payment_provider' => 'manual',
            'payment_method' => 'superadmin',
            'paid_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}