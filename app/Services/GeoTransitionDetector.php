<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Config\Services;

/**
 * GeoTransitionDetector — Detects zone enter/exit/dwell events from location points.
 * 
 * Runs after new location points are inserted, or can be triggered in batch via geo:process.
 */
class GeoTransitionDetector
{
    private BaseConnection $db;
    private int $minDwellMinutes;
    
    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Services::database();
        $this->minDwellMinutes = (int)setting('geo.zone_min_dwell_minutes', 5);
    }
    
    /**
     * Process transitions for a specific device over a time window.
     * Typically called after new location points are inserted.
     * 
     * @param int $userId
     * @param string $deviceId
     * @param int $lookbackHours How far back to check for new points
     * @return int Number of events created
     */
    public function processTransitions(int $userId, string $deviceId, int $lookbackHours = 24): int
    {
        if (!$this->featureEnabled($userId, 'geofencing')) {
            log_message('info', "PlanGate: user #{$userId} geofencing disabled — skipping processTransitions");
            return 0;
        }

        // Get active zones for this user/device
        $zones = $this->db->table('geo_zones')
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('enabled', 1)
            ->get()->getResultArray();
        
        if (empty($zones)) {
            return 0;
        }
        
        // Get new location points since last check
        $since = time() - ($lookbackHours * 3600);
        $sinceMs = $since * 1000;
        
        $points = $this->db->table('tbl_location')
            ->select('counter, latitude, longitude, location_time, extracted_at')
            ->where('owner_id', $userId)
            ->where('device_id', $deviceId)
            ->where('status', 'success')
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->where('location_time >=', $sinceMs)
            ->orderBy('location_time', 'ASC')
            ->get()->getResultArray();
        
        if (empty($points)) {
            return 0;
        }
        
        $eventsCreated = 0;
        
        foreach ($points as $point) {
            $eventsCreated += $this->processPoint($point, $zones);
        }
        
        return $eventsCreated;
    }
    
    /**
     * Process a single location point against all zones.
     */
    private function processPoint(array $point, array $zones): int
    {
        $lat = (float)$point['latitude'];
        $lng = (float)$point['longitude'];
        $timeMs = (int)($point['location_time'] ?? $point['extracted_at'] ?? 0);
        
        if ($timeMs <= 0) return 0;
        
        $eventsCreated = 0;
        
        foreach ($zones as $zone) {
            $dist = $this->haversine($lat, $lng, (float)$zone['center_lat'], (float)$zone['center_lng']);
            $inside = $dist <= (int)$zone['radius_m'];
            
            // Get last event for this zone/device
            $lastEvent = $this->db->table('geo_events')
                ->where('zone_id', $zone['id'])
                ->where('device_id', $zone['device_id'])
                ->orderBy('event_time', 'DESC')
                ->limit(1)
                ->get()->getRowArray();
            
            $wasInside = $lastEvent && in_array($lastEvent['event_type'], ['enter', 'dwell']);
            
            if ($inside && !$wasInside) {
                // ENTER event
                $this->insertEvent([
                    'user_id' => $zone['user_id'],
                    'device_id' => $zone['device_id'],
                    'zone_id' => $zone['id'],
                    'place_id' => $zone['place_id'],
                    'event_type' => 'enter',
                    'event_time' => $timeMs,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'dwell_ms' => 0,
                    'dwell_minutes' => 0,
                    'metadata' => json_encode(['distance_from_center_m' => round($dist)]),
                ]);
                $eventsCreated++;
                
            } elseif (!$inside && $wasInside) {
                // EXIT event
                $dwellMs = 0;
                if ($lastEvent && isset($lastEvent['dwell_start_ms'])) {
                    $dwellMs = $timeMs - (int)$lastEvent['dwell_start_ms'];
                }
                $dwellMinutes = $dwellMs > 0 ? (int)floor($dwellMs / 60000) : 0;
                
                $this->insertEvent([
                    'user_id' => $zone['user_id'],
                    'device_id' => $zone['device_id'],
                    'zone_id' => $zone['id'],
                    'place_id' => $zone['place_id'],
                    'event_type' => 'exit',
                    'event_time' => $timeMs,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'dwell_ms' => $dwellMs,
                    'dwell_minutes' => $dwellMinutes,
                    'metadata' => json_encode([
                        'distance_from_center_m' => round($dist),
                        'met_min_dwell' => $dwellMinutes >= (int)$zone['min_dwell_minutes'],
                    ]),
                ]);
                $eventsCreated++;
                
                // Check if valid dwell (not just drive-by)
                if ($dwellMinutes >= (int)$zone['min_dwell_minutes']) {
                    // Could trigger "arrival confirmed" notification here
                }
                
            } elseif ($inside && $wasInside) {
                // DWELL continuation - update last event's dwell time
                if ($lastEvent) {
                    $dwellMs = $timeMs - (int)$lastEvent['dwell_start_ms'];
                    $this->db->table('geo_events')
                        ->where('id', $lastEvent['id'])
                        ->update([
                            'dwell_ms' => $dwellMs,
                            'dwell_minutes' => (int)floor($dwellMs / 60000),
                        ]);
                }
            }
        }
        
        return $eventsCreated;
    }
    
    private function insertEvent(array $data): void
    {
        // Add dwell_start_ms for tracking
        if ($data['event_type'] === 'enter') {
            $data['metadata'] = json_decode($data['metadata'], true);
            $data['metadata']['dwell_start_ms'] = $data['event_time'];
            $data['metadata'] = json_encode($data['metadata']);
        }
        
        try {
            $this->db->table('geo_events')->insert($data);
        } catch (\Exception $e) {
            log_message('error', 'GeoTransition: Failed to insert event: ' . $e->getMessage());
        }
    }
    
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);
        return 2 * $r * asin(min(1, sqrt($a)));
    }

    /**
     * Check whether a platform feature is enabled for a user's plan.
     */
    private function featureEnabled(int $userId, string $feature): bool
    {
        try {
            $gate = new \App\Services\PlanGate();
            return $gate->hasFeature($userId, $feature);
        } catch (\Throwable $e) {
            log_message('error', 'PlanGate featureEnabled error: ' . $e->getMessage());
            return true;
        }
    }
}