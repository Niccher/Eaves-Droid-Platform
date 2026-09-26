<?php

namespace App\Controllers\auth;

use App\Models\LogUserActionModel;
use CodeIgniter\Controller;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\HTTP\RedirectResponse;

class LoginController extends Controller
{
    protected $helpers = ['auth', 'form', 'url'];

    /**
     * Display the login view
     */
    public function loginView()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/home');
        }

        return view('auth/login');
    }

    /**
     * Handle automated login for the Interactive Demo
     */
    public function demoLogin()
    {
        $users = auth()->getProvider();
        $email = 'demo@eavesdroid.com';
        $demoUser = $users->findByCredentials(['email' => $email]);
        
        if (!$demoUser) {
            // Auto-seed the demo environment on first click
            try {
                $seeder = \Config\Database::seeder();
                $seeder->call('DemoDataSeeder');
                // Re-fetch the user after seeding
                $demoUser = $users->findByCredentials(['email' => $email]);
            } catch (\Exception $e) {
                log_message('error', 'Failed to seed demo data automatically: ' . $e->getMessage());
            }
        }

        if ($demoUser) {
            auth()->login($demoUser);
            return redirect()->to('/home')->with('message', 'Welcome to the Interactive Demo! Explore the simulated dashboard.');
        }
        
        return redirect()->route('login')->with('error', 'Demo account is currently unavailable. Please try again later.');
    }

    /**
     * Handle login form submission
     */
    public function loginAction(): \CodeIgniter\HTTP\ResponseInterface
    {
        $logModel = new LogUserActionModel();
        $email    = $this->request->getPost('email');

        // 0. Rate-limit / lockout check (rejects locked-out IPs/accounts up-front)
        $lockout = $this->checkLockout($email);
        if ($lockout !== null) {
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'login_locked',
                'action_severity' => 'high',
                'success'         => 0,
                'error_message'   => $lockout['reason'],
                'new_values'      => json_encode([
                    'ip' => $lockout['ip'],
                    'email' => $email,
                    'failed' => $lockout['failed'],
                ]),
                'request_url'     => current_url(),
            ]);

            session()->setFlashdata('error', $lockout['message']);
            $this->response->setStatusCode(429);
            $this->response->setHeader('Retry-After', (string) $lockout['retry_after']);
            $this->response->setBody(view('auth/login'));
            return $this->response;
        }

        // 1. Define the validation rules
        $rules = $this->getValidationRules();

        // 2. RUN VALIDATION FIRST! If data is missing/invalid, STOP here.
        if (!$this->validate($rules)) {
            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'login_password',
                'action_severity' => 'medium',
                'success'         => 0,
                'error_message'   => 'Validation failed',
                'new_values'      => json_encode(['email' => $email]),
                'request_url'     => current_url(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // 3. ONLY NOW, after validation ensures data is present, create the credentials array
        $credentials = [
            'email'    => $email,
            'password' => $this->request->getPost('password'),
        ];

        // Attempt to login
        $auth = auth()->setAuthenticator('session');

        // Check if "remember me" is checked
        $remember = (bool) $this->request->getPost('remember');

        // 4. Attempt authentication with guaranteed valid credentials
        $result = $auth->attempt($credentials, $remember);

        if (!$result->isOK()) {
            // If an action is required (like 2FA or email activation), redirect to that action
            if ($result->extraInfo() instanceof \CodeIgniter\Shield\Entities\User === false && isset($result->extraInfo()['action'])) {
                $logModel->logAction([
                    'action_category' => 'authentication',
                    'action_type'     => 'login_password',
                    'action_severity' => 'medium',
                    'success'         => 0,
                    'error_message'   => $result->reason(),
                    'new_values'      => json_encode(['email' => $email]),
                    'request_url'     => current_url(),
                ]);
                return redirect()->to($result->extraInfo()['action']);
            }

            $logModel->logAction([
                'action_category' => 'authentication',
                'action_type'     => 'login_password',
                'action_severity' => 'medium',
                'success'         => 0,
                'error_message'   => $result->reason(),
                'new_values'      => json_encode(['email' => $email]),
                'request_url'     => current_url(),
            ]);

            // Login failed (e.g., bad password, user not found)
            return redirect()->route('login')
                ->withInput()
                ->with('error', $result->reason());
        }

        // Success! Log the successful login
        $user = auth()->user();
        $logModel->logAction([
            'user_id'         => $user->id,
            'action_category' => 'authentication',
            'action_type'     => 'login_password',
            'action_severity' => 'low',
            'success'         => 1,
            'request_url'     => current_url(),
        ]);

        // Success! Clear the failed-attempt counter for this IP.
        $this->clearFailedAttempts($this->request->getIPAddress());

        // If the user is suspended, route immediately to the quarantine appeal page
        if ((int)$user->active === 0 || $user->status === 'suspended') {
            return redirect()->to('/account/suspended')->with('message', 'Your account is suspended. You may submit an appeal below.');
        }

        // Success! Redirect to intended page or dashboard
        $session = session();
        $redirect = $session->getTempdata('beforeLoginUrl');

        if ($redirect !== null && strpos($redirect, '/api/') !== false) {
            $redirect = null;
        }

        if ($redirect === null) {
            if ($user->inGroup('superadmin')) {
                $redirect = '/superadmin/home';
            } elseif ($user->inGroup('admin')) {
                $redirect = '/admin/dashboard';
            } else {
                $redirect = '/home';
            }
        }

        return redirect()->to($redirect)->with('message', 'Welcome back!');
    }

    /**
     * Returns an array describing a lockout if the IP/account is over the
     * failed-attempt threshold, otherwise null.
     *
     * @param string|null $email
     * @return array{ip:string, failed:int, retry_after:int, message:string, reason:string}|null
     */
    protected function checkLockout(?string $email): ?array
    {
        $db = \Config\Database::connect();

        $security = [];
        foreach ($db->table('settings')->where('class', 'security')->get()->getResultArray() as $r) {
            $security[$r['key']] = $r['value'];
        }

        $maxAttempts   = (int) ($security['max_login_attempts'] ?? 5);
        $lockoutMinutes = (int) ($security['lockout_duration'] ?? 15);
        if ($maxAttempts <= 0) $maxAttempts = 5;
        if ($lockoutMinutes <= 0) $lockoutMinutes = 15;

        $since = date('Y-m-d H:i:s', time() - ($lockoutMinutes * 60));
        $ip = $this->request->getIPAddress();

        $ipFailed = (int) $db->table('auth_logins')
            ->where('ip_address', $ip)
            ->where('success', 0)
            ->where('date >=', $since)
            ->countAllResults();

        $emailFailed = 0;
        if ($email) {
            $emailFailed = (int) $db->table('auth_logins')
                ->where('identifier', $email)
                ->where('success', 0)
                ->where('date >=', $since)
                ->countAllResults();
        }

        $failed = max($ipFailed, $emailFailed);

        if ($failed < $maxAttempts) {
            return null;
        }

        // Over threshold: lock out. Trigger brute-force alert if enabled.
        $this->notifyBruteForce($ip, (string) $email, $failed, $lockoutMinutes);

        return [
            'ip'          => $ip,
            'failed'      => $failed,
            'retry_after' => $lockoutMinutes * 60,
            'reason'      => "Too many failed login attempts ({$failed} in {$lockoutMinutes} min)",
            'message'     => "Too many failed login attempts. Your IP has been locked for {$lockoutMinutes} minutes.",
        ];
    }

    /**
     * Clears recent failed login records for an IP after a successful login.
     */
    protected function clearFailedAttempts(string $ip): void
    {
        try {
            \Config\Database::connect()->table('auth_logins')
                ->where('ip_address', $ip)
                ->where('success', 0)
                ->delete();
        } catch (\Throwable $e) {
            log_message('error', 'clearFailedAttempts: ' . $e->getMessage());
        }
    }

    /**
     * Notifies admins when a brute-force lockout is triggered, if the
     * on_brute_force email trigger is enabled.
     */
    protected function notifyBruteForce(string $ip, string $email, int $failed, int $lockoutMinutes): void
    {
        try {
            $db = \Config\Database::connect();
            $enabled = true;
            foreach ($db->table('settings')->where('class', 'email_triggers')->get()->getResultArray() as $r) {
                if ($r['key'] === 'on_brute_force' && $r['value'] === '0') {
                    $enabled = false;
                    break;
                }
            }
            if (!$enabled) {
                return;
            }

            helper('email');
            send_superadmin_notification(
                'Eaves Droid — Brute-Force Lockout Triggered',
                'email/admin/brute_force',
                [
                    'ip'              => $ip,
                    'accountEmail'    => $email ?: 'N/A',
                    'failedAttempts'  => $failed,
                    'lockoutMinutes'  => $lockoutMinutes,
                    'securityAction'  => 'Brute-Force Lockout',
                    'securityDescription' => 'Failed login threshold exceeded; IP/account locked.',
                    'securityStatus'  => 'danger',
                    'securityInitiatedBy' => 'System (Login Throttle)',
                    'securityBrowser' => $this->request->getUserAgent()->getAgentString(),
                    'securityBrowserIp' => $ip,
                    'securityExecutedAt' => date('Y-m-d H:i:s'),
                ]
            );
        } catch (\Throwable $e) {
            log_message('error', 'notifyBruteForce: ' . $e->getMessage());
        }
    }

    /**
     * Handle logout
     */
    public function logoutAction(): RedirectResponse
    {
        $user = auth()->user();
        $logModel = new LogUserActionModel();
        $logModel->logAction([
            'user_id'         => $user ? $user->id : null,
            'action_category' => 'authentication',
            'action_type'     => 'logout',
            'action_severity' => 'low',
            'success'         => 1,
            'request_url'     => current_url(),
        ]);

        auth()->logout();
        session()->destroy();

        return redirect()->to('/')->with('message', 'You have been logged out successfully.');
    }

    /**
     * Get validation rules for login
     */
    protected function getValidationRules(): array
    {
        return [
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email is required',
                    'valid_email' => 'Please enter a valid email address',
                ]
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Password is required',
                ]
            ],
        ];
    }
}