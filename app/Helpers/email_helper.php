<?php
/**
 * Email Helper - Centralized email sending with DB SMTP config, layout, and security footer
 * 
 * Usage:
 *   send_templated_email($to, $subject, 'email/user/welcome', $data);
 *   send_admin_notification($subject, 'email/admin/backup_completed', $data);
 */

use CodeIgniter\Email\Email;

if (!function_exists('send_templated_email')) {
    function send_templated_email(
        string $to,
        string $subject,
        string $template,
        array $data = [],
        ?string $layout = 'email/_layout',
        array $customSmtpConfig = []
    ): bool {
        $GLOBALS['last_email_error'] = null;

        // Load DB SMTP config or custom SMTP array
        $db = \Config\Database::connect();
        $smtp = [];
        if (!empty($customSmtpConfig) && !empty($customSmtpConfig['smtp_host'])) {
            $smtp = $customSmtpConfig;
        } else {
            foreach ($db->table('settings')->where('class', 'notification')->get()->getResultArray() as $r) {
                $smtp[$r['key']] = $r['value'];
            }
        }

        if (empty($smtp['smtp_host'])) {
            $GLOBALS['last_email_error'] = 'No SMTP host provided. Configure your SMTP Host (e.g. smtp.gmail.com) first.';
            log_message('error', 'send_templated_email: ' . $GLOBALS['last_email_error']);
            return false;
        }

        // Generate tracking Log ID
        $emailTrackId = 'ED-' . strtoupper(bin2hex(random_bytes(4)));

        // Strip emoji from subject to prevent spam filter rejections
        $subject = preg_replace('/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE00}-\x{FEFF}]/u', '', $subject);
        $subject = trim($subject);

        // Build security footer data from current request (if available)
        $request = service('request');
        $authUser = function_exists('auth') && auth()->loggedIn() ? auth()->user() : null;
        
        $security = [
            'securityAction'        => $data['securityAction'] ?? '',
            'securityDescription'   => $data['securityDescription'] ?? '',
            'securityStatus'        => $data['securityStatus'] ?? 'success',
            'securityInitiatedBy'   => $data['securityInitiatedBy'] ?? ($authUser ? $authUser->username ?? $authUser->email ?? 'User' : 'System'),
            'securityBrowser'       => $data['securityBrowser'] ?? ($request ? $request->getUserAgent()->getAgentString() : ''),
            'securityBrowserIp'     => $data['securityBrowserIp'] ?? ($request ? $request->getIPAddress() : ''),
            'securityExecutedAt'    => $data['securityExecutedAt'] ?? date('Y-m-d H:i:s'),
            'emailTrackId'          => $emailTrackId,
        ];

        // Merge security data into template data
        $mergedData = array_merge($data, $security);

        // Render content template
        try {
            $content = view($template, $mergedData);
        } catch (\Throwable $e) {
            $GLOBALS['last_email_error'] = "Failed to render template '$template': " . $e->getMessage();
            log_message('error', "send_templated_email: " . $GLOBALS['last_email_error']);
            
            // Log rendering failure
            $db->table('tbl_email_logs')->insert([
                'email_id'      => $emailTrackId,
                'to_email'      => $to,
                'subject'       => $subject,
                'template'      => $template,
                'body'          => '',
                'sent_at'       => date('Y-m-d H:i:s'),
                'status'        => 'failed',
                'error_message' => $GLOBALS['last_email_error']
            ]);
            return false;
        }

        // Render layout with content
        $layoutData = array_merge($mergedData, ['content' => $content]);
        try {
            $body = view($layout, $layoutData);
        } catch (\Throwable $e) {
            $GLOBALS['last_email_error'] = "Failed to render layout '$layout': " . $e->getMessage();
            log_message('error', "send_templated_email: " . $GLOBALS['last_email_error']);
            
            // Log layout rendering failure
            $db->table('tbl_email_logs')->insert([
                'email_id'      => $emailTrackId,
                'to_email'      => $to,
                'subject'       => $subject,
                'template'      => $template,
                'body'          => $content,
                'sent_at'       => date('Y-m-d H:i:s'),
                'status'        => 'failed',
                'error_message' => $GLOBALS['last_email_error']
            ]);
            return false;
        }

        // Send via SMTP
        $status = 'failed';
        $errorMessage = null;
        try {
            $email = \Config\Services::email();
            $email->initialize([
                'userAgent'  => 'Eaves Droid Forensic Intelligence/1.0',
                'protocol'   => 'smtp',
                'SMTPHost'   => $smtp['smtp_host'],
                'SMTPPort'   => (int) ($smtp['smtp_port'] ?? 587),
                'SMTPUser'   => $smtp['smtp_user'] ?? '',
                'SMTPPass'   => $smtp['smtp_pass'] ?? '',
                'SMTPCrypto' => 'tls',
                'mailType'   => 'html',
                'charset'    => 'UTF-8',
                'wordWrap'   => true,
            ]);

            $sender = get_notification_sender();
            $fromEmail = !empty($smtp['smtp_from_email']) ? $smtp['smtp_from_email'] : ($sender['email'] ?? 'noreply@eavesdroid.local');
            $fromName = !empty($smtp['smtp_from_name']) ? $smtp['smtp_from_name'] : ($sender['name'] ?? 'Eaves Droid Security');

            $email->setFrom($fromEmail, $fromName);
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($body);
            $email->setHeader('X-Mailer', 'Eaves Droid Forensic Intelligence/1.0');
            $email->setHeader('User-Agent', 'Eaves Droid Forensic Intelligence/1.0');
            $email->setHeader('X-Sender', $fromEmail);

            $sent = $email->send(false);
            if (!$sent) {
                $dbg = $email->printDebugger(['headers']);
                $errorMessage = "SMTP Send Failed. Please check SMTP host, credentials, or TLS setting.";
                if (preg_match('/(535|534|550|503|451|421|Connection refused)[^\r\n]*/i', (string)$dbg, $m)) {
                    $errorMessage .= " (" . trim($m[0]) . ")";
                }
                $GLOBALS['last_email_error'] = $errorMessage;
                log_message('error', "send_templated_email: Failed to send to $to. Debug: " . $errorMessage);
            } else {
                $status = 'sent';
                $GLOBALS['last_email_error'] = null;
                log_message('info', "send_templated_email: Sent to $to (template: $template) [Log ID: $emailTrackId]");
            }
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            $GLOBALS['last_email_error'] = $errorMessage;
            log_message('error', "send_templated_email: Exception sending to $to: " . $errorMessage);
            $sent = false;
        }

        // Log final status in database
        try {
            $db->table('tbl_email_logs')->insert([
                'email_id'      => $emailTrackId,
                'to_email'      => $to,
                'subject'       => $subject,
                'template'      => $template,
                'body'          => $body,
                'sent_at'       => date('Y-m-d H:i:s'),
                'status'        => $status,
                'error_message' => $errorMessage
            ]);
        } catch (\Throwable $e) {
            log_message('error', "send_templated_email: Failed to save email log to database: " . $e->getMessage());
        }

        return $sent;
    }
}

