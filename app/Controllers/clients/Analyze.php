<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;

use App\Models\Mod_Finder;
use App\Models\Mod_Crypt;
use App\Models\Mod_Extract;
use CodeIgniter\Model;

class Analyze extends BaseController{

    public function sms($target_contact){
	    $model_finder = new Mod_Finder();
	    $model_crypt = new Mod_Crypt();
	    $model_extract = new Mod_Extract();
	    
	    $encrypter = \Config\Services::encrypter();
	    if (!auth()->loggedIn()){
		    return redirect()->to('login');
	    }

	    $data['pag'] = 'sms_analyse';
	    $data["user_info"] = $model_finder->basic_user();

	    $decod_url = $model_crypt->base64url_decode($target_contact);
        $contact_id = $encrypter->decrypt(base64_decode($decod_url));

        $contact = $model_extract->get_contact_at($contact_id);

        $number = substr($contact['Number'], 0, 1);
        $new_number = $contact['Number'];
        $old_number = $contact['Number'];

        if ( $number == 0) {
            $new_number = "+254". substr($contact['Number'], 1);
        }else if ( $number == "+") {
            $old_number = $contact['Number'];
        }

        $data['sms_person'] = str_replace(" ", "", $new_number);
        $data['sms_saved'] = $contact['Name'];

        $data['sms_thread'] = $model_extract->get_sms_between_contacts($data["user_info"]['id'], str_replace(" ", "", $new_number), str_replace(" ", "", $old_number));

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/analyze/sms', $data)
            . view('headers_footers/footer_users');
    }

    public function calls($target_contact){
        $model_finder = new Mod_Finder();
        $model_crypt = new Mod_Crypt();
        $model_extract = new Mod_Extract();

        $encrypter = \Config\Services::encrypter();
        if (!auth()->loggedIn()){
            return redirect()->to('login');
        }

        $data['pag'] = 'sms_analyse';
        $data["user_info"] = $model_finder->basic_user();

        $decod_url = $model_crypt->base64url_decode($target_contact);
        $contact_id = $encrypter->decrypt(base64_decode($decod_url));

        $contact = $model_extract->get_contact_at($contact_id);

	    $number = substr($contact['Number'], 0, 1);
	    $new_number = $contact['Number'];
	    $old_number = $contact['Number'];

	    if ( $number == 0) {
		    $new_number = "+254". substr($contact['Number'], 1);
	    }else if ( $number == "+") {
		    $old_number = $contact['Number'];
	    }

        $data['log_person'] = str_replace(" ", "", $new_number);
        $data['log_saved'] = $contact['Name'];

        $data['log_thread'] = $model_extract->get_logs_between_contacts($data["user_info"]['id'], str_replace(" ", "", $new_number), str_replace(" ", "", $old_number));

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/analyze/call_logs', $data)
            . view('headers_footers/footer_users');
    }
}
