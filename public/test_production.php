<?php

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

require_once SYSTEMPATH . 'Config/DotEnv.php';
(new CodeIgniter\Config\DotEnv(ROOTPATH))->load();

if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', env('CI_ENVIRONMENT', 'production'));
}

$app = Config\Services::codeigniter();
$app->initialize();

$pesapal = new \App\Libraries\PesapalService();

echo "1. Authenticating...\n";
$token = $pesapal->getAccessToken();
if ($token) {
    echo "SUCCESS! Token generated.\n";
} else {
    echo "FAILED to authenticate.\n";
    exit(1);
}

echo "2. Registering IPN URL...\n";
$ipnUrl = base_url('billing/pesapal/ipn');
echo "Attempting to register: " . $ipnUrl . "\n";

// Bypass local cache to force a real API request to Pesapal
$cacheKey = 'pesapal_ipn_id_' . md5($ipnUrl);
cache()->delete($cacheKey);

// Execute request manually using CurlRequest to see detailed error body
$client = \Config\Services::curlrequest();
$url = 'https://pay.pesapal.com/v3/api/URLSetup/RegisterIPN';
$response = $client->request('POST', $url, [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type'  => 'application/json',
        'Accept'        => 'application/json',
    ],
    'json' => [
        'url'                   => $ipnUrl,
        'ipn_notification_type' => 'GET',
    ],
    'http_errors' => false,
]);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response Body: " . $response->getBody() . "\n";
