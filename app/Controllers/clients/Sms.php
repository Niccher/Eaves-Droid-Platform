<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;

use App\Models\Mod_Finder;
use CodeIgniter\API\ResponseTrait;

class Sms extends BaseController
{
	use ResponseTrait;

	public function sms(){
		$model_finder = new Mod_Finder();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data['pag'] = 'sms';
		$data['sms_head'] = 'All Sms';
		$data["user_info"] = $model_finder->basic_user();

		$data["total_apps"] = $model_finder->get_count_Apps($data["user_info"]['id']);
		$data["total_contacts"] = $model_finder->get_count_Contacts($data["user_info"]['id']);
		$data["total_sms"] = $model_finder->get_count_Sms($data["user_info"]['id'] );
		$data["total_calls"] = $model_finder->get_count_Calls($data["user_info"]['id']);
		$data["active_sms"] = $model_finder->get_sms_active($data["user_info"]['id']);
		$data["active_calls"] = $model_finder->get_calls_active($data["user_info"]['id']);

		$data['sms_urls'] = '
                        <a class="btn btn-primary" href="'.base_url("sms").'">All</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("sms/inbox").'">Inbox</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("sms/sent").'">Sent</a>
                        &nbsp;&nbsp;';

		$data["sms_dump"] = $model_finder->get_sms($data["user_info"]['id']);

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/sms/sms', $data)
			. view('headers_footers/footer_users');
	}

	public function sms_inbox(){
		$model_finder = new Mod_Finder();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data['pag'] = 'sms';
		$data['sms_head'] = 'Received Sms';
		$data["user_info"] = $model_finder->basic_user();

		$data["total_apps"] = $model_finder->get_count_Apps($data["user_info"]['id']);
		$data["total_contacts"] = $model_finder->get_count_Contacts($data["user_info"]['id']);
		$data["total_sms"] = $model_finder->get_count_Sms($data["user_info"]['id'] );
		$data["total_calls"] = $model_finder->get_count_Calls($data["user_info"]['id']);
		$data["active_sms"] = $model_finder->get_sms_active($data["user_info"]['id']);
		$data["active_calls"] = $model_finder->get_calls_active($data["user_info"]['id']);

		$data['sms_urls'] = '
                        <a class="btn btn-outline-primary" href="'.base_url("sms").'">All</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-primary" href="'.base_url("sms/inbox").'">Inbox</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("sms/sent").'">Sent</a>
                        &nbsp;&nbsp;';

		$data["sms_dump"] = $model_finder->get_sms_type($data["user_info"]['id'], 'inbox');

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/sms/inbox', $data)
			. view('headers_footers/footer_users');
	}

	public function sms_sent(){
		$model_finder = new Mod_Finder();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data['pag'] = 'sms';
		$data['sms_head'] = 'Sent Sms';
		$data["user_info"] = $model_finder->basic_user();

		$data["total_apps"] = $model_finder->get_count_Apps($data["user_info"]['id']);
		$data["total_contacts"] = $model_finder->get_count_Contacts($data["user_info"]['id']);
		$data["total_sms"] = $model_finder->get_count_Sms($data["user_info"]['id'] );
		$data["total_calls"] = $model_finder->get_count_Calls($data["user_info"]['id']);
		$data["active_sms"] = $model_finder->get_sms_active($data["user_info"]['id']);
		$data["active_calls"] = $model_finder->get_calls_active($data["user_info"]['id']);

		$data['sms_urls'] = '
                        <a class="btn btn-outline-primary" href="'.base_url("sms").'">All</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("sms/inbox").'">Inbox</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-primary" href="'.base_url("sms/sent").'">Sent</a>
                        &nbsp;&nbsp;';

		$data["sms_dump"] = $model_finder->get_sms_type($data["user_info"]['id'], 'sent');

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/sms/inbox', $data)
			. view('headers_footers/footer_users');
	}
}
