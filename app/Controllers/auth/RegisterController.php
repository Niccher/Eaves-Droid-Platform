
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
            return redirect()->to('/dashboard');
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
            \CodeIgniter\Shield\Config\Auth::VALID_FIELDS,
            ['username']
        );
        $user = new User($this->request->getPost($allowedPostFields));

        try {
            // Save user to database
            $userId = $users->save($user);

            if (!$userId) {
                $errors = $users->errors();
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $errors);
            }

            // Get the user entity
            $user = $users->findById($userId);

            // Add to default group if needed
            $user->addGroup('user');

            // Send email verification if enabled
            $auth = service('auth');
            if (setting('Auth.actions')['register'] ?? false) {
                $auth->startAction('email-activate', $user);
                return redirect()->route('action-show')->with('message', 'Please check your email to activate your account.');
            }

            // Auto-login after registration (optional)
            $auth->login($user);

            // Registration successful
            return redirect()->to('/dashboard')->with('message', 'Registration successful! Welcome to our platform.');

        } catch (\Exception $e) {
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
        $passwordRules = array_merge(
            \CodeIgniter\Shield\Authentication\Passwords::getValidationRules(),
            ['strong_password']
        );

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