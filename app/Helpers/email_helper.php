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
        ?string $layout = 'email/_layout'
    ): bool {
        // Load DB SMTP config
        $db = \Config\Database::connect();
        $smtp = [];
        foreach ($db->table('settings')->where('class', 'notification')->get()->getResultArray() as $r) {
            $smtp[$r['key']] = $r['value'];
        }
        if (empty($smtp['smtp_host'])) {
            log_message('error', 'send_templated_email: No SMTP host configured in settings (class=notification)');
            return false;
        }

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
        ];

        // Merge security data into template data
        $mergedData = array_merge($data, $security);

        // Render content template
        try {
            $content = view($template, $mergedData);
        } catch (\Throwable $e) {
            log_message('error', "send_templated_email: Failed to render template '$template': " . $e->getMessage());
            return false;
        }

        // Render layout with content
        $layoutData = array_merge($mergedData, ['content' => $content]);
        try {
            $body = view($layout, $layoutData);
        } catch (\Throwable $e) {
            log_message('error', "send_templated_email: Failed to render layout '$layout': " . $e->getMessage());
            return false;
        }

        // Send via SMTP
        try {
            $email = \Config\Services::email();
            $email->initialize([
                'protocol'   => 'smtp',
                'SMTPHost'   => $smtp['smtp_host'],
                'SMTPPort'   => $smtp['smtp_port'] ?? 587,
                'SMTPUser'   => $smtp['smtp_user'] ?? '',
                'SMTPPass'   => $smtp['smtp_pass'] ?? '',
                'SMTPCrypto' => 'tls',
                'mailType'   => 'html',
                'charset'    => 'UTF-8',
                'wordWrap'   => true,
            ]);

            $sender = get_notification_sender();
            $email->setFrom($sender['email'], $sender['name']);
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($body);

            $sent = $email->send();
            if (!$sent) {
                log_message('error', "send_templated_email: Failed to send to $to (template: $template). Debug: " . json_encode($email->printDebugger(['headers'])));
            } else {
                log_message('info', "send_templated_email: Sent to $to (template: $template)");
            }
            return $sent;
        } catch (\Throwable $e) {
            log_message('error', "send_templated_email: Exception sending to $to: " . $e->getMessage());
            return false;
        }
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