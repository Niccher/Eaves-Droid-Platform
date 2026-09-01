<?php

namespace App\Libraries;

use CodeIgniter\Config\Services;

class PesapalService
{
    private string $consumerKey;
    private string $consumerSecret;
    private string $baseUrl;
    private $client;
    public string $lastError = '';

    public function __construct()
    {
        // Resolve keys across all environment sources ($_ENV, $_SERVER, getenv, env helper)
        $this->consumerKey = trim((string) (
            $_ENV['PESAPAL_CONSUMER_KEY']
            ?? $_SERVER['PESAPAL_CONSUMER_KEY']
            ?? getenv('PESAPAL_CONSUMER_KEY')
            ?? getenv('PESAPAL_KEY')
            ?? env('pesapal.consumerKey')
            ?? ''
        ));

        $this->consumerSecret = trim((string) (
            $_ENV['PESAPAL_CONSUMER_SECRET']
            ?? $_SERVER['PESAPAL_CONSUMER_SECRET']
            ?? getenv('PESAPAL_CONSUMER_SECRET')
            ?? getenv('PESAPAL_SECRET')
            ?? env('pesapal.consumerSecret')
            ?? ''
        ));

        $env = trim((string) (
            $_ENV['PESAPAL_ENVIRONMENT']
            ?? $_SERVER['PESAPAL_ENVIRONMENT']
            ?? getenv('PESAPAL_ENVIRONMENT')
            ?? env('pesapal.environment')
            ?? 'sandbox'
        ));

        if (strtolower($env) === 'production') {
            $this->baseUrl = 'https://pay.pesapal.com/v3';
        } else {
            $this->baseUrl = 'https://cybqa.pesapal.com/pesapalv3';
        }

        $this->client = Services::curlrequest();
    }

    /**
     * Get or fetch Authentication Token (valid for 5 mins usually).
     */
    public function getAccessToken(): ?string
    {
        if (empty($this->consumerKey) || empty($this->consumerSecret)) {
            $this->lastError = 'Pesapal Consumer Key or Consumer Secret environment variable is missing or empty in Railway variables.';
            log_message('error', 'PesapalService: ' . $this->lastError);
            return null;
        }

        $cacheKey = 'pesapal_access_token_' . md5($this->consumerKey);
        $cachedToken = cache($cacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $url = $this->baseUrl . '/api/Auth/RequestToken';
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ],
                'json' => [
                    'consumer_key'    => $this->consumerKey,
                    'consumer_secret' => $this->consumerSecret,
                ],
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode === 200 && isset($body['token']) && !empty($body['token'])) {
                // Cache the token slightly less than its actual duration to prevent race conditions
                cache()->save($cacheKey, $body['token'], 270); // 4.5 minutes
                return $body['token'];
            }

            $errMsg = '';
            if (is_array($body)) {
                if (isset($body['error']['message'])) {
                    $errMsg = $body['error']['message'] . (isset($body['error']['code']) ? " ({$body['error']['code']})" : '');
                } elseif (isset($body['message'])) {
                    $errMsg = $body['message'];
                } else {
                    $errMsg = json_encode($body);
                }
            } else {
                $errMsg = (string) $response->getBody();
            }

            $envName = (str_contains($this->baseUrl, 'cybqa')) ? 'Sandbox' : 'Production';
            $this->lastError = "Pesapal Auth Failed (HTTP {$statusCode}): {$errMsg}. Verify that your Railway Consumer Key & Secret match Pesapal {$envName} environment.";
            log_message('error', 'PesapalService: ' . $this->lastError);
            return null;
        } catch (\Exception $e) {
            log_message('error', 'PesapalService Exception during Auth: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Register IPN Webhook URL and get the notification ID (ipn_id).
     */
    public function registerIPN(string $ipnUrl): ?string
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        // Cache the IPN registration so we don't register it multiple times needlessly
        $cacheKey = 'pesapal_ipn_id_' . md5($ipnUrl);
        $cachedIpnId = cache($cacheKey);
        if ($cachedIpnId) {
            return $cachedIpnId;
        }

        try {
            $url = $this->baseUrl . '/api/URLSetup/RegisterIPN';
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'json' => [
                    'url'                   => $ipnUrl,
                    'ipn_notification_type' => 'GET', // Webhook method
                ],
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode === 200 && isset($body['ipn_id'])) {
                // Cache IPN registration ID for 30 days
                cache()->save($cacheKey, $body['ipn_id'], 2592000);
                return $body['ipn_id'];
            }

            $errMsg = $body['message'] ?? $body['error']['message'] ?? json_encode($body);
            $this->lastError = "Pesapal IPN Registration Failed (HTTP {$statusCode}): {$errMsg}";
            log_message('error', 'PesapalService: ' . $this->lastError);
            return null;
        } catch (\Exception $e) {
            log_message('error', 'PesapalService Exception during RegisterIPN: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Submit payment Order Request.
     */
    public function submitOrder(array $orderData, string $ipnId, string $callbackUrl): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            $url = $this->baseUrl . '/api/Transactions/SubmitOrderRequest';
            $payload = [
                'id'            => $orderData['merchant_reference'],
                'currency'      => $orderData['currency'] ?? 'KES',
                'amount'        => floatval($orderData['amount']),
                'description'   => $orderData['description'] ?? 'Eaves Droid Subscription',
                'callback_url'  => $callbackUrl,
                'notification_id' => $ipnId,
                'billing_address' => [
                    'email_address' => $orderData['email'] ?? '',
                    'phone_number'  => $orderData['phone'] ?? '',
                    'country_code'  => $orderData['country_code'] ?? 'KE',
                    'first_name'    => $orderData['first_name'] ?? 'User',
                    'last_name'     => $orderData['last_name'] ?? '',
                ]
            ];

            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'json' => $payload,
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode === 200 && isset($body['redirect_url'])) {
                return [
                    'redirect_url'      => $body['redirect_url'],
                    'order_tracking_id' => $body['order_tracking_id'],
                    'merchant_reference'=> $body['merchant_reference'],
                ];
            }

            log_message('error', 'PesapalService SubmitOrder failed. Code: ' . $statusCode . ' Body: ' . json_encode($body));
            return null;
        } catch (\Exception $e) {
            log_message('error', 'PesapalService Exception during SubmitOrder: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get details / status of a transaction.
     */
    public function getTransactionStatus(string $orderTrackingId): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            $url = $this->baseUrl . '/api/Transactions/GetTransactionStatus?orderTrackingId=' . $orderTrackingId;
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode === 200) {
                return $body;
            }

            log_message('error', 'PesapalService GetTransactionStatus failed. Code: ' . $statusCode . ' Body: ' . json_encode($body));
            return null;
        } catch (\Exception $e) {
            log_message('error', 'PesapalService Exception during GetTransactionStatus: ' . $e->getMessage());
            return null;
        }
    }
}