if (!function_exists('send_admin_notification')) {
    function send_admin_notification(string $subject, string $template, array $data = []): void {
        $db = \Config\Database::connect();
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
            $adminData = array_merge($data, [
                'adminUsername' => $admin['username'],
            ]);
            send_templated_email($admin['email'], $subject, $template, $adminData);
        }
    }
}

if (!function_exists('send_superadmin_notification')) {
    function send_superadmin_notification(string $subject, string $template, array $data = []): void {
        $db = \Config\Database::connect();
        $superadmins = $db->table('auth_identities')
            ->select('auth_identities.secret AS email, users.username')
            ->join('users', 'auth_identities.user_id = users.id')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_identities.type', 'email_password')
            ->where('auth_groups_users.group', 'superadmin')
            ->where('users.active', 1)
            ->get()
            ->getResultArray();

        foreach ($superadmins as $admin) {
            $adminData = array_merge($data, [
                'adminUsername' => $admin['username'],
            ]);
            send_templated_email($admin['email'], $subject, $template, $adminData);
        }
    }
}

if (!function_exists('get_notification_sender')) {
    function get_notification_sender(): array {
        $db = \Config\Database::connect();
        $settings = [];
        foreach ($db->table('settings')->where('class', 'notification')->get()->getResultArray() as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return [
            'email' => $settings['smtp_from_email'] ?? config('Email')->fromEmail ?? 'noreply@eavesdroid.local',
            'name'  => $settings['smtp_from_name'] ?? config('Email')->fromName ?? 'Eaves Droid',
        ];
    }
}