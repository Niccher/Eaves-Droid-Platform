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

            // Log registration action
            $logModel = new \App\Models\Mod_Access_Logs();
            $logModel->logAction([
                'user_id' => $userId,
                'action_category' => 'authentication',
                'action_type' => 'Register',
                'action_severity' => 'medium',
                'device_type' => 'desktop', // Or detect, but leaving as desktop/default for now
                'success' => 1
            ]);

            // Commit transaction
            $db->transComplete();

            if ($db->transStatus() === false) {
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
}