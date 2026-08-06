<?php

namespace App\Services;

use App\Models\SubscriptionModel;

/**
 * PlanGate — centralizes plan-based enforcement checks.
 *
 * Used by controllers, services and CLI commands to enforce a user's
 * subscription limits and feature flags across the platform.
 */
class PlanGate
{
    private SubscriptionModel $subscriptions;

    public function __construct(?SubscriptionModel $subscriptions = null)
    {
        $this->subscriptions = $subscriptions ?? new SubscriptionModel();
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
}
