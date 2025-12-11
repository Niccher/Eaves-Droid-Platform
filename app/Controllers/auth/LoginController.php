<?php

namespace App\Controllers\auth;

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
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Handle login form submission
     */
    public function loginAction(): RedirectResponse
    {
        // Validate credentials
        $rules = $this->getValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $credentials = [
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ];

        // Attempt to login
        $auth = auth()->setAuthenticator('session');

        // Check if "remember me" is checked
        $remember = (bool) $this->request->getPost('remember');

        $result = $auth->attempt($credentials, $remember);

        if (!$result->isOK()) {
            return redirect()->route('login')
                ->withInput()
                ->with('error', $result->reason());
        }

        // If an action is required (like 2FA), redirect to that action
        if ($result->extraInfo()['action'] ?? null) {
            return redirect()->to($result->extraInfo()['action']);
        }

        // Success! Redirect to intended page or dashboard
        $session = session();
        $redirect = $session->getTempdata('redirect_url') ?? '/dashboard';

        return redirect()->to($redirect)->with('message', 'Welcome back!');
    }

    /**
     * Handle logout
     */
    public function logoutAction(): RedirectResponse
    {
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