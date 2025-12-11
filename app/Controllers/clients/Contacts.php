<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;

use App\Models\Mod_Finder;
use App\Models\Mod_User;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Model;

class Contacts extends BaseController{

    public function index(){
        $model_finder = new Mod_Finder();
        $model_user = new Mod_User();
        if (!auth()->loggedIn()){
            return redirect()->to('login');
        }

	    $data['pag'] = 'contacts';
	    $data["user_info"] = $model_finder->basic_user();

	    $data["total_apps"] = $model_finder->get_count_Apps($data["user_info"]['id']);
	    $data["total_contacts"] = $model_finder->get_count_Contacts($data["user_info"]['id']);
	    $data["total_sms"] = $model_finder->get_count_Sms($data["user_info"]['id'] );
	    $data["total_calls"] = $model_finder->get_count_Calls($data["user_info"]['id']);
	    $data["active_sms"] = $model_finder->get_sms_active($data["user_info"]['id']);
	    $data["active_calls"] = $model_finder->get_calls_active($data["user_info"]['id']);

	    $data["contacts_dump"] = $model_finder->get_contacts($data["user_info"]['id']);

	    return view('headers_footers/head_users')
		    . view('headers_footers/sidebar_users', $data)
		    . view('users/contacts', $data)
		    . view('headers_footers/footer_data_datatables');
    }

}
