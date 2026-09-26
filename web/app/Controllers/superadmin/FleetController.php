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

        // User Activity Metrics (Admin commands & FCM actions)
        $userActivity = $this->getUserActivityStats();

        // Geography Breakdown for main overview
        $geoOverview = $this->getGeoOverviewStats();

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
            'user_activity' => $userActivity,
            'geo_overview' => $geoOverview,
        ]);
    }

    private function getUserActivityStats(): array
    {
        $db = $this->getDb();

        $commands24h = $db->table('tbl_user_actions')
            ->like('action_type', 'remote_cmd_')
            ->where('created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)')
            ->countAllResults();

        $topCommandRow = $db->table('tbl_user_actions')
            ->select('action_type as command, COUNT(*) as cnt')
            ->like('action_type', 'remote_cmd_')
            ->groupBy('action_type')
            ->orderBy('cnt', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $topAdminRow = $db->table('tbl_user_actions ual')
            ->select('u.username, COUNT(*) as cnt')
            ->join('users u', 'u.id = ual.user_id', 'left')
            ->where('ual.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')
            ->groupBy('ual.user_id')
            ->orderBy('cnt', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $adminActions24h = $db->table('tbl_user_actions')
            ->where('created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)')
            ->countAllResults();

        $topCmdName = !empty($topCommandRow['command']) ? str_replace('remote_cmd_', '', $topCommandRow['command']) : 'None';

        return [
            'fcm_commands_24h' => $commands24h,
            'top_command' => $topCmdName,
            'top_command_count' => $topCommandRow['cnt'] ?? 0,
            'top_admin' => $topAdminRow['username'] ?? 'System',
            'admin_actions_24h' => $adminActions24h,
        ];
    }

    private function getGeoOverviewStats(): array
    {
        $db = $this->getDb();

        $topCountries = $db->table('tbl_device_profiles')
            ->select('country, COUNT(DISTINCT device_id) as cnt')
            ->where('country IS NOT NULL')
            ->where('country !=', '')
            ->groupBy('country')
            ->orderBy('cnt', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $topCarriers = $db->table('tbl_device_profiles')
            ->select('network_operator, COUNT(DISTINCT device_id) as cnt')
            ->where('network_operator IS NOT NULL')
            ->where('network_operator !=', '')
            ->groupBy('network_operator')
            ->orderBy('cnt', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return [
            'top_countries' => $topCountries,
            'top_carriers' => $topCarriers,
        ];
    }

    // ================================================================
    // DATA QUALITY METHODS - FIXED
    // ================================================================

    private function getLatestDeviceProfiles(): array
    {
        $db = $this->getDb();

        $sql = "SELECT dp.* 
                FROM tbl_device_profiles dp
                INNER JOIN (
                    SELECT device_id, MAX(counter) AS max_counter
                    FROM tbl_device_profiles
                    WHERE device_id IS NOT NULL AND device_id != ''
                    GROUP BY device_id
                ) latest ON dp.counter = latest.max_counter";

        return $db->query($sql)->getResultArray();
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

        if (is_string($value) && preg_match('/^\d{4}$/', $value)) {
            return strtotime($value . '-01-01');
        }

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

        $filesSize = $db->table('tbl_extracted_device_files')->selectSum('size_bytes')->get()->getRow()->size_bytes ?? 0;
        $appsSize = $db->table('tbl_extracted_installed_apps')->selectSum('app_size')->get()->getRow()->app_size ?? 0;
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
        $queueStats = $db->table('tbl_upload_queue')
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

        $failedCount = $db->table('tbl_upload_queue')
            ->where('status', 'failed')
            ->where('queued_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)')
            ->countAllResults();

        $lowStorage = $db->table('tbl_device_profiles')
            ->where('internal_storage_free_gb <', 1)
            ->where('internal_storage_free_gb >', 0)
            ->countAllResults();

        $avgBattery = $db->table('tbl_device_profiles')
            ->selectAvg('battery_level')
            ->where('battery_level >', 0)
            ->get()
            ->getRow()
            ->battery_level ?? 0;

        $avgFreeGb = $db->table('tbl_device_profiles')
            ->selectAvg('internal_storage_free_gb')
            ->where('internal_storage_free_gb >', 0)
            ->get()
            ->getRow()
            ->internal_storage_free_gb ?? 0;

        $fcmReachable = $db->table('tbl_device_profiles')
            ->where('fcm_token !=', '')
            ->where('fcm_token IS NOT NULL')
            ->select('COUNT(DISTINCT device_id) as cnt')
            ->get()
            ->getRow()
            ->cnt ?? 0;

        $totalDevices = $this->getLatestDeviceProfilesCount();

        return [
            'sync_success_rate' => $successRate,
            'failed_uploads_24h' => $failedCount,
            'pending_uploads' => $queueStats['pending'] ?? 0,
            'low_storage_devices' => $lowStorage,
            'avg_battery_level' => round($avgBattery, 1),
            'avg_free_gb' => round($avgFreeGb, 1),
            'fcm_reachable_devices' => $fcmReachable,
            'fcm_reachability_rate' => $totalDevices > 0 ? round(($fcmReachable / $totalDevices) * 100, 1) : 0,
        ];
    }

    private function getSecurityStats(): array
    {
        $db = $this->getDb();

        $rooted = $db->table('tbl_device_profiles')->where('is_rooted', 1)->countAllResults();
        $debuggable = $db->table('tbl_device_profiles')->where('is_debuggable', 1)->countAllResults();

        $sideloaded = $db->table('tbl_device_profiles')
            ->where('app_installer !=', 'com.android.vending')
            ->where('app_installer !=', '')
            ->where('app_installer IS NOT NULL')
            ->countAllResults();

        $patched = $db->table('tbl_device_profiles')
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
            'patch_compliance_rate' => $total > 0 ? min(100, round(($patched / $total) * 100, 1)) : 100,
            'total_devices' => $total,
        ];
    }

    private function getLatestDeviceProfilesCount(): int
    {
        $db = $this->getDb();
        return $db->table('tbl_device_profiles')
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

        $daily = $db->table('tbl_device_profiles')
            ->select("DATE(FROM_UNIXTIME(CASE WHEN extraction_timestamp > 10000000000 THEN extraction_timestamp / 1000 ELSE extraction_timestamp END)) as date, COUNT(DISTINCT device_id) as syncs")
            ->where('extraction_timestamp >', (time() - 604800) * 1000)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();

        $filled = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $filled[$date] = 0;
        }
        foreach ($daily as $row) {
            if (!empty($row['date']) && isset($filled[$row['date']])) {
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
                'route' => 'superadmin/fleet/alerts',
            ];
        }

        // Rooted devices
        $rooted = array_filter($devices, fn($d) => $d['is_rooted'] ?? 0);
        if (count($rooted) > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Rooted Devices Detected',
                'message' => count($rooted) . ' devices are rooted',
                'route' => 'superadmin/fleet/alerts',
            ];
        }

        // Low storage
        $lowStorage = array_filter($devices, fn($d) => ($d['internal_storage_free_gb'] ?? 0) > 0 && $d['internal_storage_free_gb'] < 1);
        if (count($lowStorage) > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Storage',
                'message' => count($lowStorage) . ' devices have < 1GB free space',
                'route' => 'superadmin/fleet/alerts',
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
                'route' => 'superadmin/fleet/patches',
            ];
        }

        return $alerts;
    }

    // ================================================================
    // SUB-PAGE METHODS
    // ================================================================

    public function deviceDetail(string $deviceId)
    {
        $db = $this->getDb();

        $device = $db->table('tbl_device_profiles')
            ->where('device_id', $deviceId)
            ->orderBy('extraction_timestamp', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$device) {
            return redirect()->to('superadmin/fleet')->with('error', 'Device not found');
        }

        // Get upload history
        $uploads = $db->table('tbl_upload_queue')
            ->where('device_print_id', $deviceId)
            ->orderBy('queued_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        // Get data counts
        $dataCounts = [
            'sms' => $db->table('tbl_extracted_sms')->where('device_id', $deviceId)->countAllResults(),
            'calls' => $db->table('tbl_extracted_call_logs')->where('device_id', $deviceId)->countAllResults(),
            'contacts' => $db->table('tbl_extracted_contacts')->where('device_id', $deviceId)->countAllResults(),
            'locations' => $db->table('tbl_extracted_locations')->where('device_id', $deviceId)->countAllResults(),
            'apps' => $db->table('tbl_extracted_installed_apps')->where('device_id', $deviceId)->countAllResults(),
            'files' => $db->table('tbl_extracted_device_files')->where('device_id', $deviceId)->countAllResults(),
        ];

        return $this->renderView('superadmin/fleet/device_detail', [
            'pag' => 'superadmin-fleet-device',
            'device' => $device,
            'uploads' => $uploads,
            'data_counts' => $dataCounts,
        ]);
    }
}