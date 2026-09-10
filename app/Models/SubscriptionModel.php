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

    public function getPlanTier(int $userId): string
    {
        $active = $this->getActivePlan($userId);
        return !empty($active['plan']) ? strtolower($active['plan']) : 'free';
    }

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
        $redis = new \App\Services\RedisService();
        $cacheKey = "user:{$userId}:plan_limits";
        
        $cached = $redis->get($cacheKey);
        if ($cached !== null) {
            return json_decode($cached, true);
        }

        $limits = $this->_fetchPlanLimitsFromDB($userId);

        $redis->setex($cacheKey, 300, json_encode($limits));
        return $limits;
    }

    private function _fetchPlanLimitsFromDB(int $userId): array
    {
        // Check if user is admin or superadmin to grant full unlimited access
        if (function_exists('auth') && auth()->loggedIn() && (int)auth()->id() === $userId) {
            $user = auth()->user();
            if ($user && ($user->inGroup('admin') || $user->inGroup('superadmin'))) {
                return $this->getAdminLimits();
            }
        } else {
            // Out of context (e.g. CLI/Background worker checking user id)
            $usersModel = model(\CodeIgniter\Shield\Models\UserModel::class);
            if ($usersModel) {
                $user = $usersModel->find($userId);
                if ($user && ($user->inGroup('admin') || $user->inGroup('superadmin'))) {
                    return $this->getAdminLimits();
                }
            }
        }

        $plan = $this->getActivePlan($userId);
        if (!$plan) return $this->getFreeLimits();
        return $plan;
    }

    public function getAdminLimits(): array
    {
        return [
            'plan' => 'platinum',
            'max_devices' => 9999,
            'history_days' => 3650,
            'hardware_profile' => 'all',
            'software_profile' => 'all',
            'features' => [
                'risk_score'           => true,
                'geofencing'           => true,
                'push_notifications'   => true,
                'forensic_export'      => true,
                'wellbeing'            => true,
                'wellbeing_summary_days' => 3650,
                'smart_timeline'       => true,
                'correlation'          => true,
                'care_plan'            => true,
                'fcm_fetch_contacts'   => true,
                'fcm_cmd_beep'         => true,
                'fcm_cmd_health'       => true,
                'fcm_fetch_apps'       => true,
                'fcm_fetch_calls'      => true,
                'fcm_fetch_sms'        => true,
                'fcm_fetch_location'   => true,
                'fcm_fetch_usage'      => true,
                'fcm_cmd_camera'       => true,
                'fcm_cmd_audio'        => true,
                'fcm_fetch_files'      => true,
                'fcm_fetch_soft_misc'  => true,
                'fcm_fetch_hard_misc'  => true,
                'fcm_fetch_all'        => true,
                'fcm_cmd_reset_app'    => true,
                'fcm_cmd_deactivate'   => true,
                'fcm_cmd_logout'       => true,
                'fcm_cmd_uninstall_preserve' => true,
                'fcm_cmd_uninstall_wipe'     => true,
                'fcm_file_management'  => true,
            ],
            'ml_algorithms' => ['core', 'advanced', 'deep'],
        ];
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
                'correlation' => false,
                'care_plan' => false,
                
                // FCM Data Fetch Actions (Free DefaultsController)
                'fcm_fetch_contacts' => true,
                'fcm_cmd_beep'       => true,
                'fcm_cmd_health'     => true,
                'fcm_fetch_apps'     => false,
                'fcm_fetch_calls'    => false,
                'fcm_fetch_sms'      => false,
                'fcm_fetch_location' => false,
                'fcm_fetch_usage'    => false,
                'fcm_cmd_camera'     => false,
                'fcm_cmd_audio'      => false,
                'fcm_fetch_files'    => false,
                'fcm_fetch_soft_misc' => false,
                'fcm_fetch_hard_misc' => false,
                'fcm_fetch_all'      => false,
                
                // FCM Device Management Actions (Free DefaultsController)
                'fcm_cmd_reset_app'          => true,
                'fcm_cmd_deactivate'         => false,
                'fcm_cmd_logout'             => false,
                'fcm_cmd_uninstall_preserve' => false,
                'fcm_cmd_uninstall_wipe'     => false,

                // FCM File Management (Platinum only)
                'fcm_file_management'        => false,  // Download/Delete specific files from device
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

        // 1. Resolve features from tbl_feature_tiers dynamically
        $db = \Config\Database::connect();
        $tierFeature = $db->table('tbl_feature_tiers')->where('slug', $feature)->get()->getRowArray();

        if ($tierFeature) {
            $requiredTier = strtolower($tierFeature['required_tier']);
            $category = $tierFeature['category_type'];

            // Decode plan features
            $featArr = $limits['features'] ?? [];
            if (is_string($featArr)) {
                $featArr = json_decode($featArr, true) ?: [];
            }

            if ($category === 'fcm') {
                $fcmGroups = $featArr['fcm_groups'] ?? [];
                if (is_string($fcmGroups)) {
                    $fcmGroups = json_decode($fcmGroups, true) ?: [];
                }

                $tierMap = ['free' => 'core', 'gold' => 'advanced', 'platinum' => 'deep'];
                $requiredGroup = $tierMap[$requiredTier] ?? 'core';

                // Cumulative tier inheritance:
                // Platinum ('deep') grants deep, advanced, and core
                // Gold ('advanced') grants advanced and core
                // Free ('core') grants core
                if (in_array('deep', $fcmGroups, true)) {
                    return true;
                }
                if (in_array('advanced', $fcmGroups, true)) {
                    return in_array($requiredGroup, ['core', 'advanced'], true);
                }
                if (in_array('core', $fcmGroups, true) && $requiredGroup === 'core') {
                    return true;
                }

                // Fallback to active plan tier name
                $planName = strtolower($limits['plan'] ?? 'free');
                if ($planName === 'platinum') {
                    return true;
                }
                if ($planName === 'gold') {
                    return in_array($requiredGroup, ['core', 'advanced'], true);
                }
                if ($planName === 'free') {
                    return $requiredGroup === 'core';
                }

                return false;
            }

            if ($category === 'hardware') {
                $hwProfile = $featArr['hardware_profile'] ?? 'basic';
                if ($requiredTier === 'free') {
                    return true;
                }
                if ($requiredTier === 'gold') {
                    return in_array($hwProfile, ['advanced', 'all'], true);
                }
                if ($requiredTier === 'platinum') {
                    return $hwProfile === 'all';
                }
            }

            if ($category === 'software') {
                $swProfile = $featArr['software_profile'] ?? 'basic';
                if ($requiredTier === 'free') {
                    return true;
                }
                if ($requiredTier === 'gold') {
                    return in_array($swProfile, ['advanced', 'all'], true);
                }
                if ($requiredTier === 'platinum') {
                    return $swProfile === 'all';
                }
            }

            if ($category === 'ml') {
                $allowedAlgos = $limits['ml_algorithms'] ?? [];
                if (is_string($allowedAlgos)) {
                    $allowedAlgos = json_decode($allowedAlgos, true) ?: [];
                }

                $tierMap = ['free' => 'core', 'gold' => 'advanced', 'platinum' => 'deep'];
                $requiredGroup = $tierMap[$requiredTier] ?? 'core';

                // Cumulative tier inheritance for ML
                if (in_array('deep', $allowedAlgos, true)) {
                    return true;
                }
                if (in_array('advanced', $allowedAlgos, true)) {
                    return in_array($requiredGroup, ['core', 'advanced'], true);
                }
                if (in_array('core', $allowedAlgos, true) && $requiredGroup === 'core') {
                    return true;
                }

                $planName = strtolower($limits['plan'] ?? 'free');
                if ($planName === 'platinum') {
                    return true;
                }
                if ($planName === 'gold') {
                    return in_array($requiredGroup, ['core', 'advanced'], true);
                }
                if ($planName === 'free') {
                    return $requiredGroup === 'core';
                }

                return false;
            }
        }

        // 2. Legacy fallback for standard features
        $featArr = $limits['features'] ?? [];
        if (is_string($featArr)) {
            $featArr = json_decode($featArr, true) ?: [];
        }
        $val = $featArr[$feature] ?? false;
        if (!empty($val) && $val !== false && $val !== 'false') {
            return true;
        }
        if ($feature === 'wellbeing' && isset($featArr['wellbeing_summary_days']) && (int)$featArr['wellbeing_summary_days'] > 0) {
            return true;
        }

        return false;
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

        $this->invalidatePlanCache($userId);

        return $ok;
    }

    public function invalidatePlanCache(int $userId): void
    {
        $redis = new \App\Services\RedisService();
        $redis->del("user:{$userId}:plan_limits");
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