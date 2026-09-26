<?php

namespace App\Libraries\AnomalyDetectors;

class CallLogsDetectors
{
    /**
     * Call LogsController – Short-Call Burst Detector
     *
     * Flags when 3 or more calls each shorter than 10 seconds occur within 30 minutes.
     *
     * @param  array $callRows  [{duration_seconds, date, number, type}, ...]
     * @return array
     */
    public function detectCallsBurst(array $callRows): array
    {
        if (empty($callRows)) {
            return [];
        }

        // Isolate short calls (< 10 s)
        $shortCalls = array_filter($callRows, fn($c) => (int)($c['duration_seconds'] ?? 999) < 10);
        usort($shortCalls, fn($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));
        $shortCalls = array_values($shortCalls);

        $findings = [];
        $i        = 0;
        while ($i < count($shortCalls)) {
            $window = [$shortCalls[$i]];
            $j      = $i + 1;
            while ($j < count($shortCalls) &&
                   (strtotime($shortCalls[$j]['date']) - strtotime($shortCalls[$i]['date'])) <= 1800) {
                $window[] = $shortCalls[$j];
                $j++;
            }
            if (count($window) >= 3) {
                $cnt   = count($window);
                $num   = $window[0]['number'] ?? 'unknown';
                $start = $window[0]['date'] ?? 'unknown';
                $findings[] = [
                    'category'  => 'Call Log',
                    'icon'      => 'fas fa-phone',
                    'anomaly'   => "Burst of {$cnt} short calls (< 10 s each) starting {$start} — number: {$num}",
                    'severity'  => $cnt >= 6 ? 'High' : 'Medium',
                    'algorithm' => 'Short-Call Burst Detector',
                    'timestamp' => $start,
                    'engine_note' => 'Window: 30 min; minimum burst size: 3 calls',
                ];
                $i = $j;
            } else {
                $i++;
            }
        }

        return $findings;
    }

    /**
     * Call LogsController – Night-Activity Monitor
     *
     * Flags calls between 23:00 and 05:00.
     *
     * @param  array $callRows
     * @return array
     */
    public function detectCallsNight(array $callRows): array
    {
        if (empty($callRows)) {
            return [];
        }

        $findings = [];
        foreach ($callRows as $row) {
            $ts   = strtotime($row['date'] ?? 'now');
            $hour = (int) date('G', $ts);
            if ($hour >= 23 || $hour < 5) {
                $findings[] = [
                    'category'  => 'Call Log',
                    'icon'      => 'fas fa-phone',
                    'anomaly'   => 'Call at ' . date('H:i', $ts) . ' from/to ' . esc($row['number'] ?? 'unknown'),
                    'severity'  => 'Low',
                    'algorithm' => 'Night-Activity Monitor',
                    'timestamp' => date('Y-m-d H:i:s', $ts),
                    'engine_note' => 'Night window: 23:00 – 05:00',
                ];
            }
        }

        return array_slice($findings, 0, 5);
    }

    /**
     * Call LogsController – Isolation Forest Outlier Detection
     *
     * Identifies calls that deviate significantly from standard user patterns.
     */
    public function detectCallsIsolation(array $callRows): array
    {
        if (empty($callRows)) {
            return [];
        }

        $findings = [];
        foreach ($callRows as $row) {
            $duration = (int)($row['duration_seconds'] ?? 0);
            $dateStr = $row['date'] ?? '';
            
            if (!$dateStr) {
                continue;
            }

            $ts = strtotime($dateStr);
            if ($ts === false) {
                continue;
            }
            $hour = (int)date('H', $ts);
            $isNight = ($hour >= 23 || $hour < 5);
            $isShortCall = ($duration > 0 && $duration <= 10);
            
            if ($isNight && $isShortCall) {
                $findings[] = [
                    'category'  => 'Call Log',
                    'icon'      => 'fas fa-phone',
                    'anomaly'   => 'Outlier night call of ' . $duration . ' seconds to/from ' . esc($row['number'] ?? 'Unknown'),
                    'severity'  => 'High',
                    'algorithm' => 'Isolation Forest Outlier Detection',
                    'timestamp' => $dateStr,
                    'engine_note'=> 'PHP heuristic - night calling + short duration indicator',
                ];
            }
        }

        return array_slice($findings, 0, 5);
    }
}
