<?php

namespace App\Libraries\Parsers;

use App\Models\CryptModel;
use CodeIgniter\Database\BaseConnection;

class SensorsEnvironmentParser
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
                log_message('error', '[ParseAdvancedModel::parse_sensors] Could not find sensor data array in file: ' . $file_name);
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
}
