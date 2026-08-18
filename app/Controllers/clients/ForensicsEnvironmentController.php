<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class ForensicsEnvironmentController extends BaseClientController
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

    /** POST /advanced/hardware/gnss_hardware/delete/(:num) */
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

    /** POST /advanced/hardware/vibration/delete/(:num) */
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

        $data = array_merge($this->commonData('sensors_location', 'Sensors & LocationController'), [
            'latest_sp' => $latest_sp,
            'latest_gh' => $latest_gh,
            'latest_vb' => $latest_vb,
            'history' => $history,
            'total' => $total,
            'pager' => $pager,
        ]);

        return $this->renderAppView('users/advanced/sensors_location', $data);
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
}
