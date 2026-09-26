<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class ForensicsSystemController extends BaseClientController
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

    /** GET /advanced/battery_stats */
    public function battery_stats()
    {
        $db = \Config\Database::connect();
        $historyRows = $db->table('tbl_telemetry_battery_stats')
            ->where('owner_id', $this->userId)
            ->orderBy('extracted_at', 'DESC')
            ->limit(60)
            ->get()
            ->getResultArray();

        $history = array_reverse($historyRows);

        $data = array_merge($this->commonData('battery_stats', 'Battery Stats'), [
            'rows' => $this->finderModel->get_battery_stats($this->userId),
            'history' => $history,
            'total' => $this->finderModel->get_count_BatteryStats($this->userId),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderAppView('users/advanced/battery_stats', $data);
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
}
