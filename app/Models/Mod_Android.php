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
            $builder = $this->db->table('tbl_user_api_tokens');
            $result = $builder->where('token', $token)
                ->where('created_at = last_used_at', NULL, FALSE)
                ->where('expires_at > NOW()', NULL, FALSE)
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
     * Cross-checks X-Device-UUID + X-Device-Checksum against tbl_device_profiles.
     *
     * Returns true  → device is known and checksum matches → allow request.
     * Returns false → checksum mismatch, unknown device, or spoofed UUID → block request.
     * Returns null  → device not yet registered (fingerprint endpoint hasn't run yet) → allow through.
     *
     * @param int    $ownerId  Token owner ID resolved from tbl_user_api_tokens
     * @param string $uuid     X-Device-UUID header  (ANDROID_ID)
     * @param string $checksum X-Device-Checksum header  (SHA256(UUID+MODEL+salt))
     * @return bool|null
     */
    public function verify_device_checksum(int $ownerId, string $uuid, string $checksum)
    {
        try {
            // Look up any registered device for this owner with this android_id
            $device = $this->db->table('tbl_device_profiles')
                ->select('device_id, android_id')
                ->where('owner_id', $ownerId)
                ->where('android_id', $uuid)
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!$device) {
                // Device UUID not in DB yet — fingerprint hasn't been submitted yet
                // Return null to signal "unregistered, allow through"
                return null;
            }

            // Device exists — compare stored checksum (device_id column) against header
            $storedChecksum = $device['device_id'] ?? '';
            $matches = hash_equals($storedChecksum, $checksum);

            if (!$matches) {
                log_message('warning', "Checksum mismatch for owner #{$ownerId} uuid={$uuid}: " .
                    "sent={$checksum} stored={$storedChecksum}");
            }

            return $matches;

        } catch (\Exception $e) {
            log_message('error', 'verify_device_checksum failed: ' . $e->getMessage());
            // On DB error, fail open (allow) to avoid locking out legitimate devices
            return null;
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
                'user_id' => $user_id,
                'action' => $action,
                'ip_address' => $ip_add,
                'created_at' => $date,
            ];

            $builder = $this->db->table('tbl_user_interactions');
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

    public function data_del_apps(int $user_id): bool { return $this->data_delete_by_user('tbl_extracted_installed_apps', $user_id); }
    public function data_del_call_logs(int $user_id): bool { return $this->data_delete_by_user('tbl_extracted_call_logs', $user_id); }
    public function data_del_contacts(int $user_id): bool { return $this->data_delete_by_user('tbl_extracted_contacts', $user_id); }
    public function data_del_sms(int $user_id): bool { return $this->data_delete_by_user('tbl_extracted_sms', $user_id); }

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