<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

class ImpersonateFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?RedirectResponse
    {
        // Authentication is handled by the session filter.
        // This filter only governs superadmin-route access for impersonating users.
        if (!auth()->loggedIn()) {
            return null;
        }

        $path = $request->getUri()->getPath();
        $path = ltrim($path, '/');

        // Only enforce on superadmin routes
        if (!str_starts_with($path, 'superadmin')) {
            return null;
        }

        $isSuperadmin = auth()->user()->inGroup('superadmin');
        $isImpersonating = session()->get('impersonated_by') !== null;

        if ($isSuperadmin || $isImpersonating) {
            return null;
        }

        return redirect()->to(config('Auth')->groupDeniedRedirect())
            ->with('error', lang('Auth.notEnoughPrivilege'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
