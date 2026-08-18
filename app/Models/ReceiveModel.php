<?php

namespace App\Models;

use CodeIgniter\Model;

class ReceiveModel extends Model
{
    /**
     * Creates or updates a device profile — idempotent upsert.
     * The table has UNIQUE KEY uq_device_fcm (device_id, fcm_token_hash) so
     * repeated registration calls with the same device + FCM token are safe.
     *
     * @param array $print_dump
     * @return string|false  JSON string
     */
    public function make_device_print(array $print_dump)
    {
        try {
            $db = \Config\Database::connect();

            // Normalise field name: input uses 'device_checksum', table uses 'device_id'
            $deviceChecksum = $print_dump['device_checksum'] ?? ($print_dump['device_id'] ?? '');
            unset($print_dump['device_checksum']);
            $print_dump['device_id'] = $deviceChecksum;

            // Ensure created_at is set (used during first insert)
            if (empty($print_dump['created_at'])) {
                $print_dump['created_at'] = date('Y-m-d H:i:s');
            }

            // Check if this exact (device_id + fcm_token) already exists
            $fcmToken = $print_dump['fcm_token'] ?? null;
            $existing = $db->table('tbl_device_profiles')
                ->select('counter, owner_id')
                ->where('device_id', $deviceChecksum)
                ->where('fcm_token', $fcmToken)
                ->get()
                ->getRowArray();

            if ($existing) {
                // Exact match — just update metadata, never create a new row
                $updateData = $print_dump;
                unset($updateData['created_at'], $updateData['device_id'], $updateData['fcm_token']);
                $db->table('tbl_device_profiles')
                    ->where('counter', $existing['counter'])
                    ->update($updateData);
                $action = 'updated';
            } else {
                // New (device_id + fcm_token) pair — enforce plan device limit
                $ownerId = $print_dump['owner_id'] ?? 0;
                if ($ownerId) {
                    $currentCount = $this->countDevicesForOwner($ownerId);
                    $gate = new \App\Services\PlanGate();
                    if (!$gate->canAddDevice($ownerId, $currentCount)) {
                        log_message('info', "PlanGate: user #{$ownerId} device limit reached ({$currentCount}), rejecting {$deviceChecksum}");
                        return json_encode([
                            'success'              => false,
                            'message'              => 'Device limit reached for your current plan. Upgrade to add more devices.',
                            'device_limit_reached' => true,
                        ]);
                    }
                }

                $db->table('tbl_device_profiles')->insert($print_dump);
                $action = 'created';

                // Send paired-device email only on genuine first registration
                if ($ownerId) {
                    $this->sendDevicePairedEmail($print_dump, $ownerId, $action);
                }
            }

            return json_encode([
                'success'         => true,
                'dev_chck_sum'    => $deviceChecksum,
                'dev_adr_id'      => $print_dump['android_id'] ?? null,
                'fcm_token_saved' => isset($print_dump['fcm_token']),
                'action'          => $action,
                'is_new'          => ($action === 'created'),
            ]);

        } catch (\Exception $e) {
            log_message('error', 'make_device_print error: ' . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }


    /**
     * Count devices currently linked to an owner.
     * Reuses the checksum + owner_id resolution from UserModel.
     */
    private function countDevicesForOwner(int $ownerId): int
    {
        try {
            $userModel = new \App\Models\UserModel();
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
     * LogsController a token verification attempt.
     * Note: tbl_tokentest was removed in migration 20260815123000. Audit is now log-only.
     *
     * @param string $var_sent_token
     * @param string $var_time
     * @param string $var_ip
     * @param string $var_format
     * @return bool
     */
    public function make_test_token(string $var_sent_token, string $var_time, string $var_ip, string $var_format): bool
    {
        log_message('info', "Token verify attempt | token={$var_sent_token} | ip={$var_ip} | format={$var_format} | sent_time={$var_time}");
        return true;
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
            $result = $this->db->table('tbl_user_api_tokens')
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
                'created_at' => $dated,
                'token' => $tr_token,
                'file_name' => $tr_namenew,
                'file_realname' => $tr_namereal,
                'file_size' => $tr_size,
                'file_extension' => $tr_ext,
                'file_text' => $tr_text,
                'file_viewed' => 0,
                'file_downloaded' => 0,
            ];

            if ($this->db->table('tbl_uploaded')->insert($data)) {
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