<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\GeoIntelligenceService;
use App\Services\GeoTransitionDetector;

class GeoProcess extends BaseCommand
{
    protected $group = 'geo';
    protected $name = 'geo:process';
    protected $description = 'Process location intelligence: cluster places, detect transitions, check anomalies';
    
    protected $usage = 'geo:process [--user-id=ID] [--device-id=ID] [--force] [--days=N] [--hours=N]';
    protected $arguments = [];
    protected $options = [
        'user-id'    => 'Process specific user only',
        'device-id'  => 'Process specific device only',
        'force'      => 'Force re-clustering all data',
        'days'       => 'Lookback window for clustering (default: 90)',
        'hours'      => 'Lookback hours for transitions (default: 24)',
    ];

    public function run(array $params)
    {
        $userId = $params['user-id'] ?? null;
        $deviceId = $params['device-id'] ?? null;
        $force = (bool)($params['force'] ?? false);
        $days = (int)($params['days'] ?? 90);
        $hours = (int)($params['hours'] ?? 24);
        
        CLI::write("=== geo:process started ===", 'green');
        CLI::write("User: " . ($userId ?? 'ALL') . ", Device: " . ($deviceId ?? 'ALL'));
        CLI::write("Force: " . ($force ? 'yes' : 'no') . ", Days: $days, Hours: $hours");
        
        $geoIntel = new GeoIntelligenceService();
        $transition = new GeoTransitionDetector();
        
        // 1. Cluster places
        CLI::write("\n[1/4] Clustering places...", 'yellow');
        $places = $geoIntel->clusterPlaces($userId, $deviceId, $days, $force);
        CLI::write("Places created/updated: $places", 'green');
        
        // 2. Sync zones from places
        CLI::write("\n[2/4] Syncing geofence zones...", 'yellow');
        $zones = $geoIntel->syncZones($userId, $deviceId);
        CLI::write("Zones synced: $zones", 'green');
        
        // 3. Detect transitions
        CLI::write("\n[3/4] Detecting transitions...", 'yellow');
        $transitions = 0;
        if ($userId && $deviceId) {
            $transitions = $transition->processTransitions($userId, $deviceId, $hours);
        } else {
            // Process all devices with zones
            $devices = $this->getDevicesWithZones($userId);
            foreach ($devices as $d) {
                $transitions += $transition->processTransitions($d['user_id'], $d['device_id'], $hours);
            }
        }
        CLI::write("Transition events created: $transitions", 'green');
        
        // 4. Check anomalies
        CLI::write("\n[4/4] Checking anomalies...", 'yellow');
        $anomalies = $this->checkAnomalies($userId, $deviceId, $days);
        CLI::write("Anomalies found: $anomalies", 'green');
        
        CLI::write("\n=== geo:process completed ===", 'green');
    }
    
    private function getDevicesWithZones(?int $userId): array
    {
        $builder = \Config\Services::database()->table('geo_zones')
            ->select('user_id, device_id')
            ->where('enabled', 1)
            ->groupBy('user_id, device_id');
        
        if ($userId) $builder->where('user_id', $userId);
        
        return $builder->get()->getResultArray();
    }
    
    private function checkAnomalies(?int $userId, ?string $deviceId, int $days): int
    {
        // Placeholder for anomaly detection
        // Could check: odd-hour enters, new areas far from zones, excessive travel, etc.
        return 0;
    }
}