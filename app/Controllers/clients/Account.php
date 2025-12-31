<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;
use App\Models\Mod_Finder;
use App\Models\Mod_User;
use CodeIgniter\HTTP\RedirectResponse;

class Account extends BaseController
{
    protected $modFinder;
    protected $modUser;
    protected $userId;
    protected $userData;

    /**
     * Initialize controller
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Check authentication once
        if (!auth()->loggedIn()) {
            session()->setFlashdata('error', 'Please login to continue');
            return redirect()->to('login')->send();
        }

        // Initialize models
        $this->modFinder = new Mod_Finder();
        $this->modUser = new Mod_User();

        // Get user data once
        $this->userData = $this->getAuthenticatedUserData();
        $this->userId = $this->userData['id'] ?? null;

        if (!$this->userId) {
            session()->setFlashdata('error', 'User data not found');
            return redirect()->to('login')->send();
        }
    }

    /**
     * Account profile page
     */
    public function home()
    {
        try {
            // Get user data
            $userVars = $this->getUserVars();
            $userToken = $this->ensureUserToken();

            // Get data counts (optimized)
            $dataCounts = $this->getUserDataCounts();

            $viewData = [
                'pag' => 'account_profile',
                'user_info' => $this->userData,
                'user_vars' => $userVars,
                'user_token' => $userToken,
                'total_apps' => $dataCounts['apps'] ?? 0,
                'total_contacts' => $dataCounts['contacts'] ?? 0,
                'total_sms' => $dataCounts['sms'] ?? 0,
                'total_calls' => $dataCounts['calls'] ?? 0,
                'csrf_token' => csrf_hash(), // CSRF protection
            ];
            echo '11111111111';
//            echo '<pre>'.print_r($viewData, true).'</pre>';
            return $this->renderView('profile', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Account index error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load account information');
            echo '22222222222';
//            echo '<pre>'.print_r($viewData, true).'</pre>';
//            return redirect()->back();
        }
    }

    /**
     * Account settings page
     */
    public function setting(): string
    {
        try {
            $userVars = $this->getUserVars();
            $userToken = $this->ensureUserToken();
            $userDevices = $this->getUserDevices();

            $viewData = [
                'pag' => 'account_setting',
                'user_info' => $this->userData,
                'user_vars' => $userVars,
                'user_token' => $userToken,
                'user_devices' => $userDevices,
                'csrf_token' => csrf_hash(),
            ];

            return $this->renderView('settings', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Account settings error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load settings');
            return redirect()->back();
        }
    }

    /**
     * Access logs page
     */
    public function access_logs(): string
    {
        try {
            $userLogs = $this->getUserInteractions();

            $viewData = [
                'pag' => 'account_logs',
                'user_info' => $this->userData,
                'user_logs' => $userLogs,
                'csrf_token' => csrf_hash(),
            ];

            return $this->renderView('access_logs', $viewData);

        } catch (\Exception $e) {
            log_message('error', 'Access logs error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to load access logs');
            return redirect()->back();
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Get authenticated user data
     */
    private function getAuthenticatedUserData(): array
    {
        $user = auth()->user();
        return $user ? $user->toArray() : [];
    }

    /**
     * Get user variables
     */
    private function getUserVars(): array
    {
        $vars = $this->modUser->get_vars($this->userId);
        return is_array($vars) ? $vars : [];
    }

    /**
     * Get user variables
     */
    private function getUserVar2(): array
    {
        $vars = $this->modUser->get_vars($this->userId);
        return is_array($vars) ? $vars : [];
    }

    /**
     * Ensure user has a token, create if not exists
     */
    private function ensureUserToken(): array
    {
        $tokenData = $this->modUser->get_token($this->userId);

        if (empty($tokenData) || !isset($tokenData['Token'])) {
            $newToken = $this->generateSecureToken();

            // Create token with IP address
            $this->modUser->create_token(
                $this->userId,
                $newToken,
                $this->request->getIPAddress()
            );

            $tokenData = $this->modUser->get_token($this->userId);
        }

        return is_array($tokenData) ? $tokenData : [];
    }

    /**
     * Generate secure token
     */
    private function generateSecureToken(): string
    {
        // Use cryptographically secure random bytes
        return bin2hex(random_bytes(16));

        // Alternative: if you need numeric only
        // return random_int(10000000, 99999999);
    }

    /**
     * Get user devices
     */
    private function getUserDevices(): array
    {
        $devices = $this->modUser->get_devices($this->userId);
        return is_array($devices) ? $devices : [];
    }

    /**
     * Get user interactions/logs
     */
    private function getUserInteractions(): array
    {
        $interactions = $this->modUser->get_interactions($this->userId);
        return is_array($interactions) ? $interactions : [];
    }

    /**
     * Get all user data counts in one optimized query
     */
    private function getUserDataCounts(): array
    {
        try {
            // Method 1: Individual queries (your current approach)
            // This is fine for small datasets

            $counts = [
                'apps' => $this->modFinder->get_count_Apps($this->userId),
                'contacts' => $this->modFinder->get_count_Contacts($this->userId),
                'sms' => $this->modFinder->get_count_Sms($this->userId),
                'calls' => $this->modFinder->get_count_Calls($this->userId),
            ];

            return $counts;

        } catch (\Exception $e) {
            log_message('error', 'Failed to get user data counts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Optimized version using single query (if needed)
     * Add this method to Mod_Finder model
     */
    private function getUserDataCountsOptimized(): array
    {
        // This would require modifying Mod_Finder to have a single method
        // that returns all counts in one query
        return $this->modFinder->get_all_counts($this->userId);
    }

    /**
     * Render view with common layout
     */
    private function renderView(string $page, array $data = [])
    {
        $viewPath = 'users/account/' . $page;

        // Ensure view exists
        if (!file_exists(APPPATH . 'Views/' . str_replace('/', DIRECTORY_SEPARATOR, $viewPath) . '.php')) {
            throw new \RuntimeException("View not found: {$viewPath}");
        }

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($viewPath, $data)
            . view('headers_footers/footer_users');
    }

    /**
     * Update user profile (example POST handler)
     */
    public function updateProfile()
    {
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
        }

        // Validate CSRF token
        if (!csrf_val($this->request->getPost('csrf_token'))) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid CSRF token'
            ]);
        }

        // Validate input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            // Update logic here
            // $updated = $this->modUser->updateProfile($this->userId, $this->request->getPost());

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Profile update failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to update profile'
            ]);
        }
    }

    /**
     * Regenerate token
     */
    public function regenerateToken()
    {
        if (!$this->request->is('post')) {
            return redirect()->back()->with('error', 'Invalid request method');
        }

        try {
            $newToken = $this->generateSecureToken();
            $result = $this->modUser->create_token(
                $this->userId,
                $newToken,
                $this->request->getIPAddress()
            );

            if ($result) {
                session()->setFlashdata('success', 'Token regenerated successfully');
            } else {
                session()->setFlashdata('error', 'Failed to regenerate token');
            }

        } catch (\Exception $e) {
            log_message('error', 'Token regeneration failed: ' . $e->getMessage());
            session()->setFlashdata('error', 'Token regeneration failed');
        }

        return redirect()->to('account/setting');
    }
}