<?php

namespace App\Controllers\clients;

use App\Controllers\clients\BaseClientController;
use App\Models\Mod_Finder;
use App\Models\Mod_Android;
use App\Models\Mod_User;
use App\Models\Mod_Access_Logs;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Model;

class Profile extends BaseClientController
{
    use ResponseTrait;

    public function profile_upload(){
		$model_finder = new Mod_Finder();
		if (!auth()->loggedIn()){
			return redirect()->to('login');
		}

		$data["user_info"] = $model_finder->basic_user();
		$person_id = $data["user_info"]['id'];
		$lognow = new Mod_Access_Logs();

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

                // Log action
                $lognow = new Mod_Access_Logs();

                $logdata = $lognow->logAction([
                    'user_id' => $this->userId,
                    'action_type' => 'New Profile Picture',
                    'action_category' => 'profile',
                    'action_severity' => 'low',
                    'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getUserAgent()->getAgentString(),
                    'request_url'     => current_url(),
                    'device_type' => 'desktop',
                    'success' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
                ]);
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
			$oldEmail = $this->userData['email'] ?? '';
			$new_email = base64_encode($this->model_cryption->Enc_String($_POST['ed_email']));
			$this->model_user->update_profile_mail($person_id, $new_email);
			
			// Send email changed notification
			$this->sendEmailChangedEmail($oldEmail, $_POST['ed_email']);
		}

        // Log action
        $lognow = new Mod_Access_Logs();

        $logdata = $lognow->logAction([
            'user_id' => $this->userId,
            'action_type' => 'Profile Update',
            'action_category' => 'profile',
            'action_severity' => 'low',
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'request_url'     => current_url(),
            'device_type' => 'desktop',
            'success' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
        ]);

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

        // Log action
        $lognow = new Mod_Access_Logs();

        $logdata = $lognow->logAction([
            'user_id' => $this->userId,
            'action_type' => 'Data Deletion (Apps)',
            'action_category' => 'profile',
            'action_severity' => 'low',
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'request_url'     => current_url(),
            'device_type' => 'desktop',
            'success' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
        ]);

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

        $model_android->data_del_call_logs($person_id);

        // Log action
        $lognow = new Mod_Access_Logs();

        $logdata = $lognow->logAction([
            'user_id' => $this->userId,
            'action_type' => 'Data Deletion (Call Logs)',
            'action_category' => 'profile',
            'action_severity' => 'critical',
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'request_url'     => current_url(),
            'device_type' => 'desktop',
            'success' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
        ]);

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

        $model_android->data_del_contacts($person_id);

        // Log action
        $lognow = new Mod_Access_Logs();

        $logdata = $lognow->logAction([
            'user_id' => $this->userId,
            'action_type' => 'Data Deletion (Contacts)',
            'action_category' => 'profile',
            'action_severity' => 'critical',
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'request_url'     => current_url(),
            'device_type' => 'desktop',
            'success' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
        ]);

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

        $model_android->data_del_sms($person_id);

        // Log action
        $lognow = new Mod_Access_Logs();

        $logdata = $lognow->logAction([
            'user_id' => $this->userId,
            'action_type' => 'Data Deletion (SMS)',
            'action_category' => 'profile',
            'action_severity' => 'critical',
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'request_url'     => current_url(),
            'device_type' => 'desktop',
            'success' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
        ]);

        return redirect()->to('account/profile');
    }
}
