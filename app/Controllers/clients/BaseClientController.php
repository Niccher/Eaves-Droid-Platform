<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;
use App\Models\Mod_Finder;
use CodeIgniter\HTTP\RedirectResponse;

class BaseClientController extends BaseController
{
    protected $finderModel;
    protected $userData;
    protected $userId;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        // Force login
        if (!auth()->loggedIn()) {
            session()->setFlashdata('error', 'Please login to continue');
            return redirect()->to('login')->send();
        }

        $this->finderModel = new Mod_Finder();

        $this->userData = $this->finderModel->basic_user();
        if (!$this->userData || !isset($this->userData['id'])) {
            session()->setFlashdata('error', 'User session invalid');
            return redirect()->to('login')->send();
        }

        $this->userId = (int) $this->userData['id'];
    }

    protected function getUserDataCounts(): array
    {
        return [
            'total_apps'     => $this->finderModel->get_count_Apps($this->userId),
            'total_contacts' => $this->finderModel->get_count_Contacts($this->userId),
            'total_sms'      => $this->finderModel->get_count_Sms($this->userId),
            'total_calls'    => $this->finderModel->get_count_Calls($this->userId),
            'active_sms'     => $this->finderModel->get_sms_active($this->userId),
            'active_calls'   => $this->finderModel->get_calls_active($this->userId),
        ];
    }

    protected function renderUserView(string $mainView, array $extraData = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
        ], $this->getUserDataCounts(), $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_data_datatables', $data);
    }
}