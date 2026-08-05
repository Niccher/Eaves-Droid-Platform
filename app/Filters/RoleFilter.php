<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

class RoleFilter implements FilterInterface
{
    /**
     * Role requirements for different route groups
     */
    protected array $roleMap = [
        // Admin routes: admin OR superadmin
        'admin'      => ['admin', 'superadmin'],
        // Superadmin routes: only superadmin
        'superadmin' => ['superadmin'],
        // Client/User routes: user OR superadmin (superadmin can access everything)
        'client'     => ['user', 'superadmin'],
    ];

    public function before(RequestInterface $request, $arguments = null): ?RedirectResponse
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }

        $route = $request->getUri()->getPath();
        $requiredRoles = [];

        // Determine required roles based on route prefix
        if (str_starts_with($route, '/admin')) {
            $requiredRoles = $this->roleMap['admin'];
        } elseif (str_starts_with($route, '/superadmin')) {
            $requiredRoles = $this->roleMap['superadmin'];
        } elseif (str_starts_with($route, '/home') 
            || str_starts_with($route, '/globalsearch')
            || str_starts_with($route, '/apps')
            || str_starts_with($route, '/call_logs')
            || str_starts_with($route, '/files')
            || str_starts_with($route, '/sms')
            || str_starts_with($route, '/contacts')
            || str_starts_with($route, '/call_logs')
            || str_starts_with($route, '/analysis')
            || str_starts_with($route, '/account')
            || str_starts_with($route, '/faqs')
            || str_starts_with($route, '/remote-device')) {
            $requiredRoles = $this->roleMap['client'];
        }

        // Check if user has any of the required roles
        if (!empty($requiredRoles)) {
            $hasRole = false;
            foreach ($requiredRoles as $role) {
                if (auth()->user()->inGroup($role)) {
                    $hasRole = true;
                    break;
                }
            }

            if (!$hasRole) {
                return redirect()->to(config('Auth')->groupDeniedRedirect())
                    ->with('error', lang('Auth.notEnoughPrivilege'));
            }
        }

        // Handle impersonation for superadmin routes
        if (str_starts_with($route, '/superadmin')) {
            $isSuperadmin = auth()->user()->inGroup('superadmin');
            $isImpersonating = session()->get('impersonated_by') !== null;

            if (!$isSuperadmin && !$isImpersonating) {
                return redirect()->to(config('Auth')->groupDeniedRedirect())
                    ->with('error', lang('Auth.notEnoughPrivilege'));
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}