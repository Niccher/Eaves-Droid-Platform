<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

class ThrottleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();
        // Allow up to 60 requests per minute per IP to accommodate burst uploads
        if ($throttler->check($request->getIPAddress(), 60, 60) === false) {
            return Services::response()
                ->setJSON(['success' => false, 'message' => 'Too many requests'])
                ->setStatusCode(429);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing needed after
    }
}