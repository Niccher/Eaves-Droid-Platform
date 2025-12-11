<?php

namespace App\Models;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Model;

class Mod_User extends Model{
    use ResponseTrait;

    public function basic_user(){
        if (auth()->loggedIn()){
            $user_data = json_decode(json_encode(auth()->user()), true);
            return $user_data;
        }
    }

	public function get_vars($user_id){//$device_id
		$builder = $this->db->table('tbl_Users');
		$query_sent = $builder->select('*')
			->where('Person_ID', $user_id)
			->limit(1)
			->get();

		return $query_sent->getRowArray();
	}

	public function create_token($user_id, $token, $ip_add){
		//Token_ID  Token_Created   Token_Owner     Token_Status    Token_Initiator     Token_Expiry
        $dated = date('Y-m-d H:i:s');
        $future_date = strtotime('+1 month', strtotime($dated));
        $future_dated = date('Y-m-d H:i:s', $future_date);
		$data = array(
			'Token_Created' => $dated,
			'Token_Owner' => $user_id,
			'Token' => $token,
			'Token_Status' => "00",
			'Token_Initiator' => $ip_add,
			//'Token_Expiry' => time() + (90 * 24 * 60 * 60)
			'Token_Expiry' => $future_dated
		);
        return $this->db->table('tbl_Tokens')->insert($data);
	}

	public function token_mark($token_owner, $token, $token_id){
		$builder = $this->db->table('tbl_Tokens');
		$query_sent = $builder->set('Token_Status',  "11")
			->where('Token', $token)
			->where('Token_ID', $token_id)
			->where('Token_Owner', $token_owner);
		return $query_sent->update();
	}

	public function get_token($user_id){
		$builder = $this->db->table('tbl_Tokens');
		$query_sent = $builder->select('*')
			->where('Token_Owner', $user_id)
			->orderBy('Token_ID', 'DESC')
			->limit(1)
			->get();
		return $query_sent->getRowArray();
	}

	public function get_devices($user_id){
		$builder = $this->db->table('tbl_Interactions');
		$query_sent = $builder->select('*')
			->where('User_ID', $user_id)
			->orderBy('Interaction', 'DESC')
			->groupBy("IP")
			->get();
		return $query_sent->getResultArray();
	}

	public function get_interactions($user_id){
		$builder = $this->db->table('tbl_Interactions');
		$query_sent = $builder->select('*')
			->where('User_ID', $user_id)
			->orderBy('Interaction', 'DESC')
			->groupBy("IP")
			->get();
		return $query_sent->getResultArray();
	}
}
