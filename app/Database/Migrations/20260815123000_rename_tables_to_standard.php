<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameTablesToStandard extends Migration
{
    private array $renameRules = [
        'tbl_device_profile'          => 'tbl_device_profiles',
        'tbl_devices'                 => 'tbl_devices_raw',
        'tbl_device_config'           => 'tbl_device_configs',
        'tbl_device_context'          => 'tbl_device_hardware_contexts',
        'device_risk'                 => 'tbl_device_risk_scores',
        'device_risk_history'         => 'tbl_device_risk_history',
        'tbl_sms'                     => 'tbl_extracted_sms',
        'tbl_logs'                    => 'tbl_extracted_call_logs',
        'tbl_contacts'                => 'tbl_extracted_contacts',
        'tbl_apps'                    => 'tbl_extracted_installed_apps',
        'tbl_location'                => 'tbl_extracted_locations',
        'tbl_activity'                => 'tbl_extracted_activities',
        'tbl_notifications'           => 'tbl_extracted_notifications',
        'tbl_ui_scrape'               => 'tbl_extracted_ui_scrapes',
        'tbl_browser_history'         => 'tbl_extracted_browser_history',
        'tbl_clipboard'               => 'tbl_extracted_clipboard_entries',
        'tbl_calendar_events'         => 'tbl_extracted_calendar_events',
        'tbl_email_accounts'          => 'tbl_extracted_email_accounts',
        'tbl_captured_media'          => 'tbl_extracted_media_files',
        'tbl_screenshots'             => 'tbl_extracted_screenshots',
        'tbl_device_files'            => 'tbl_extracted_device_files',
        'tbl_battery_stats'           => 'tbl_telemetry_battery_stats',
        'tbl_bluetooth'               => 'tbl_telemetry_bluetooth_devices',
        'tbl_bluetooth_paired'        => 'tbl_telemetry_bluetooth_devices_paired',
        'tbl_saved_wifi'              => 'tbl_telemetry_wifi_networks_saved',
        'tbl_nearby_wifi'             => 'tbl_telemetry_wifi_networks_nearby',
        'tbl_cell_towers'             => 'tbl_telemetry_cell_towers',
        'tbl_sensor_profile'          => 'tbl_telemetry_sensors',
        'tbl_camera_info'             => 'tbl_telemetry_cameras',
        'tbl_storage'                 => 'tbl_telemetry_storage_stats',
        'tbl_display_info'            => 'tbl_telemetry_display_info',
        'tbl_audio_devices'           => 'tbl_telemetry_audio_devices',
        'tbl_nfc'                     => 'tbl_telemetry_nfc',
        'tbl_power_rails'             => 'tbl_telemetry_power_rails',
        'tbl_thermal'                 => 'tbl_telemetry_thermal',
        'tbl_usb_devices'             => 'tbl_telemetry_usb_devices',
        'tbl_vibration'               => 'tbl_telemetry_vibration',
        'tbl_gnss_hardware'           => 'tbl_telemetry_gnss_hardware',
        'tbl_accessibility_services'  => 'tbl_system_accessibility_services',
        'tbl_alarms'                  => 'tbl_system_alarms',
        'tbl_app_defaults'            => 'tbl_system_default_apps',
        'tbl_default_apps'            => 'tbl_system_default_apps_device',
        'tbl_app_permissions'         => 'tbl_system_app_permissions',
        'tbl_app_security'            => 'tbl_system_app_security',
        'tbl_app_usage'               => 'tbl_system_app_usage',
        'tbl_app_usage_sessions'      => 'tbl_system_app_usage_sessions',
        'tbl_crash_logs'              => 'tbl_system_crash_logs',
        'tbl_digital_wellbeing'       => 'tbl_system_digital_wellbeing',
        'tbl_digital_wellbeing_apps'  => 'tbl_system_digital_wellbeing_apps',
        'tbl_doze_standby'            => 'tbl_system_doze_standby',
        'tbl_doze_standby_apps'       => 'tbl_system_doze_standby_apps',
        'tbl_keyguard_events'         => 'tbl_system_keyguard_events',
        'tbl_network_info'            => 'tbl_system_network_info',
        'tbl_proc_info'               => 'tbl_system_running_processes',
        'tbl_input_methods'           => 'tbl_system_input_methods',
        'tbl_input_method_subtypes'   => 'tbl_system_input_method_subtypes',
        'tbl_running_processes_detailed' => 'tbl_system_running_processes_detailed',
        'tbl_running_process_details' => 'tbl_system_running_process_details',
        'tbl_running_services'        => 'tbl_system_running_services',
        'upload_queue'                => 'tbl_upload_queue',
        'uploaded_files'              => 'tbl_uploaded_files',
        'tbl_tokens'                  => 'tbl_user_api_tokens',
        'tbl_blocklist'               => 'tbl_user_blocklists',
        'tbl_interactions'            => 'tbl_user_interactions',
    ];

    public function up()
    {
        // 1. Rename existing tables
        foreach ($this->renameRules as $oldName => $newName) {
            if ($this->db->tableExists($oldName) && !$this->db->tableExists($newName)) {
                $this->forge->renameTable($oldName, $newName);
            }
        }

        // 2. Drop legacy duplicates/test tables
        $this->forge->dropTable('tbl_tokentest', true);
        $this->forge->dropTable('tbl_uploaded', true);
    }

    public function down()
    {
        // Revert renaming
        foreach ($this->renameRules as $oldName => $newName) {
            if ($this->db->tableExists($newName) && !$this->db->tableExists($oldName)) {
                $this->forge->renameTable($newName, $oldName);
            }
        }
    }
}
