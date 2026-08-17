<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Errors extends Controller
{
    /**
     * Constructor.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param LoggerInterface $logger
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Load helpers
        helper('url');
    }

    private function isApiRequest(): bool
    {
        $path = $this->request->getUri()->getPath();
        return str_contains($path, 'api/v1') || str_contains($path, 'api/');
    }

    /**
     * Displays a 403 Forbidden error page.
     *
     * @return mixed
     */
    public function show403()
    {
        if ($this->isApiRequest()) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'status' => 403,
                    'error' => 'Forbidden',
                    'message' => session()->getFlashdata('error') ?: 'Access denied.'
                ]);
        }
        return view('errors/custom_errors/error_403', [
            'message' => session()->getFlashdata('error') ?: null,
        ]);
    }

    /**
     * Displays a 404 Not Found error page.
     *
     * @return mixed
     */
    public function show404()
    {
        if ($this->isApiRequest()) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'status' => 404,
                    'error' => 'Not Found',
                    'message' => 'The requested API endpoint does not exist.'
                ]);
        }
        return view('errors/custom_errors/error_404');
    }

    /**
     * Displays a 500 Internal Server Error page.
     *
     * @return mixed
     */
    public function show500()
    {
        if ($this->isApiRequest()) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'status' => 500,
                    'error' => 'Internal Server Error',
                    'message' => 'An unexpected server error occurred.'
                ]);
        }
        return view('errors/custom_errors/error_500');
    }

    /**
     * Displays a 503 Service Unavailable error page.
     *
     * @return mixed
     */
    public function show503()
    {
        if ($this->isApiRequest()) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'status' => 503,
                    'error' => 'Service Unavailable',
                    'message' => 'The service is temporarily unavailable.'
                ]);
        }
        return view('errors/custom_errors/error_503');
    }

    /**
     * Displays a general error page.
     *
     * @return mixed
     */
    public function showGeneral()
    {
        if ($this->isApiRequest()) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'status' => 500,
                    'error' => 'Internal Server Error',
                    'message' => 'A general server error occurred.'
                ]);
        }
        return view('errors/custom_errors/error_general');
    }

    /**
     * Simulate a 403 error for testing.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function trigger403()
    {
        return $this->response
            ->setStatusCode(403)
            ->setBody(view('errors/custom_errors/error_403'));
    }

    /**
     * Simulate a 404 error for testing.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function trigger404()
    {
        return $this->response
            ->setStatusCode(404)
            ->setBody(view('errors/custom_errors/error_404'));
    }

    /**
     * Simulate a 500 error for testing.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function trigger500()
    {
        return $this->response
            ->setStatusCode(500)
            ->setBody(view('errors/custom_errors/error_500'));
    }

    /**
     * Simulate a 503 error for testing.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function trigger503()
    {
        return $this->response
            ->setStatusCode(503)
            ->setBody(view('errors/custom_errors/error_503'));
    }
}