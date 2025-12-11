<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;

use App\Models\Mod_Finder;
use App\Models\Mod_User;

class Requests extends BaseController
{

	public function send_request(){
		$model_finder = new Mod_Finder();
		//$model_user = new Mod_User();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$pg = 'requests';
		$data['pag'] = 'requests';
		$data["user_info"] = $model_finder->basic_user();

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/account/'.$pg, $data)
			. view('headers_footers/footer_users');
	}
}
