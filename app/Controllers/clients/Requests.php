<?php

namespace App\Controllers\clients;



use App\Models\Mod_Finder;
use App\Models\Mod_User;

class Requests extends BaseClientController
{

	public function send_request(){
        // Auth check and init handled in parent

		$pg = 'requests';
		$data['pag'] = 'requests';
		$data["user_info"] = $this->finderModel->basic_user(); // Or $this->userData from parent

        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/account/'.$pg, $data)
			. view('headers_footers/footer_users');
	}
    
    // send_sleep seems missing based on file view, but if it exists in another version or I missed it...
    // I will only replace what I see.
}
