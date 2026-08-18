<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;
use App\Models\SubscriptionModel;
use App\Models\PlanModel;
use App\Services\PlanGate;

/**
 * BillingController – simulated subscription checkout.
 *
 * This is a demo endpoint: it does NOT contact a real payment gateway.
 * It accepts a selected plan + billing cycle + payment method, then
 * self-upgrades the authenticated user's subscription (see
 * SubscriptionModel::setPlan()).
 */
class BillingController extends BaseClientController
{
    use ResponseTrait;

    /** @var SubscriptionModel */
    protected SubscriptionModel $subscriptions;

    /** @var PlanModel */
    protected PlanModel $plans;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->subscriptions = new SubscriptionModel();
        $this->plans         = new PlanModel();
    }

    /**
     * Valid plan keys that can be upgraded to.
     */
    private const PLANS = ['gold', 'platinum'];

    /**
     * GET /billing
     *
     * Standalone billing / upgrade page with plan comparison and a
     * simulated checkout modal. Always reachable from the user sidebar,
     * not just behind the 403 upgrade gate.
     *
     * @return string
     */
    public function index(): string
    {
        $gate = new PlanGate();
        $current = $gate->currentPlanKey($this->userId);

        // Load every plan's current version for the cards + comparison table.
        $versions = $this->plans->getCurrentVersions(); // keyed by slug
        $hierarchy = ['free', 'gold', 'platinum'];
        $currentIdx = array_search($current, $hierarchy, true);
        if ($currentIdx === false) {
            $currentIdx = 0;
        }

        // Upgrade options = plans strictly higher than the current one.
        $upgradePlans = array_values(array_filter($hierarchy, function ($p) use ($currentIdx) {
            return array_search($p, ['free', 'gold', 'platinum'], true) > $currentIdx;
        }));

        // Feature labels shared by the comparison table.
        $featureLabels = [
            'geofencing'         => 'Geo-Fencing & LocationController Intelligence',
            'risk_score'         => 'Anomaly Detection (Risk Scoring)',
            'forensic_export'    => 'Forensic Export & ReportsController',
            'push_notifications' => 'Real-time Push Alerts',
            'wellbeing'          => 'Digital Wellbeing Analytics',
            'correlation'        => 'CorrelationController Engine',
            'care_plan'          => 'Risk Score & Care PlansController',
            'smart_timeline'     => 'Unified Smart Timeline',
        ];

        return $this->renderBillingView('users/billing/index', [
            'pag'            => 'billing',
            'current_plan'   => $current,
            'plans'          => $versions,
            'upgrade_plans'  => $upgradePlans,
            'feature_labels' => $featureLabels,
            'csrf_token'     => csrf_hash(),
        ]);
    }

    /**
     * Render an authenticated user view with the shared users layout.
     *
     * @param string $mainView
     * @param array  $data
     * @return string
     */
    private function renderBillingView(string $mainView, array $data = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
        ], $this->getDeviceViewData(), $data);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_users', $data);
    }

    /**
     * POST /billing/simulate
     *
     * Body: plan, billing (monthly|yearly), payment_method, redirect_to (optional)
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function simulateUpgrade()
    {
        $body = $this->request->getJSON(true) ?? [];

        $plan    = strtolower((string) ($this->request->getPost('plan') ?? ($body['plan'] ?? '')));
        $billing = strtolower((string) ($this->request->getPost('billing') ?? ($body['billing'] ?? 'yearly')));
        $method  = (string) ($this->request->getPost('payment_method') ?? ($body['payment_method'] ?? 'card'));
        $redirectTo = (string) ($this->request->getPost('redirect_to') ?? ($body['redirect_to'] ?? ''));

        // ── Validate inputs ──
        if (!in_array($plan, self::PLANS, true)) {
            return $this->fail('Invalid plan selected.', 422);
        }
        if (!in_array($billing, ['monthly', 'yearly'], true)) {
            $billing = 'yearly';
        }

        $gate = new PlanGate();
        $current = $gate->currentPlanKey($this->userId);
        $hierarchy = ['free', 'gold', 'platinum'];
        $currentIdx = array_search($current, $hierarchy, true);
        $newIdx = array_search($plan, $hierarchy, true);
        if ($newIdx === false || $newIdx <= $currentIdx) {
            return $this->fail('This plan is not an upgrade from your current plan.', 422);
        }

        $version = $this->plans->getCurrentVersion($plan);
        if (!$version) {
            return $this->fail('Selected plan is not available.', 422);
        }

        $days = $billing === 'monthly' ? 30 : 365;
        $ok = $this->subscriptions->setPlan($this->userId, $plan, $days, $billing, 'active');

        if (!$ok) {
            return $this->fail('Could not update your subscription. Please try again.', 500);
        }

        // ── Determine the payable amount for the receipt ──
        $amountCents = $billing === 'monthly'
            ? (int) $version['price_monthly_cents']
            : (int) $version['price_yearly_cents'];

        // ── Send a simulated "upgrade confirmed" email to the account holder ──
        $this->sendUpgradeConfirmationEmail($current, $plan, $billing, $amountCents, $version);

        $this->response->setHeader('Cache-Control', 'no-store');

        return $this->respond([
            'success'        => true,
            'message'        => 'Subscription upgraded successfully.',
            'plan'           => $plan,
            'billing'        => $billing,
            'payment_method' => $method,
            'amount_cents'   => $amountCents,
            'currency'       => $version['currency'] ?? 'USD',
            'plan_name'      => ucfirst($plan),
            'redirect_to'    => $redirectTo ?: base_url('home'),
        ], 200);
    }

    /**
     * Compute the benefits gained when moving from $oldPlan to $newPlan:
     * newly-unlocked features and newly-unlocked algorithm tiers.
     *
     * @param string $oldPlan
     * @param string $newPlan
     * @return array{features: array, tiers: array}
     */
    private function planUpgradeDiff(string $oldPlan, string $newPlan): array
    {
        $featureLabels = [
            'geofencing'         => 'Geo-Fencing & LocationController Intelligence',
            'risk_score'         => 'Anomaly Detection (Risk Scoring)',
            'forensic_export'    => 'Forensic Export & ReportsController',
            'push_notifications' => 'Real-time Push Alerts',
            'wellbeing'          => 'Digital Wellbeing Analytics',
            'correlation'        => 'CorrelationController Engine',
            'care_plan'          => 'Risk Score & Care PlansController',
            'smart_timeline'     => 'Unified Smart Timeline',
        ];

        $tierLabels = [
            'core'     => 'Core Algorithms',
            'advanced' => 'AdvancedController Algorithms',
            'deep'     => 'ML-Engine Detectors',
        ];

        $oldVer = $this->plans->getCurrentVersion($oldPlan);
        $newVer = $this->plans->getCurrentVersion($newPlan);

        $oldFeatures = is_array($oldVer['features'] ?? null) ? $oldVer['features'] : [];
        $newFeatures = is_array($newVer['features'] ?? null) ? $newVer['features'] : [];
        $oldTiers    = is_array($oldVer['ml_algorithms'] ?? null) ? $oldVer['ml_algorithms'] : [];
        $newTiers    = is_array($newVer['ml_algorithms'] ?? null) ? $newVer['ml_algorithms'] : [];

        // Features enabled in the new plan but not in the old plan.
        $newFeatureKeys = array_keys(array_filter($newFeatures, function ($enabled) {
            return $enabled === true;
        }));
        $oldFeatureKeys = array_keys(array_filter($oldFeatures, function ($enabled) {
            return $enabled === true;
        }));
        $gainedFeatures = array_values(array_diff($newFeatureKeys, $oldFeatureKeys));
        $gainedFeatureLabels = array_values(array_filter(array_map(
            fn($k) => $featureLabels[$k] ?? null,
            $gainedFeatures
        )));

        // Algorithm tiers gained.
        $gainedTiers = array_values(array_diff($newTiers, $oldTiers));
        $gainedTierLabels = array_values(array_filter(array_map(
            fn($t) => $tierLabels[$t] ?? null,
            $gainedTiers
        )));

        return [
            'features' => $gainedFeatureLabels,
            'tiers'    => $gainedTierLabels,
        ];
    }

    /**
     * Send the simulated "plan upgraded" email to the account holder.
     */
    private function sendUpgradeConfirmationEmail(string $oldPlan, string $newPlan, string $billing, int $amountCents, array $newVersion): void
    {
        try {
            // Resolve the account holder's email from auth_identities.
            $db = \Config\Database::connect();
            $row = $db->table('auth_identities')
                ->where('user_id', $this->userId)
                ->where('type', 'email_password')
                ->get()
                ->getRowArray();
            $email = $row['secret'] ?? '';

            if (!$email) {
                log_message('error', "BillingController::sendUpgradeConfirmationEmail: no email identity for user #{$this->userId}");
                return;
            }

            $diff = $this->planUpgradeDiff($oldPlan, $newPlan);

            $periodEnd = $db->table('user_subscriptions')
                ->where('user_id', $this->userId)
                ->orderBy('current_period_end', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
            $periodEndStr = $periodEnd['current_period_end'] ?? null;
            $periodEndDisplay = $periodEndStr ? date('F j, Y', strtotime($periodEndStr)) : date('F j, Y', strtotime("+{$this->subscriptionDays($billing)} days"));

            helper('email');

            $username = $this->userData['username'] ?? 'User';

            send_templated_email(
                $email,
                "Your Eaves Droid plan has been upgraded to " . ucfirst($newPlan),
                'email/user/subscription_upgraded',
                [
                    'username'       => $username,
                    'oldPlan'        => $oldPlan,
                    'oldPlanName'    => ucfirst($oldPlan),
                    'newPlan'        => $newPlan,
                    'newPlanName'    => ucfirst($newPlan),
                    'billing'        => $billing,
                    'amountCents'    => $amountCents,
                    'currency'       => $newVersion['currency'] ?? 'USD',
                    'periodEnd'      => $periodEndDisplay,
                    'newFeatures'    => $diff['features'],
                    'newTiers'       => $diff['tiers'],
                    'dashboardUrl'   => base_url('billing'),
                ]
            );
        } catch (\Throwable $e) {
            log_message('error', "BillingController::sendUpgradeConfirmationEmail exception: " . $e->getMessage());
        }
    }

    /**
     * Helper: days for a billing cycle.
     */
    private function subscriptionDays(string $billing): int
    {
        return $billing === 'monthly' ? 30 : 365;
    }

    /**
     * GET /billing/subscription
     *
     * Returns the authenticated user's active subscription (AJAX).
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function subscription()
    {
        $gate = new PlanGate();
        $plan = $gate->currentPlanKey($this->userId);

        $this->response->setHeader('Cache-Control', 'no-store');

        return $this->respond([
            'success' => true,
            'plan'    => $plan,
        ], 200);
    }
}
