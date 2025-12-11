<?php
namespace App\Models;

use CodeIgniter\Model;

class Mod_Android extends Model{

    public function token_test($token){
        $builder = $this->db->table('tbl_Tokens');
        $query_sent = $builder->where('Token', $token)
            ->where('Token_Status', "00")
            ->get();
        $results = $query_sent->getResultArray();

        if (count($results) == 1) {
            return $results[0];
        } else {
            return "--nill--";
        }
    }

    public function data_register_action($user_id, $action, $ip_add, $date){
        $data = array(
            'User_ID' => $user_id,
            'Action' => $action,
            'IP' => $ip_add,
            'Timestamps' => $date,
        );

        $builder = $this->db->table('tbl_Interactions');
        $builder->insert($data);
    }

    public function data_del_apps($user_id){
        $builder = $this->db->table('tbl_Apps');
        $builder->where('meta_Owner', $user_id);
        $builder->delete();
    }

    public function data_del_call_logs($user_id){
        $builder = $this->db->table('tbl_Logs');
        $builder->where('meta_Owner', $user_id);
        $builder->delete();
    }

    public function data_del_contacts($user_id){
        $builder = $this->db->table('tbl_Contacts');
        $builder->where('meta_Owner', $user_id);
        $builder->delete();
    }

    public function data_del_sms($user_id){
        $builder = $this->db->table('tbl_Sms');
        $builder->where('meta_Owner', $user_id);
        $builder->delete();
    }

    public function contacts_trim_number_length($phone_number){
        //$has_254 = str_starts_with($phone_number, "+254") ;
        $has_254 = substr( $phone_number, 0, 4 ) === "+254";
        $char_count = strlen($phone_number);
        if ($has_254 && ($char_count === 13)){
            $phone = str_replace("+2547", "07", $phone_number);
            return $phone;
        }else{
            return $phone_number;
        }
    }
}
?>