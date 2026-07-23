<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;

class BaseAdminController extends BaseController
{
    protected $userData;
    protected $userId;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void
    {
        parent::initController($request, $response, $logger);

        if (!auth()->loggedIn()) {
            session()->setFlashdata('error', 'Please login to continue');
            throw new \RuntimeException('Authentication required');
        }

        $user = auth()->user();
        $this->userId = (int) $user->id;
        $this->userData = [
            'id' => $this->userId,
            'username' => $user->username,
            'email' => $user->getEmail(),
        ];
    }

    protected function renderView(string $mainView, array $extraData = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
            'active_device_id' => null,
            'sidebar_user_devices' => [],
        ], $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_admin', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_users', $data);
    }

    protected function getDb(): \CodeIgniter\Database\BaseConnection
    {
        return \Config\Database::connect();
    }
}
