<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

class ImpersonateFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }

        $isSuperadmin = auth()->user()->inGroup('superadmin');
        $isImpersonating = session()->get('impersonated_by') !== null;

        if (!$isSuperadmin && !$isImpersonating) {
            return redirect()->to(config('Auth')->groupDeniedRedirect())
                ->with('error', lang('Auth.notEnoughPrivilege'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}