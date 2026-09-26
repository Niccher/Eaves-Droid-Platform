<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;
use App\Libraries\PesapalService;
use App\Models\SubscriptionModel;
use App\Models\PaymentModel;

class PesapalCallbackController extends BaseController
{
    protected PesapalService $pesapal;
    protected SubscriptionModel $subscriptions;
    protected PaymentModel $payments;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->pesapal = new PesapalService();
        $this->subscriptions = new SubscriptionModel();
        $this->payments = new PaymentModel();
    }

    /**
     * GET /billing/pesapal/callback
     * Handles user redirect from Pesapal.
     */
    public function handleCallback()
    {
        $orderTrackingId = $this->request->getGet('OrderTrackingId');
        $reference = $this->request->getGet('OrderMerchantReference');

        if (!$orderTrackingId) {
            return redirect()->to(base_url('billing'))->with('error', 'Invalid payment tracking ID.');
        }

        // Fetch transaction status from Pesapal
        $statusData = $this->pesapal->getTransactionStatus($orderTrackingId);

        if ($statusData && isset($statusData['status_code'])) {
            $statusCode = (int)$statusData['status_code']; // 1 = Success, 0 = Pending, 2 = Failed, 3 = Refunded
            
            if ($statusCode === 1) {
                // Success
                $this->processPaymentSuccess($statusData);
                return redirect()->to(base_url('billing'))->with('success', 'Your payment was successful! Your subscription has been updated.');
            } elseif ($statusCode === 0) {
                // Pending
                return redirect()->to(base_url('billing'))->with('info', 'Your payment is being processed. We will update your subscription as soon as it clears.');
            }
        }

        return redirect()->to(base_url('billing'))->with('error', 'Payment failed or was canceled.');
    }

    /**
     * GET /billing/pesapal/ipn
     * Handles background IPN call from Pesapal (GET request).
     */
    /**
     * GET / POST /billing/pesapal/ipn
     * Handles background IPN call from Pesapal server.
     */
    public function handleIPN()
    {
        $ipAddress = $this->request->getIPAddress();
        $method = $this->request->getMethod();
        $orderTrackingId = $this->request->getVar('OrderTrackingId');
        $reference = $this->request->getVar('OrderMerchantReference');
        $notificationType = $this->request->getVar('OrderNotificationType');

        log_message('notice', sprintf(
            'Pesapal IPN Access: IP=%s | Method=%s | TrackingID=%s | Ref=%s | Type=%s',
            $ipAddress,
            $method,
            $orderTrackingId ?? 'NONE',
            $reference ?? 'NONE',
            $notificationType ?? 'NONE'
        ));

        if (!$orderTrackingId) {
            $ipnInfo = [
                'ip'               => $ipAddress,
                'method'           => $method,
                'orderTrackingId'  => 'MISSING',
                'reference'        => $reference ?? 'N/A',
                'notificationType' => $notificationType ?? 'N/A',
                'status'           => 'REJECTED (Missing OrderTrackingId)',
            ];
            $this->logIPNToDatabase($ipnInfo);
            $this->notifyAdminsOfIPNAccess($ipnInfo);

            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Missing OrderTrackingId']);
        }

        // Fetch official transaction status directly from Pesapal server
        $statusData = $this->pesapal->getTransactionStatus($orderTrackingId);

        if ($statusData && isset($statusData['status_code'])) {
            $statusCode = (int)$statusData['status_code'];
            $statusText = ($statusCode === 1) ? 'SUCCESS (COMPLETED)' : (($statusCode === 0) ? 'PENDING' : 'FAILED');

            if ($statusCode === 1) {
                $this->processPaymentSuccess($statusData);
            } else {
                $this->processPaymentFailure($statusData);
            }

            $ipnInfo = [
                'ip'               => $ipAddress,
                'method'           => $method,
                'orderTrackingId'  => $orderTrackingId,
                'reference'        => $reference ?? 'N/A',
                'notificationType' => $notificationType ?? 'N/A',
                'status'           => $statusText,
            ];
            $this->logIPNToDatabase($ipnInfo);
            $this->notifyAdminsOfIPNAccess($ipnInfo);

            // Pesapal expects a specific JSON response format to acknowledge receipt
            return $this->response->setJSON([
                'orderNotificationType' => $notificationType,
                'orderTrackingId'       => $orderTrackingId,
                'status'                => '200'
            ]);
        }

        $ipnInfo = [
            'ip'               => $ipAddress,
            'method'           => $method,
            'orderTrackingId'  => $orderTrackingId,
            'reference'        => $reference ?? 'N/A',
            'notificationType' => $notificationType ?? 'N/A',
            'status'           => 'REJECTED (Pesapal Verification Failed)',
        ];
        $this->logIPNToDatabase($ipnInfo);
        $this->notifyAdminsOfIPNAccess($ipnInfo);

        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid transaction details']);
    }

    /**
     * Log IPN access attempt to database table tbl_ipn_logs.
     */
    private function logIPNToDatabase(array $data): void
    {
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('tbl_ipn_logs')) {
                $headers = [];
                foreach ($this->request->getHeaders() as $name => $val) {
                    $headers[$name] = is_object($val) && method_exists($val, 'getValue') ? $val->getValue() : (string)$val;
                }

                $db->table('tbl_ipn_logs')->insert([
                    'ip_address'         => $data['ip'],
                    'http_method'        => $data['method'],
                    'order_tracking_id'  => $data['orderTrackingId'] !== 'MISSING' ? $data['orderTrackingId'] : null,
                    'merchant_reference' => $data['reference'] !== 'N/A' ? $data['reference'] : null,
                    'notification_type'  => $data['notificationType'] !== 'N/A' ? $data['notificationType'] : null,
                    'pesapal_status'     => $data['status'],
                    'raw_payload'        => json_encode([
                        'get'     => $this->request->getGet(),
                        'post'    => $this->request->getPost(),
                        'headers' => $headers,
                    ]),
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'logIPNToDatabase failed: ' . $e->getMessage());
        }
    }

    /**
     * Send instant email alert to Superadmin and Admin users upon IPN webhook access.
     */
    private function notifyAdminsOfIPNAccess(array $ipnInfo): void
    {
        try {
            helper('email');
            
            $data = [
                'ip'               => $ipnInfo['ip'],
                'method'           => $ipnInfo['method'],
                'orderTrackingId'  => $ipnInfo['orderTrackingId'],
                'reference'        => $ipnInfo['reference'],
                'notificationType' => $ipnInfo['notificationType'],
                'status'           => $ipnInfo['status'],
                'time'             => date('F j, Y, g:i a T'),
            ];

            if (function_exists('send_superadmin_notification')) {
                send_superadmin_notification("[IPN Alert] Pesapal Payment Event: {$ipnInfo['status']}", 'email/admin/ipn_alert', $data);
            }

            if (function_exists('send_admin_notification')) {
                send_admin_notification("[IPN Alert] Pesapal Payment Event: {$ipnInfo['status']}", 'email/admin/ipn_alert', $data);
            }
        } catch (\Throwable $e) {
            log_message('error', 'notifyAdminsOfIPNAccess Exception: ' . $e->getMessage());
        }
    }

    private function processPaymentSuccess(array $statusData): void
    {
        $reference = $statusData['merchant_reference'];
        $trackingId = $statusData['order_tracking_id'];
        
        $paymentMethod = $statusData['payment_method'] ?? 'pesapal';
        $amount = floatval($statusData['amount']);
        $amountCents = (int)round($amount * 100);
        $currency = $statusData['currency'] ?? 'KES';

        // Retrieve reference structure: "user_{userId}_{plan}_{billing}_{timestamp}"
        $parts = explode('_', $reference);
        if (count($parts) >= 4 && $parts[0] === 'user') {
            $userId = (int)$parts[1];
            $plan = $parts[2];
            $billing = $parts[3];
        } else {
            log_message('error', 'PesapalCallback: Invalid merchant reference format: ' . $reference);
            return;
        }

        // 1. Check if this payment already succeeded in our database to avoid duplicate handling
        $existing = $this->payments->where('provider_payment_id', $trackingId)->first();
        if ($existing && $existing['status'] === 'succeeded') {
            return;
        }

        // 2. Insert or update the payment record
        $now = date('Y-m-d H:i:s');
        $paymentData = [
            'user_id'             => $userId,
            'plan'                => $plan,
            'billing_cycle'       => $billing,
            'amount_cents'        => $amountCents,
            'currency'            => $currency,
            'status'              => 'succeeded',
            'payment_provider'    => 'pesapal',
            'provider_payment_id' => $trackingId,
            'payment_method'      => $paymentMethod,
            'paid_at'             => $now,
            'updated_at'          => $now,
        ];

        if ($existing) {
            $this->payments->update($existing['id'], $paymentData);
        } else {
            $paymentData['created_at'] = $now;
            $this->payments->insert($paymentData);
        }

        // 3. Update the user subscription status
        $days = $billing === 'monthly' ? 30 : 365;
        
        // Find existing subscription
        $sub = $this->subscriptions->where('user_id', $userId)->orderBy('current_period_end', 'DESC')->first();
        $periodStart = date('Y-m-d H:i:s');
        $periodEnd = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        $subData = [
            'user_id'                  => $userId,
            'plan'                     => $plan,
            'status'                   => 'active',
            'billing_cycle'            => $billing,
            'current_period_start'     => $periodStart,
            'current_period_end'       => $periodEnd,
            'canceled_at'              => null,
            'payment_provider'         => 'pesapal',
            'payment_method'           => $paymentMethod,
            'provider_subscription_id' => $trackingId,
        ];

        if ($sub) {
            $this->subscriptions->update($sub['id'], $subData);
        } else {
            $subData['created_at'] = $periodStart;
            $subData['updated_at'] = $periodStart;
            $this->subscriptions->insert($subData);
        }

        log_message('info', "PesapalCallback: Subscription updated successfully for user #{$userId} on plan {$plan}");
    }

    private function processPaymentFailure(array $statusData): void
    {
        $reference = $statusData['merchant_reference'];
        $trackingId = $statusData['order_tracking_id'];
        $paymentMethod = $statusData['payment_method'] ?? 'pesapal';
        $amount = floatval($statusData['amount']);
        $amountCents = (int)round($amount * 100);
        $currency = $statusData['currency'] ?? 'KES';

        $parts = explode('_', $reference);
        if (count($parts) >= 4 && $parts[0] === 'user') {
            $userId = (int)$parts[1];
            $plan = $parts[2];
            $billing = $parts[3];
        } else {
            return;
        }

        $existing = $this->payments->where('provider_payment_id', $trackingId)->first();
        if ($existing && $existing['status'] === 'failed') {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $paymentData = [
            'user_id'             => $userId,
            'plan'                => $plan,
            'billing_cycle'       => $billing,
            'amount_cents'        => $amountCents,
            'currency'            => $currency,
            'status'              => 'failed',
            'payment_provider'    => 'pesapal',
            'provider_payment_id' => $trackingId,
            'payment_method'      => $paymentMethod,
            'updated_at'          => $now,
        ];

        if ($existing) {
            $this->payments->update($existing['id'], $paymentData);
        } else {
            $paymentData['created_at'] = $now;
            $this->payments->insert($paymentData);
        }
    }
}
