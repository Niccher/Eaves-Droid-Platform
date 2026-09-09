<?php

namespace App\Controllers\admin;

use CodeIgniter\API\ResponseTrait;

class MailSettingsController extends BaseAdminController
{
    use ResponseTrait;

    private function requirePermission(string $permission)
    {
        if (!auth()->user()->can($permission)) {
            $this->logAdminAction('permission_denied', 'medium', false, [
                'new_values' => json_encode(['uri' => current_url()]),
            ]);
            return redirect()->to('admin/dashboard')->with('error', 'You do not have permission to access this page.');
        }
        return null;
    }

    public function notification_settings()
    {
        $db = $this->getDb();

        $this->logAdminAction('settings_view', 'low', true, [
            'section' => 'notification',
        ]);

        $saved = [];
        $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        return $this->renderView('admin/settings/notifications', [
            'pag' => 'admin-settings-notifications',
            'settings' => $saved,
        ]);
    }

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

    public function testEmail()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        // Use POSTed values first, fall back to saved settings
        $smtpHost = $this->request->getPost('smtp_host');
        $smtpPort = $this->request->getPost('smtp_port');
        $smtpUser = $this->request->getPost('smtp_user');
        $smtpPass = $this->request->getPost('smtp_pass');
        $smtpFromEmail = $this->request->getPost('smtp_from_email');
        $smtpFromName = $this->request->getPost('smtp_from_name');
        $recipient = $this->request->getPost('email');

        // Fall back to DB if not provided via POST
        if (!$smtpHost || !$smtpUser) {
            $db = $this->getDb();
            $settings = [];
            $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
            foreach ($rows as $r) {
                $settings[$r['key']] = $r['value'];
            }
            $smtpHost = $smtpHost ?: ($settings['smtp_host'] ?? '');
            $smtpPort = $smtpPort ?: ($settings['smtp_port'] ?? '587');
            $smtpUser = $smtpUser ?: ($settings['smtp_user'] ?? '');
            $smtpPass = $smtpPass ?: ($settings['smtp_pass'] ?? '');
            $smtpFromEmail = $smtpFromEmail ?: ($settings['smtp_from_email'] ?? '');
            $smtpFromName = $smtpFromName ?: ($settings['smtp_from_name'] ?? 'Eaves Droid');
        }

        if (empty($smtpHost)) {
            return $this->respond(["success" => false, "message" => "No SMTP host provided. Please enter your SMTP Host (e.g. smtp.gmail.com)."]);
        }

        $customSmtpConfig = [
            'smtp_host'       => $smtpHost,
            'smtp_port'       => (int) $smtpPort,
            'smtp_user'       => $smtpUser,
            'smtp_pass'       => $smtpPass,
            'smtp_from_email' => $smtpFromEmail,
            'smtp_from_name'  => $smtpFromName,
        ];

        // Use email helper with POSTed (or DB fallback) SMTP config
        helper("email");
        $sent = send_templated_email(
            $recipient,
            "Eaves Droid - SMTP Configuration Test",
            "email/admin/smtp_test",
            [
                "smtpHost"      => $smtpHost,
                "smtpPort"      => $smtpPort,
                "smtpUser"      => $smtpUser,
                "smtpFromEmail" => $smtpFromEmail,
                "smtpFromName"  => $smtpFromName,
            ],
            'email/_layout',
            $customSmtpConfig
        );

