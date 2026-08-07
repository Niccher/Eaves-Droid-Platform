<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;
use App\Models\SubscriptionModel;
use App\Models\PlanModel;
use App\Services\PlanGate;

/**
 * Billing – simulated subscription checkout.
 *
 * This is a demo endpoint: it does NOT contact a real payment gateway.
 * It accepts a selected plan + billing cycle + payment method, then
 * self-upgrades the authenticated user's subscription (see
 * SubscriptionModel::setPlan()).
 */
class Billing extends BaseClientController
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

        // Determine the payable amount for the receipt
        $amountCents = $billing === 'monthly'
            ? (int) $version['price_monthly_cents']
            : (int) $version['price_yearly_cents'];

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
