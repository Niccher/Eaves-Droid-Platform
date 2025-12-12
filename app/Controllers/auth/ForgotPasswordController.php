<?php

namespace App\Controllers\auth;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class ForgotPasswordController extends Controller
{
    protected $helpers = ['auth', 'form', 'url'];

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
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');

        // Check if user exists
        $users = model(UserModel::class);
        $user = $users->where('email', $email)->first();

        if (!$user) {
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
        $rules = $this->getResetValidationRules();

        if (!$this->validate($rules)) {
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
            return redirect()->route('forgot')->with('error', 'Invalid or expired reset token.');
        }

        // Get user
        $users = model(UserModel::class);
        $user = $users->find($identity->user_id);

        if (!$user) {
            return redirect()->route('forgot')->with('error', 'User not found.');
        }

        // Update password
        $user->password = $password;

        if (!$users->save($user)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to reset password. Please try again.');
        }

        // Delete used token
        $identities->delete($identity->id);

        // Delete all sessions for this user (optional)
        $identities->where('user_id', $user->id)
            ->where('type', 'session')
            ->delete();

        return redirect()->route('login')->with('message', 'Password reset successfully. Please login with your new password.');
    }

    /**
     * Get validation rules for password reset
     */
    protected function getResetValidationRules(): array
    {
        $passwordRules = array_merge(
            \CodeIgniter\Shield\Authentication\Passwords::getValidationRules(),
            ['strong_password']
        );

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
                'rules' => $passwordRules,
                'errors' => [
                    'required' => 'New password is required',
                    'min_length' => 'Password must be at least 8 characters',
                ]
            ],
            'password_confirm' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'Please confirm your new password',
                    'matches' => 'Passwords do not match',
                ]
            ],
        ];
    }

    /**
     * Send password reset email
     */
    protected function sendResetEmail(string $email, string $token, string $username): bool
    {
        $emailService = \Config\Services::email();

        $resetLink = site_url('reset-password?token=' . $token);

        $message = view('auth/email_password', [
            'username' => $username,
            'reset_link' => $resetLink,
            'token' => $token,
        ]);

        $emailService->setTo($email);
        $emailService->setSubject('Password Reset Request - Prj Imgs');
        $emailService->setMessage($message);

        return $emailService->send();
    }
}