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

$consumerKey = env('pesapal.consumerKey') ?? '';
$consumerSecret = env('pesapal.consumerSecret') ?? '';

$client = \Config\Services::curlrequest();

echo "Keys detected:\n";
echo "Key: " . substr($consumerKey, 0, 10) . "...\n";
echo "Secret: " . substr($consumerSecret, 0, 10) . "...\n\n";

// --- TRY SANDBOX ---
echo "--- Testing Sandbox Endpoint (cybqa.pesapal.com) ---\n";
try {
    $response = $client->request('POST', 'https://cybqa.pesapal.com/pesapalv3/api/Auth/RequestToken', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ],
        'json' => [
            'consumer_key'    => $consumerKey,
            'consumer_secret' => $consumerSecret,
        ],
        'http_errors' => false,
    ]);
    echo "Sandbox Status Code: " . $response->getStatusCode() . "\n";
    echo "Sandbox Body: " . $response->getBody() . "\n\n";
} catch (\Exception $e) {
    echo "Sandbox Error: " . $e->getMessage() . "\n\n";
}

// --- TRY PRODUCTION ---
echo "--- Testing Production Endpoint (pay.pesapal.com) ---\n";
try {
    $response = $client->request('POST', 'https://pay.pesapal.com/v3/api/Auth/RequestToken', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ],
        'json' => [
            'consumer_key'    => $consumerKey,
            'consumer_secret' => $consumerSecret,
        ],
        'http_errors' => false,
    ]);
    echo "Production Status Code: " . $response->getStatusCode() . "\n";
    echo "Production Body: " . $response->getBody() . "\n\n";
} catch (\Exception $e) {
    echo "Production Error: " . $e->getMessage() . "\n\n";
}
