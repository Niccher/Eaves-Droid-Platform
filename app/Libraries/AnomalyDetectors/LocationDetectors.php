<?php

namespace App\Libraries\AnomalyDetectors;

class LocationDetectors
{
    /**
     * Locations – Geo-Fence Violation Detector
     *
     * Builds a home zone (centroid ± radius) from the most frequent location cluster,
     * then flags any point outside that zone.
     *
     * @param  array $locationRows  [{latitude, longitude, timestamp}, ...]
     * @return array
     */
    public function detectLocationGeofence(array $locationRows): array
    {
        if (count($locationRows) < 5) {
            return [];
        }

        $lats = array_column($locationRows, 'latitude');
        $lngs = array_column($locationRows, 'longitude');
        $centLat = array_sum($lats) / count($lats);
        $centLng = array_sum($lngs) / count($lngs);

        // Build distances from centroid
        $distances = [];
        foreach ($locationRows as $row) {
            $distances[] = $this->haversine(
                (float)$row['latitude'], (float)$row['longitude'],
                $centLat, $centLng
            );
        }

        // HomeController zone radius = mean + 1 stddev (in km)
        $mean   = array_sum($distances) / count($distances);
        $std    = $this->stdDev($distances, $mean);
        $radius = $mean + $std;

        $findings = [];
        foreach ($locationRows as $row) {
            $dist = $this->haversine(
                (float)$row['latitude'], (float)$row['longitude'],
                $centLat, $centLng
            );
            if ($dist > $radius * 1.5) {
                $findings[] = [
                    'category'  => 'LocationController',
                    'icon'      => 'fas fa-map-marker-alt',
                    'anomaly'   => 'Device ' . round($dist, 1) . ' km from home zone at ' . ($row['timestamp'] ?? 'unknown'),
                    'severity'  => $dist > $radius * 3 ? 'High' : 'Medium',
                    'algorithm' => 'Geo-Fence Violation Detector',
                    'timestamp' => $row['timestamp'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'HomeController zone radius: ' . round($radius, 1) . ' km from centroid (' . round($centLat, 4) . ', ' . round($centLng, 4) . ')',
                ];
            }
        }

        return $findings;
    }

    /**
     * Locations – Travel Speed Anomaly
     *
     * Detects physically impossible travel speeds between consecutive points.
     *
     * @param  array $locationRows  [{latitude, longitude, timestamp}, ...] (ordered chronologically)
     * @return array
     */
    public function detectLocationSpeed(array $locationRows): array
    {
        if (count($locationRows) < 2) {
            return [];
        }

        usort($locationRows, fn($a, $b) => strtotime($a['timestamp']) <=> strtotime($b['timestamp']));
        $findings = [];

        for ($i = 1; $i < count($locationRows); $i++) {
            $prev  = $locationRows[$i - 1];
            $curr  = $locationRows[$i];
            $dist  = $this->haversine(
                (float)$prev['latitude'], (float)$prev['longitude'],
                (float)$curr['latitude'], (float)$curr['longitude']
            );
            $secs  = abs(strtotime($curr['timestamp']) - strtotime($prev['timestamp']));
            if ($secs < 1) {
                continue;
            }
            $kmph  = ($dist / $secs) * 3600;
            if ($kmph > 900) {
                $findings[] = [
                    'category'  => 'LocationController',
                    'icon'      => 'fas fa-map-marker-alt',
                    'anomaly'   => 'Impossible travel speed ' . round($kmph) . ' km/h between ' . ($prev['timestamp'] ?? '') . ' and ' . ($curr['timestamp'] ?? ''),
                    'severity'  => 'High',
                    'algorithm' => 'Travel Speed Anomaly',
                    'timestamp' => $curr['timestamp'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'Threshold: 900 km/h (commercial air speed)',
                ];
            }
        }

        return $findings;
    }

    /**
     * Locations – DBSCAN Trajectory Clustering
     *
     * Clusters coordinates to find frequent/normal zones, and flags coordinate points
     * that do not belong to any cluster as trajectory anomalies.
     *
     * @param  array $locationRows  [{latitude, longitude, timestamp}, ...]
     * @return array
     */
    public function detectLocationDbscan(array $locationRows): array
    {
        if (count($locationRows) < 5) {
            return [];
        }

        // Prepare sample data: [[lat, lng], [lat, lng], ...]
        $samples = [];
        foreach ($locationRows as $idx => $row) {
            $samples[$idx] = [
                (float)($row['latitude'] ?? 0.0),
                (float)($row['longitude'] ?? 0.0)
            ];
        }

        // Read DBSCAN params from DB settings
        $epsilon    = max(0.001, (float)($this->getMlSetting('ml_phpml_dbscan_epsilon', '0.01')));
        $minSamples = max(1, (int)($this->getMlSetting('ml_phpml_dbscan_minpoints', '2')));
        try {
            $dbscan = new \Phpml\Clustering\DBSCAN($epsilon, $minSamples);
            $clusters = $dbscan->cluster($samples);
        } catch (\Throwable $e) {
            log_message('error', 'DBSCAN failed: ' . $e->getMessage());
            return [];
        }

        // Identify noise/outliers (points not in any cluster)
        $clusteredIndices = [];
        foreach ($clusters as $cluster) {
            foreach ($cluster as $point) {
                // Find matching original indices
                foreach ($samples as $origIdx => $origVal) {
                    if ($origVal === $point) {
                        $clusteredIndices[$origIdx] = true;
                    }
                }
            }
        }

        $findings = [];
        foreach ($locationRows as $idx => $row) {
            if (!isset($clusteredIndices[$idx])) {
                $findings[] = [
                    'category'  => 'LocationController',
                    'icon'      => 'fas fa-map-marker-alt',
                    'anomaly'   => 'Anomalous trajectory point detected: (' . round($row['latitude'], 4) . ', ' . round($row['longitude'], 4) . ')',
                    'severity'  => 'Medium',
                    'algorithm' => 'DBSCAN Trajectory Clustering',
                    'timestamp' => $row['timestamp'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'PHP-ML DBSCAN outlier (epsilon=' . $epsilon . ', minSamples=' . $minSamples . ')',
                ];
            }
        }

        return $findings;
    }

    protected function stdDev(array $values, float $mean = 0): float
    {
        if (count($values) < 2) {
            return 0.0;
        }
        if ($mean === 0.0) {
            $mean = array_sum($values) / count($values);
        }
        $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / count($values);
        return sqrt($variance);
    }

    protected function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R  = 6371; // Earth radius in km
        $dL = deg2rad($lat2 - $lat1);
        $dG = deg2rad($lon2 - $lon1);
        $a  = sin($dL / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dG / 2) ** 2;
        return $R * 2 * asin(sqrt($a));
    }

    protected function getMlSetting(string $key, $default = null)
    {
        $db = \Config\Database::connect();
        $row = $db->table('settings')
            ->where('class', 'ml')
            ->where('key', $key)
            ->get()
            ->getRow();
        return $row ? $row->value : $default;
    }
}
