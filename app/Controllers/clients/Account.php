<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;

use App\Models\Mod_Finder;
use App\Models\Mod_User;

class Account extends BaseController
{

	public function index(){
		$model_finder = new Mod_Finder();
		$model_user = new Mod_User();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$pg = 'profile';
		$data['pag'] = 'account_profile';
		$data["user_info"] = $model_finder->basic_user();

		$data['user_vars'] = $model_user->get_vars($data["user_info"]['id']);
		$data['user_token'] = $model_user->get_token($data["user_info"]['id']);

		if (empty($data['user_token'])){
			$token = random_string('numeric', 8);
			$model_user->create_token($data["user_info"]['id'], $token);
			$data['user_token'] = $model_user->get_token($data["user_info"]['id']);
		}

		$data["total_apps"] = $model_finder->get_count_Apps($data["user_info"]['id']);
		$data["total_contacts"] = $model_finder->get_count_Contacts($data["user_info"]['id']);
		$data["total_sms"] = $model_finder->get_count_Sms($data["user_info"]['id'] );
		$data["total_calls"] = $model_finder->get_count_Calls($data["user_info"]['id']);

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/account/'.$pg, $data)
			. view('headers_footers/footer_users');
	}

	public function setting(){
		$model_finder = new Mod_Finder();
		$model_user = new Mod_User();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$pg = 'settings';
		$data['pag'] = 'account_setting';
		$data["user_info"] = $model_finder->basic_user();

		$data['user_vars'] = $model_user->get_vars($data["user_info"]['id']);
		$data['user_token'] = $model_user->get_token($data["user_info"]['id']);

		if (empty($data['user_token'])){
			$token = random_string('numeric', 8);
			$model_user->create_token($data["user_info"]['id'], $token);
			$data['user_token'] = $model_user->get_token($data["user_info"]['id']);
		}

		$data['user_devices'] = $model_user->get_devices($data["user_info"]['id']);

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/account/'.$pg, $data)
			. view('headers_footers/footer_users');
	}

	public function access_logs(){
		$model_finder = new Mod_Finder();
		$model_user = new Mod_User();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$pg = 'access_logs';
		$data['pag'] = 'account_logs';
		$data["user_info"] = $model_finder->basic_user();

		$data['user_logs'] = $model_user->get_interactions($data["user_info"]['id']);

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/account/'.$pg, $data)
			. view('headers_footers/footer_users');
	}
}
