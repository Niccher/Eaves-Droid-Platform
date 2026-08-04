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

    /**
     * Displays a 403 Forbidden error page.
     *
     * @return string
     */
    public function show403(): string
    {
        return view('errors/custom_errors/error_403', [
            'message' => session()->getFlashdata('error') ?: null,
        ]);
    }

    /**
     * Displays a 404 Not Found error page.
     *
     * @return string
     */
    public function show404(): string
    {
        return view('errors/custom_errors/error_404');
    }

    /**
     * Displays a 500 Internal Server Error page.
     *
     * @return string
     */
    public function show500(): string
    {
        return view('errors/custom_errors/error_500');
    }

    /**
     * Displays a 503 Service Unavailable error page.
     *
     * @return string
     */
    public function show503(): string
    {
        return view('errors/custom_errors/error_503');
    }

    /**
     * Displays a general error page.
     *
     * @return string
     */
    public function showGeneral(): string
    {
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