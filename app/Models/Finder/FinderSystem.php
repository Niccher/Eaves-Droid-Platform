<?php

namespace App\Models\Finder;

use CodeIgniter\Model;

class FinderSystem extends Model
{
    protected $parent;
    protected $db;

    public function __construct($parent)
    {
        parent::__construct();
        $this->parent = $parent;
        $this->db = $parent->db;
    }

    public function __get($name)
    {
        if ($name === 'deviceId') {
            return $this->parent->deviceId;
        }
        if ($name === 'pager') {
            return $this->parent->pager;
        }
        return null;
    }

    public function __set($name, $value)
    {
        if ($name === 'pager') {
            $this->parent->pager = $value;
        }
        if ($name === 'total_timeline') {
            $this->parent->total_timeline = $value;
        }
    }

    // Helper wrappers
    protected function applyOwnerDeviceFilter($builder, int $user_id)
    {
        return $this->parent->applyOwnerDeviceFilter($builder, $user_id);
    }

    protected function fq(string $table, int $userId)
    {
        return $this->parent->fq($table, $userId);
    }

    protected function cq(string $table, int $userId): int
    {
        return $this->parent->cq($table, $userId);
    }

    protected function getCount(string $table, int $user_id, array $extraWhere = [], ?string $blockColumn = null, array $blockedValues = []): int
    {
        return $this->parent->getCount($table, $user_id, $extraWhere, $blockColumn, $blockedValues);
    }

    protected function getBlockedIdentifiers(int $userId, string $category): array
    {
        return $this->parent->getBlockedIdentifiers($userId, $category);
    }

    public function deleteAppsByUser(int $user_id): bool
    {
        try {
            $builder = $this->db->table('tbl_extracted_installed_apps');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteAppsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteDeviceContextByUser(int $user_id): bool
    {
        return $this->fq('tbl_device_hardware_contexts', $user_id)->delete();
    }

    public function deleteNetworkInfoByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_network_info', $user_id)->delete();
    }

    public function deleteAccountsByUser(int $user_id): bool
    {
        return $this->fq('tbl_accounts', $user_id)->delete();
    }

    public function deleteAccessibilityByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_accessibility_services', $user_id)->delete();
    }

