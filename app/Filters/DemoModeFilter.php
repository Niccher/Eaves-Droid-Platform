<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DemoModeFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // If not logged in, nothing to restrict
        if (!function_exists('auth') || !auth()->loggedIn()) {
            return;
        }

        $user = auth()->user();
        
        // If it's the demo account
        if ($user && $user->email === 'demo@eavesdroid.com') {
            // Allow GET requests to view pages
            if (strtolower($request->getMethod()) === 'get') {
                return;
            }

            // Always allow logout
            $path = $request->uri->getPath();
            if (strpos($path, 'logout') !== false) {
                return;
            }

            // Block POST, PUT, DELETE, PATCH actions
            if ($request->isAJAX()) {
                return response()->setJSON([
                    'status' => 'error',
                    'message' => 'Action not permitted in Demo Mode.'
                ])->setStatusCode(403);
            }

            return redirect()->back()->with('error', 'Action not permitted in Demo Mode. Your changes were not saved.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}
