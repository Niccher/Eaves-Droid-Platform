<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Android extends Model
{
    /**
     * Tests if a token is valid and active.
     *
     * @param string $token
     * @return array|false
     */
    public function token_test(string $token)
    {
        try {
//            $builder = $this->db->table('tbl_tokens');
//            $result = $builder->where('token', $token)
//                ->where('status', "00")
//                ->limit(1)
//                ->get()
//                ->getRowArray();
            $builder = $this->db->table('tbl_tokens');
            $result = $builder->where('token', $token)
                ->where('created_at = last_used_at', NULL, FALSE)  // Assumes initial last_used_at equals created_at for unused tokens
                ->where('expires_at > NOW()', NULL, FALSE)  // Checks if token has not expired (expires_at after current timestamp)
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($result) {
                log_message('info', 'Token test successful for: ' . $token);
                return $result;
            }

            log_message('error', 'Invalid token: ' . $token);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'Token test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Registers a user action.
     *
     * @param int $user_id
     * @param string $action
     * @param string $ip_add
     * @param string $date
     * @return bool
     */
    public function data_register_action(int $user_id, string $action, string $ip_add, string $date): bool
    {
        try {
            $data = [
                'User_ID' => $user_id,
                'Action' => $action,
                'IP' => $ip_add,
                'Timestamps' => $date,
            ];

            $builder = $this->db->table('tbl_Interactions');
            if ($builder->insert($data)) {
                log_message('info', 'Action registered: ' . $action . ' for user ' . $user_id);
                return true;
            }

            log_message('error', 'Failed to register action: ' . $action . ' for user ' . $user_id);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'Action registration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes data from a table by user ID.
     *
     * @param string $table
     * @param int $user_id
     * @return bool
     */
    protected function data_delete_by_user(string $table, int $user_id): bool
    {
        try {
            $builder = $this->db->table($table);
            $builder->where('owner_id', $user_id);
            if ($builder->delete()) {
                log_message('info', 'Deleted all from ' . $table . ' for user ' . $user_id);
                return true;
            }

            log_message('error', 'Delete failed for ' . $table . ' user ' . $user_id);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'Delete error in ' . $table . ': ' . $e->getMessage());
            return false;
        }
    }

    public function data_del_apps(int $user_id): bool { return $this->data_delete_by_user('tbl_apps', $user_id); }
    public function data_del_call_logs(int $user_id): bool { return $this->data_delete_by_user('tbl_call_logs', $user_id); }
    public function data_del_contacts(int $user_id): bool { return $this->data_delete_by_user('tbl_contacts', $user_id); }
    public function data_del_sms(int $user_id): bool { return $this->data_delete_by_user('tbl_sms', $user_id); }

    /**
     * Normalizes phone number length.
     *
     * @param string $phone_number
     * @return string
     */
    public function contacts_trim_number_length(string $phone_number): string
    {
        $has_254 = substr($phone_number, 0, 4) === "+254";
        $char_count = strlen($phone_number);
        if ($has_254 && $char_count === 13) {
            return str_replace("+2547", "07", $phone_number);
        }
        return $phone_number;
    }
}