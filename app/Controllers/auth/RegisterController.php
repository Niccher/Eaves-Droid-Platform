<?php

namespace App\Controllers\auth;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class RegisterController extends Controller
{
    protected $helpers = ['auth', 'form', 'url', 'group'];

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
            $logModel = new \App\Models\LogUserActionModel();
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
            ['username', 'password']
        );

        // Get form data
        $postData = $this->request->getPost();

        // Prepare user data for Shield
        $userRegistrationData = $this->request->getPost($allowedPostFields);
        $userRegistrationData['active'] = 1;
        $user = new User($userRegistrationData);

        try {
            // Start database transaction
            $db = \Config\Database::connect();
            $db->transStart();

            // Save user to Shield users table
            $result = $users->save($user);

            if (!$result) {
                $db->transRollback();
                $errors = $users->errors();
                $logModel = new \App\Models\LogUserActionModel();
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

            // Reliably obtain the newly created user record from the users table
            $savedUser = null;
            if (!empty($user->id)) {
                $savedUser = $users->find($user->id);
            }
            if (!$savedUser && !empty($userRegistrationData['username'])) {
                $savedUser = $users->where('username', $userRegistrationData['username'])->first();
            }
            if (!$savedUser) {
                $savedUser = $users->findById($users->getInsertID());
            }

            if (!$savedUser || empty($savedUser->id)) {
                $db->transRollback();
                throw new \Exception('Failed to locate newly created user record.');
            }

            $user   = $savedUser;
            $userId = (int) $user->id;

            // Assign the default (exclusive) group
            helper('group');
            setUserGroup($userId, 'user');

            // Create user profile record
            $profileData = [
                'user_id'               => $userId,
                'language'              => 'en',
                'timezone'              => null,
                'theme'                 => 'system',
                'account_status'        => 'active',
                'notifications_enabled' => 1,
                'email_notifications'   => 1,
                'push_notifications'    => 1,
                'onboarding_completed'  => 0,
                'profile_completed'     => 0,
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ];

            // Prevent duplicate key if already created by another event/hook
            $existingProfile = $db->table('user_profiles')->where('user_id', $userId)->get()->getRow();
            if (!$existingProfile) {
                $db->table('user_profiles')->insert($profileData);
            } else {
                $db->table('user_profiles')->where('user_id', $userId)->update($profileData);
            }

            // Commit transaction
            $db->transComplete();

            if ($db->transStatus() === false) {
                $dbError = $db->error();
                $detail = !empty($dbError['message']) ? ': ' . $dbError['message'] : '';
                throw new \Exception('Failed to save user profile record' . $detail);
            }

            // Send welcome email (outside transaction so email failure doesn't abort registration)
            try {
                $this->sendWelcomeEmail($user);
            } catch (\Throwable $mEx) {
                log_message('warning', 'Welcome email failed: ' . $mEx->getMessage());
            }

            // Log registration action (outside transaction)
            try {
                $logModel = new \App\Models\LogUserActionModel();
                $logModel->logAction([
                    'user_id'         => $userId,
                    'action_category' => 'authentication',
                    'action_type'     => 'register',
                    'action_severity' => 'medium',
                    'success'         => 1,
                    'request_url'     => current_url(),
                ]);
            } catch (\Throwable $lEx) {
                log_message('warning', 'Registration action log failed: ' . $lEx->getMessage());
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

        } catch (\Throwable $e) {
            // Rollback on error
            if (isset($db) && $db->transStatus() !== false) {
                $db->transRollback();
            }

            $logModel = new \App\Models\LogUserActionModel();
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

            helper('email');
            send_templated_email(
                $emailAddr,
                'Welcome to Eaves Droid — Your AccountController Is Ready',
                'email/user/welcome',
                [
                    'username' => $user->username ?? '',
                    'email' => $emailAddr,
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'Welcome email failed: ' . $e->getMessage());
        }
    }
}