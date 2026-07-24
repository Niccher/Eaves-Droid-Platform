<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Database;
use Config\Services;
use App\Models\Mod_Log_User_Action;

class MaintenanceFilter implements FilterInterface
{
    private const ALLOWED_ROUTES = [
        'login',
        'logout',
        'register',
        'forgot',
        'forgot/offline',
        'reset-password',
        'landing',
        'download',
        'aboutus',
        'faqs_terms',
        'how_to',
        'contactus',
        'pricing',
        'error/403',
        'error/404',
        'error/500',
        'error/503',
        'error/general',
        'admin/settings/maintenance',
        'admin/settings/update',
        'api/v1/*',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $db = Database::connect();
        $settings = [];
        $rows = $db->table('settings')->where('class', 'app')->get()->getResultArray();
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }

        $maintenanceMode = $settings['maintenance_mode'] ?? '0';
        if ($maintenanceMode !== '1') {
            return;
        }

        $maintenanceType = $settings['maintenance_type'] ?? 'now_until_unknown';
        $maintenanceStart = $settings['maintenance_start'] ?? null;
        $maintenanceEnd = $settings['maintenance_end'] ?? null;
        $now = time();

        if ($maintenanceType === 'scheduled' && $maintenanceStart) {
            $startTs = strtotime($maintenanceStart);
            if ($startTs === false || $now < $startTs) {
                return;
            }
        }

        if ($maintenanceType === 'now_until' && $maintenanceEnd) {
            $endTs = strtotime($maintenanceEnd);
            if ($endTs !== false && $now > $endTs) {
                return;
            }
        }

        if ($maintenanceType === 'scheduled' && $maintenanceStart && $maintenanceEnd) {
            $startTs = strtotime($maintenanceStart);
            $endTs = strtotime($maintenanceEnd);
            if ($endTs !== false && $now > $endTs) {
                return;
            }
        }

        $currentUri = $request->getUri()->getPath();
        $currentUri = ltrim($currentUri, '/');

        foreach (self::ALLOWED_ROUTES as $allowed) {
            // Support wildcard: api/v1/* matches api/v1/token/verify, api/v1/data/sms, etc.
            $pattern = str_replace(['*', '/'], ['.*', '\/'], $allowed);
            if (preg_match('#^' . $pattern . '$#', $currentUri)) {
                return;
            }
            if ($currentUri === $allowed || strpos($currentUri, $allowed . '/') === 0) {
                return;
            }
        }

        if (auth()->loggedIn()) {
            $user = auth()->user();
            if ($user && in_array('admin', $user->getGroups())) {
                return;
            }
            if ($user && in_array('superadmin', $user->getGroups())) {
                return;
            }
        }

        $logModel = new Mod_Log_User_Action();
        $authUser = auth()->loggedIn() ? auth()->user() : null;
        $logModel->logAction([
            'user_id'         => $authUser ? (int) $authUser->id : null,
            'action_category' => 'system',
            'action_type'     => 'maintenance_blocked',
            'action_severity' => 'medium',
            'success'         => 0,
            'request_url'     => current_url(),
            'request_method'  => $request->getMethod(),
            'response_code'   => 503,
            'new_values'      => json_encode([
                'maintenance_type' => $maintenanceType,
                'user_authenticated' => $authUser ? true : false,
            ]),
        ]);

        $response = Services::response();
        $response->setStatusCode(503);

        // Return JSON for API requests, HTML for browser requests
        if (strpos($currentUri, 'api/') === 0) {
            $response->setContentType('application/json');
            $response->setBody(json_encode([
                'success' => false,
                'message' => 'System is in maintenance mode.',
                'error' => 'maintenance_mode',
            ]));
        } else {
            $response->setBody(view('errors/custom_errors/error_503', [
                'message' => 'The system is currently in maintenance mode. Only administrators can access the system. Please try again later.',
            ]));
        }
        return $response;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
