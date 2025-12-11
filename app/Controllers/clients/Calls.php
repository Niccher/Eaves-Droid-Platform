<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;

use App\Models\Mod_Finder;
use CodeIgniter\API\ResponseTrait;

class Calls extends BaseController
{
	use ResponseTrait;

	public function call_logs(){
		$model_finder = new Mod_Finder();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data['pag'] = 'call_logs';
		$data["user_info"] = $model_finder->basic_user();

		$data["total_apps"] = $model_finder->get_count_Apps($data["user_info"]['id']);
		$data["total_contacts"] = $model_finder->get_count_Contacts($data["user_info"]['id']);
		$data["total_sms"] = $model_finder->get_count_Sms($data["user_info"]['id'] );
		$data["total_calls"] = $model_finder->get_count_Calls($data["user_info"]['id']);
		$data["active_sms"] = $model_finder->get_sms_active($data["user_info"]['id']);
		$data["active_calls"] = $model_finder->get_calls_active($data["user_info"]['id']);

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

		$data["call_logs_dump"] = $model_finder->get_call_logs($data["user_info"]['id']);

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/call_logs/logs_all', $data)
			. view('headers_footers/footer_data_datatables');
	}

	public function call_incoming(){
		$model_finder = new Mod_Finder();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data['pag'] = 'call_logs';
		$data["title"] = "Incoming Calls";
		$data["user_info"] = $model_finder->basic_user();

		$data["total_apps"] = $model_finder->get_count_Apps($data["user_info"]['id']);
		$data["total_contacts"] = $model_finder->get_count_Contacts($data["user_info"]['id']);
		$data["total_sms"] = $model_finder->get_count_Sms($data["user_info"]['id'] );
		$data["total_calls"] = $model_finder->get_count_Calls($data["user_info"]['id']);
		$data["active_sms"] = $model_finder->get_sms_active($data["user_info"]['id']);
		$data["active_calls"] = $model_finder->get_calls_active($data["user_info"]['id']);

		$data['call_urls'] = '
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs").'">All</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-primary" href="'.base_url("call_logs/incoming").'">Incoming</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/outgoing").'">Outgoing</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/rejected").'">Rejected</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/blocked").'">Blocked</a>
                        &nbsp;&nbsp;';

		$data["call_logs_dump"] = $model_finder->get_calls_limited($data["user_info"]['id'], "Incoming");

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/call_logs/logs_incoming', $data)
			. view('headers_footers/footer_data_datatables');
	}

	public function call_outgoing(){
		$model_finder = new Mod_Finder();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data['pag'] = 'call_logs';
		$data["title"] = "Outgoing Calls";
		$data["user_info"] = $model_finder->basic_user();

		$data["total_apps"] = $model_finder->get_count_Apps($data["user_info"]['id']);
		$data["total_contacts"] = $model_finder->get_count_Contacts($data["user_info"]['id']);
		$data["total_sms"] = $model_finder->get_count_Sms($data["user_info"]['id'] );
		$data["total_calls"] = $model_finder->get_count_Calls($data["user_info"]['id']);
		$data["active_sms"] = $model_finder->get_sms_active($data["user_info"]['id']);
		$data["active_calls"] = $model_finder->get_calls_active($data["user_info"]['id']);

		$data['call_urls'] = '
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs").'">All</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/incoming").'">Incoming</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-primary" href="'.base_url("call_logs/outgoing").'">Outgoing</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/rejected").'">Rejected</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/blocked").'">Blocked</a>
                        &nbsp;&nbsp;';

		$data["call_logs_dump"] = $model_finder->get_calls_limited($data["user_info"]['id'],"Outgoing");

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/call_logs/logs_incoming', $data)
			. view('headers_footers/footer_data_datatables');
	}
	
	public function call_blocked(){
		$model_finder = new Mod_Finder();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data['pag'] = 'call_logs';
		$data["title"] = "Blocked Calls";
		$data["user_info"] = $model_finder->basic_user();

		$data["total_apps"] = $model_finder->get_count_Apps($data["user_info"]['id']);
		$data["total_contacts"] = $model_finder->get_count_Contacts($data["user_info"]['id']);
		$data["total_sms"] = $model_finder->get_count_Sms($data["user_info"]['id'] );
		$data["total_calls"] = $model_finder->get_count_Calls($data["user_info"]['id']);
		$data["active_sms"] = $model_finder->get_sms_active($data["user_info"]['id']);
		$data["active_calls"] = $model_finder->get_calls_active($data["user_info"]['id']);

		$data['call_urls'] = '
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs").'">All</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/incoming").'">Incoming</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/outgoing").'">Outgoing</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/rejected").'">Rejected</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-primary" href="'.base_url("call_logs/blocked").'">Blocked</a>
                        &nbsp;&nbsp;';

		$data["call_logs_dump"] = $model_finder->get_calls_limited($data["user_info"]['id'],"Blocked");

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/call_logs/logs_incoming', $data)
			. view('headers_footers/footer_data_datatables');
	}
	
	public function call_rejected(){
		$model_finder = new Mod_Finder();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data['pag'] = 'call_logs';
		$data["title"] = "Missed and Rejected Calls";
		$data["user_info"] = $model_finder->basic_user();

		$data["total_apps"] = $model_finder->get_count_Apps($data["user_info"]['id']);
		$data["total_contacts"] = $model_finder->get_count_Contacts($data["user_info"]['id']);
		$data["total_sms"] = $model_finder->get_count_Sms($data["user_info"]['id'] );
		$data["total_calls"] = $model_finder->get_count_Calls($data["user_info"]['id']);
		$data["active_sms"] = $model_finder->get_sms_active($data["user_info"]['id']);
		$data["active_calls"] = $model_finder->get_calls_active($data["user_info"]['id']);

		$data['call_urls'] = '
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs").'">All</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/incoming").'">Incoming</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/outgoing").'">Outgoing</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-primary" href="'.base_url("call_logs/rejected").'">Rejected</a>
                        &nbsp;&nbsp;
                        <a class="btn btn-outline-primary" href="'.base_url("call_logs/blocked").'">Blocked</a>
                        &nbsp;&nbsp;';

		$data["call_logs_dump"] = $model_finder->get_calls_limited($data["user_info"]['id'],"Rejected");

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/call_logs/logs_all', $data)
			. view('headers_footers/footer_data_datatables');
	}
}