    public function deleteInputMethodsByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_input_methods', $user_id)->delete();
    }

    public function deleteSecurityAuditByUser(int $user_id): bool
    {
        return $this->fq('tbl_security_audit', $user_id)->delete();
    }

    public function deleteProcInfoByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_running_processes', $user_id)->delete();
    }

    public function deleteDefaultAppsByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_default_apps_device', $user_id)->delete();
    }

    public function deleteAlarmsByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_alarms', $user_id)->delete();
    }

    public function deleteAppSecurityByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_app_security', $user_id)->delete();
    }

    public function deleteNetworkSecurityByUser(int $user_id): bool
    {
        return $this->fq('tbl_network_security', $user_id)->delete();
    }

    public function deleteTelephonyNetworkByUser(int $user_id): bool
    {
        return $this->fq('tbl_telephony_network', $user_id)->delete();
    }

    public function deleteSystemLocaleByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_locale', $user_id)->delete();
    }

    public function deleteHardwareGraphicsByUser(int $user_id): bool
    {
        return $this->fq('tbl_hardware_graphics', $user_id)->delete();
    }

    public function deleteHardwareNetworkByUser(int $user_id): bool
    {
        return $this->fq('tbl_hardware_network', $user_id)->delete();
    }

    public function deleteCameraInfoByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_cameras', $user_id)->delete();
    }

    public function deleteBatteryStatsByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_battery_stats', $user_id)->delete();
    }

    public function deleteDisplayInfoByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_display_info', $user_id)->delete();
    }

    public function deleteProcessesByUser(int $user_id): bool
    {
        return $this->fq('tbl_running_processes', $user_id)->delete();
    }

    public function deleteCrashLogsByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_crash_logs', $user_id)->delete();
    }

    public function deleteDozeStandbyByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_doze_standby', $user_id)->delete();
    }

    public function deleteEmailAccountsByUser(int $user_id): bool
    {
        return $this->fq('tbl_extracted_email_accounts', $user_id)->delete();
    }

    public function deleteKeyboardInputByUser(int $user_id): bool
    {
        return $this->fq('tbl_keyboard_input', $user_id)->delete();
    }

    public function deleteKeyguardEventsByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_keyguard_events', $user_id)->delete();
    }

    public function deleteVpnConfigByUser(int $user_id): bool
    {
        return $this->fq('tbl_vpn_config', $user_id)->delete();
    }

    public function deleteRunningProcessesDetailedByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_running_processes_detailed', $user_id)->delete();
    }

    public function deleteAudioDevicesByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_audio_devices', $user_id)->delete();
    }

    public function deleteBiometricByUser(int $user_id): bool
    {
        return $this->fq('tbl_biometric', $user_id)->delete();
    }

    public function deletePowerRailsByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_power_rails', $user_id)->delete();
    }

    public function deleteUsbDevicesByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_usb_devices', $user_id)->delete();
    }

    public function deleteVibrationByUser(int $user_id): bool
    {
        return $this->fq('tbl_telemetry_vibration', $user_id)->delete();
    }

    public function deleteMlJobsByUser(int $user_id): bool
    {
        try {
            return $this->db->table('ml_jobs')->where('user_id', $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteMlJobsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteMlResultsByUser(int $user_id): bool
    {
        try {
            return $this->db->table('ml_results')->where('user_id', $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteMlResultsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteMlAnalysisTrackingByUser(int $user_id): bool
    {
        try {
            return $this->db->table('ml_analysis_tracking')->where('user_id', $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteMlAnalysisTrackingByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_Apps(int $user_id): int
    {
        try {
            $blocked = $this->getBlockedIdentifiers($user_id, 'app_usage');
            $builder = $this->fq('tbl_extracted_installed_apps', $user_id);
            if (!empty($blocked)) {
                $builder->whereNotIn('package_name', $blocked);
            }
            return $builder->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'get_count_Apps error: ' . $e->getMessage());
            return 0;
        }
    }

    public function get_count_Apps_category(int $user_id, int $is_system): int
    {
        return $this->getCount('tbl_extracted_installed_apps', $user_id, ['is_system_app' => $is_system]);
    }

    public function get_apps(int $user_id, int $perPage = 20): array
    {
        try {
            $builder = $this->db->table('tbl_extracted_installed_apps');

            // Get total count for pagination
            $total = $this->get_count_Apps($user_id);

            // Scope to this user (and active device) — never show another user's apps.
            $this->applyOwnerDeviceFilter($builder, $user_id);

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $query = $builder->select('
                counter,
                app_name as Name,
                package_name as Package,
                version_code as Code,
                version_name,
                app_icon,
                app_size,
                permissions,
                permission_count,
                is_system_app,
                first_install_time,
                last_update_time,
                target_sdk,
                min_sdk
            ');
                
            $blocked = $this->getBlockedIdentifiers($user_id, 'app_usage');
            if (!empty($blocked)) {
                $query->whereNotIn('package_name', $blocked);
            }

            $results = $query->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_apps error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_count_DeviceContext(int $user_id): int
    {
        return $this->getCount('tbl_device_hardware_contexts', $user_id);
    }

    public function get_count_NetworkInfo(int $user_id): int
    {
        return $this->getCount('tbl_system_network_info', $user_id);
    }

    public function get_count_Accounts(int $user_id): int
    {
        return $this->getCount('tbl_accounts', $user_id);
    }

    public function get_count_SecurityAudit(int $user_id): int
    {
        return $this->getCount('tbl_security_audit', $user_id);
    }

    public function get_count_HardwareGraphics(int $user_id): int
    {
        return $this->getCount('tbl_hardware_graphics', $user_id);
    }

    public function get_count_CameraInfo(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_cameras', $user_id);
    }

    public function get_count_BatteryStats(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_battery_stats', $user_id);
    }

    public function get_count_Accessibility(int $user_id): int
    {
        return $this->getCount('tbl_system_accessibility_services', $user_id);
    }

    public function get_count_InputMethods(int $user_id): int
    {
        return $this->getCount('tbl_system_input_methods', $user_id);
    }

    public function get_count_Processes(int $user_id): int
    {
        return $this->getCount('tbl_running_processes', $user_id);
    }

    public function get_count_ProcInfo(int $user_id): int
    {
        return $this->getCount('tbl_system_running_processes', $user_id);
    }

    public function get_count_SimConfig(int $user_id): int
    {
        return $this->getCount('tbl_sim_configs', $user_id);
    }

    public function export_device_context(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_device_hardware_contexts', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_network_info(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_network_info', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_accounts(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_accounts', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_security_audit(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_security_audit', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_accessibility(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_accessibility_services', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_input_methods(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_input_methods', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_proc_info(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_running_processes', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_default_apps(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_default_apps_device', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_alarms(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_alarms', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_app_security(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_app_security', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_network_security(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_network_security', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_telephony_network(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telephony_network', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_system_locale(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_locale', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_hardware_graphics(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_hardware_graphics', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_hardware_network(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_hardware_network', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_camera_info(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_cameras', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_battery_stats(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_battery_stats', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_display_info(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_display_info', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_processes(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_running_processes', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_crash_logs(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_crash_logs', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_doze_standby(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_doze_standby', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_email_accounts(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_extracted_email_accounts', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_keyboard_input(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_keyboard_input', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_keyguard_events(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_keyguard_events', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_vpn_config(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_vpn_config', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_running_processes_detailed(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_running_processes_detailed', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_audio_devices(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_audio_devices', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_biometric(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_biometric', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_power_rails(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_power_rails', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_usb_devices(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_usb_devices', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_vibration(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_telemetry_vibration', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function get_device_context(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_DeviceContext($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_device_hardware_contexts', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_device_context: ' . $e->getMessage());
            return [];
        }
    }

    public function get_network_info(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_NetworkInfo($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_network_info', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_network_info: ' . $e->getMessage());
            return [];
        }
    }

    public function get_accounts(int $user_id, int $perPage = 50): array
    {
        try {
            $total = $this->get_count_Accounts($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_accounts', $user_id)
                ->orderBy('account_type', 'ASC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_accounts: ' . $e->getMessage());
            return [];
        }
    }

    public function getAccountsQuery(int $user_id): \CodeIgniter\Database\BaseBuilder
    {
        return $this->fq('tbl_accounts', $user_id)
            ->select('id, owner_id, device_id, account_name, account_type, summary_json, total_count, extracted_at, created_at, updated_at, account_label, is_syncable, sync_auto, sync_interval, last_sync_time, last_sync_result, last_sync_error, user_data, auth_token_type, features')
            ->orderBy('account_type', 'ASC');
    }

    public function tableQuery(string $table, int $userId, string $orderCol = 'extracted_at', string $orderDir = 'DESC'): \CodeIgniter\Database\BaseBuilder
    {
        return $this->fq($table, $userId)->orderBy($orderCol, $orderDir);
    }

    public function get_app_detail_by_package(int $user_id, string $package_name): array
    {
        try {
            $builder = $this->fq('tbl_extracted_installed_apps', $user_id);
            $row = $builder
                ->select('app_name, package_name, version_name, version_code, app_size, permission_count, target_sdk, min_sdk, first_install_time, last_update_time, is_system_app')
                ->where('package_name', $package_name)
                ->get()
                ->getRowArray();
            if ($row && !empty($row['first_install_time'])) {
                $ts = is_numeric($row['first_install_time'])
                    ? (strlen($row['first_install_time']) > 11 ? (int)($row['first_install_time'] / 1000) : (int)$row['first_install_time'])
                    : strtotime($row['first_install_time']);
                $row['first_install_display'] = $ts ? date('M j, Y, g:i A', $ts) : '—';
            } else {
                $row['first_install_display'] = '—';
            }
            if ($row && !empty($row['last_update_time'])) {
                $ts = is_numeric($row['last_update_time'])
                    ? (strlen($row['last_update_time']) > 11 ? (int)($row['last_update_time'] / 1000) : (int)$row['last_update_time'])
                    : strtotime($row['last_update_time']);
                $row['last_update_display'] = $ts ? date('M j, Y, g:i A', $ts) : '—';
            } else {
                $row['last_update_display'] = '—';
            }
            if ($row && !empty($row['app_size'])) {
                $size = (int)$row['app_size'];
                if ($size > 1048576) {
                    $row['app_size_display'] = round($size / 1048576, 1) . ' MB';
                } elseif ($size > 1024) {
                    $row['app_size_display'] = round($size / 1024, 1) . ' KB';
                } else {
                    $row['app_size_display'] = $size . ' B';
                }
            } else {
                $row['app_size_display'] = '—';
            }
            return $row ?: [];
        } catch (\Exception $e) {
            log_message('error', 'get_app_detail_by_package: ' . $e->getMessage());
            return [];
        }
    }

    public function get_security_audit(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_SecurityAudit($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_security_audit', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            if (!empty($results)) {
                foreach ($results as &$r) {
                    if (!empty($r['user_ca_certs_json'])) {
                        $decoded = json_decode($r['user_ca_certs_json'], true);
                        $r['user_ca_certs'] = is_array($decoded) ? $decoded : [];
                    } else {
                        $r['user_ca_certs'] = [];
                    }
                    if (!empty($r['system_ca_certs_json'])) {
                        $decoded = json_decode($r['system_ca_certs_json'], true);
                        $r['system_ca_certs'] = is_array($decoded) ? $decoded : [];
                    } else {
                        $r['system_ca_certs'] = [];
                    }
                    if (!empty($r['vpn_config_json'])) {
                        $decoded = json_decode($r['vpn_config_json'], true);
                        $r['vpn_config'] = is_array($decoded) ? $decoded : [];
                    } else {
                        $r['vpn_config'] = [];
                    }
                    if (!empty($r['device_admin_apps_json'])) {
                        $decoded = json_decode($r['device_admin_apps_json'], true);
                        $r['device_admin_apps'] = is_array($decoded) ? $decoded : [];
                    } else {
                        $r['device_admin_apps'] = [];
                    }
                    if (!empty($r['dns_config_json'])) {
                        $decoded = json_decode($r['dns_config_json'], true);
                        $r['dns_config'] = is_array($decoded) ? $decoded : [];
                    } else {
                        $r['dns_config'] = [];
                    }
                    if (!empty($r['open_ports_json'])) {
                        $decoded = json_decode($r['open_ports_json'], true);
                        $r['open_ports'] = is_array($decoded) ? $decoded : [];
                    } else {
                        $r['open_ports'] = [];
                    }
                }
                unset($r);
            }
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_security_audit: ' . $e->getMessage());
            return [];
        }
    }

    public function get_hardware_graphics(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_HardwareGraphics($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_hardware_graphics', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            if (!empty($results)) {
                foreach ($results as &$r) {
                    foreach (['gpu_renderer_json', 'media_codecs_json', 'input_devices_json'] as $col) {
                        if (!empty($r[$col])) {
                            $decoded = json_decode($r[$col], true);
                            $r[str_replace('_json', '', $col)] = is_array($decoded) ? $decoded : [];
                        } else {
                            $r[str_replace('_json', '', $col)] = [];
                        }
                    }
                }
                unset($r);
            }
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_hardware_graphics: ' . $e->getMessage());
            return [];
        }
    }

    public function get_camera_info(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_CameraInfo($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_cameras', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['available_focal_lengths','available_effects','available_scene_modes','available_video_stabilization','available_ae_modes','available_af_modes'] as $col) {
                    if (isset($r[$col]) && is_string($r[$col])) {
                        $r[$col] = json_decode($r[$col], true) ?? [];
                    }
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_camera_info: ' . $e->getMessage());
            return [];
        }
    }

    public function get_battery_stats(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_BatteryStats($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_battery_stats', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_battery_stats: ' . $e->getMessage());
            return [];
        }
    }

    public function get_accessibility(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Accessibility($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_accessibility_services', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_accessibility: ' . $e->getMessage());
            return [];
        }
    }

    public function get_input_methods(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_InputMethods($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_input_methods', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            // Attach subtypes
            foreach ($results as &$r) {
                $subs = $this->db->table('tbl_system_input_method_subtypes')
                    ->where('input_method_id', $r['id'])
                    ->get()->getResultArray();
                $r['subtypes'] = $subs;
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_input_methods: ' . $e->getMessage());
            return [];
        }
    }

    public function get_proc_info(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_ProcInfo($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_running_processes', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['meminfo_json','cpuinfo_json','stat_json','uptime_json','net_interfaces_json','net_connections_json'] as $col) {
                    if (isset($r[$col]) && is_string($r[$col])) {
                        $r[$col] = json_decode($r[$col], true) ?? [];
                    }
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_proc_info: ' . $e->getMessage());
            return [];
        }
    }

    public function get_processes(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Processes($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_running_processes', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            // Attach process details and services
            foreach ($results as &$r) {
                $details = $this->db->table('tbl_system_running_process_details')
                    ->where('running_processes_id', $r['id'])
                    ->get()->getResultArray();
                $r['process_details'] = $details;
                $services = $this->db->table('tbl_system_running_services')
                    ->where('running_process_id', $r['id'])
                    ->get()->getResultArray();
                $r['services'] = $services;
                // Decode pkg_list_json
                if (!empty($r['pkg_list_json'])) {
                    $r['pkg_list'] = json_decode($r['pkg_list_json'], true);
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_processes: ' . $e->getMessage());
            return [];
        }
    }

    public function get_display_info(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_DisplayInfo($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_display_info', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            foreach ($results as &$r) {
                if (isset($r['displays_json']) && is_string($r['displays_json'])) {
                    $r['displays'] = json_decode($r['displays_json'], true) ?? [];
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_display_info: ' . $e->getMessage());
            return [];
        }
    }

    public function get_count_DisplayInfo(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_display_info', $user_id);
    }

    public function get_default_apps(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_DefaultApps($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $timestamps = $this->fq('tbl_system_default_apps_device', $user_id)
                ->select('extracted_at')
                ->groupBy('extracted_at')
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();

            $results = [];
            foreach ($timestamps as $ts) {
                $extractedAt = $ts['extracted_at'];
                $handlers = $this->fq('tbl_system_default_apps_device', $user_id)
                    ->where('extracted_at', $extractedAt)
                    ->get()->getResultArray();

                $handlerMap = [];
                foreach ($handlers as $h) {
                    $handlerMap[$h['handler_type']] = [
                        'package_name' => $h['package_name'] ?? '',
                        'app_name' => $h['app_name'] ?? '',
                        'is_system' => $h['is_system'] ?? 0,
                    ];
                }

                $results[] = [
                    'id' => $handlers[0]['id'] ?? 0,
                    'owner_id' => $user_id,
                    'device_id' => $handlers[0]['device_id'] ?? '',
                    'default_browser_json' => json_encode($handlerMap['browser'] ?? []),
                    'default_dialer_json' => json_encode($handlerMap['dialer'] ?? []),
                    'default_sms_json' => json_encode($handlerMap['sms'] ?? []),
                    'default_launcher_json' => json_encode($handlerMap['launcher'] ?? []),
                    'default_email_json' => json_encode($handlerMap['email'] ?? []),
                    'default_maps_json' => json_encode($handlerMap['maps'] ?? []),
                    'default_music_json' => json_encode($handlerMap['music'] ?? []),
                    'default_gallery_json' => json_encode($handlerMap['gallery'] ?? []),
                    'default_sms_package' => $handlerMap['sms_package']['package_name'] ?? null,
                    'extracted_at' => $extractedAt,
                    'created_at' => $handlers[0]['created_at'] ?? '',
                    'updated_at' => $handlers[0]['updated_at'] ?? '',
                ];
            }

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_default_apps: ' . $e->getMessage());
            return [];
        }
    }

    public function get_count_DefaultApps(int $user_id): int
    {
        return $this->getCount('tbl_system_default_apps_device', $user_id);
    }

    public function get_alarms(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Alarms($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $timestamps = $this->fq('tbl_system_alarms', $user_id)
                ->select('extracted_at')
                ->groupBy('extracted_at')
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();

            $results = [];
            foreach ($timestamps as $ts) {
                $extractedAt = $ts['extracted_at'];
                $alarms = $this->fq('tbl_system_alarms', $user_id)
                    ->where('extracted_at', $extractedAt)
                    ->get()->getResultArray();

                $scheduledJobs = [];
                $alarmClocks = [];

                foreach ($alarms as $a) {
                    if ($a['alarm_type'] === 'job') {
                        $scheduledJobs[] = [
                            'job_id' => $a['job_id'] ?? 0,
                            'service' => $a['service_class'] ?? '',
                            'package' => $a['package_name'] ?? '',
                            'is_periodic' => $a['is_periodic'] ?? 0,
                            'interval_millis' => $a['interval_millis'] ?? null,
                            'min_flex_millis' => $a['min_flex_millis'] ?? null,
                            'requires_charging' => $a['requires_charging'] ?? 0,
                            'requires_idle' => $a['requires_idle'] ?? 0,
                            'network_type' => $a['network_type'] ?? '',
                            'persisted' => $a['persisted'] ?? 0,
                            'initial_delay_millis' => $a['initial_delay_millis'] ?? null,
                            'minimum_latency_millis' => $a['minimum_latency_millis'] ?? null,
                            'important_while_foreground' => $a['important_foreground'] ?? 0,
                        ];
                    } else {
                        $alarmClocks[] = [
                            'trigger_time_millis' => $a['trigger_time'] ?? 0,
                            'trigger_time_formatted' => $a['trigger_time_formatted'] ?? '',
                            'package' => $a['package_name'] ?? '',
                        ];
                    }
                }

                $results[] = [
                    'id' => $alarms[0]['id'] ?? 0,
                    'owner_id' => $user_id,
                    'device_id' => $alarms[0]['device_id'] ?? '',
                    'scheduled_jobs_json' => json_encode($scheduledJobs),
                    'alarm_clocks_json' => json_encode($alarmClocks),
                    'job_count' => count($scheduledJobs),
                    'extracted_at' => $extractedAt,
                    'created_at' => $alarms[0]['created_at'] ?? '',
                    'updated_at' => $alarms[0]['updated_at'] ?? '',
                ];
            }

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_alarms: ' . $e->getMessage());
            return [];
        }
    }

    public function get_count_Alarms(int $user_id): int
    {
        return $this->getCount('tbl_system_alarms', $user_id);
    }

    public function search_apps(int $userId, string $query, int $limit = 0, int $offset = 0): array
    {
        $builder = $this->db->table('tbl_extracted_installed_apps')
            ->select('app_name as Name, package_name as Package, app_size as AppSize, app_icon, version_name as Version, is_system_app as IsSystem')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('app_name', $query)
            ->orLike('package_name', $query)
            ->groupEnd()
            ->orderBy('app_name', 'ASC');

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function search_apps_count(int $userId, string $query): int
    {
        return (int) $this->db->table('tbl_extracted_installed_apps')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('app_name', $query)
            ->orLike('package_name', $query)
            ->groupEnd()
            ->countAllResults(false);
    }

    public function get_device_health(int $userId): array
    {
        // Try getting device_id from location updates first (most frequent)
        $query = $this->db->table('tbl_extracted_locations')
            ->select('device_id')
            ->where('owner_id', $userId)
            ->orderBy('location_time', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        $device_id = $query ? $query->device_id : null;

        // Fallback to apps if no location data
        if (!$device_id) {
            $query = $this->db->table('tbl_extracted_installed_apps')
                ->select('device_id')
                ->where('owner_id', $userId)
                ->orderBy('updated_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRow();
            $device_id = $query ? $query->device_id : null;
        }

        if (!$device_id)
            return [];

        $profile = $this->db->table('tbl_device_profiles')
            ->where('device_id', $device_id)
            ->get()
            ->getRowArray() ?? [];

        // Get latest activity for network/battery
        $activity = $this->db->table('tbl_extracted_activities')
            ->where('owner_id', $userId)
            ->where('device_id', $device_id)
            ->orderBy('extracted_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        // Get latest access log for IP
        $log = $this->db->table('tbl_user_actions')
            ->select('ip_address, created_at')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        // Merge data
        if ($activity) {
            $profile['network_type'] = $activity['network_type'];
            $profile['battery_level'] = $activity['battery_level']; // Prefer activity battery if newer
            $profile['charging_status'] = $activity['charging_status'];
            $profile['last_activity_time'] = $activity['extracted_at'];
        }

        if ($log) {
            $profile['last_ip_address'] = $log['ip_address'];
            $profile['last_login_time'] = $log['created_at'];
        }

        return $profile;
    }

    public function get_app_privacy_audit(int $userId): array
    {
        $apps = $this->db->table('tbl_extracted_installed_apps')
            ->select('app_name, package_name, permissions, app_icon, version_name, version_code, app_size, permission_count, target_sdk, min_sdk, first_install_time, last_update_time, is_system_app')
            ->where('owner_id', $userId)
            ->get()
            ->getResultArray();

        $audit = [];

        foreach ($apps as $app) {
            $perms = explode(',', $app['permissions']);
            $score = 0;
            $risks = [];

            $hasInternet = in_array('android.permission.INTERNET', $perms);
            $hasSms = in_array('android.permission.READ_SMS', $perms) || in_array('android.permission.RECEIVE_SMS', $perms) || in_array('android.permission.SEND_SMS', $perms);
            $hasAudio = in_array('android.permission.RECORD_AUDIO', $perms);
            $hasCamera = in_array('android.permission.CAMERA', $perms);
            $hasLocation = in_array('android.permission.ACCESS_FINE_LOCATION', $perms) || in_array('android.permission.ACCESS_COARSE_LOCATION', $perms) || in_array('android.permission.ACCESS_BACKGROUND_LOCATION', $perms);

            if ($hasSms) {
                $score += 3;
                $risks[] = 'Reads/Sends Private Messages';
            }
            if ($hasLocation) {
                $score += 2;
            }
            if ($hasAudio || $hasCamera) {
                $score += 2;
            }

            // Dangerous Combos
            if ($hasSms && $hasInternet) {
                $score += 5;
                $risks[] = 'Data Exfiltration Risk (SMS + Internet)';
            }
            if ($hasAudio && $hasCamera) {
                $score += 4;
                $risks[] = 'Privacy Intrusion (Microphone + Camera)';
            }
            if (in_array('android.permission.ACCESS_FINE_LOCATION', $perms)) {
                $score += 3;
                $risks[] = 'Movement Tracking (Fine LocationController)';
            }

            // Suspicious App Check
            if (preg_match('/spy|tracker|hack|cheat|monitor|stealth/i', $app['package_name'])) {
                $score += 8;
                $risks[] = 'Suspicious App Signature (Spyware/Tracker)';
            }

            if ($score > 0) {
                $audit[] = [
                    'name' => $app['app_name'],
                    'package' => $app['package_name'],
                    'score' => $score,
                    'risks' => $risks,
                    'app_data' => $app // Pass the full app data for the modal
                ];
            }
        }

        usort($audit, fn($a, $b) => $b['score'] <=> $a['score']);
        return $audit;
    }

    public function get_app_category_dist(int $userId): array
    {
        $apps = $this->db->table('tbl_extracted_installed_apps')
            ->select('package_name, is_system_app')
            ->where('owner_id', $userId)
            ->get()
            ->getResultArray();

        $dist = [
            'Social & Communication' => 0,
            'Finance & Banking' => 0,
            'Entertainment & Media' => 0,
            'Productivity & Work' => 0,
            'Tools & Utilities' => 0,
            'System & Core' => 0,
            'Shopping & Lifestyle' => 0,
            'Other' => 0
        ];

        foreach ($apps as $app) {
            if ($app['is_system_app']) {
                $dist['System & Core']++;
                continue;
            }

            $pkg = strtolower($app['package_name']);
            if (preg_match('/whatsapp|facebook|instagram|tiktok|twitter|linkedin|snapchat|telegram|messenger|discord|viber|skype/i', $pkg)) {
                $dist['Social & Communication']++;
            } else if (preg_match('/bank|kcb|equity|mcoop|pay|binance|stripe|paypal|wallet|crypto|mpesa|ncba|stanchart|absa/i', $pkg)) {
                $dist['Finance & Banking']++;
            } else if (preg_match('/netflix|youtube|spotify|music|player|video|games|sport|bet|tv|media/i', $pkg)) {
                $dist['Entertainment & Media']++;
            } else if (preg_match('/office|mail|calendar|slack|note|drive|zoom|teams|meet|docs|pdf|word|excel/i', $pkg)) {
                $dist['Productivity & Work']++;
            } else if (preg_match('/cleaner|antivirus|browser|launcher|tool|vpn|keyboard|filemanager|share/i', $pkg)) {
                $dist['Tools & Utilities']++;
            } else if (preg_match('/shop|amazon|jumia|alibaba|glovo|uber|bolt|food|health|fitness/i', $pkg)) {
                $dist['Shopping & Lifestyle']++;
            } else {
                $dist['Other']++;
            }
        }

        // Clean up empty categories to make charts look better
        return array_filter($dist, fn($val) => $val > 0);
    }

    public function delete_app(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_installed_apps')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_app error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_device_context_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_device_hardware_contexts')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_device_context_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_network_info_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_network_info')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_network_info_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_accounts_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_accounts')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_accounts_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_proc_info_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_running_processes')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_proc_info_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_processes_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_running_processes')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_processes_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_security_audit_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_security_audit')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_security_audit_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_accessibility_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_accessibility_services')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_accessibility_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_input_methods_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_input_methods')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_input_methods_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_display_info_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_display_info')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_display_info_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_default_apps_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_default_apps_device')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_default_apps_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_alarms_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_alarms')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_alarms_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_HardwareNetwork(int $user_id): int
    {
        return $this->getCount('tbl_hardware_network', $user_id);
    }

    public function get_hardware_network(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_HardwareNetwork($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_hardware_network', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['network_interfaces' => 'network_interfaces_json', 'proc_net_dev' => 'proc_net_dev_json', 'link_properties' => 'link_properties_json', 'arp_cache' => 'arp_cache_json', 'wifi_passpoint' => 'wifi_passpoint_json'] as $key => $field) {
                    $r[$key] = json_decode($r[$field] ?? '{}', true);
                }
                $r['ts_display'] = $r['extracted_at'] ? date('Y-m-d H:i:s', (int)$r['extracted_at']) : '';
            }
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_hardware_network: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_hardware_network_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_hardware_network')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_hardware_network_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_AppSecurity(int $user_id): int
    {
        return $this->getCount('tbl_system_app_security', $user_id);
    }

    public function get_app_security(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_AppSecurity($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_app_security', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                $r['ts_display'] = $r['extracted_at'] ? date('Y-m-d H:i:s', (int)$r['extracted_at']) : '';
            }
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_app_security: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_app_security_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_app_security')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_app_security_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_NetworkSecurity(int $user_id): int
    {
        return $this->getCount('tbl_network_security', $user_id);
    }

    public function get_network_security(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_NetworkSecurity($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_network_security', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['dns_config' => 'dns_config_json', 'vpn_config' => 'vpn_config_json'] as $key => $field) {
                    $r[$key] = json_decode($r[$field] ?? '{}', true);
                }
                $r['ts_display'] = $r['extracted_at'] ? date('Y-m-d H:i:s', (int)$r['extracted_at']) : '';
            }
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_network_security: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_network_security_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_network_security')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_network_security_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_TelephonyNetwork(int $user_id): int
    {
        return $this->getCount('tbl_telephony_network', $user_id);
    }

    public function get_telephony_network(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_TelephonyNetwork($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telephony_network', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['ims_volte' => 'ims_volte_json', 'data_roaming' => 'data_roaming_json'] as $key => $field) {
                    $r[$key] = json_decode($r[$field] ?? '{}', true);
                }
                $r['ts_display'] = $r['extracted_at'] ? date('Y-m-d H:i:s', (int)$r['extracted_at']) : '';
            }
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_telephony_network: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_telephony_network_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telephony_network')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_telephony_network_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_SystemLocale(int $user_id): int
    {
        return $this->getCount('tbl_system_locale', $user_id);
    }

    public function get_system_locale(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_SystemLocale($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_locale', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['locale_region' => 'locale_region_json', 'system_fonts' => 'system_fonts_json'] as $key => $field) {
                    $r[$key] = json_decode($r[$field] ?? '{}', true);
                }
                $r['ts_display'] = $r['extracted_at'] ? date('Y-m-d H:i:s', (int)$r['extracted_at']) : '';
            }
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_system_locale: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_system_locale_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_locale')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_system_locale_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_AudioDevices(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_audio_devices', $user_id);
    }

    public function get_audio_devices(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_AudioDevices($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_audio_devices', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['sample_rates', 'channel_masks', 'channel_counts'] as $key) {
                    $r[$key] = $r[$key] ?? null;
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_audio_devices: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_audio_devices_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_audio_devices')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_audio_devices_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_Biometric(int $user_id): int
    {
        return $this->getCount('tbl_biometric', $user_id);
    }

    public function get_biometric(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Biometric($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_biometric', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                $r['enrolled_users'] = is_string($r['enrolled_users'] ?? null) ? json_decode($r['enrolled_users'], true) : ($r['enrolled_users'] ?? []);
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_biometric: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_biometric_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_biometric')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_biometric_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_GnssHardware(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_gnss_hardware', $user_id);
    }

    public function get_gnss_hardware(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_GnssHardware($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_gnss_hardware', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['constellations_supported', 'frequencies_supported', 'antenna_info', 'measurement_capabilities'] as $key) {
                    $r[$key] = is_string($r[$key] ?? null) ? json_decode($r[$key], true) : ($r[$key] ?? []);
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_gnss_hardware: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_gnss_hardware_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_gnss_hardware')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_gnss_hardware_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_PowerRails(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_power_rails', $user_id);
    }

    public function get_power_rails(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_PowerRails($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_power_rails', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['constraints', 'consumer_names'] as $key) {
                    $r[$key] = is_string($r[$key] ?? null) ? json_decode($r[$key], true) : ($r[$key] ?? []);
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_power_rails: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_power_rails_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_power_rails')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_power_rails_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_UsbDevices(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_usb_devices', $user_id);
    }

    public function get_usb_devices(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_UsbDevices($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_usb_devices', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_usb_devices: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_usb_devices_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_usb_devices')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_usb_devices_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_Vibration(int $user_id): int
    {
        return $this->getCount('tbl_telemetry_vibration', $user_id);
    }

    public function get_vibration(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Vibration($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_telemetry_vibration', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['primitives', 'frequency_range_hz', 'composite_primitives'] as $key) {
                    $r[$key] = is_string($r[$key] ?? null) ? json_decode($r[$key], true) : ($r[$key] ?? []);
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_vibration: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_vibration_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_vibration')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_vibration_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_AppPermissions(int $user_id): int
    {
        return $this->getCount('tbl_system_app_permissions', $user_id);
    }

    public function get_app_permissions(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_AppPermissions($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_app_permissions', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_app_permissions: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_app_permissions_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_app_permissions')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_app_permissions_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_CrashLogs(int $user_id): int
    {
        return $this->getCount('tbl_system_crash_logs', $user_id);
    }

    public function get_crash_logs(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_CrashLogs($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_crash_logs', $user_id)
                ->orderBy('crash_time', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_crash_logs: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_crash_logs_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_crash_logs')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_crash_logs_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_DozeStandby(int $user_id): int
    {
        return $this->getCount('tbl_system_doze_standby', $user_id);
    }

    public function get_doze_standby(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_DozeStandby($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_doze_standby', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                $r['apps'] = $this->db->table('tbl_system_doze_standby_apps')
                    ->where('owner_id', $user_id)
                    ->where('doze_id', $r['id'])
                    ->orderBy('whitelisted', 'DESC')
                    ->get()->getResultArray();
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_doze_standby: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_doze_standby_row(int $id, int $userId): bool
    {
        try {
            $this->db->table('tbl_system_doze_standby_apps')->where('doze_id', $id)->where('owner_id', $userId)->delete();
            return (bool) $this->db->table('tbl_system_doze_standby')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_doze_standby_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_EmailAccounts(int $user_id): int
    {
        return $this->getCount('tbl_extracted_email_accounts', $user_id);
    }

    public function get_email_accounts(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_EmailAccounts($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_extracted_email_accounts', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_email_accounts: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_email_accounts_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_email_accounts')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_email_accounts_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_KeyboardInput(int $user_id): int
    {
        return $this->getCount('tbl_keyboard_input', $user_id);
    }

    public function get_keyboard_input(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_KeyboardInput($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_keyboard_input', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                $r['subtypes'] = is_string($r['subtypes'] ?? null) ? json_decode($r['subtypes'], true) : ($r['subtypes'] ?? []);
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_keyboard_input: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_keyboard_input_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_keyboard_input')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_keyboard_input_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_KeyguardEvents(int $user_id): int
    {
        return $this->getCount('tbl_system_keyguard_events', $user_id);
    }

    public function get_keyguard_events(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_KeyguardEvents($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_keyguard_events', $user_id)
                ->orderBy('timestamp', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_keyguard_events: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_keyguard_events_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_keyguard_events')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_keyguard_events_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_VpnConfig(int $user_id): int
    {
        return $this->getCount('tbl_vpn_config', $user_id);
    }

    public function get_vpn_config(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_VpnConfig($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_vpn_config', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['vpn_dns_servers', 'vpn_routes', 'vpn_apps', 'vpn_dns_search_domains', 'vpn_excluded_apps', 'vpn_included_apps'] as $key) {
                    $r[$key] = is_string($r[$key] ?? null) ? json_decode($r[$key], true) : ($r[$key] ?? []);
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_vpn_config: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_vpn_config_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_vpn_config')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_vpn_config_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_RunningProcessesDetailed(int $user_id): int
    {
        return $this->getCount('tbl_system_running_processes_detailed', $user_id);
    }

    public function get_running_processes_detailed(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_RunningProcessesDetailed($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_running_processes_detailed', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['groups', 'env_vars', 'wake_channels', 'open_files', 'memory_maps', 'stack_trace', 'capabilities_eff', 'capabilities_prm', 'capabilities_inh', 'capabilities_bnd', 'capabilities_amb'] as $key) {
                    $r[$key] = is_string($r[$key] ?? null) ? json_decode($r[$key], true) : ($r[$key] ?? null);
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_running_processes_detailed: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_running_processes_detailed_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_running_processes_detailed')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_running_processes_detailed_row: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_battery_stats_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_battery_stats')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_battery_stats_row: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_camera_info_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_telemetry_cameras')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_camera_info_row: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_device_profile_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_device_profiles')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_device_profile_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_ScreenState(int $user_id): int
    {
        return $this->cq('tbl_screen_state', $user_id);
    }

    public function get_count_Screenshots(int $user_id): int
    {
        return $this->cq('tbl_extracted_screenshots', $user_id);
    }

    public function get_count_DigitalWellbeing(int $user_id): int
    {
        return $this->cq('tbl_system_digital_wellbeing', $user_id);
    }

    public function get_count_ContentProviders(int $user_id): int
    {
        return $this->cq('tbl_content_providers', $user_id);
    }

    public function get_screen_state(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_ScreenState($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_screen_state', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_screen_state: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_screen_state_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_screen_state')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_screen_state_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_screenshots(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Screenshots($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_extracted_screenshots', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_screenshots: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_screenshots_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_screenshots')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_screenshots_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_digital_wellbeing(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_DigitalWellbeing($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_digital_wellbeing', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_digital_wellbeing: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_digital_wellbeing_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_digital_wellbeing')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_digital_wellbeing_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_content_providers(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_ContentProviders($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_content_providers', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_content_providers: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_content_providers_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_content_providers')->where('id', $id)->where('owner_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_content_providers_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_app_usage_grouped(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_app_usage_packages($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            
            $results = $this->fq('tbl_system_app_usage', $user_id)
                ->select('package_name, app_name, is_system_app, SUM(foreground_time_ms) as foreground_time_ms, MAX(last_time_used) as last_time_used, COUNT(*) as snapshot_count')
                ->groupBy('package_name, app_name, is_system_app')
                ->orderBy('last_time_used', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
                
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_app_usage_grouped: ' . $e->getMessage());
            return [];
        }
    }

    public function get_count_app_usage_packages(int $user_id): int
    {
        try {
            $row = $this->fq('tbl_system_app_usage', $user_id)
                ->select('COUNT(DISTINCT(package_name)) as total')
                ->get()->getRowArray();
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            log_message('error', 'get_count_app_usage_packages: ' . $e->getMessage());
            return 0;
        }
    }
}
