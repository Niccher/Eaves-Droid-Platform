<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
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
        // NOTE: anomaly detection is NOT blocked here — it renders for all
        // users and is tiered inside the Anomalies controller (free=empty,
        // gold=basic, platinum=all).
        'geofencing' => [
            'location',
        ],
        'forensic_export' => [
            'forensic-export',
        ],
        'wellbeing' => [
            'analysis/wellbeing',
        ],
        'correlation' => [
            'analysis/correlation-engine',
        ],
        'care_plan' => [
            'analysis/care-plan',
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

    /**
     * Sidebar navigation context (pag / sub_pag) to pass to the upgrade page so
     * the matching sidebar item stays highlighted and the section stays open.
     */
    protected array $navMap = [
        'analysis/care-plan'           => ['intelligence', 'risk_care_plan'],
        'analysis/correlation-engine'  => ['intelligence', 'correlation_engine'],
        'analysis/wellbeing'           => ['intelligence', 'wellbeing'],
        'analysis/anomalies'           => ['intelligence', 'anomalies'],
        'location'                     => ['data', 'location'],
    ];

    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
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
        $gate   = new PlanGateService();

        foreach ($this->featureRoutes as $feature => $prefixes) {
            foreach ($prefixes as $prefix) {
                if ($route === $prefix || str_starts_with($route, $prefix . '/')) {
                    if (!$gate->hasFeature($userId, $feature)) {

                        $upgradePlans = $gate->upgradePlansForFeature($userId, $feature);

                        $nav = $this->navMap[$prefix] ?? [];

                        return service('response')
                            ->setStatusCode(403)
                            ->setBody(view('errors/custom_errors/subscription_upgrade', [
                                'feature'       => $feature,
                                'upgradePlans'  => $upgradePlans,
                                'current_plan'  => $gate->currentPlanKey($userId),
                                'redirect_to'   => base_url(parse_url($request->getUri(), PHP_URL_PATH) ?: 'home'),
                                'pag'           => $nav[0] ?? null,
                                'sub_pag'       => $nav[1] ?? null,
                            ]));
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
