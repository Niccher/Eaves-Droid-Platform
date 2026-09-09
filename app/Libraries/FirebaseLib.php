<?php

namespace App\Libraries;

use Config\Firebase;

class FirebaseLib
{
    private $config;
    private $accessToken = null;

    public function __construct()
    {
        $this->config = new Firebase();
    }

    /**
     * Send a data message to a device using FCM HTTP v1 API.
     * 
     * @param string $token Device FCM token
     * @param array $data Key-value pairs to send
     * @return array|bool Response from Firebase or false on failure
     */
    public function sendDataMessage($token, $data)
    {
        // Convert all data values to strings as required by FCM v1
        $stringData = array_map('strval', $data);

        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            log_message('error', 'FCM: Failed to get Access Token');
            return false;
        }

        $credentials = $this->getCredentialsJson();
        if (!$credentials) {
            log_message('error', 'FCM: Failed to load Firebase Service Account credentials');
            return false;
        }

        $projectId = $credentials['project_id'] ?? ($this->config->projectId ?? '');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $token,
                'data' => $stringData,
                'android' => [
                    'priority' => 'high'
                ]
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            $result = curl_exec($ch);
            
            if ($result === FALSE) {
                log_message('error', 'FCM Curl Error: ' . curl_error($ch));
                curl_close($ch);
                return false;
            }

            curl_close($ch);
            
            $response = json_decode($result, true);
            
            // Check for API errors
            if (isset($response['error'])) {
                log_message('error', 'FCM Send Failure: ' . json_encode($response));
                return false;
            } else {
                log_message('info', 'FCM Send Success: ' . json_encode($response));
            }

            return $response;

        } catch (\Exception $e) {
            log_message('error', 'FCM Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resolve Service Account Credentials from files, DB settings, or environment variables
     */
    public function getCredentialsJson(): ?array
    {
        // 1. Filesystem paths
        $paths = [
            WRITEPATH . 'firebase_credentials.json',
            $this->config->serviceAccountPath ?? '',
            FCPATH . 'firebase_service_account.json',
            WRITEPATH . 'credentials/firebase_service_account.json'
        ];
        foreach ($paths as $path) {
            if (!empty($path) && file_exists($path)) {
                $content = @file_get_contents($path);
                if (!empty($content)) {
                    $json = json_decode($content, true);
                    if (is_array($json) && !empty($json['client_email']) && !empty($json['private_key'])) {
                        return $json;
                    }
                }
            }
        }

        // 2. Database settings
        try {
            $db = \Config\Database::connect();
            $row = $db->table('settings')
                ->where('class', 'notification')
                ->where('key', 'firebase_credentials_json')
                ->get()
                ->getRow();
            if ($row && !empty($row->value)) {
                $json = json_decode($row->value, true);
                if (is_array($json) && !empty($json['client_email']) && !empty($json['private_key'])) {
                    @file_put_contents(WRITEPATH . 'firebase_credentials.json', $row->value);
                    return $json;
                }
            }
        } catch (\Throwable $e) {
            log_message('warning', 'Failed to fetch Firebase credentials from DB: ' . $e->getMessage());
        }

        // 3. Environment variables
        $envJson = env('FIREBASE_CREDENTIALS_JSON') ?: env('FIREBASE_CREDENTIALS');
        if (!empty($envJson)) {
            $json = json_decode($envJson, true);
            if (is_array($json) && !empty($json['client_email']) && !empty($json['private_key'])) {
                @file_put_contents(WRITEPATH . 'firebase_credentials.json', $envJson);
                return $json;
            }
        }

        return null;
    }

    /**
     * Get OAuth 2.0 Access Token using Service Account Credentials
     */
    private function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $credentials = $this->getCredentialsJson();
        if (!$credentials || !isset($credentials['client_email']) || !isset($credentials['private_key'])) {
            log_message('error', 'Invalid or missing Firebase Service Account credentials');
            return false;
        }

        $now = time();
        $tokenPayload = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];

        // Encode Header
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        // Encode Payload
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($tokenPayload)));

        // Create Signature
        $signature = '';
        $dataToSign = $base64Header . "." . $base64Payload;
        
        if (!openssl_sign($dataToSign, $signature, $credentials['private_key'], 'SHA256')) {
            log_message('error', 'FCM: Failed to sign JWT');
            return false;
        }

        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $dataToSign . "." . $base64Signature;

        // Exchange JWT for Access Token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result, true);

        if (isset($response['access_token'])) {
            $this->accessToken = $response['access_token'];
            return $this->accessToken;
        }
        
        log_message('error', 'FCM: Failed to exchange JWT for Access Token: ' . json_encode($response));
        return false;
    }
}
