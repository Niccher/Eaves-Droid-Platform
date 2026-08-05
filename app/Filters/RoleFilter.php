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

    /**
     * Routes to skip RBAC check entirely
     */
    protected array $skipRoutes = [
        'login',
        'logout',
        'register',
        'forgot',
        'forgot/offline',
        'reset-password',
        'error/403',
        'error/404',
        'error/500',
        'error/503',
        'error/general',
        'landing',
        'download',
        'aboutus',
        'faqs_terms',
        'how_to',
        'contactus',
        'pricing',
    ];

    public function before(RequestInterface $request, $arguments = null): ?RedirectResponse
    {
        // Skip RBAC for allowed routes
        $route = $request->getUri()->getPath();
        $route = ltrim($route, '/');
        
        foreach ($this->skipRoutes as $skip) {
            if ($route === $skip || str_starts_with($route, $skip . '/')) {
                return null;
            }
        }

        // Allow access to root path
        if ($route === '') {
            return null;
        }

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

        // Impersonating sessions retain the acting superadmin's privileges
        $isImpersonating = session()->get('impersonated_by') !== null;

        // Check if user has any of the required roles
        if (!$isImpersonating && !empty($requiredRoles)) {
            $hasRole = false;
            foreach ($requiredRoles as $role) {
                if (auth()->user()->inGroup($role)) {
                    $hasRole = true;
                    break;
                }
            }

            if (!$hasRole) {
                // Redirect to appropriate dashboard based on user's role
                $user = auth()->user();
                if ($user->inGroup('superadmin')) {
                    return redirect()->to('/superadmin/home');
                } elseif ($user->inGroup('admin')) {
                    return redirect()->to('/admin/dashboard');
                } else {
                    return redirect()->to('/home');
                }
            }
        }

        // Handle non-impersonating non-superadmin access to superadmin routes
        if (!$isImpersonating && str_starts_with($route, '/superadmin')) {
            $isSuperadmin = auth()->user()->inGroup('superadmin');

            if (!$isSuperadmin) {
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