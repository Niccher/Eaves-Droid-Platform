<?php

namespace App\Services;

use App\Models\SubscriptionModel;
use App\Models\PlanModel;

/**
 * PlanGate — centralizes plan-based enforcement checks.
 *
 * Used by controllers, services and CLI commands to enforce a user's
 * subscription limits and feature flags across the platform.
 */
class PlanGate
{
    private SubscriptionModel $subscriptions;
    private PlanModel $plans;

    /** plan hierarchy – lowest → highest */
    private const HIERARCHY = ['free', 'gold', 'platinum'];

    public function __construct(?SubscriptionModel $subscriptions = null, ?PlanModel $plans = null)
    {
        $this->subscriptions = $subscriptions ?? new SubscriptionModel();
        $this->plans         = $plans ?? new PlanModel();
    }

    /**
     * Can this user register/link another device?
     */
    public function canAddDevice(int $userId, int $currentCount): bool
    {
        return $this->subscriptions->canAddDevice($userId, $currentCount);
    }

    public function getDeviceLimit(int $userId): int
    {
        return $this->subscriptions->getDeviceLimit($userId);
    }

    public function getHistoryDays(int $userId): int
    {
        return $this->subscriptions->getHistoryDays($userId);
    }

    /**
     * Is a given platform feature enabled for this user?
     * Features: geofencing, risk_score, push_notifications, forensic_export.
     */
    public function hasFeature(int $userId, string $feature): bool
    {
        return $this->subscriptions->hasFeature($userId, $feature);
    }

    /**
     * Algorithms allowed for this user, filtered to the given candidate list.
     */
    public function filterAlgorithms(int $userId, array $candidates): array
    {
        $allowed = $this->subscriptions->getAllowedAlgorithms($userId);
        if (empty($allowed)) {
            return $candidates;
        }
        return array_values(array_intersect($candidates, $allowed));
    }

    /**
     * Current plan limits for a user.
     */
    public function limits(int $userId): array
    {
        return $this->subscriptions->getPlanLimits($userId);
    }

    /**
     * Convert a user's allowed plan algorithm tiers (core|advanced|deep)
     * into the concrete algorithm IDs allowed for them.
     */
    public function allowedAlgorithmIds(int $userId, array $tierMap): array
    {
        $tiers = $this->subscriptions->getAllowedAlgorithms($userId);
        $tiers = is_array($tiers) ? $tiers : ['core'];

        $ids = [];
        foreach ($tierMap as $algId => $tier) {
            if (in_array($tier, $tiers, true)) {
                $ids[] = $algId;
            }
        }
        return $ids;
    }

    /* ------------------------------------------------------------------ */
    /*  NEW: helpers used by the filter                                    */
    /* ------------------------------------------------------------------ */

    /**
     * All plan keys that have the given feature enabled (according to the
     * *current* version row in `plan_versions`).
     */
    public function plansWithFeature(string $feature): array
    {
        $rows = $this->plans->getCurrentVersions();   // see PlanModel
        $allowed = [];

        foreach ($rows as $row) {
            $features = $row['features'] ?? [];
            if (($features[$feature] ?? false) === true) {
                $allowed[] = $row['slug'];            // 'gold', 'platinum', …
            }
        }
        return array_unique($allowed);
    }

    /**
     * Current user's active plan key ('free'|'gold'|'platinum').
     * Returns 'free' when the user has no active subscription row.
     */
    public function currentPlanKey(int $userId): string
    {
        $active = $this->subscriptions->getActivePlan($userId);
        return $active['plan'] ?? 'free';
    }

    /**
     * Given a feature, return the *upgrade* plans that
     *   a) own the feature, and
     *   b) are strictly higher than the user's current plan.
     */
    public function upgradePlansForFeature(int $userId, string $feature): array
    {
        $current = $this->currentPlanKey($userId);
        $currentIdx = array_search($current, self::HIERARCHY, true);
        if ($currentIdx === false) $currentIdx = 0;          // safety

        $candidates = $this->plansWithFeature($feature);

        return array_values(array_filter($candidates, function (string $plan) use ($currentIdx) {
            $idx = array_search($plan, self::HIERARCHY, true);
            return $idx !== false && $idx > $currentIdx;
        }));
    }
}
