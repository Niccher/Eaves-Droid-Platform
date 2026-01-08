<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Receive extends Model
{
    /**
     * Creates or gets device print ID.
     *
     * @param array $print_dump
     * @return string|false
     */
    public function make_device_print(array $print_dump)
    {
        try {
            $builder = $this->db->table('tbl_device_profile');
            $deviceChecksum = $print_dump['device_checksum'];

            // Always use update - will insert if not exists in some databases
            // But for MySQL with InnoDB, we need to check first

            $existing = $builder->select('1')
                ->where('device_checksum', $deviceChecksum)
                ->get()
                ->getRow();

            if ($existing) {
                // Update existing
                $builder->where('device_checksum', $deviceChecksum)
                    ->update($print_dump);
                $action = 'updated';
            } else {
                // Insert new
                $builder->insert($print_dump);
                $action = 'created';
            }

            return json_encode([
                'success' => true,
                'dev_chck_sum' => $deviceChecksum,
                'dev_adr_id' => $print_dump['android_id'] ?? null,
                'action' => $action,
                'is_new' => ($action === 'created')
            ]);

        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Makes a test token entry.
     *
     * @param string $var_sent_token
     * @param string $var_time
     * @param string $var_ip
     * @param string $var_format
     * @return bool
     */
    public function make_test_token(string $var_sent_token, string $var_time, string $var_ip, string $var_format): bool
    {
        try {
            $data = [
                'token_submitted' => $var_sent_token,
                'token_senttime' => $var_time,
                'token_received' => time(),
                'token_ip' => $var_ip,
                'token_format' => $var_format,
            ];

            if ($this->db->table('tbl_Tokentest')->insert($data)) {
                log_message('info', 'Test token created: ' . $var_sent_token);
                return true;
            }

            log_message('error', 'Failed to insert test token');
            return false;
        } catch (\Exception $e) {
            log_message('error', 'make_test_token error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets token owner.
     *
     * @param string $token
     * @return array|false
     */
    public function get_token_owner(string $token)
    {
        try {
            $result = $this->db->table('tbl_tokens')
                ->where('token', $token)
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($result) {
                log_message('info', 'Token owner found for: ' . $token);
                return $result;
            }

            log_message('error', 'No owner found for token: ' . $token);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'get_token_owner error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Formats file size.
     *
     * @param int $attachment_size
     * @return string
     */
    public function get_file_size(int $attachment_size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $power = $attachment_size > 0 ? floor(log($attachment_size, 1024)) : 0;
        return number_format($attachment_size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    /**
     * Makes an upload entry.
     *
     * @param string $tr_token
     * @param string $tr_namereal
     * @param string $tr_namenew
     * @param int $tr_size
     * @param string $tr_ext
     * @param string $tr_text
     * @return bool
     */
    public function make_upload(string $tr_token, string $tr_namereal, string $tr_namenew, int $tr_size, string $tr_ext, string $tr_text): bool
    {
        try {
            $dated = date('Y-m-d H:i:s');
            $data = [
                'Up_time' => $dated,
                'Up_token' => $tr_token,
                'Up_file_name' => $tr_namenew,
                'Up_file_realname' => $tr_namereal,
                'Up_file_size' => $tr_size,
                'Up_file_extension' => $tr_ext,
                'Up_file_text' => $tr_text,
                'Up_file_viewed' => 0,
                'Up_fille_downloaded' => 0,
            ];

            if ($this->db->table('tbl_Uploaded')->insert($data)) {
                log_message('info', 'Upload entry created: ' . $tr_namenew);
                return true;
            }

            log_message('error', 'Failed to insert upload entry');
            return false;
        } catch (\Exception $e) {
            log_message('error', 'make_upload error: ' . $e->getMessage());
            return false;
        }
    }
}