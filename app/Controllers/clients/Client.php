<?php

namespace App\Controllers\clients;



use App\Models\Mod_Finder;
use CodeIgniter\API\ResponseTrait;

class Client extends BaseClientController
{
	use ResponseTrait;

	public function home(){
        // Auth check is handled in BaseClientController::initController
        
		$data['pag'] = 'home';
		$data["user_info"] = $this->finderModel->basic_user();

        // Get counts using BaseClientController method
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/dash', $data)
			. view('headers_footers/footer_users_home');
	}

	public function call_logs(){
        // Auth check is handled in BaseClientController::initController

		$data['pag'] = 'call_logs';
		$data["user_info"] = $this->finderModel->basic_user();

        // Get counts using BaseClientController method
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

		$data['call_urls'] = '
                        <a class="btn btn-primary" href="'.base_url("call_logs").'">All</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/incoming").'">Incoming</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/outgoing").'">Outgoing</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/rejected").'">Rejected</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/blocked").'">Blocked</a>
                        &nbsp;&nbsp;';

		$data["call_logs_dump"] = $this->finderModel->get_call_logs($data["user_info"]['id']);

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/logs_all', $data)
			. view('headers_footers/footer_users');
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
