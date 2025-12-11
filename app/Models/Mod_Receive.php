<?php

namespace App\Models;

use CodeIgniter\Model;

use Config\Encryption;
use Config\Services;

class Mod_Receive extends Model{

    public function make_device_print($print_dump){
        $builder = $this->db->table('tbl_Print');
        $query_sent = $builder->select('*')
            ->where('p_Board', $print_dump["p_Board"])
            ->where('p_Brand', $print_dump["p_Brand"])
            ->where('p_Device', $print_dump["p_Device"])
            ->where('p_Display', $print_dump["p_Display"])
            ->where('p_Hardware', $print_dump["p_Hardware"])
            ->where('p_Manufacturer', $print_dump["p_Manufacturer"])
            ->where('p_Model', $print_dump["p_Model"])
            ->get();

        $results = $query_sent->getResultArray();

        if (count($results)==1) {
            $pd_id = $results[0]['pd_id'];
            return "{'pd_id':'$pd_id'}";
        }else{
            $builder = $this->db->table('tbl_Print');
            $builder->insert($print_dump);

            $builder = $this->db->table('tbl_Print');
            $query_dev = $builder->selectMax('pd_id', 'maxid')->get();
            $query_dev->getRow();

            if ($query_dev) {
                $pd_id = $query_dev->maxid;
            }
            return "{'pd_id':'$pd_id'}";
        }

    }

    public function make_test_token($var_sent_token , $var_time, $var_ip, $var_format){
        $data = array(
            'token_submitted' => $var_sent_token,
            'token_senttime' => $var_time,
            'token_received' => time(),
            'token_ip' => $var_ip,
            'token_format' => $var_format,
        );
        $builder = $this->db->table('tbl_Tokentest');
        $builder->insert($data);
    }

    public function get_token_owner($token){
        $builder = $this->db->table('tbl_Tokens');
        $query_sent = $builder->where('Token', $token)
            ->get();
        $results = $query_sent->getResultArray();

        if (count($results)==1) {
            return  $results[0];
        }else{
            return "-0-";
        }
    }

    public function get_file_size($attachment_size){
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $attachment_size > 0 ? floor(log($attachment_size, 1024)) : 0;
        return number_format($attachment_size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    public function make_upload($tr_token , $tr_namereal , $tr_namenew , $tr_size, $tr_ext, $tr_text){
        $dated = date('Y-m-d H:i:s');

        $data = array(
            'Up_time' => $dated,
            'Up_token' => $tr_token,
            'Up_file_name' => $tr_namenew,
            'Up_file_realname' => $tr_namereal,
            'Up_file_size' => $tr_size,
            'Up_file_extension' => $tr_ext,
            'Up_file_text' => $tr_text,
            'Up_file_viewed' => 0,
            'Up_fille_downloaded' => 0,
        );
        $builder = $this->db->table('tbl_Uploaded');
        $builder->insert($data);
    }

}
