<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;
use App\Services\PlanGate as PlanGateService;

/**
 * PlanGate — route-level plan feature enforcement.
 *
 * Blocks access to routes that require a paid feature flag. Features are
 * resolved from the user's active subscription plan (see SubscriptionModel).
 *
 * Example: a free user hitting /analysis/anomalies is redirected back with
 * an "upgrade required" message when the 'risk_score' feature is off.
 */
class PlanGate implements FilterInterface
{
    /**
     * Map feature => list of route prefixes that require it.
     */
    protected array $featureRoutes = [
        'risk_score' => [
            'analysis/anomalies',
            'analysis/anomaly',
        ],
        'geofencing' => [
            'location',
        ],
        'forensic_export' => [
            'forensic-export',
        ],
        'wellbeing' => [
            'analysis/wellbeing',
        ],
    ];

    protected array $skipRoutes = [
        'login',
        'logout',
        'register',
        'forgot',
        'error/403',
        'error/404',
        'error/500',
        'error/503',
        'landing',
        'api',
        'superadmin',
        'admin',
    ];

    public function before(RequestInterface $request, $arguments = null): ?RedirectResponse
    {
        $route = ltrim($request->getUri()->getPath(), '/');

        foreach ($this->skipRoutes as $skip) {
            if ($route === $skip || str_starts_with($route, $skip . '/')) {
                return null;
            }
        }

        if (!auth()->loggedIn()) {
            return null;
        }

        $userId = (int) auth()->id();
        $gate = new PlanGateService();

        foreach ($this->featureRoutes as $feature => $prefixes) {
            foreach ($prefixes as $prefix) {
                if ($route === $prefix || str_starts_with($route, $prefix . '/')) {
                    if (!$gate->hasFeature($userId, $feature)) {
                        return redirect()->back()
                            ->with('error', "Upgrade required to access this feature ($feature).");
                    }
                }
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
