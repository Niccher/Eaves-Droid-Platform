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
            
            // Map input field 'device_checksum' to database column 'device_id'
            // and ensure we don't try to insert non-existent columns
            $print_dump['device_id'] = $deviceChecksum;
            unset($print_dump['device_checksum']);
            
            // Also handle fcm_token if it's optional/missing in input but table might have it
            // if input doesn't have it, we shouldn't try to update it to null necessarily,
            // or maybe we should? For now, let's just stick to the checksum fix.

            // Always use update - will insert if not exists in some databases
            // But for MySQL with InnoDB, we need to check first

            $existing = $builder->select('1')
                ->where('device_id', $deviceChecksum)
                ->get()
                ->getRow();

            if ($existing) {
                // Update existing
                $builder->where('device_id', $deviceChecksum)
                    ->update($print_dump);
                $action = 'updated';
            } else {
                // Insert new — enforce plan device limit before creating a new device
                $ownerId = $print_dump['owner_id'] ?? 0;
                if ($ownerId) {
                    $currentCount = $this->countDevicesForOwner($ownerId);
                    $gate = new \App\Services\PlanGate();
                    if (!$gate->canAddDevice($ownerId, $currentCount)) {
                        log_message('info', "PlanGate: user #{$ownerId} device limit reached ({$currentCount}), rejecting new device {$deviceChecksum}");
                        return json_encode([
                            'success' => false,
                            'message' => 'Device limit reached for your current plan. Upgrade to add more devices.',
                            'device_limit_reached' => true,
                        ]);
                    }
                }

                $builder->insert($print_dump);
                $action = 'created';
            }

            // Send device paired email for new devices
            $ownerId = $print_dump['owner_id'] ?? 0;
            if ($ownerId && $action === 'created') {
                $this->sendDevicePairedEmail($print_dump, $ownerId, $action);
            }

            return json_encode([
                'success' => true,
                'dev_chck_sum' => $deviceChecksum,
                'dev_adr_id' => $print_dump['android_id'] ?? null,
                'fcm_token_saved' => isset($print_dump['fcm_token']),
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
     * Count devices currently linked to an owner.
     * Reuses the checksum + owner_id resolution from Mod_User.
     */
    private function countDevicesForOwner(int $ownerId): int
    {
        try {
            $userModel = new \App\Models\Mod_User();
            return count($userModel->get_user_devices_from_profile($ownerId));
        } catch (\Throwable $e) {
            log_message('error', 'PlanGate countDevices error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Send device paired email notification
     */
    private function sendDevicePairedEmail(array $print_dump, int $owner_id, string $action): void
    {
        if ($action !== 'created') return; // Only notify on new device pairing

        $fcmToken = $print_dump['fcm_token'] ?? '';
        if (empty($fcmToken)) return;

        try {
            $db = \Config\Database::connect();
            $user = $db->table('auth_identities')
                ->select('auth_identities.secret AS email, users.username')
                ->join('users', 'auth_identities.user_id = users.id')
                ->where('auth_identities.type', 'email_password')
                ->where('users.id', $owner_id)
                ->get()
                ->getRowArray();
            if (!$user || empty($user['email'])) return;

            // Check if email notifications enabled
            $profile = $db->table('user_profiles')->select('email_notifications')->where('user_id', $owner_id)->get()->getRowArray();
            if ($profile && isset($profile['email_notifications']) && !$profile['email_notifications']) return;

            // Check email_triggers setting
            $settings = $db->table('settings')->where('class', 'email_triggers')->get()->getResultArray();
            $triggerEnabled = true;
            foreach ($settings as $s) {
                if ($s['key'] === 'on_device_paired' && $s['value'] === '0') {
                    $triggerEnabled = false;
                    break;
                }
            }
            if (!$triggerEnabled) return;

            helper('email');
            send_templated_email(
                $user['email'],
                'Eaves Droid — New Device Paired',
                'email/user/device_paired',
                [
                    'username' => $user['username'],
                    'deviceModel' => $print_dump['device_model'] ?? 'Unknown',
                    'deviceName' => $print_dump['device_name'] ?? ($print_dump['device_device'] ?? 'Android Device'),
                    'pairedAt' => date('Y-m-d H:i:s'),
                    'securityAction' => 'Device Paired',
                    'securityDescription' => 'A new Android device has been linked to your account.',
                    'securityStatus' => 'success',
                    'securityInitiatedBy' => $user['username'],
                    'securityBrowser' => 'Android App (FCM Token Registration)',
                    'securityBrowserIp' => 'N/A',
                    'securityExecutedAt' => date('Y-m-d H:i:s'),
                ]
            );
        } catch (\Throwable $e) {
            log_message('error', 'Device paired email failed: ' . $e->getMessage());
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