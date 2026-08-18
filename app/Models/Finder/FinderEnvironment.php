<?php

namespace App\Models\Finder;

use CodeIgniter\Model;

class FinderEnvironment extends Model
{
    protected $parent;
    protected $db;

    public function __construct($parent)
    {
        parent::__construct();
        $this->parent = $parent;
        $this->db = $parent->db;
    }

    public function __get($name)
    {
        if ($name === 'deviceId') {
            return $this->parent->deviceId;
        }
        if ($name === 'pager') {
            return $this->parent->pager;
        }
        return null;
    }

    public function __set($name, $value)
    {
        if ($name === 'pager') {
            $this->parent->pager = $value;
        }
        if ($name === 'total_timeline') {
            $this->parent->total_timeline = $value;
        }
    }

    // Helper wrappers
    protected function applyOwnerDeviceFilter($builder, int $user_id)
    {
        return $this->parent->applyOwnerDeviceFilter($builder, $user_id);
    }

    protected function fq(string $table, int $userId)
    {
        return $this->parent->fq($table, $userId);
    }

    protected function cq(string $table, int $userId): int
    {
        return $this->parent->cq($table, $userId);
    }

    protected function getCount(string $table, int $user_id, array $extraWhere = [], ?string $blockColumn = null, array $blockedValues = []): int
    {
        return $this->parent->getCount($table, $user_id, $extraWhere, $blockColumn, $blockedValues);
    }

    protected function getBlockedIdentifiers(int $userId, string $category): array
    {
        return $this->parent->getBlockedIdentifiers($userId, $category);
    }

    public function deleteLocationsByUser(int $user_id): bool
    {
        try {
            $builder = $this->db->table('tbl_extracted_locations');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteLocationsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteWifiTrafficByUser(int $user_id): bool
    {
        return $this->fq('tbl_extracted_wifi_network_traffic', $user_id)->delete();
    }

    public function deleteBluetoothDevicesByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_bluetooth_devices', $user_id)->delete();
    }

    public function deleteWifiNeighboursByUser(int $user_id): bool
    {
        return $this->fq('tbl_extracted_wifi_neighbours', $user_id)->delete();
    }

    public function deleteNfcTelemetryByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_nfc', $user_id)->delete();
    }

    public function deleteNfcByUser(int $user_id): bool
    {
        return $this->deleteNfcTelemetryByUser($user_id);
    }

