<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;

use App\Models\Mod_Finder;
use App\Models\Mod_Android;
use App\Models\Mod_User;

use CodeIgniter\API\ResponseTrait;

class Profile extends BaseController
{
    use ResponseTrait;

	public function profile_upload(){
		$model_finder = new Mod_Finder();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data["user_info"] = $model_finder->basic_user();
		$person_id = $data["user_info"]['id'];

		if (!empty($_FILES) ) {

			$allowed = array("png","jpeg", "jpg",'gif','bmp','tiff','webp');

			$tempFile = $_FILES['file']['tmp_name'];
			$realFile = $_FILES['file']['name'];

			$ext = strtolower(pathinfo($realFile, PATHINFO_EXTENSION));

			if (in_array($ext, $allowed)) {
				$newer_name = random_string('alnum', 8).'_'.random_string('alnum', 8).'.'.$ext;
				$code = substr(time(), -7);
				$newfilename = $code."_".$newer_name;

				$this->model_user->update_profile($person_id, $newfilename);

				move_uploaded_file($tempFile, "uploads/profiles/" . $newfilename);
			}
		}
	}
	
	public function profile_update(){
		$model_finder = new Mod_Finder();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data['pag'] = 'contacts';
		$data["user_info"] = $model_finder->basic_user();

		$person_id = $data["user_info"]['id'];

		if(($_POST['ed_name']) != "") {
			$new_name = base64_encode($this->model_cryption->Enc_String($_POST['ed_name']));
			$this->model_user->update_profile_name($person_id, $new_name);
		}

		if(($_POST['ed_description']) != "") {
			$new_bio = base64_encode($this->model_cryption->Enc_String($_POST['ed_description']));
			$this->model_user->update_profile_bio($person_id, $new_bio);
		}

		if(($_POST['ed_email']) != "") {
			$new_email = base64_encode($this->model_cryption->Enc_String($_POST['ed_email']));
			$this->model_user->update_profile_mail($person_id, $new_email);
		}

		return redirect()->to('account/profile');
	}

	public function token_generate(){
		$model_finder = new Mod_Finder();
        $model_user = new Mod_User();

		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data["user_info"] = $model_finder->basic_user();
		$person_id = $data["user_info"]['id'];

		$token = random_string('numeric', 8);
        $ip_add = $this->request->getIPAddress();

		$model_user->create_token($person_id, $token, $ip_add);

		return redirect()->to('account/setting');
	}

    public function profile_del_apps(){
        $model_finder = new Mod_Finder();
        $model_android = new Mod_Android();

        if (!auth()->loggedIn()){
            return redirect()->to('login');
        }

        $data["user_info"] = $model_finder->basic_user();

        $person_id = $data["user_info"]['id'];
        $dated = date('Y-m-d H:i:s');
        $ip_add = $this->request->getIPAddress();

        $model_android->data_register_action($person_id,"Delete All Apps", $ip_add, $dated);
        $model_android->data_del_apps($person_id);

        return redirect()->to('account/profile');
    }

    public function profile_del_call_logs(){
        $model_finder = new Mod_Finder();
        $model_android = new Mod_Android();

        if (!auth()->loggedIn()){
            return redirect()->to('login');
        }

        $data["user_info"] = $model_finder->basic_user();

        $person_id = $data["user_info"]['id'];
        $dated = date('Y-m-d H:i:s');
        $ip_add = $this->request->getIPAddress();

        $model_android->data_register_action($person_id,"Delete All Calls", $ip_add, $dated);
        $model_android->data_del_call_logs($person_id);

        return redirect()->to('account/profile');
    }

    public function profile_del_contacts(){
        $model_finder = new Mod_Finder();
        $model_android = new Mod_Android();

        if (!auth()->loggedIn()){
            return redirect()->to('login');
        }

        $data["user_info"] = $model_finder->basic_user();

        $person_id = $data["user_info"]['id'];
        $dated = date('Y-m-d H:i:s');
        $ip_add = $this->request->getIPAddress();

        $model_android->data_register_action($person_id,"Delete All Contacts", $ip_add, $dated);
        $model_android->data_del_contacts($person_id);

        return redirect()->to('account/profile');
    }

    public function profile_del_sms(){
        $model_finder = new Mod_Finder();
        $model_android = new Mod_Android();

        if (!auth()->loggedIn()){
            return redirect()->to('login');
        }

        $data["user_info"] = $model_finder->basic_user();

        $person_id = $data["user_info"]['id'];
        $dated = date('Y-m-d H:i:s');
        $ip_add = $this->request->getIPAddress();

        $model_android->data_register_action($person_id,"Delete All Sms", $ip_add, $dated);
        $model_android->data_del_sms($person_id);

        return redirect()->to('account/profile');
    }
}
