<?php

namespace App\Models;

use CodeIgniter\Model;


class Mod_Extract extends Model{

    public function get_sms_between_contacts($user_id, $contactNumber1, $contactNumber2){
        $builder = $this->db->table('tbl_Sms');
        $query_sent = $builder
            ->where('meta_Owner', $user_id)
            ->where('sms_number', $contactNumber1)
            ->orWhere('sms_number', $contactNumber2)
            ->orderBy('sms_time', 'DESC')
            ->get();
        return $query_sent->getResultArray();
    }

    public function get_logs_between_contacts($user_id, $contactNumber1, $contactNumber2){
        $builder = $this->db->table('tbl_Logs');
        $query_sent = $builder
            ->where('meta_Owner', $user_id)
            ->where('Caller', $contactNumber1)
            ->orWhere('Caller', $contactNumber2)
            ->orderBy('Timestamp', 'DESC')
            ->get();
        return $query_sent->getResultArray();
    }

    public function get_contact_at($contact_id){
        $builder = $this->db->table('tbl_Contacts');
        $query_sent = $builder
            ->where('ID', $contact_id)
            ->get();
        return $query_sent->getRowArray();
    }

    public function get_sms_from($user_id, $sender){
        $builder = $this->db->table('tbl_Sms');
        $query_sent = $builder
            ->where('meta_Owner', $user_id)
            ->where('sms_number', $sender)
            ->get();
        return $query_sent->getResultArray();
    }
}