    public function deleteSensorsByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_sensors', $user_id)->delete();
    }

    public function deleteGnssHardwareByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_gnss_hardware', $user_id)->delete();
    }

    public function deleteBluetoothStateByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_bluetooth_state', $user_id)->delete();
    }

    public function deleteCellInfoByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_cell_info', $user_id)->delete();
    }

    public function deleteCellTowersByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_cell_towers', $user_id)->delete();
    }

    public function deleteStorageByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_storage_stats', $user_id)->delete();
    }

    public function deleteThermalByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_thermal', $user_id)->delete();
    }

    public function deleteDataUsageByUser(int $user_id): bool
    {
        return $this->fq('tbl_data_usage', $user_id)->delete();
    }

    public function deleteSavedWifiByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_wifi_networks', $user_id)->delete();
    }

    public function deleteSensorsEnvironmentalByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_sensors_environmental', $user_id)->delete();
    }

    public function get_count_Location(int $user_id, bool $hasCoordsOnly = false): int
    {
        $blocked = $this->getBlockedIdentifiers($user_id, 'location');
        if ($hasCoordsOnly) {
            try {
                $builder = $this->fq('tbl_extracted_locations', $user_id);
                if (!empty($blocked)) {
                    $builder->whereNotIn('provider', $blocked);
                }
                return $builder->where('latitude IS NOT NULL')
                    ->where('longitude IS NOT NULL')
                    ->where('latitude !=', '')
                    ->where('longitude !=', '')
                    ->countAllResults();
            } catch (\Exception $e) {
                log_message('error', 'get_count_Location (hasCoords) error: ' . $e->getMessage());
                return 0;
            }
        }
        return $this->getCount('tbl_extracted_locations', $user_id, [], 'provider', $blocked);
    }

    public function get_count_LocationActivity(int $user_id): int
    {
        try {
            $row = $this->db->query("
                SELECT COUNT(DISTINCT l.fetched_at) AS c 
                FROM tbl_extracted_locations l
                WHERE l.owner_id = ? 
                  AND l.fetched_at IS NOT NULL
            ", [$user_id])->getRow();
            return $row ? (int)$row->c : 0;
        } catch (\Exception $e) {
            log_message('error', 'get_count_LocationActivity error: ' . $e->getMessage());
            return 0;
        }
    }

    public function get_count_Locations(int $user_id): int
    {
        return $this->get_count_Location($user_id);
    }

    public function get_count_CellTowers(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_cell_towers', $user_id);
    }

    public function get_count_Storage(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_storage_stats', $user_id);
    }

    public function get_count_Thermal(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_thermal', $user_id);
    }

    public function get_count_DataUsage(int $user_id): int
    {
        return $this->getCount('tbl_data_usage', $user_id);
    }

    public function get_count_SavedWifi(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_wifi_networks', $user_id);
    }

    public function get_count_WifiTraffic(int $user_id): int
    {
        return $this->getCount('tbl_extracted_wifi_network_traffic', $user_id);
    }

    public function get_count_BluetoothDevices(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_bluetooth_devices', $user_id);
    }

    public function get_count_WifiNeighbours(int $user_id): int
    {
        return $this->getCount('tbl_extracted_wifi_neighbours', $user_id);
    }

    public function get_count_NfcTelemetry(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_nfc', $user_id);
    }

    public function get_count_Nfc(int $user_id): int
    {
        return $this->get_count_NfcTelemetry($user_id);
    }

    public function get_count_Sensors(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_sensors', $user_id);
    }

    public function export_locations(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_extracted_locations', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_wifi_traffic(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_extracted_wifi_network_traffic', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_bluetooth_devices(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_bluetooth_devices', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_wifi_neighbours(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_extracted_wifi_neighbours', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_nfc_telemetry(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_nfc', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_sensors(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_sensors', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_bluetooth_state(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_bluetooth_state', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_cell_info(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_cell_info', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_sensors_environmental(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_sensors_environmental', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_gnss_hardware(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_gnss_hardware', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function get_locations(int $user_id, int $perPage = 25, bool $withCoords = false): array
    {
        try {
            $builder = $this->db->table('tbl_extracted_locations');

            // Scope to this user (and active device) — never show another user's locations.
            $this->applyOwnerDeviceFilter($builder, $user_id);

            // Optionally restrict to rows that have valid GPS coordinates
            if ($withCoords) {
                $builder->where('latitude IS NOT NULL')
                        ->where('longitude IS NOT NULL')
                        ->where('latitude !=', 0)
                        ->where('longitude !=', 0);
            }

            // Get total count for pagination (after filters)
            $total = $builder->countAllResults(false);

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            // Select original column names so views can use $loc['latitude'] etc.
            $query = $builder->select(
                'counter, latitude, longitude, altitude, accuracy,
                 speed, bearing, provider, location_time, fetched_at'
            );

            $blocked = $this->getBlockedIdentifiers($user_id, 'location');
            if (!empty($blocked)) {
                $query->whereNotIn('provider', $blocked);
            }

            $results = $query->orderBy('location_time', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_locations error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_cell_towers(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_CellTowers($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $timestamps = $this->fq('tbl_telemetry_cell_towers', $user_id)
                ->select('extracted_at, network_operator, network_operator_name, phone_type, sim_state')
                ->groupBy('extracted_at')
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();

            $results = [];
            foreach ($timestamps as $ts) {
                $extractedAt = $ts['extracted_at'];
                $towers = $this->fq('tbl_telemetry_cell_towers', $user_id)
                    ->where('extracted_at', $extractedAt)
                    ->get()->getResultArray();

                $towerList = [];
                foreach ($towers as $tower) {
                    $towerList[] = [
                        'type' => $tower['tower_type'] ?? '',
                        'cid' => $tower['cid'] ?? '',
                        'lac' => $tower['lac'] ?? '',
                        'mcc' => $tower['mcc'] ?? '',
                        'mnc' => $tower['mnc'] ?? '',
                        'pci' => $tower['pci'] ?? '',
                        'nci' => $tower['nci'] ?? '',
                        'tac' => $tower['tac'] ?? '',
                        'nrarfcn' => $tower['nrarfcn'] ?? '',
                        'bandwidth' => $tower['bandwidth'] ?? '',
                        'psc' => $tower['psc'] ?? '',
                        'system_id' => $tower['system_id'] ?? '',
                        'rssi' => $tower['rssi'] ?? '',
                        'rsrp' => $tower['rsrp'] ?? '',
                        'rsrq' => $tower['rsrq'] ?? '',
                        'rssnr' => $tower['rssnr'] ?? '',
                        'cqi' => $tower['cqi'] ?? '',
                        'asu_level' => $tower['asu_level'] ?? '',
                        'csi_rsrp' => $tower['csi_rsrp'] ?? '',
                        'csi_rsrq' => $tower['csi_rsrq'] ?? '',
                        'csi_sinr' => $tower['csi_sinr'] ?? '',
                        'is_registered' => $tower['is_registered'] ?? 0,
                    ];
                }

                $results[] = [
                    'id' => $towers[0]['id'] ?? 0,
                    'owner_id' => $user_id,
                    'device_id' => $towers[0]['device_id'] ?? '',
                    'towers_json' => json_encode($towerList),
                    'network_operator' => $ts['network_operator'] ?? '',
                    'network_operator_name' => $ts['network_operator_name'] ?? '',
                    'phone_type' => $ts['phone_type'] ?? 0,
                    'sim_state' => $ts['sim_state'] ?? 0,
                    'extracted_at' => $extractedAt,
                    'created_at' => $towers[0]['created_at'] ?? '',
                    'updated_at' => $towers[0]['updated_at'] ?? '',
                ];
            }

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_cell_towers: ' . $e->getMessage());
            return [];
        }
    }

    public function get_storage(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Storage($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $timestamps = $this->fq('tbl_telemetry_storage_stats', $user_id)
                ->select('extracted_at')
                ->groupBy('extracted_at')
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();

            $results = [];
            foreach ($timestamps as $ts) {
                $extractedAt = $ts['extracted_at'];
                $volumes = $this->fq('tbl_telemetry_storage_stats', $user_id)
                    ->where('extracted_at', $extractedAt)
                    ->get()->getResultArray();

                $volumeList = [];
                $appCache = null;
                $appData = null;

                foreach ($volumes as $vol) {
                    $path = $vol['volume_path'] ?? '';
                    if ($path === 'app_cache') {
                        $appCache = [
                            'total_bytes' => $vol['total_bytes'] ?? 0,
                            'available_bytes' => $vol['available_bytes'] ?? 0,
                            'free_bytes' => $vol['free_bytes'] ?? 0,
                            'used_bytes' => $vol['used_bytes'] ?? 0,
                            'total_formatted' => $vol['total_formatted'] ?? '',
                            'available_formatted' => $vol['available_formatted'] ?? '',
                            'used_formatted' => $vol['used_formatted'] ?? '',
                        ];
                    } elseif ($path === 'app_data') {
                        $appData = [
                            'total_bytes' => $vol['total_bytes'] ?? 0,
                            'available_bytes' => $vol['available_bytes'] ?? 0,
                            'free_bytes' => $vol['free_bytes'] ?? 0,
                            'used_bytes' => $vol['used_bytes'] ?? 0,
                            'total_formatted' => $vol['total_formatted'] ?? '',
                            'available_formatted' => $vol['available_formatted'] ?? '',
                            'used_formatted' => $vol['used_formatted'] ?? '',
                        ];
                    } else {
                        $volumeList[] = [
                            'path' => $path,
                            'description' => $vol['description'] ?? '',
                            'is_removable' => $vol['is_removable'] ?? 0,
                            'state' => $vol['state'] ?? '',
                            'info' => [
                                'total_bytes' => $vol['total_bytes'] ?? 0,
                                'available_bytes' => $vol['available_bytes'] ?? 0,
                                'free_bytes' => $vol['free_bytes'] ?? 0,
                                'used_bytes' => $vol['used_bytes'] ?? 0,
                                'total_formatted' => $vol['total_formatted'] ?? '',
                                'available_formatted' => $vol['available_formatted'] ?? '',
                                'used_formatted' => $vol['used_formatted'] ?? '',
                            ],
                        ];
                    }
                }

                $results[] = [
                    'id' => $volumes[0]['id'] ?? 0,
                    'owner_id' => $user_id,
                    'device_id' => $volumes[0]['device_id'] ?? '',
                    'volumes_json' => json_encode($volumeList),
                    'app_cache_json' => $appCache ? json_encode($appCache) : null,
                    'app_data_json' => $appData ? json_encode($appData) : null,
                    'extracted_at' => $extractedAt,
                    'created_at' => $volumes[0]['created_at'] ?? '',
                    'updated_at' => $volumes[0]['updated_at'] ?? '',
                ];
            }

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_storage: ' . $e->getMessage());
            return [];
        }
    }

    public function get_thermal(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Thermal($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $timestamps = $this->fq('tbl_telemetry_thermal', $user_id)
                ->select('extracted_at')
                ->groupBy('extracted_at')
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();

            $results = [];
            foreach ($timestamps as $ts) {
                $extractedAt = $ts['extracted_at'];
                $rows = $this->fq('tbl_telemetry_thermal', $user_id)
                    ->where('extracted_at', $extractedAt)
                    ->get()->getResultArray();

                $thermalZones = [];
                $cpuThrottle = [];
                $cpuFreqs = [];

                foreach ($rows as $row) {
                    $dt = $row['data_type'] ?? '';
                    if ($dt === 'thermal_zone') {
                        $thermalZones[] = [
                            'zone' => $row['zone_name'] ?? '',
                            'type' => $row['zone_type'] ?? '',
                            'temp_raw' => $row['temp_raw'] ?? '',
                            'temp_celsius' => $row['temp_celsius'] ?? '',
                            'policy' => $row['policy'] ?? '',
                        ];
                    } elseif ($dt === 'cpu_throttle') {
                        $cpuThrottle[] = [
                            'cpu' => $row['cpu_name'] ?? '',
                            'core_limit_max' => $row['core_limit_max'] ?? '',
                            'package_limit_max' => $row['package_limit_max'] ?? '',
                            'throttle_count' => $row['throttle_count'] ?? '',
                        ];
                    } elseif ($dt === 'cpu_frequency') {
                        $cpuFreqs[] = [
                            'cpu' => $row['cpu_name'] ?? '',
                            'scaling_min_freq' => $row['scaling_min_freq'] ?? '',
                            'scaling_max_freq' => $row['scaling_max_freq'] ?? '',
                            'scaling_cur_freq' => $row['scaling_cur_freq'] ?? '',
                            'scaling_governor' => $row['scaling_governor'] ?? '',
                        ];
                    }
                }

                $results[] = [
                    'id' => $rows[0]['id'] ?? 0,
                    'owner_id' => $user_id,
                    'device_id' => $rows[0]['device_id'] ?? '',
                    'thermal_zones_json' => json_encode($thermalZones),
                    'cpu_throttle_json' => json_encode($cpuThrottle),
                    'cpu_frequencies_json' => json_encode($cpuFreqs),
                    'extracted_at' => $extractedAt,
                    'created_at' => $rows[0]['created_at'] ?? '',
                    'updated_at' => $rows[0]['updated_at'] ?? '',
                ];
            }

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_thermal: ' . $e->getMessage());
            return [];
        }
    }

    public function get_data_usage(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_DataUsage($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $timestamps = $this->fq('tbl_data_usage', $user_id)
                ->select('extracted_at')
                ->groupBy('extracted_at')
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();

            $results = [];
            foreach ($timestamps as $ts) {
                $extractedAt = $ts['extracted_at'];
                $records = $this->fq('tbl_data_usage', $user_id)
                    ->where('extracted_at', $extractedAt)
                    ->get()->getResultArray();

                $usageRecords = [];
                $totals = null;

                foreach ($records as $rec) {
                    if ($rec['network_type'] === 'total') {
                        $totals = [
                            'total_rx' => $rec['rx_bytes'] ?? 0,
                            'total_tx' => $rec['tx_bytes'] ?? 0,
                            'total_rx_formatted' => $rec['rx_formatted'] ?? '',
                            'total_tx_formatted' => $rec['tx_formatted'] ?? '',
                        ];
                    } else {
                        $usageRecords[] = [
                            'network_type' => $rec['network_type'] ?? '',
                            'sub_id' => $rec['sub_id'] ?? 0,
                            'is_wifi' => $rec['is_wifi'] ?? 0,
                            'rx_bytes' => $rec['rx_bytes'] ?? 0,
                            'tx_bytes' => $rec['tx_bytes'] ?? 0,
                            'total_bytes' => $rec['total_bytes'] ?? 0,
                            'rx_formatted' => $rec['rx_formatted'] ?? '',
                            'tx_formatted' => $rec['tx_formatted'] ?? '',
                            'bucket_start' => $rec['bucket_start'] ?? 0,
                            'bucket_end' => $rec['bucket_end'] ?? 0,
                        ];
                    }
                }

                $results[] = [
                    'id' => $records[0]['id'] ?? 0,
                    'owner_id' => $user_id,
                    'device_id' => $records[0]['device_id'] ?? '',
                    'usage_records_json' => json_encode($usageRecords),
                    'totals_json' => $totals ? json_encode($totals) : null,
                    'extracted_at' => $extractedAt,
                    'created_at' => $records[0]['created_at'] ?? '',
                    'updated_at' => $records[0]['updated_at'] ?? '',
                ];
            }

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_data_usage: ' . $e->getMessage());
            return [];
        }
    }

    public function get_saved_wifi(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_SavedWifi($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $timestamps = $this->fq('tbl_telemetry_wifi_networks', $user_id)
                ->select('extracted_at')
                ->groupBy('extracted_at')
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();

            $results = [];
            foreach ($timestamps as $ts) {
                $extractedAt = $ts['extracted_at'];
                $networks = $this->fq('tbl_telemetry_wifi_networks', $user_id)
                    ->where('extracted_at', $extractedAt)
                    ->get()->getResultArray();

                $networkList = [];
                foreach ($networks as $net) {
                    $networkList[] = [
                        'ssid' => $net['ssid'] ?? '',
                        'bssid' => $net['bssid'] ?? '',
                        'network_id' => $net['network_id'] ?? 0,
                        'priority' => $net['priority'] ?? 0,
                        'status' => $net['status'] ?? 0,
                        'is_hidden' => $net['is_hidden'] ?? 0,
                        'security' => $net['security'] ?? '',
                        'protocols' => json_decode($net['protocols_json'] ?? '[]', true) ?? [],
                        'auth_algorithms' => json_decode($net['auth_algorithms_json'] ?? '[]', true) ?? [],
                    ];
                }

                $results[] = [
                    'id' => $networks[0]['id'] ?? 0,
                    'owner_id' => $user_id,
                    'device_id' => $networks[0]['device_id'] ?? '',
                    'networks_json' => json_encode($networkList),
                    'network_count' => count($networkList),
                    'extracted_at' => $extractedAt,
                    'created_at' => $networks[0]['created_at'] ?? '',
                    'updated_at' => $networks[0]['updated_at'] ?? '',
                ];
            }

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_saved_wifi: ' . $e->getMessage());
            return [];
        }
    }

    public function get_wifi_traffic(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_WifiTraffic($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_extracted_wifi_network_traffic', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_wifi_traffic: ' . $e->getMessage());
            return [];
        }
    }

    public function get_bluetooth_devices(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_BluetoothDevices($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_bluetooth_devices', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();

            foreach ($results as &$row) {
                $row['paired_devices'] = $this->db->table('tbl_telemetry_bluetooth_devices_paired')
                    ->where('bluetooth_id', $row['id'])
                    ->get()
                    ->getResultArray();
            }
            unset($row);

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_bluetooth_devices: ' . $e->getMessage());
            return [];
        }
    }

    public function get_wifi_neighbours(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_WifiNeighbours($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_extracted_wifi_neighbours', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_wifi_neighbours: ' . $e->getMessage());
            return [];
        }
    }

    public function get_nfc_telemetry(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_NfcTelemetry($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_nfc', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_nfc_telemetry: ' . $e->getMessage());
            return [];
        }
    }

    public function get_sensors(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Sensors($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_sensors', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_sensors: ' . $e->getMessage());
            return [];
        }
    }

    public function get_count_BluetoothState(int $user_id): int
    {
        return $this->getCount('tbl_system_bluetooth_state', $user_id);
    }

    public function get_bluetooth_state(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_BluetoothState($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_bluetooth_state', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            foreach ($results as &$r) {
                $r['profiles_connected'] = is_string($r['profiles_connected'] ?? null) ? json_decode($r['profiles_connected'], true) : ($r['profiles_connected'] ?? []);
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_bluetooth_state: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_bluetooth_state_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_bluetooth_state')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_bluetooth_state_row: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_location(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_locations')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_location error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_wifi_traffic_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_wifi_network_traffic')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_wifi_traffic_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_bluetooth_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_bluetooth_devices')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_bluetooth_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_wifi_neighbour_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_wifi_neighbours')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_wifi_neighbour_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_nfc_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_nfc')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_nfc_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_sensor_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_sensors')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_sensor_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_CellInfo(int $user_id): int
    {
        return $this->getCount('tbl_system_cell_info', $user_id);
    }

    public function get_cell_info(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_CellInfo($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_cell_info', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['cell_identity', 'cell_signal_strength'] as $key) {
                    $r[$key] = is_string($r[$key] ?? null) ? json_decode($r[$key], true) : ($r[$key] ?? []);
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_cell_info: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_cell_info_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_cell_info')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_cell_info_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_SensorsEnvironmental(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_sensors_environmental', $user_id);
    }

    public function get_sensors_environmental(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_SensorsEnvironmental($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_sensors_environmental', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_sensors_environmental: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_sensors_environmental_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_sensors_environmental')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_sensors_environmental_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_paired_location_activities(int $user_id, int $perPage = 25): array
    {
        try {
            $builder = $this->db->table('tbl_extracted_locations l');
            $builder->join('tbl_extracted_activities a', 'a.fetched_at = l.fetched_at AND a.owner_id = l.owner_id');
            
            $builder->where('l.owner_id', $user_id);
            $builder->where('l.fetched_at IS NOT NULL');
            $builder->where('l.fetched_at !=', '');
            $builder->where('l.latitude IS NOT NULL');
            $builder->where('l.longitude IS NOT NULL');
            $builder->where('l.latitude !=', 0);
            $builder->where('l.longitude !=', 0);

            // Apply owner/device filter
            if (!empty($this->parent->deviceId) && $this->parent->deviceId !== 'all') {
                $builder->where('l.device_id', $this->parent->deviceId);
            }

            // Get total count for pagination
            $total = $builder->countAllResults(false);

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $builder->select('
                l.counter as loc_counter,
                l.latitude,
                l.longitude,
                l.altitude,
                l.accuracy,
                l.speed,
                l.bearing,
                l.provider,
                l.location_time,
                l.fetched_at,
                l.satellite_count,
                l.hdop,
                l.vdop,
                l.pdop,
                l.gnss_status,
                l.speed_kmh,
                l.vertical_accuracy,
                l.floor_level,
                l.building_id,
                l.indoor_level,
                a.counter as act_counter,
                a.activity_type,
                a.confidence,
                a.battery_level,
                a.charging_status,
                a.network_type,
                a.screen_on,
                a.activity_time,
                a.extracted_at as act_extracted_at
            ');

            $results = $builder->orderBy('l.location_time', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_paired_location_activities error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_count_paired(int $user_id): int
    {
        try {
            $builder = $this->db->table('tbl_extracted_locations l');
            $builder->join('tbl_extracted_activities a', 'a.fetched_at = l.fetched_at AND a.owner_id = l.owner_id');
            
            $builder->where('l.owner_id', $user_id);
            $builder->where('l.fetched_at IS NOT NULL');
            $builder->where('l.fetched_at !=', '');
            $builder->where('l.latitude IS NOT NULL');
            $builder->where('l.longitude IS NOT NULL');
            $builder->where('l.latitude !=', 0);
            $builder->where('l.longitude !=', 0);

            if (!empty($this->parent->deviceId) && $this->parent->deviceId !== 'all') {
                $builder->where('l.device_id', $this->parent->deviceId);
            }

            return $builder->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'get_count_paired error: ' . $e->getMessage());
            return 0;
        }
    }
}
