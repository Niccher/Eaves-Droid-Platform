<?php

namespace App\Libraries\AnomalyDetectors;

class ActivityDetectors
{
    /**
     * Device Activity – Screen-Time Anomaly Detector
     *
     * Flags days where screen-on time deviates > 2σ from the 30-day mean.
     *
     * @param  array $activityRows  [{date, screen_on_minutes}, ...]
     * @return array
     */
    public function detectActivityScreenTime(array $activityRows): array
    {
        if (count($activityRows) < 3) {
            return [];
        }

        $times  = array_column($activityRows, 'screen_on_minutes');
        $mean   = array_sum($times) / count($times);
        $std    = $this->stdDev($times, $mean);

        $findings = [];
        foreach ($activityRows as $row) {
            $mins   = (float)$row['screen_on_minutes'];
            $zscore = ($std > 0) ? abs($mins - $mean) / $std : 0;
            if ($zscore > 2) {
                $hours = round($mins / 60, 1);
                $findings[] = [
                    'category'  => 'Activity',
                    'icon'      => 'fas fa-heartbeat',
                    'anomaly'   => "Screen-time {$hours} h on " . ($row['date'] ?? 'unknown') . " (baseline: " . round($mean / 60, 1) . ' h ± ' . round($std / 60, 1) . ' h)',
                    'severity'  => $zscore > 3 ? 'High' : 'Medium',
                    'algorithm' => 'Screen-Time Anomaly Detector',
                    'timestamp' => ($row['date'] ?? date('Y-m-d')) . ' 23:59:00',
                    'engine_note' => 'Z-Score: ' . round($zscore, 2) . ' σ above mean',
                ];
            }
        }

        return $findings;
    }

    /**
     * Device Activity – App-Switch Rate Monitor
     *
     * Flags hourly periods with > 60 app switches.
     *
     * @param  array $activityRows  [{timestamp, app_package}, ...]  (ordered chronologically)
     * @return array
     */
    public function detectActivitySwitchRate(array $activityRows): array
    {
        if (count($activityRows) < 5) {
            return [];
        }

        // Bucket by hour
        $hourBuckets = [];
        foreach ($activityRows as $row) {
            $hr = date('Y-m-d H', strtotime($row['timestamp'] ?? 'now'));
            if (!isset($hourBuckets[$hr])) {
                $hourBuckets[$hr] = ['switches' => 0, 'prev' => null];
            }
            if ($hourBuckets[$hr]['prev'] !== ($row['app_package'] ?? null)) {
                $hourBuckets[$hr]['switches']++;
                $hourBuckets[$hr]['prev'] = $row['app_package'] ?? null;
            }
        }

        $findings = [];
        foreach ($hourBuckets as $hr => $data) {
            if ($data['switches'] > 60) {
                $findings[] = [
                    'category'  => 'Activity',
                    'icon'      => 'fas fa-heartbeat',
                    'anomaly'   => $data['switches'] . ' app switches in one hour (' . $hr . ':00) — possible scripted behaviour',
                    'severity'  => $data['switches'] > 100 ? 'High' : 'Medium',
                    'algorithm' => 'App-Switch Rate Monitor',
                    'timestamp' => $hr . ':00:00',
                    'engine_note' => 'Threshold: 60 app switches/hour',
                ];
            }
        }

        return $findings;
    }

    /**
     * Device Activity – Activity Sequence Predictor
     *
     * Checks user activity records for unusual active-hour sequences.
     */
    public function detectActivityLstm(array $activityRows): array
    {
        if (empty($activityRows)) {
            return [];
        }

        $findings = [];
        $nightActivityCount = 0;
        $totalActivityCount = 0;
        $timestamps = [];

        foreach ($activityRows as $row) {
            $tsStr = $row['timestamp'] ?? '';
            if (!$tsStr) {
                continue;
            }
            
            $ts = strtotime($tsStr);
            if ($ts === false) {
                continue;
            }

            $totalActivityCount++;
            $hour = (int)date('H', $ts);
            if ($hour >= 0 && $hour < 6) {
                $nightActivityCount++;
                $timestamps[] = $tsStr;
            }
        }

        if ($totalActivityCount > 10) {
            $ratio = $nightActivityCount / $totalActivityCount;
            if ($ratio > 0.40) {
                $findings[] = [
                    'category'  => 'Activity',
                    'icon'      => 'fas fa-heartbeat',
                    'anomaly'   => 'Abnormal device activity sequence detected between 12 AM and 6 AM',
                    'severity'  => 'Medium',
                    'algorithm' => 'Activity Sequence Predictor (MLP)',
                    'timestamp' => !empty($timestamps) ? end($timestamps) : date('Y-m-d H:i:s'),
                    'engine_note'=> 'PHP heuristic - night activity ratio: ' . round($ratio * 100, 1) . '% (threshold: 40%)',
                ];
            }
        }

        return array_slice($findings, 0, 5);
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
}
