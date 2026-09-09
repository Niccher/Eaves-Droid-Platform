<?php

namespace App\Controllers\admin;

use CodeIgniter\API\ResponseTrait;

class FCMSettingsController extends BaseAdminController
{
    use ResponseTrait;

    /**
     * Display Firebase Cloud Messaging (FCM) configuration page.
     */
    public function fcm_settings()
    {
        $db = $this->getDb();

        $this->logAdminAction('settings_view', 'low', true, [
            'section' => 'fcm',
        ]);

        $saved = [];
        $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        // Check Firebase Credentials status
        $fcm = new \App\Controllers\api\v1\FCMCommandController();
        $fbCreds = $fcm->getCredentialsJson();
        $firebaseStatus = [
            'configured' => !empty($fbCreds),
            'project_id' => $fbCreds['project_id'] ?? ($saved['firebase_project_id'] ?? null),
            'client_email' => $fbCreds['client_email'] ?? ($saved['firebase_client_email'] ?? null),
            'updated_at' => $saved['firebase_updated_at'] ?? (file_exists(WRITEPATH . 'firebase_credentials.json') ? date('Y-m-d H:i:s', filemtime(WRITEPATH . 'firebase_credentials.json')) : null),
        ];

        return $this->renderView('admin/settings/fcm', [
            'pag' => 'admin-settings-fcm',
            'settings' => $saved,
            'firebaseStatus' => $firebaseStatus,
        ]);
    }

    /**
     * Upload and validate Firebase Service Account credentials.
     */
    public function uploadFirebaseCredentials()
    {
        $jsonStr = '';

        // Check if file was uploaded
        $file = $this->request->getFile('firebase_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $jsonStr = file_get_contents($file->getTempName());
        } else {
            // Check raw textarea
            $jsonStr = $this->request->getPost('firebase_json_raw');
        }

        if (empty($jsonStr) || !is_string($jsonStr)) {
            if ($this->request->isAJAX()) {
                return $this->fail('No Firebase credentials JSON provided.');
            }
            return redirect()->back()->with('error', 'Please upload a JSON file or paste the JSON credentials.');
        }

        $jsonStr = trim($jsonStr);
        $decoded = json_decode($jsonStr, true);
        if (!$decoded || !is_array($decoded)) {
            if ($this->request->isAJAX()) {
                return $this->fail('Invalid JSON format. Please ensure valid JSON was uploaded.');
            }
            return redirect()->back()->with('error', 'Invalid JSON format.');
        }

        if (empty($decoded['client_email']) || empty($decoded['private_key']) || empty($decoded['project_id'])) {
            if ($this->request->isAJAX()) {
                return $this->fail('JSON is missing required Service Account fields (project_id, client_email, or private_key).');
            }
            return redirect()->back()->with('error', 'Missing required Service Account fields in JSON.');
        }

        // Test generation of OAuth2 token to verify credentials are valid with Google
        $testJwt = $this->testGenerateOAuthToken($decoded);
        if (!$testJwt['success']) {
            if ($this->request->isAJAX()) {
                return $this->fail('Credentials validation failed: ' . $testJwt['message']);
            }
            return redirect()->back()->with('error', 'Credentials validation failed: ' . $testJwt['message']);
        }

        // Save to Database
        $this->saveOrUpdateSetting('notification', 'firebase_credentials_json', $jsonStr);
        $this->saveOrUpdateSetting('notification', 'firebase_project_id', $decoded['project_id']);
        $this->saveOrUpdateSetting('notification', 'firebase_client_email', $decoded['client_email']);
        $this->saveOrUpdateSetting('notification', 'firebase_updated_at', date('Y-m-d H:i:s'));

        // Save to disk file
        $credentialsPath = WRITEPATH . 'firebase_credentials.json';
        @file_put_contents($credentialsPath, $jsonStr);

        $this->logAdminAction('firebase_credentials_uploaded', 'medium', true, [
            'project_id' => $decoded['project_id'],
            'client_email' => $decoded['client_email'],
        ]);

        $msg = "Firebase Service Account for project '{$decoded['project_id']}' ({$decoded['client_email']}) configured and verified successfully!";
        if ($this->request->isAJAX()) {
            return $this->respond([
                'success' => true,
                'message' => $msg,
                'project_id' => $decoded['project_id'],
                'client_email' => $decoded['client_email'],
            ]);
        }
        return redirect()->back()->with('message', $msg);
    }

    /**
     * Test active Firebase credentials with live Google Cloud OAuth2 handshake.
     */
    public function testFirebaseCredentials()
    {
        $fcm = new \App\Controllers\api\v1\FCMCommandController();
        $creds = $fcm->getCredentialsJson();

        if (!$creds) {
            return $this->respond([
                'success' => false,
                'message' => 'No Firebase credentials found. Please upload or paste your Service Account JSON first.',
            ]);
        }

        $res = $this->testGenerateOAuthToken($creds);
        return $this->respond($res);
    }

    /**
     * Generate signed JWT and request OAuth2 bearer token from Google Cloud.
     */
    private function testGenerateOAuthToken(array $json): array
    {
        try {
            $now = time();
            $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = base64_encode(json_encode([
                'iss' => $json['client_email'],
                'scope' => 'https://www.googleapis.com/auth/cloud-platform',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now
            ]));

            $signature = '';
            if (!openssl_sign("$header.$payload", $signature, $json['private_key'], 'SHA256')) {
                return ['success' => false, 'message' => 'Failed to sign JWT with private key. Check key format.'];
            }
            $jwt = "$header.$payload." . base64_encode($signature);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);
            if ($httpCode === 200 && !empty($result['access_token'])) {
                return [
                    'success' => true,
                    'message' => 'Firebase OAuth2 Token successfully generated and validated with Google Cloud.',
                    'project_id' => $json['project_id'] ?? 'Unknown',
                    'client_email' => $json['client_email'] ?? 'Unknown',
                    'expires_in' => $result['expires_in'] ?? 3600,
                ];
            } else {
                $errMsg = $result['error_description'] ?? $result['error'] ?? 'Google OAuth2 error (HTTP ' . $httpCode . ')';
                return ['success' => false, 'message' => $errMsg];
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'OAuth2 test error: ' . $e->getMessage()];
        }
    }

    /**
     * Save or update database setting row.
     */
    private function saveOrUpdateSetting(string $class, string $key, string $value): void
    {
        $db = $this->getDb();
        $existing = $db->table('settings')
            ->where('class', $class)
            ->where('key', $key)
            ->get()
            ->getRow();

        if ($existing) {
            $db->table('settings')
                ->where('id', $existing->id)
                ->update(['value' => $value, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            $db->table('settings')->insert([
                'class' => $class,
                'key' => $key,
                'value' => $value,
                'type' => 'string',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
