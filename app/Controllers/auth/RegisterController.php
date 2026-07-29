<?php

namespace App\Controllers\auth;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class RegisterController extends Controller
{
    protected $helpers = ['auth', 'form', 'url'];

    /**
     * Display the registration view
     */
    public function registerView()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/home');
        }

        return view('auth/register');
    }

    /**
     * Handle registration form submission
     */
    public function registerAction(): RedirectResponse
    {
        // Validate input
        $rules = $this->getValidationRules();

        if (!$this->validate($rules)) {
            $logModel = new \App\Models\Mod_Log_User_Action();
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'register',
                'action_severity' => 'low',
                'success'         => 0,
                'error_message'   => 'Validation failed',
                'request_url'     => current_url(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Save the user
        $users = model(UserModel::class);

        $allowedPostFields = array_merge(
            config('Auth')->validFields,
            ['username']
        );

        // Get form data
        $postData = $this->request->getPost();

        // Prepare user data for Shield
        $user = new User($this->request->getPost($allowedPostFields));

        try {
            // Start database transaction
            $db = \Config\Database::connect();
            $db->transStart();

            // Save user to Shield users table
            $result = $users->save($user);

            if (!$result) {
                $errors = $users->errors();
                $logModel = new \App\Models\Mod_Log_User_Action();
                $logModel->logAction([
                    'action_category' => 'authentication',
                    'action_type'     => 'register',
                    'action_severity' => 'low',
                    'success'         => 0,
                    'error_message'   => json_encode($errors),
                    'request_url'     => current_url(),
                ]);
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $errors);
            }

            // Get the new user's ID
            $userId = $users->getInsertID();

            // Get the user entity
            $user = $users->findById($userId);

            // Add to default group
            $user->addGroup('user');

            // Create user profile record
            $profileData = [
                'user_id' => $userId,
                'language' => 'en',
                'timezone' => null,
                'theme' => 'system',
                'account_status' => 'active',
                'notifications_enabled' => true,
                'email_notifications' => true,
                'push_notifications' => true,
                'onboarding_completed' => false,
                'profile_completed' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db->table('user_profiles')->insert($profileData);

            // Send welcome email
            $this->sendWelcomeEmail($user);

            // Log registration action
            $logModel = new \App\Models\Mod_Log_User_Action();
            $logModel->logAction([
                'user_id'         => $userId,
                'action_category' => 'authentication',
                'action_type'     => 'register',
                'action_severity' => 'medium',
                'success'         => 1,
                'request_url'     => current_url(),
            ]);

            // Commit transaction
            $db->transComplete();

            if ($db->transStatus() === false) {
                $logModel = new \App\Models\Mod_Log_User_Action();
                $logModel->logAction([
                    'action_category' => 'authentication',
                    'action_type'     => 'register',
                    'action_severity' => 'low',
                    'success'         => 0,
                    'error_message'   => 'Transaction failed',
                    'request_url'     => current_url(),
                ]);
                throw new \Exception('Failed to save user to tbl_Users table.');
            }

            // Send email verification if enabled
            $auth = service('auth');
            if (setting('Auth.actions')['register'] ?? false) {
                $auth->startAction('email-activate', $user);
                return redirect()->route('action-show')->with('message', 'Please check your email to activate your account.');
            }

            // Auto-login after registration
            $auth->login($user);

            // Registration successful
            return redirect()->to('/home')->with('message', 'Registration successful! Welcome to our platform.');

        } catch (\Exception $e) {
            // Rollback on error
            if (isset($db) && $db->transStatus() !== false) {
                $db->transRollback();
            }

            $logModel = new \App\Models\Mod_Log_User_Action();
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'register',
                'action_severity' => 'low',
                'success'         => 0,
                'error_message'   => $e->getMessage(),
                'request_url'     => current_url(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Get validation rules for registration
     */
    protected function getValidationRules(): array
    {
        $passwordRules = [
            'required',
            \CodeIgniter\Shield\Authentication\Passwords::getMaxLengthRule(),
            'strong_password'
        ];

        return [
            'username' => [
                'label' => 'Full Name',
                'rules' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
                'errors' => [
                    'required' => 'Full name is required',
                    'min_length' => 'Full name must be at least 3 characters',
                    'max_length' => 'Full name cannot exceed 30 characters',
                    'is_unique' => 'This username is already taken',
                ]
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|is_unique[auth_identities.secret]',
                'errors' => [
                    'required' => 'Email is required',
                    'valid_email' => 'Please enter a valid email address',
                    'is_unique' => 'This email is already registered',
                ]
            ],
            'password' => [
                'label' => 'Password',
                'rules' => $passwordRules,
                'errors' => [
                    'required' => 'Password is required',
                    'min_length' => 'Password must be at least 8 characters',
                ]
            ],
            'password_confirm' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'Please confirm your password',
                    'matches' => 'Passwords do not match',
                ]
            ],
        ];
    }

    /**
     * Sends a welcome email after successful registration.
     */
    private function sendWelcomeEmail($user): void
    {
        try {
            $emailAddr = $user->email ?? '';
            if (!$emailAddr) return;

            $db = \Config\Database::connect();
            $smtp = [];
            $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
            foreach ($rows as $r) {
                $smtp[$r['key']] = $r['value'];
            }
            if (empty($smtp['smtp_host'])) return;

            $email = \Config\Services::email();
            $email->initialize([
                'protocol'   => 'smtp',
                'SMTPHost'   => $smtp['smtp_host'],
                'SMTPPort'   => $smtp['smtp_port'] ?? '587',
                'SMTPUser'   => $smtp['smtp_user'] ?? '',
                'SMTPPass'   => $smtp['smtp_pass'] ?? '',
                'SMTPCrypto' => 'tls',
                'mailType'   => 'html',
            ]);
            $email->setFrom($smtp['smtp_from_email'] ?? '', $smtp['smtp_from_name'] ?? 'Eaves Droid');
            $email->setTo($emailAddr);
            $email->setSubject('Welcome to Eaves Droid — Your Account Is Ready');
            $email->setMessage('
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
<div style="background:#007bff;padding:25px;text-align:center;">
<h1 style="color:#fff;margin:0;font-size:24px;">👋 Welcome to Eaves Droid</h1>
</div>
<div style="padding:25px;">
<p style="color:#333;font-size:15px;line-height:1.6;">Hello <strong>' . htmlspecialchars($user->username ?? '') . '</strong>,</p>
<p style="color:#333;font-size:15px;line-height:1.6;">Your account has been created successfully. Here are your account details:</p>
<table style="width:100%;border-collapse:collapse;margin:20px 0;background:#f8f9fa;border-radius:6px;">
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Email</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . htmlspecialchars($emailAddr) . '</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Username</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . htmlspecialchars($user->username ?? '') . '</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Password</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">Set during registration (not stored in plain text)</td></tr>
<tr><td style="padding:10px 15px;font-weight:bold;color:#495057;">Status</td><td style="padding:10px 15px;"><span style="color:#28a745;font-weight:bold;">Active</span></td></tr>
</table>
<div style="background:#e8f4fd;border-left:4px solid #007bff;padding:15px;margin:20px 0;border-radius:4px;">
<p style="margin:0 0 8px 0;color:#333;font-size:14px;font-weight:bold;">🚀 Getting Started</p>
<ul style="margin:0;padding-left:18px;color:#333;font-size:14px;line-height:1.8;">
<li><strong>Log in</strong> using your email and password</li>
<li><strong>Generate an API token</strong> from your account settings to connect your Android device</li>
<li><strong>Install the Eaves Droid app</strong> on your Android device and scan the token</li>
<li><strong>Data collection</strong> begins automatically once the device is paired</li>
</ul>
</div>
<div style="background:#fef3cd;border-left:4px solid #ffc107;padding:12px 15px;margin:15px 0;border-radius:4px;">
<p style="margin:0;color:#856404;font-size:13px;"><strong>🔒 Security Tip:</strong> Never share your password or API tokens with anyone. Enable two-factor authentication in your security settings for added protection.</p>
</div>
<p style="color:#333;font-size:15px;line-height:1.6;">If you have any questions, refer to the documentation or contact support.</p>
            <p style="color:#333;font-size:15px;line-height:1.6;">Best regards,<br><strong>Eaves Droid Team</strong></p>
            <div style="margin-top:20px;padding:12px 15px;background:#e9ecef;border-radius:6px;font-size:11px;color:#555;">
                <table style="width:100%;border-collapse:collapse;">
                    <tr><td style="padding:2px 5px;"><strong>Action:</strong> Account Registration</td></tr>
                    <tr><td style="padding:2px 5px;"><strong>Status:</strong> <span style="color:#28a745;font-weight:bold;">Success</span></td></tr>
                    <tr><td style="padding:2px 5px;"><strong>What This Does:</strong> Creates your Eaves Droid account and enables device pairing.</td></tr>
                    <tr><td style="padding:2px 5px;"><strong>Browser:</strong> ' . htmlspecialchars($this->request->getUserAgent()->getAgentString() ?: '') . '</td></tr>
                    <tr><td style="padding:2px 5px;"><strong>Browser IP:</strong> ' . $this->request->getIPAddress() . '</td></tr>
                    <tr><td style="padding:2px 5px;"><strong>Executed At:</strong> ' . date('Y-m-d H:i:s') . '</td></tr>
                </table>
            </div>
            </div>
            <div style="background:#f1f1f1;padding:12px;text-align:center;font-size:11px;color:#888;">
                Eaves Droid — Advanced Mobile Forensic & Data Intelligence Platform
            </div>
            </div></body></html>');
            $email->send();
        } catch (\Exception $e) {
            log_message('error', 'Welcome email failed: ' . $e->getMessage());
        }
    }
}