        if ($sent) {
            return $this->respond(["success" => true, "message" => "Test email sent successfully to " . $recipient]);
        } else {
            $lastErr = $GLOBALS['last_email_error'] ?? 'SMTP connection failed. Please check your SMTP host, port, and password.';
            return $this->respond(["success" => false, "message" => "Failed to send test email: " . $lastErr]);
        }
    }

    public function email_triggers()
    {
        $db = $this->getDb();

        $triggerGroups = $this->getEmailTriggerGroups();

        // Get all trigger keys from all groups
        $allTriggerKeys = array_merge(
            array_keys($triggerGroups['userTriggers']),
            array_keys($triggerGroups['userSystemTriggers']),
            array_keys($triggerGroups['adminTriggers']),
            array_keys($triggerGroups['systemTriggers'])
        );

        $saved = [];
        $rows = $db->table('settings')->where('class', 'email_triggers')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        // Default all triggers to enabled (1) if not explicitly set
        foreach ($allTriggerKeys as $key) {
            if (!isset($saved[$key])) {
                $saved[$key] = '1';
            }
        }

        return $this->renderView('admin/settings/email_triggers', [
            'pag' => 'admin-settings-email-triggers',
            'settings' => $saved,
            'userTriggers' => $triggerGroups['userTriggers'],
            'userSystemTriggers' => $triggerGroups['userSystemTriggers'],
            'adminTriggers' => $triggerGroups['adminTriggers'],
            'systemTriggers' => $triggerGroups['systemTriggers'],
        ]);
    }

    public function getEmailTriggerGroups(): array
    {
        return [
            'userTriggers' => [
                'on_new_user' => ['label' => 'New User Registered', 'description' => 'Send welcome email when a new user signs up.'],
                'on_password_reset' => ['label' => 'Password Reset Request', 'description' => 'Send password reset link when user requests it.'],
                'on_password_changed' => ['label' => 'Password Changed', 'description' => 'Notify user when their password is successfully changed.'],
                'on_email_changed' => ['label' => 'Email Changed', 'description' => 'Notify user when their email address is updated.'],
            ],
            'userSystemTriggers' => [
                'on_user_suspended' => ['label' => 'AccountController Suspended', 'description' => 'Notify user when their account is suspended by admin.'],
                'on_user_reactivated' => ['label' => 'AccountController Reactivated', 'description' => 'Notify user when their suspended account is reactivated.'],
                'on_maintenance_notice' => ['label' => 'Maintenance Notice', 'description' => 'Notify all users when maintenance mode is enabled.'],
            ],
            'adminTriggers' => [
                'on_user_deleted' => ['label' => 'User Deleted', 'description' => 'Notify admins when a user account is deleted.'],
                'on_maintenance_toggle' => ['label' => 'Maintenance Mode Changed', 'description' => 'Notify admins when maintenance mode is enabled/disabled.'],
                'on_backup_success' => ['label' => 'Backup Completed', 'description' => 'Notify admins when a scheduled backup finishes successfully.'],
                'on_backup_failed' => ['label' => 'Backup Failed', 'description' => 'Alert admins when a scheduled backup fails.'],
                'on_anomaly_high' => ['label' => 'High Severity Anomaly', 'description' => 'Alert admins when ML detects high-severity anomaly.'],
                'on_cron_failed' => ['label' => 'Cron Job Failed', 'description' => 'Alert admins when a scheduled cron job fails.'],
                'on_settings_changed' => ['label' => 'Critical SettingsController Changed', 'description' => 'Alert admins when critical system settings are modified.'],
                'on_brute_force' => ['label' => 'Brute-Force Lockout', 'description' => 'Alert superadmins when the failed-login threshold is exceeded.'],
            ],
            'systemTriggers' => [
                'on_storage_warning' => ['label' => 'Storage Warning', 'description' => 'Alert when disk usage exceeds warning threshold.'],
                'on_storage_critical' => ['label' => 'Storage Critical', 'description' => 'Alert when disk usage exceeds critical threshold.'],
                'on_queue_stalled' => ['label' => 'Upload Queue Stalled', 'description' => 'Alert when upload queue has too many pending items.'],
                'on_ssl_expiring' => ['label' => 'SSL Certificate Expiring', 'description' => 'Alert when SSL certificate expires within 30 days.'],
            ],
        ];
    }

    public function sendMaintenanceToggledEmail(string $mode, array $changes, array $post = []): void
    {
        try {
            helper('email');
            $db = $this->getDb();
            
            // Send to admins
            $admins = $db->table('auth_identities')
                ->select('auth_identities.secret AS email, users.username')
                ->join('users', 'auth_identities.user_id = users.id')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                ->where('auth_identities.type', 'email_password')
                ->where('auth_groups_users.group', 'admin')
                ->where('users.active', 1)
                ->get()
                ->getResultArray();

            $enabled = $mode === '1' || $mode === 'true';
            $subject = "Eaves Droid — Maintenance Mode " . ($enabled ? 'Enabled' : 'Disabled');
            
            foreach ($admins as $admin) {
                send_templated_email(
                    $admin['email'],
                    $subject,
                    'email/admin/maintenance_toggled',
                    [
                        'mode' => $enabled ? 'enabled' : 'disabled',
                        'initiated_by' => auth()->user()->username ?? 'Admin',
                        'window_start' => $post['maintenance_start'] ?? null,
                        'window_end' => $post['maintenance_end'] ?? null,
                        'message' => $post['maintenance_type'] ?? null,
                        'username' => $admin['username'],
                        'securityAction' => 'Maintenance Mode ' . ($enabled ? 'Enabled' : 'Disabled'),
                        'securityDescription' => 'Maintenance mode has been ' . ($enabled ? 'enabled' : 'disabled') . ' on the platform.',
                        'securityStatus' => 'success',
                        'securityInitiatedBy' => auth()->user()->username ?? 'Admin',
                        'securityBrowser' => $this->request->getUserAgent()->getAgentString() ?? 'Admin Panel',
                        'securityBrowserIp' => $this->request->getIPAddress(),
                        'securityExecutedAt' => date('Y-m-d H:i:s'),
                    ]
                );
            }

            // Send to all users if maintenance is being enabled
            if ($enabled) {
                $users = $db->table('auth_identities')
                    ->select('auth_identities.secret AS email, users.id, users.username')
                    ->join('users', 'auth_identities.user_id = users.id')
                    ->where('auth_identities.type', 'email_password')
                    ->where('users.active', 1)
                    ->get()
                    ->getResultArray();

                foreach ($users as $user) {
                    if (empty($user['email'])) continue;
                    
                    // Check user's email notification preference
                    $profile = $db->table('user_profiles')
                        ->select('email_notifications')
                        ->where('user_id', $user['id'])
                        ->get()
                        ->getRowArray();
                    if ($profile && isset($profile['email_notifications']) && !$profile['email_notifications']) continue;

                    send_templated_email(
                        $user['email'],
                        'Eaves Droid — Maintenance Mode Enabled',
                        'email/admin/maintenance_toggled',
                        [
                            'mode' => 'enabled',
                            'initiated_by' => auth()->user()->username ?? 'System',
                            'window_start' => $post['maintenance_start'] ?? null,
                            'window_end' => $post['maintenance_end'] ?? null,
                            'message' => $post['maintenance_type'] ?? 'Scheduled maintenance is in progress.',
                            'username' => $user['username'],
                            'securityAction' => 'Maintenance Mode Enabled',
                            'securityDescription' => 'The platform is entering maintenance mode. Services may be temporarily unavailable.',
                            'securityStatus' => 'warning',
                            'securityInitiatedBy' => auth()->user()->username ?? 'System',
                            'securityBrowser' => 'System (Scheduled)',
                            'securityBrowserIp' => 'N/A',
                            'securityExecutedAt' => date('Y-m-d H:i:s'),
                        ]
                    );
                }
            }

            // Send to all users if maintenance is being disabled (ended)
            if (!$enabled) {
                $users = $db->table('auth_identities')
                    ->select('auth_identities.secret AS email, users.id, users.username')
                    ->join('users', 'auth_identities.user_id = users.id')
                    ->where('auth_identities.type', 'email_password')
                    ->where('users.active', 1)
                    ->get()
                    ->getResultArray();

                foreach ($users as $user) {
                    if (empty($user['email'])) continue;
                    
                    // Check user's email notification preference
                    $profile = $db->table('user_profiles')
                        ->select('email_notifications')
                        ->where('user_id', $user['id'])
                        ->get()
                        ->getRowArray();
                    if ($profile && isset($profile['email_notifications']) && !$profile['email_notifications']) continue;

                    send_templated_email(
                        $user['email'],
                        'Eaves Droid — Maintenance Mode Ended',
                        'email/admin/maintenance_toggled',
                        [
                            'mode' => 'disabled',
                            'initiated_by' => auth()->user()->username ?? 'System',
                            'window_start' => $post['maintenance_start'] ?? null,
                            'window_end' => $post['maintenance_end'] ?? null,
                            'message' => 'Maintenance has been completed. All services are now operational.',
                            'username' => $user['username'],
                            'securityAction' => 'Maintenance Mode Disabled',
                            'securityDescription' => 'Maintenance mode has ended. All services are now fully operational.',
                            'securityStatus' => 'success',
                            'securityInitiatedBy' => auth()->user()->username ?? 'System',
                            'securityBrowser' => 'System (Scheduled)',
                            'securityBrowserIp' => 'N/A',
                            'securityExecutedAt' => date('Y-m-d H:i:s'),
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Maintenance toggled email failed: ' . $e->getMessage());
        }
    }

    public function sendSettingsChangedEmail(array $changes): void
    {
        try {
            helper('email');
            $db = $this->getDb();
            
            $admins = $db->table('auth_identities')
                ->select('auth_identities.secret AS email, users.username')
                ->join('users', 'auth_identities.user_id = users.id')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                ->where('auth_identities.type', 'email_password')
                ->where('auth_groups_users.group', 'admin')
                ->where('users.active', 1)
                ->get()
                ->getResultArray();

            foreach ($admins as $admin) {
                send_templated_email(
                    $admin['email'],
                    'Eaves Droid — Critical SettingsController Changed',
                    'email/admin/settings_changed',
                    [
                        'changes' => $changes,
                        'changed_by' => auth()->user()->username ?? 'Admin',
                        'changed_at' => date('Y-m-d H:i:s'),
                        'securityAction' => 'Critical SettingsController Changed',
                        'securityDescription' => 'One or more critical system settings have been modified.',
                        'securityStatus' => 'warning',
                        'securityInitiatedBy' => auth()->user()->username ?? 'Admin',
                        'securityBrowser' => $this->request->getUserAgent()->getAgentString() ?? 'Admin Panel',
                        'securityBrowserIp' => $this->request->getIPAddress(),
                        'securityExecutedAt' => date('Y-m-d H:i:s'),
                    ]
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'SettingsController changed email failed: ' . $e->getMessage());
        }
    }

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
