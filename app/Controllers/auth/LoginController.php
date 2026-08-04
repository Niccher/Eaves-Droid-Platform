<?php

namespace App\Controllers\auth;

use App\Models\Mod_Log_User_Action;
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
     * Handle login form submission
     */
    public function loginAction(): RedirectResponse
    {
        $logModel = new Mod_Log_User_Action();
        $email    = $this->request->getPost('email');

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

        // Success! Redirect to intended page or dashboard
        $session = session();
        $redirect = $session->getTempdata('beforeLoginUrl');

        if ($redirect === null) {
            $redirect = $user->inGroup('superadmin') ? '/superadmin/home' : '/home';
        }

        return redirect()->to($redirect)->with('message', 'Welcome back!');
    }

    /**
     * Handle logout
     */
    public function logoutAction(): RedirectResponse
    {
        $user = auth()->user();
        $logModel = new Mod_Log_User_Action();
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