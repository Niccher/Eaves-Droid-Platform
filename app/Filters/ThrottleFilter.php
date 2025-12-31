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
        // 10 requests per minute per IP
        if ($throttler->check($request->getIPAddress(), 10, 60) === false) {
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