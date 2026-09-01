<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\PlanGate as PlanGateService;
use App\Models\SubscriptionModel;

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
        'geofencing' => [
            'location',
        ],
        'forensic_export' => [
            'forensic-export',
        ],
        'wellbeing' => [
            'analysis/wellbeing',
        ],
        'risk_score' => [
            'analysis/behavioral-anomalies',
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
        'analysis/wellbeing'            => ['intelligence', 'wellbeing'],
        'analysis/behavioral-anomalies'  => ['intelligence', 'behavioral'],
        'analysis/anomalies'            => ['intelligence', 'anomalies'],
        'location'                      => ['data', 'location'],
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

        $user = auth()->user();
        if ($user && ($user->inGroup('admin') || $user->inGroup('superadmin'))) {
            return null; // Admin and Superadmin bypass subscription gates
        }

        $userId = (int) auth()->id();
        $subModel = new SubscriptionModel();
        $limits = $subModel->getPlanLimits($userId);

        $featuresArr = $limits['features'] ?? [];
        if (is_string($featuresArr)) {
            $featuresArr = json_decode($featuresArr, true) ?: [];
        }

        $allowedHardware = $featuresArr['hardware_profile'] ?? 'basic'; // basic | advanced | all
        $allowedSoftware = $featuresArr['software_profile'] ?? 'basic'; // basic | advanced | all

        // 1. Check Analysis Suite Feature Gates (Free, Gold, Platinum)
        $analysisSlugMap = [
            'analysis/storage'              => 'storage_analysis',
            'analysis/apps'                 => 'apps_analysis',
            'analysis/lifestyle'            => 'lifestyle_analysis',
            'analysis/social'               => 'social_analysis',
            'analysis/privacy'              => 'privacy_analysis',
            'analysis/subscriptions'        => 'subscriptions_analysis',
            'analysis/sentiment'            => 'sentiment_analysis',
            'analysis/finance'              => 'finance_analysis',
            'analysis/location'             => 'location_analysis',
            'analysis/hotspots'             => 'hotspots_analysis',
            'analysis/report'               => 'report_export',
            'analysis/behavioral-anomalies' => 'anomalies_analysis',
            'analysis/anomalies/results'    => 'anomalies_analysis',
            'analysis/anomalies'           => 'anomalies_analysis',
            'analysis/wellbeing'           => 'wellbeing_analysis',
        ];

        $analysisSlug = $analysisSlugMap[$route] ?? null;

        // Query required tier dynamically from database
        $slug = $analysisSlug;
        if (!$slug) {
            if (str_starts_with($route, 'advanced/hardware/')) {
                $parts = explode('/', substr($route, strlen('advanced/hardware/')));
                $slug = $parts[0] ?? null;
            } elseif (str_starts_with($route, 'advanced/software/')) {
                $parts = explode('/', substr($route, strlen('advanced/software/')));
                $slug = $parts[0] ?? null;
            }
        }

        if ($slug) {
            $db = \Config\Database::connect();
            $feature = $db->table('tbl_feature_tiers')->where('slug', $slug)->get()->getRowArray();

            if ($feature) {
                $requiredTier = strtolower($feature['required_tier']);
                $label = $feature['label'];
                $currentPlan = strtolower($limits['plan'] ?? 'free');

                if ($requiredTier === 'platinum' && $currentPlan !== 'platinum') {
                    session()->setFlashdata('error', "Upgrade to Platinum plan to unlock {$label}.");
                    return redirect()->to(base_url('billing'));
                }
                if ($requiredTier === 'gold' && $currentPlan === 'free') {
                    session()->setFlashdata('error', "Upgrade to Gold or Platinum plan to unlock {$label}.");
                    return redirect()->to(base_url('billing'));
                }
            }
        }

        // 3. Enforce Legacy Feature Gates (e.g. Geofencing, wellbeing, etc.)
        $gate = new PlanGateService();

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
