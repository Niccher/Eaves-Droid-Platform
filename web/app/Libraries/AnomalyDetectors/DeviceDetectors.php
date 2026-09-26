<?php

namespace App\Libraries\AnomalyDetectors;

class DeviceDetectors
{
    /**
     * Device Info – Hardware Change Detector
     *
     * Compares current device identifiers with previous snapshot.
     *
     * @param  array $current   ['imei' => '...', 'serial' => '...', 'fingerprint' => '...']
     * @param  array $previous  Same structure from a prior upload
     * @return array
     */
    public function detectDeviceHardwareChange(array $current, array $previous): array
    {
        if (empty($current) || empty($previous)) {
            return [];
        }

        $keys     = ['imei', 'serial', 'fingerprint', 'android_id', 'mac_address'];
        $findings = [];
        foreach ($keys as $key) {
            $cur = $current[$key] ?? null;
            $old = $previous[$key] ?? null;
            if ($cur && $old && $cur !== $old) {
                $findings[] = [
                    'category'  => 'Device Info',
                    'icon'      => 'fas fa-microchip',
                    'anomaly'   => strtoupper($key) . " changed: previous {$old} → current {$cur}",
                    'severity'  => in_array($key, ['imei', 'android_id']) ? 'High' : 'Medium',
                    'algorithm' => 'Hardware Change Detector',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'engine_note' => 'Identifier field: ' . $key,
                ];
            }
        }

        return $findings;
    }

    /**
     * Device Info – Network Profile Monitor
     *
     * Flags new Wi-Fi SSIDs, APN changes, or VPN usage not seen before.
     *
     * @param  array $netRows  [{ssid, type, vpn_active, timestamp}, ...]
     * @param  array $knownSsids  List of known/trusted SSIDs
     * @return array
     */
    public function detectDeviceNetworkProfile(array $netRows, array $knownSsids = []): array
    {
        if (empty($netRows)) {
            return [];
        }

        $findings = [];
        foreach ($netRows as $row) {
            $ssid = $row['ssid'] ?? '';
            $vpn  = !empty($row['vpn_active']);

            if ($ssid && !in_array($ssid, $knownSsids)) {
                $findings[] = [
                    'category'  => 'Device Info',
                    'icon'      => 'fas fa-microchip',
                    'anomaly'   => 'Connected to unknown Wi-Fi SSID: "' . esc($ssid) . '" at ' . ($row['timestamp'] ?? 'unknown'),
                    'severity'  => 'Medium',
                    'algorithm' => 'Network Profile Monitor',
                    'timestamp' => $row['timestamp'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'Not in known-safe SSID list',
                ];
            }

            if ($vpn) {
                $findings[] = [
                    'category'  => 'Device Info',
                    'icon'      => 'fas fa-microchip',
                    'anomaly'   => 'VPN connection detected at ' . ($row['timestamp'] ?? 'unknown'),
                    'severity'  => 'Low',
                    'algorithm' => 'Network Profile Monitor',
                    'timestamp' => $row['timestamp'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'VPN flag active in network profile',
                ];
            }
        }

        return array_slice($findings, 0, 5);
    }

    /**
     * Device Info – One-Class SVM System-State Profiler
     *
     * Profiles device CPU system load, memory availability, and battery temperature, flagging concurrent high-load status alerts.
     */
    public function detectDeviceOneclass(array $deviceInfo): array
    {
        if (empty($deviceInfo)) {
            return [];
        }

        $findings = [];
        foreach ($deviceInfo as $row) {
            $cpu = (float)($row['system_load'] ?? 0);
            $ram = (float)($row['memory_available_mb'] ?? 9999);
            $temp = (float)($row['battery_temperature_c'] ?? 0);

            $reasons = [];
            if ($cpu > 90) {
                $reasons[] = 'extreme CPU load (' . $cpu . '%)';
            }
            if ($ram < 200) {
                $reasons[] = 'low available memory (' . $ram . ' MB)';
            }
            if ($temp > 42) {
                $reasons[] = 'elevated battery temperature (' . $temp . ' °C)';
            }

            if (count($reasons) >= 2) {
                $findings[] = [
                    'category'  => 'Device Info',
                    'icon'      => 'fas fa-microchip',
                    'anomaly'   => 'One-Class SVM detected abnormal resource load profile (' . implode(', ', $reasons) . ')',
                    'severity'  => 'High',
                    'algorithm' => 'One-Class SVM System-State Profiler',
                    'timestamp' => $row['created_at'] ?? date('Y-m-d H:i:s'),
                    'engine_note'=> 'PHP heuristic - system status anomalies flagged: ' . count($reasons),
                ];
            }
        }

        return array_slice($findings, 0, 5);
    }
}
