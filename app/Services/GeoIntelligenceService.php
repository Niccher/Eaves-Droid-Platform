<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Config\Services;

/**
 * GeoIntelligenceService — DBSCAN clustering of location points into significant places.
 */
class GeoIntelligenceService
{
    private BaseConnection $db;
    
    // Configurable via settings table
    private int $epsilonMeters = 150;
    private int $minPoints = 3;
    private int $timeWindowDays = 90;
    private int $zoneMinDwellMinutes = 5;
    
    // Working state during clustering
    private array $points = [];
    
    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Services::database();
        $this->loadSettings();
    }
    
    private function loadSettings(): void
    {
        $this->epsilonMeters = (int)setting('geo.epsilon_m', 150);
        $this->minPoints = (int)setting('geo.min_points', 3);
        $this->timeWindowDays = (int)setting('geo.time_window_days', 90);
        $this->zoneMinDwellMinutes = (int)setting('geo.zone_min_dwell_minutes', 5);
    }
    
    /**
     * Main entry point: cluster location points into places.
     */
    public function clusterPlaces(
        ?int $userId = null,
        ?string $deviceId = null,
        int $days = 90,
        bool $force = false
    ): int {
        $this->loadSettings();

        // Plan gate: geofencing must be enabled for this user
        if ($userId && !$this->featureEnabled($userId, 'geofencing')) {
            log_message('info', "PlanGate: user #{$userId} geofencing disabled — skipping clusterPlaces");
            return 0;
        }

        $this->points = $this->fetchLocationPoints($userId, $deviceId, $days);
        
        if (count($this->points) < $this->minPoints) {
            log_message('info', "GeoIntel: Insufficient points (" . count($this->points) . ") for clustering, need {$this->minPoints}");
            return 0;
        }
        
        log_message('info', "GeoIntel: Clustering " . count($this->points) . " points (eps={$this->epsilonMeters}m, minPts={$this->minPoints})");
        
        $clusters = $this->dbscan();
        
        if (empty($clusters)) {
            log_message('info', 'GeoIntel: No clusters found');
            return 0;
        }
        
        $placesCreated = 0;
        foreach ($clusters as $cluster) {
            if ($this->savePlace($cluster)) {
                $placesCreated++;
            }
        }
        
        // Auto-create/update zones from places
        $this->syncZones($userId, $deviceId);
        
        log_message('info', "GeoIntel: Created/updated {$placesCreated} places");
        return $placesCreated;
    }
    
    /**
     * Fetch valid location points from tbl_location.
     */
    private function fetchLocationPoints(?int $userId, ?string $deviceId, int $days): array
    {
        $builder = $this->db->table('tbl_location')
            ->select('counter, owner_id, device_id, latitude, longitude, location_time, 
                      extracted_at, is_home, is_work, is_saved_place')
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->where('latitude !=', 0)
            ->where('longitude !=', 0)
            ->where('status', 'success')
            ->where('location_time >=', strtotime("-{$days} days") * 1000);
        
        if ($userId) $builder->where('owner_id', $userId);
        if ($deviceId) $builder->where('device_id', $deviceId);
        
        if (!$force) {
            // Exclude points already associated with a place
            $builder->where('counter NOT IN (
                SELECT DISTINCT counter FROM geo_places 
                WHERE user_id = tbl_location.owner_id 
                AND device_id = tbl_location.device_id
            )', null, false);
        }
        
        $results = $builder->orderBy('location_time', 'ASC')->get()->getResultArray();
        
        $points = [];
        foreach ($results as $row) {
            $time = (int)($row['location_time'] ?? $row['extracted_at'] ?? 0) / 1000;
            if ($time <= 0) continue;
            
            $dt = new \DateTime('@' . $time);
            $points[] = [
                'counter' => $row['counter'],
                'user_id' => $row['owner_id'],
                'device_id' => $row['device_id'],
                'lat' => (float)$row['latitude'],
                'lng' => (float)$row['longitude'],
                'time' => $time,
                'hour' => (int)$dt->format('H'),
                'day_of_week' => (int)$dt->format('w'),
                'is_weekend' => in_array($dt->format('w'), [0, 6]),
                'is_home_hint' => (bool)$row['is_home'],
                'is_work_hint' => (bool)$row['is_work'],
            ];
        }
        
        return $points;
    }
    
    /**
     * DBSCAN clustering implementation.
     */
    private function dbscan(): array
    {
        $n = count($this->points);
        $visited = array_fill(0, $n, false);
        $clustered = array_fill(0, $n, false);
        $clusterId = -1;
        $clusters = [];
        $distCache = [];
        
        for ($i = 0; $i < $n; $i++) {
            if ($visited[$i]) continue;
            $visited[$i] = true;
            
            $neighbors = $this->regionQuery($i, $distCache);
            
            if (count($neighbors) < $this->minPoints) {
                continue; // Noise point
            }
            
            $clusterId++;
            $clusterPoints = [];
            $this->expandCluster($i, $neighbors, $clusterId, $visited, $clustered, $clusterPoints, $distCache);
            
            if (count($clusterPoints) >= $this->minPoints) {
                $clusters[] = [
                    'id' => $clusterId,
                    'points' => $clusterPoints,
                    'size' => count($clusterPoints),
                ];
            }
        }
        
        // Handle noise points with hints
        $this->handleNoisePoints($clustered, $clusters);
        
        return $clusters;
    }
    
    private function regionQuery(int $pointIdx, array &$distCache): array
    {
        $neighbors = [];
        $p1 = $this->points[$pointIdx];
        
        for ($j = 0; $j < count($this->points); $j++) {
            if ($j === $pointIdx) continue;
            
            $key = $pointIdx < $j ? "$pointIdx,$j" : "$j,$pointIdx";
            if (!isset($distCache[$key])) {
                $distCache[$key] = $this->haversine($p1['lat'], $p1['lng'], $this->points[$j]['lat'], $this->points[$j]['lng']);
            }
            
            if ($distCache[$key] <= $this->epsilonMeters) {
                $neighbors[] = $j;
            }
        }
        
        return $neighbors;
    }
    
    private function expandCluster(
        int $pointIdx,
        array $neighbors,
        int $clusterId,
        array &$visited,
        array &$clustered,
        array &$clusterPoints,
        array &$distCache
    ): void {
        $clustered[$pointIdx] = true;
        $clusterPoints[] = $pointIdx;
        
        for ($i = 0; $i < count($neighbors); $i++) {
            $neighborIdx = $neighbors[$i];
            
            if (!$visited[$neighborIdx]) {
                $visited[$neighborIdx] = true;
                $neighborNeighbors = $this->regionQuery($neighborIdx, $distCache);
                
                if (count($neighborNeighbors) >= $this->minPoints) {
                    $neighbors = array_merge($neighbors, $neighborNeighbors);
                }
            }
            
            if (!$clustered[$neighborIdx]) {
                $clustered[$neighborIdx] = true;
                $clusterPoints[] = $neighborIdx;
            }
        }
    }
    
    private function handleNoisePoints(array $clustered, array &$clusters): void
    {
        for ($i = 0; $i < count($this->points); $i++) {
            if ($clustered[$i]) continue;
            
            $p = $this->points[$i];
            // Only create singleton if it has a hint
            if ($p['is_home_hint'] || $p['is_work_hint'] || $p['is_saved_place']) {
                $clusters[] = [
                    'id' => 'noise_' . $p['counter'],
                    'points' => [$i],
                    'size' => 1,
                    'is_noise' => true,
                ];
            }
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
     * Save a cluster as a geo_places row.
     */
    private function savePlace(array $cluster): bool
    {
        $pointIndices = $cluster['points'];
        if (empty($pointIndices)) return false;
        
        // Compute centroid
        $sumLat = 0;
        $sumLng = 0;
        $times = [];
        $homeVotes = 0;
        $workVotes = 0;
        $firstTime = PHP_INT_MAX;
        $lastTime = 0;
        $userId = 0;
        $deviceId = '';
        
        foreach ($pointIndices as $idx) {
            $p = $this->points[$idx];
            $sumLat += $p['lat'];
            $sumLng += $p['lng'];
            $times[] = $p['time'];
            $firstTime = min($firstTime, $p['time']);
            $lastTime = max($lastTime, $p['time']);
            $homeVotes += $p['is_home_hint'] ? 1 : 0;
            $workVotes += $p['is_work_hint'] ? 1 : 0;
            $userId = $p['user_id'];
            $deviceId = $p['device_id'];
        }
        
        $count = count($pointIndices);
        $centroidLat = $sumLat / $count;
        $centroidLng = $sumLng / $count;
        
        // Compute radius (95th percentile distance from centroid)
        $distances = [];
        foreach ($pointIndices as $idx) {
            $p = $this->points[$idx];
            $distances[] = $this->haversine($centroidLat, $centroidLng, $p['lat'], $p['lng']);
        }
        sort($distances);
        $radius = (int)ceil($distances[(int)ceil($count * 0.95) - 1]);
        $radius = max($radius, 50); // minimum 50m
        
        // Determine label
        $label = 'poi';
        if ($homeVotes > $workVotes && $homeVotes >= 2) {
            $label = 'home';
        } elseif ($workVotes > $homeVotes && $workVotes >= 2) {
            $label = 'work';
        }
        
        // Typical arrival/departure (median hour)
        $hours = array_map(fn($t) => (new \DateTime('@' . $t))->format('H'), $times);
        sort($hours);
        $typicalArrival = $hours[(int)floor($count * 0.25)]; // 25th percentile
        $typicalDeparture = $hours[(int)ceil($count * 0.75)]; // 75th percentile
        
        // Confidence: based on visit count and label certainty
        $confidence = min(1.0, ($count / 10) * (($homeVotes + $workVotes) / max(1, $count)));
        
        $data = [
            'user_id' => $userId,
            'device_id' => $deviceId,
            'label' => $label,
            'centroid_lat' => $centroidLat,
            'centroid_lng' => $centroidLng,
            'radius_m' => $radius,
            'visit_count' => $count,
            'first_visit' => date('Y-m-d H:i:s', $firstTime),
            'last_visit' => date('Y-m-d H:i:s', $lastTime),
            'typical_arrival' => $typicalArrival . ':00:00',
            'typical_departure' => $typicalDeparture . ':00:00',
            'confidence' => round($confidence, 2),
        ];
        
        try {
            $this->db->table('geo_places')->insert($data);
            return true;
        } catch (\Exception $e) {
            log_message('error', 'GeoIntel: Failed to save place: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Auto-create/update geo_zones from geo_places.
     */
    public function syncZones(?int $userId = null, ?string $deviceId = null): int
    {
        if ($userId && !$this->featureEnabled($userId, 'geofencing')) {
            log_message('info', "PlanGate: user #{$userId} geofencing disabled — skipping syncZones");
            return 0;
        }

        $builder = $this->db->table('geo_places');
        if ($userId) $builder->where('user_id', $userId);
        if ($deviceId) $builder->where('device_id', $deviceId);
        
        $places = $builder->get()->getResultArray();
        $synced = 0;
        
        foreach ($places as $place) {
            // Check if zone exists for this place
            $existing = $this->db->table('geo_zones')
                ->where('place_id', $place['id'])
                ->get()->getRowArray();
            
            $radius = max($place['radius_m'], 150);
            $zoneData = [
                'user_id' => $place['user_id'],
                'device_id' => $place['device_id'],
                'place_id' => $place['id'],
                'name' => $place['label'] === 'home' ? 'Home' : ($place['label'] === 'work' ? 'Work' : 'Place'),
                'type' => $place['label'],
                'center_lat' => $place['centroid_lat'],
                'center_lng' => $place['centroid_lng'],
                'radius_m' => $radius,
                'enabled' => 1,
                'notify_on_enter' => 1,
                'notify_on_exit' => 1,
                'min_dwell_minutes' => $this->zoneMinDwellMinutes,
            ];
            
            try {
                if ($existing) {
                    $this->db->table('geo_zones')
                        ->where('id', $existing['id'])
                        ->update($zoneData);
                } else {
                    $this->db->table('geo_zones')->insert($zoneData);
                }
                $synced++;
            } catch (\Exception $e) {
                log_message('error', 'GeoIntel: Failed to sync zone: ' . $e->getMessage());
            }
        }
        
        return $synced;
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