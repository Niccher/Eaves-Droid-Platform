<?php

namespace App\Controllers\clients;



use App\Models\FinderModel;
use CodeIgniter\API\ResponseTrait;

class ClientController extends BaseClientController
{
	use ResponseTrait;

	public function home(){
        // Auth check is handled in BaseClientController::initController
        
		$data['pag'] = 'home';
		$data["user_info"] = $this->finderModel->basic_user();

        // Get counts using BaseClientController method
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts, $this->getDeviceViewData());

        // Additional dashboard data
        $data['device_health'] = $this->finderModel->get_device_health($this->userId);
        $data['recent_locations'] = $this->finderModel->get_locations($this->userId, 3);

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/dash', $data)
			. view('headers_footers/footer_users_home');
	}



	public function faqs(){
        // Auth check is handled in BaseClientController::initController
        
		$data['pag'] = 'faqs';
		$data["user_info"] = $this->finderModel->basic_user();

        // Get counts for sidebar
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/account/faqs', $data)
			. view('headers_footers/footer_users');
	}
}
