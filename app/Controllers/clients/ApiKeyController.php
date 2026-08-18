<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\AccessLogsModel;

class ApiKeyController extends BaseClientController
{
    use ResponseTrait;

    /**
     * @var UserModel
     */
    protected $modUser;

    /**
     * @var AccessLogsModel
     */
    protected $modAccessLogs;

    /**
     * @var array
     */
    protected $userData;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->modUser = new UserModel();
        $this->modAccessLogs = new AccessLogsModel();

        $this->userData = $this->getAuthenticatedUserData();
        $this->userId = $this->userData['id'] ?? null;
    }

    /**
     * Regenerates user token.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function regenerateToken()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
        }

        // Validate CSRF token
        $csrfToken = $this->request->getPost('csrf_token');
        if (!$csrfToken || !csrf_hash($csrfToken)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid or expired CSRF token. Please refresh the page.'
            ]);
        }

        try {
            $db = \Config\Database::connect();

            // Mark old active tokens as inactive
            $db->table('tbl_user_api_tokens')
                ->where('owner_id', $this->userId)
                ->where('status', '00')
                ->set('status', '11')
                ->set('last_used_at', date('Y-m-d H:i:s'))
                ->update();

            // Generate new token
            $newToken = bin2hex(random_bytes(4));

            // Get user info
            $userEmail = auth()->user()->getEmail();
            $username = auth()->user()->username ?? explode('@', $userEmail)[0];

            // Create new token using the model method
            $this->modUser->create_token(
                $this->userId,
                $newToken,
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString()
            );

            // Log the action
            $this->logUserAction('token_regenerate', 'security', 'medium', 1);

            // Check if it's an AJAX request
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Token regenerated successfully!',
                    'token' => $newToken,
                    'qrCodeData' => $this->generateQRCodeData($newToken),
                    'expiry' => date('M d, Y H:i', strtotime('+30 days'))
                ]);
            } else {
                // For non-AJAX requests, redirect with flash message
                session()->setFlashdata('success', 'Token regenerated successfully!');
                return redirect()->to('account/setting');
            }

        } catch (\Exception $e) {
            log_message('error', 'Token regeneration failed: ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Token regeneration failed: ' . $e->getMessage()
                ]);
            } else {
                session()->setFlashdata('error', 'Token regeneration failed: ' . $e->getMessage());
                return redirect()->to('account/setting');
            }
        }
    }

    /**
     * POST /account/createToken
     * Creates a new named token.
     */
    public function createToken()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
        }

        $tokenName = trim($this->request->getPost('token_name') ?? '');

        try {
            $newToken = bin2hex(random_bytes(4));

            $this->modUser->create_token(
                $this->userId,
                $newToken,
                $this->request->getIPAddress(),
                $tokenName ?: null
            );

            $this->logUserAction('token_create', 'security', 'medium', 1);

            // Send email notification for API token created
            $this->sendTokenCreatedEmail($newToken, $tokenName);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Token created successfully!',
                'token' => $newToken,
                'token_name' => $tokenName,
                'qrCodeData' => $this->generateQRCodeData($newToken),
                'expiry' => date('M d, Y H:i', strtotime('+30 days'))
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Token creation failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Token creation failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * POST /account/revokeToken
     * Revokes a user's API token.
     */
    public function revokeToken()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
        }

        try {
            $db = \Config\Database::connect();
            
            // Revoke all active tokens for this user
            $db->table('tbl_user_api_tokens')
                ->where('owner_id', $this->userId)
                ->where('status', '00')
                ->set('status', '11')
                ->set('last_used_at', date('Y-m-d H:i:s'))
                ->update();

            $this->logUserAction('token_revoke', 'security', 'medium', 1);

            // Send email notification for API token revoked
            $this->sendTokenRevokedEmail(substr($this->request->getPost('token_prefix') ?? 'unknown', 0, 8));

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Token revoked successfully!'
                ]);
            } else {
                session()->setFlashdata('success', 'Token revoked successfully!');
                return redirect()->to('account/setting');
            }
        } catch (\Exception $e) {
            log_message('error', 'Token revocation failed: ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Token revocation failed: ' . $e->getMessage()
                ]);
            } else {
                session()->setFlashdata('error', 'Token revocation failed: ' . $e->getMessage());
                return redirect()->to('account/setting');
            }
        }
    }

    /**
     * Generates QR code data for token.
     *
     * @param string $token
     * @return array
     */
    private function generateQRCodeData(string $token): array
    {
        return [
            'text' => $token,
            'size' => 200,
            'color' => '#000000',
            'bgColor' => '#ffffff',
            'level' => 'M'
        ];
    }

    /**
     * Gets authenticated user data from Shield.
     *
     * @return array
     */
    private function getAuthenticatedUserData(): array
    {
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        $userArray = $user->toArray();
        $userArray['email'] = $user->getEmail();
        $userArray['id'] = $user->id;

        // Get profile data for avatar
        if ($this->userId || $user->id) {
            $profile = (new UserModel())->get_data_tbl_users($this->userId ?? $user->id);
            if ($profile) {
                $userArray['profile_image'] = $profile['profile_image'] ?? null;
            }
        }

        return $userArray;
    }

    /**
     * LogsController user actions.
     *
     * @param string $actionType
     * @param string $category
     * @param string $severity
     * @param int $success
     * @param array $additionalData
     * @return bool
     */
    private function logUserAction(
        string $actionType,
        string $category = 'system',
        string $severity = 'low',
        int $success = 1,
        array $additionalData = []
    ): bool {
        try {
            $logData = [
                'user_id' => $this->userId,
                'action_type' => $actionType,
                'action_category' => $category,
                'action_severity' => $severity,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'request_url' => current_url(),
                'device_type' => 'web',
                'success' => $success,
                'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (!empty($additionalData)) {
                $logData = array_merge($logData, $additionalData);
            }

            return $this->modAccessLogs->logAction($logData) !== false;
        } catch (\Exception $e) {
            log_message('error', 'Failed to log user action: ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // EMAIL TRIGGER HELPER METHODS
    // =================================================================

    /**
     * Send email for API token creation
     */
    private function sendApiTokenCreatedEmail(string $tokenName, string $tokenPrefix): void
    {
        if (!$this->isTriggerEnabled('on_api_token_created')) return;
        $this->sendUserTriggerEmail('api_token_created', [
            'tokenName' => $tokenName,
            'tokenPrefix' => $tokenPrefix,
            'createdAt' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Send email for API token revocation
     */
    private function sendApiTokenRevokedEmail(string $tokenPrefix): void
    {
        if (!$this->isTriggerEnabled('on_api_token_revoked')) return;
        $this->sendUserTriggerEmail('api_token_revoked', [
            'tokenPrefix' => $tokenPrefix,
            'revokedAt' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Check if an email trigger is enabled
     */
    private function isTriggerEnabled(string $key): bool
    {
        $db = \Config\Database::connect();
        $row = $db->table('settings')
            ->where('class', 'email_triggers')
            ->where('key', $key)
            ->get()
            ->getRowArray();
        return $row && $row['value'] === '1';
    }

    /**
     * Send user-facing trigger email
     */
    private function sendUserTriggerEmail(string $template, array $data = []): void
    {
        try {
            $userEmail = auth()->user()->getEmail();
            $username = auth()->user()->username ?? 'User';

            if (!$userEmail) return;

            // Check user email notifications preference
            $db = \Config\Database::connect();
            $profile = $db->table('user_profiles')
                ->select('email_notifications')
                ->where('user_id', $this->userId)
                ->get()
                ->getRowArray();
            if ($profile && isset($profile['email_notifications']) && !$profile['email_notifications']) {
                return;
            }

            $securityData = [
                'securityAction' => ucfirst(str_replace('_', ' ', $template)),
                'securityDescription' => $this->getTriggerDescription($template),
                'securityStatus' => 'success',
                'securityInitiatedBy' => $username,
                'securityBrowser' => $this->request->getUserAgent()->getAgentString() ?: 'Unknown',
                'securityBrowserIp' => $this->request->getIPAddress(),
                'securityExecutedAt' => date('Y-m-d H:i:s'),
            ];

            helper('email');
            send_templated_email(
                $userEmail,
                'Eaves Droid — ' . ucfirst(str_replace('_', ' ', $template)),
                'email/user/' . $template,
                array_merge($data, $securityData)
            );
        } catch (\Throwable $e) {
            log_message('error', "User trigger email failed ($template): " . $e->getMessage());
        }
    }

    private function getTriggerDescription(string $template): string
    {
        $descriptions = [
            'api_token_created' => 'A new API token was generated for your account.',
            'api_token_revoked' => 'An API token was revoked from your account.',
        ];
        return $descriptions[$template] ?? 'An action was performed on your account.';
    }

    /**
     * Send API token created email
     */
    private function sendTokenCreatedEmail(string $token, string $name): void
    {
        $this->sendUserTriggerEmail('api_token_created', [
            'token' => $token,
            'token_name' => $name ?: 'Unnamed Token',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Send API token revoked email
     */
    private function sendTokenRevokedEmail(string $tokenPrefix): void
    {
        $this->sendUserTriggerEmail('api_token_revoked', [
            'token_prefix' => $tokenPrefix,
            'revoked_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
