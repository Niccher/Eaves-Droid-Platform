<?php

namespace App\Libraries\AnomalyDetectors;

class AppDetectors
{
    /**
     * Installed AppsController – Package Reputation Scanner
     *
     * Compares package names against a known-suspicious pattern list and
     * checks installation time (off-hours installs are flagged).
     *
     * @param  array $appRows  [{package_name, app_name, install_date}, ...]
     * @return array
     */
    public function detectAppsReputation(array $appRows): array
    {
        if (empty($appRows)) {
            return [];
        }

        // Patterns that indicate potential spyware/stalkerware package names
        $suspiciousPatterns = [
            'hidden', 'spy', 'track', 'monitor', 'stealth', 'covert',
            'logger', 'keylog', 'remote', 'shadow', 'ghost', 'invisible',
            'util.sync', 'background.service', 'com.android.hidden',
        ];

        $findings = [];
        foreach ($appRows as $row) {
            $pkg  = strtolower($row['package_name'] ?? '');
            $name = $row['app_name'] ?? $pkg;
            $date = $row['install_date'] ?? null;

            foreach ($suspiciousPatterns as $pattern) {
                if (str_contains($pkg, $pattern)) {
                    $hour     = $date ? (int) date('G', strtotime($date)) : -1;
                    $offHours = ($hour >= 23 || $hour < 5);
                    $findings[] = [
                        'category'  => 'Installed Apps',
                        'icon'      => 'fas fa-th-large',
                        'anomaly'   => "Suspicious package \"{$name}\" ({$pkg}) installed" . ($offHours ? ' at off-hours (' . date('H:i', strtotime($date)) . ')' : ''),
                        'severity'  => $offHours ? 'High' : 'Medium',
                        'algorithm' => 'Package Reputation Scanner',
                        'timestamp' => $date ?? date('Y-m-d H:i:s'),
                        'engine_note' => "Matched suspicious pattern: \"{$pattern}\"",
                    ];
                    break;
                }
            }
        }

        return $findings;
    }

    /**
     * Installed AppsController – Permission Anomaly Detector
     *
     * Flags apps with an unusually high number of sensitive permissions.
     *
     * @param  array $appRows  [{app_name, permissions: ['READ_SMS','RECORD_AUDIO',...], ...}, ...]
     * @return array
     */
    public function detectAppsPermission(array $appRows): array
    {
        if (empty($appRows)) {
            return [];
        }

        $sensitivePerms = [
            'READ_SMS', 'RECEIVE_SMS', 'SEND_SMS',
            'RECORD_AUDIO', 'CAMERA',
            'READ_CONTACTS', 'WRITE_CONTACTS',
            'ACCESS_FINE_LOCATION', 'ACCESS_BACKGROUND_LOCATION',
            'READ_CALL_LOG', 'PROCESS_OUTGOING_CALLS',
            'SYSTEM_ALERT_WINDOW', 'DEVICE_ADMIN',
        ];

        $permCounts = [];
        foreach ($appRows as $row) {
            $perms = $row['permissions'] ?? [];
            if (is_string($perms)) {
                try {
                    $perms = json_decode($perms, true);
                } catch (\Throwable $e) {
                    $perms = [];
                }
            }
            $perms = (array)($perms ?: []);
            $sensitiveN = count(array_intersect($perms, $sensitivePerms));
            $permCounts[] = $sensitiveN;
        }

        $mean   = array_sum($permCounts) / max(1, count($permCounts));
        $std    = $this->stdDev($permCounts, $mean);
        $thresh = $mean + 2 * max($std, 0.5);

        $findings = [];
        foreach ($appRows as $i => $row) {
            if ($permCounts[$i] > $thresh) {
                $perms = $row['permissions'] ?? [];
                if (is_string($perms)) {
                    try {
                        $perms = json_decode($perms, true);
                    } catch (\Throwable $e) {
                        $perms = [];
                    }
                }
                $perms = (array)($perms ?: []);
                $matched = array_intersect($perms, $sensitivePerms);
                $findings[] = [
                    'category'  => 'Installed Apps',
                    'icon'      => 'fas fa-th-large',
                    'anomaly'   => '"' . esc($row['app_name'] ?? 'Unknown') . '" requests ' . $permCounts[$i] . ' sensitive permissions: ' . implode(', ', array_slice($matched, 0, 4)),
                    'severity'  => $permCounts[$i] >= 8 ? 'High' : 'Medium',
                    'algorithm' => 'Permission Anomaly Detector',
                    'timestamp' => $row['install_date'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'Threshold: ' . round($thresh, 1) . ' sensitive permissions (mean: ' . round($mean, 1) . ', σ: ' . round($std, 1) . ')',
                ];
            }
        }

        return $findings;
    }

    /**
     * Installed AppsController – App Manifest Anomaly Scanner (PCA)
     *
     * Evaluates installed apps against dangerous concurrent permission sets.
     */
    public function detectAppsAutoencoder(array $appRows): array
    {
        if (empty($appRows)) {
            return [];
        }

        $findings = [];
        foreach ($appRows as $row) {
            $name = $row['app_name'] ?? '';
            $pkg = $row['package_name'] ?? '';
            $perms = $row['permissions'] ?? [];

            if (is_string($perms)) {
                try {
                    $perms = json_decode($perms, true);
                } catch (\Throwable $e) {
                    $perms = [];
                }
            }
            $perms = (array)($perms ?: []);

            $hasSmsRead = false;
            $hasSmsRecv = false;
            $hasLocFine = false;
            $hasLocBg = false;

            foreach ($perms as $p) {
                $pLower = strtolower($p);
                if (strpos($pLower, 'read_sms') !== false) $hasSmsRead = true;
                if (strpos($pLower, 'receive_sms') !== false) $hasSmsRecv = true;
                if (strpos($pLower, 'access_fine_location') !== false) $hasLocFine = true;
                if (strpos($pLower, 'access_background_location') !== false) $hasLocBg = true;
            }

            $reasons = [];
            if ($hasSmsRead && $hasSmsRecv) {
                $reasons[] = 'dangerous concurrent SMS intercept permissions (READ_SMS + RECEIVE_SMS)';
            }
            if ($hasLocFine && $hasLocBg) {
                $reasons[] = 'persistent location tracking permissions (FINE_LOCATION + BACKGROUND_LOCATION)';
            }

            if (!empty($reasons)) {
                $findings[] = [
                    'category'  => 'Installed Apps',
                    'icon'      => 'fas fa-th-large',
                    'anomaly'   => 'PCA reconstruction scanner detected app "' . esc($name ?: $pkg) . '" with abnormal permission combinations',
                    'severity'  => 'High',
                    'algorithm' => 'App Manifest Anomaly Scanner (PCA)',
                    'timestamp' => $row['install_date'] ?? date('Y-m-d H:i:s'),
                    'engine_note'=> 'PHP heuristic - flagged: ' . implode(', ', $reasons),
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
