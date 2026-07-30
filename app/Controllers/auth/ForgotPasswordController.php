<?php

namespace App\Controllers\auth;

use App\Models\Mod_Log_User_Action;
use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class ForgotPasswordController extends BaseController
{
    protected $helpers = ['auth', 'form', 'url', 'text'];

    /**
     * Display forgot password view
     */
    public function forgotView()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/dashboard');
        }

        return view('auth/forgot');
    }

    /**
     * Handle forgot password form submission
     */
    public function forgotAction(): RedirectResponse
    {
        $logModel = new Mod_Log_User_Action();

        // Validate email
        $rules = [
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email is required',
                    'valid_email' => 'Please enter a valid email address',
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'password_forgot',
                'action_severity' => 'low',
                'success'         => 0,
                'error_message'   => 'Validation failed',
                'request_url'     => current_url(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');

        // Check if user exists
        $users = model(UserModel::class);
        $user = $users->findByCredentials(['email' => $email]);

        if (!$user) {
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'password_forgot',
                'action_severity' => 'low',
                'success'         => 0,
                'error_message'   => 'Email not found',
                'new_values'      => json_encode(['email' => $email]),
                'request_url'     => current_url(),
            ]);
            // For security, don't reveal if email exists or not
            return redirect()->back()
                ->with('message', 'If your email exists in our system, you will receive a password reset link shortly.');
        }

        // Create reset hash
        $identities = model(UserIdentityModel::class);

        // Delete any existing reset tokens for this user
        $identities->deleteIdentitiesByType($user, 'password_reset');

        // Generate reset token
        $token = random_string('crypto', 64);

        $identity = [
            'user_id' => $user->id,
            'type'    => 'password_reset',
            'secret'  => $token,
            'expires' => date('Y-m-d H:i:s', time() + 3600), // 1 hour expiry
        ];

        $identities->insert($identity);

        // Send reset email
        $this->sendResetEmail($user->email, $token, $user->username);

        $logModel->logAction([
            'user_id'         => $user->id,
            'action_category' => 'authentication',
            'action_type'     => 'password_forgot',
            'action_severity' => 'medium',
            'success'         => 1,
            'request_url'     => current_url(),
        ]);

        return redirect()->back()
            ->with('message', 'If your email exists in our system, you will receive a password reset link shortly.');
    }

    /**
     * Display reset password view
     */
    public function resetView()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/dashboard');
        }

        $token = $this->request->getGet('token');

        if (!$token) {
            return redirect()->route('forgot')->with('error', 'Invalid or missing reset token.');
        }

        // Validate token
        $identities = model(UserIdentityModel::class);
        $identity = $identities->where('type', 'password_reset')
            ->where('secret', $token)
            ->where('expires >', date('Y-m-d H:i:s'))
            ->first();

        if (!$identity) {
            return redirect()->route('forgot')->with('error', 'Invalid or expired reset token.');
        }

        return view('auth/reset', ['token' => $token]);
    }

    /**
     * Handle reset password form submission
     */
    public function resetAction(): RedirectResponse
    {
        $logModel = new Mod_Log_User_Action();
        $rules = $this->getResetValidationRules();

        if (!$this->validate($rules)) {
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'password_reset',
                'action_severity' => 'low',
                'success'         => 0,
                'error_message'   => 'Validation failed',
                'request_url'     => current_url(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');

        // Validate token
        $identities = model(UserIdentityModel::class);
        $identity = $identities->where('type', 'password_reset')
            ->where('secret', $token)
            ->where('expires >', date('Y-m-d H:i:s'))
            ->first();

        if (!$identity) {
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'password_reset',
                'action_severity' => 'low',
                'success'         => 0,
                'error_message'   => 'Invalid or expired reset token',
                'request_url'     => current_url(),
            ]);
            return redirect()->route('forgot')->with('error', 'Invalid or expired reset token.');
        }

        // Get the user's email/password identity and update password hash directly
        $emailIdentity = $identities->where('user_id', $identity->user_id)
            ->where('type', 'email_password')
            ->first();

        if (!$emailIdentity) {
            $logModel->logAction([
                'user_id'         => $identity->user_id,
                'action_category' => 'authentication',
                'action_type'     => 'password_reset',
                'action_severity' => 'medium',
                'success'         => 0,
                'error_message'   => 'User identity not found',
                'request_url'     => current_url(),
            ]);
            return redirect()->route('forgot')->with('error', 'User identity not found.');
        }

        // Hash and save the new password directly in auth_identities
        $newHash = service('passwords')->hash($password);
        $identities->update($emailIdentity->id, ['secret2' => $newHash]);

        // Delete used reset token
        $identities->delete($identity->id);

        // Delete all session tokens for this user (force logout everywhere)
        $identities->where('user_id', $identity->user_id)
            ->where('type', 'session')
            ->delete();

        $logModel->logAction([
            'user_id'         => $identity->user_id,
            'action_category' => 'authentication',
            'action_type'     => 'password_reset',
            'action_severity' => 'high',
            'success'         => 1,
            'request_url'     => current_url(),
        ]);

        // Send password changed email notification
        $this->sendPasswordChangedEmail($identity->user_id);

        return redirect()->route('login')->with('message', 'Password reset successfully. Please login with your new password.');
    }

    /**
     * Get validation rules for password reset
     */
    protected function getResetValidationRules(): array
    {
        return [
            'token' => [
                'label' => 'Token',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Invalid reset token',
                ]
            ],
            'password' => [
                'label' => 'New Password',
                'rules' => 'required|min_length[8]|max_length[255]',
                'errors' => [
                    'required'   => 'New password is required',
                    'min_length' => 'Password must be at least 8 characters',
                ]
            ],
            'password_confirm' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'Please confirm your new password',
                    'matches'  => 'Passwords do not match',
                ]
            ],
        ];
    }


    /**
     * Display offline password reset view (no email required)
     */
    public function offlineResetView()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/dashboard');
        }

        return view('auth/forgot_offline');
    }

    /**
     * Handle offline password reset (no email required)
     */
    public function offlineResetAction(): RedirectResponse
    {
        $logModel = new Mod_Log_User_Action();
        $rules = [
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email',
            ],
            'password' => [
                'label' => 'New Password',
                'rules' => 'required|min_length[8]|max_length[255]',
            ],
            'password_confirm' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
            ],
        ];

        if (!$this->validate($rules)) {
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'password_reset_offline',
                'action_severity' => 'low',
                'success'         => 0,
                'error_message'   => 'Validation failed',
                'request_url'     => current_url(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $users = model(UserModel::class);
        $user = $users->findByCredentials(['email' => $email]);

        if (!$user) {
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'password_reset_offline',
                'action_severity' => 'low',
                'success'         => 0,
                'error_message'   => 'Email not found',
                'new_values'      => json_encode(['email' => $email]),
                'request_url'     => current_url(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'No account found with that email address.');
        }

        $identities = model(UserIdentityModel::class);

        $emailIdentity = $identities->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->first();

        if (!$emailIdentity) {
            $logModel->logAction([
                'user_id'         => $user->id,
                'action_category' => 'authentication',
                'action_type'     => 'password_reset_offline',
                'action_severity' => 'medium',
                'success'         => 0,
                'error_message'   => 'User identity not found',
                'request_url'     => current_url(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'User identity not found.');
        }

        $newHash = service('passwords')->hash($password);
        $identities->update($emailIdentity->id, ['secret2' => $newHash]);

        // Delete all session tokens for this user (force logout everywhere)
        $identities->where('user_id', $user->id)
            ->where('type', 'session')
            ->delete();

        $logModel->logAction([
            'user_id'         => $user->id,
            'action_category' => 'authentication',
            'action_type'     => 'password_reset_offline',
            'action_severity' => 'high',
            'success'         => 1,
            'request_url'     => current_url(),
        ]);

        return redirect()->route('forgot-offline')
            ->with('message', 'Password reset successfully!');
    }

    /**
     * Send password reset email
     */
    protected function sendResetEmail(string $email, string $token, string $username): bool
    {
        $db = \Config\Database::connect();

        // Load SMTP settings from database (notification class)
        $smtpSettings = [];
        $rows = $db->table('settings')
            ->where('class', 'notification')
            ->get()
            ->getResultArray();
        foreach ($rows as $r) {
            $smtpSettings[$r['key']] = $r['value'];
        }

        $emailService = \Config\Services::email();
        
        // Configure SMTP if settings exist
        if (!empty($smtpSettings['smtp_host'])) {
            $emailService->initialize([
                'protocol'   => 'smtp',
                'SMTPHost'   => $smtpSettings['smtp_host'] ?? '',
                'SMTPPort'   => $smtpSettings['smtp_port'] ?? 587,
                'SMTPUser'   => $smtpSettings['smtp_user'] ?? '',
                'SMTPPass'   => $smtpSettings['smtp_pass'] ?? '',
                'SMTPCrypto' => 'tls',
                'mailType'   => 'html',
                'wordWrap'   => true,
            ]);
            $fromEmail = $smtpSettings['smtp_from_email'] ?? $emailService->getFromEmail();
            $fromName  = $smtpSettings['smtp_from_name'] ?? 'Eaves Droid';
            $emailService->setFrom($fromEmail, $fromName);
        }

        $resetLink = site_url('reset-password?token=' . $token);

        $message = view('auth/email_password', [
            'username' => $username,
            'reset_link' => $resetLink,
            'token' => $token,
        ]);

        $emailService->setTo($email);
        $emailService->setSubject('Password Reset Request - Eaves Droid');
        $emailService->setMessage($message);

        return $emailService->send();
    }

    /**
     * Send password changed confirmation email
     */
    protected function sendPasswordChangedEmail(int $userId): void
    {
        try {
            helper('email');
            $db = \Config\Database::connect();
            
            $user = $db->table('auth_identities')
                ->select('auth_identities.secret AS email, users.username')
                ->join('users', 'auth_identities.user_id = users.id')
                ->where('auth_identities.type', 'email_password')
                ->where('users.id', $userId)
                ->get()
                ->getRowArray();
            
            if (!$user || empty($user['email'])) return;

            // Check if user has email notifications enabled
            $profile = $db->table('user_profiles')
                ->select('email_notifications')
                ->where('user_id', $userId)
                ->get()
                ->getRowArray();
            if ($profile && isset($profile['email_notifications']) && !$profile['email_notifications']) return;

            // Check if trigger is enabled
            $setting = $db->table('settings')
                ->where('class', 'email_triggers')
                ->where('key', 'on_password_changed')
                ->get()
                ->getRowArray();
            if ($setting && $setting['value'] === '0') return;

            send_templated_email(
                $user['email'],
                'Eaves Droid — Password Changed',
                'email/user/password_changed',
                [
                    'username' => $user['username'],
                    'changedAt' => date('Y-m-d H:i:s'),
                    'securityAction' => 'Password Changed',
                    'securityDescription' => 'Your account password was successfully changed.',
                    'securityStatus' => 'success',
                    'securityInitiatedBy' => $user['username'],
                    'securityBrowser' => $this->request->getUserAgent()->getAgentString() ?? 'Unknown',
                    'securityBrowserIp' => $this->request->getIPAddress(),
                    'securityExecutedAt' => date('Y-m-d H:i:s'),
                ]
            );
        } catch (\Throwable $e) {
            log_message('error', 'Password changed email failed: ' . $e->getMessage());
        }
    }
}