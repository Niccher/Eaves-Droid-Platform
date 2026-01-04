<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_User extends Model
{
    /**
     * Gets basic user data if logged in.
     *
     * @return array|false
     */
    public function basic_user()
    {
        try {
            if (auth()->loggedIn()) {
                return json_decode(json_encode(auth()->user()), true);
            }
            log_message('error', 'User not logged in');
            return false;
        } catch (\Exception $e) {
            log_message('error', 'basic_user error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets user variables.
     *
     * @param int $user_id
     * @return array|false
     */
    public function get_data_tbl_users(int $user_id)
    {
        try {
            $result = $this->db->table('tbl_Users')
                ->where('Person_ID', $user_id)
                ->limit(1)
                ->get()
                ->getRowArray();

            if (is_array($result)) {
                return $result;
            }

            log_message('info', 'No custom user data found in tbl_Users for user ID: ' . $user_id);
            return false;

        } catch (\Exception $e) {
            log_message('error', 'get_custom_user_data error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user data from Shield's default 'users' table
     *
     * @param int $user_id
     * @return array|false Returns user row as array or false if not found
     */
    public function get_data_users(int $user_id)
    {
        try {
            $result = $this->db->table('users')
                ->where('id', $user_id)
                ->limit(1)
                ->get()
                ->getRowArray();

            if (is_array($result)) {
                return $result;
            }

            log_message('info', 'No Shield user data found in users table for user ID: ' . $user_id);
            return false;

        } catch (\Exception $e) {
            log_message('error', 'get_shield_user_data error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get combined user variables from both tables
     * Merges data from tbl_Users and Shield's users table
     *
     * @param int $user_id
     * @return array|false Merged data or false if nothing found
     */
    public function get_vars(int $user_id)
    {
        try {
            $customData  = $this->get_data_tbl_users($user_id);
            $shieldData  = $this->get_data_users($user_id);

            $result = [];

            if (is_array($customData)) {
                $result = array_merge($result, $customData);
            }

            if (is_array($shieldData)) {
                $result = array_merge($result, $shieldData);
            }

            if (!empty($result)) {
                return $result;
            }

            log_message('info', 'No user variables found for user ID: ' . $user_id);
            return false;

        } catch (\Exception $e) {
            log_message('error', 'get_vars error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a secure token with expiration.
     *
     * @param int $user_id
     * @param string $token
     * @param string $ip_add
     * @return bool
     */
    public function create_token(int $user_id, string $token, string $ip_add): bool
    {
        try {
            $dated = date('Y-m-d H:i:s');
            $future_date = date('Y-m-d H:i:s', strtotime('+1 month', strtotime($dated)));

            $data = [
                'Token_Created' => $dated,
                'Token_Owner' => $user_id,
                'Token' => $token,
                'Token_Status' => "00",
                'Token_Initiator' => $ip_add,
                'Token_Expiry' => $future_date,
            ];

            if ($this->db->table('tbl_Tokens')->insert($data)) {
                log_message('info', 'Token created for user ' . $user_id);
                return true;
            }

            log_message('error', 'Token creation failed for user ' . $user_id);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'create_token error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marks a token as used.
     *
     * @param int $token_owner
     * @param string $token
     * @param int $token_id
     * @return bool
     */
    public function token_mark(int $token_owner, string $token, int $token_id): bool
    {
        try {
            $builder = $this->db->table('tbl_Tokens');
            $builder->set('Token_Status', "11")
                ->where('Token', $token)
                ->where('Token_ID', $token_id)
                ->where('Token_Owner', $token_owner);

            if ($builder->update()) {
                log_message('info', 'Token marked for owner ' . $token_owner);
                return true;
            }

            log_message('error', 'Token mark failed for owner ' . $token_owner);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'token_mark error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets latest token for user.
     *
     * @param int $user_id
     * @return array|false
     */
    public function get_token(int $user_id)
    {
        try {
            $result = $this->db->table('tbl_Tokens')
                ->where('Token_Owner', $user_id)
                ->orderBy('Token_ID', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
            if ($result) {
                return $result;
            }
            log_message('error', 'No token found for user ' . $user_id);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'get_token error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets user devices.
     *
     * @param int $user_id
     * @return array
     */
    public function get_devices(int $user_id): array
    {
        try {
            return $this->db->table('tbl_Interactions')
                ->where('User_ID', $user_id)
                ->orderBy('Interaction', 'DESC')
                ->groupBy('IP')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_devices error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets user interactions.
     *
     * @param int $user_id
     * @return array
     */
    public function get_interactions(int $user_id): array
    {
        try {
            return $this->db->table('tbl_Interactions')
                ->where('User_ID', $user_id)
                ->orderBy('Interaction', 'DESC')
//                ->groupBy('IP')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_interactions error: ' . $e->getMessage());
            return [];
        }
    }
}