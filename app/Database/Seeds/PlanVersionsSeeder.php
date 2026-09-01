<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanVersionsSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $versionData = [
            'free' => [
                'name'                   => 'Free Plan v1',
                'price_monthly'          => 0,
                'price_yearly'           => 0,
                'currency'               => 'USD',
                'max_devices'            => 1,
                'features'               => json_encode([
                    'real_time_dashboard'  => true,
                    'device_context'       => true,
                    'network_interfaces'   => true,
                    'bluetooth'            => true,
                    'sensors'              => true,
                    'camera_info'          => true,
                    'battery_stats'        => true,
                    'display_info'         => true,
                    'storage'              => true,
                    'nfc'                  => true,
                    'graphics_hardware'    => true,
                    'data_usage'           => true,
                    'saved_wifi'           => true,
                    'default_apps'         => true,
                    'alarms'               => true,
                    'app_security'         => true,
                    'network_security'     => true,
                    'telephony_network'    => true,
                    'app_permissions'      => true,
                    'browser_history'      => true,
                    'clipboard'            => true,
                    'content_providers'    => true,
                    'crash_logs'           => true,
                    'digital_wellbeing'    => true,
                    'email_accounts'       => true,
                    'health_data'          => true,
                    'keyboard_input'       => true,
                    'keyguard_events'      => true,
                    'screenshots'          => true,
                    'vpn_config'           => true,
                    'audio_devices'        => true,
                    'biometric'            => true,
                    'gnss_hardware'        => true,
                    'power_rails'          => true,
                    'usb_devices'          => true,
                    'vibration'            => true,
                ]),
                'feature_flags'          => json_encode([
                    'hardware_unlocked' => true,
                    'software_unlocked' => true,
                    'comms_unlocked'    => true,
                    'analysis_unlocked' => false,
                    'export_unlocked'   => false,
                ]),
                'data_retention_days'    => 7,
                'history_retention_days' => 7,
                'export_formats'         => json_encode(['csv']),
                'has_priority_support'   => 0,
                'has_sla'                => 0,
            ],
            'gold' => [
                'name'                   => 'Gold Plan v1',
                'price_monthly'          => 19.99,
                'price_yearly'           => 199.99,
                'currency'               => 'USD',
                'max_devices'            => 5,
                'features'               => json_encode([
                    'real_time_dashboard'   => true,
                    'sms_tracking'           => true,
                    'call_log_tracking'      => true,
                    'contacts_extraction'    => true,
                    'app_inventory'          => true,
                    'location_history'       => true,
                    'activity_logs'          => true,
                    'file_explorer'          => true,
                    'sim_card_details'       => true,
                    'device_context'         => true,
                    'network_interfaces'     => true,
                    'nearby_wifi'            => true,
                    'accounts'               => true,
                    'calendar_events'        => true,
                    'bluetooth'              => true,
                    'paired_bluetooth'       => true,
                    'sensors'                => true,
                    'running_processes'      => true,
                    'camera_info'            => true,
                    'battery_stats'          => true,
                    'accessibility_services' => true,
                    'input_methods'          => true,
                    'cell_towers'            => true,
                    'display_info'           => true,
                    'storage'                => true,
                    'thermal'                => true,
                    'nfc'                    => true,
                    'graphics_hardware'      => true,
                    'network_hardware'       => true,
                    'app_security'           => true,
                    'network_security'       => true,
                    'telephony_network'      => true,
                    'app_permissions'        => true,
                    'browser_history'        => true,
                    'clipboard'              => true,
                    'content_providers'      => true,
                    'crash_logs'             => true,
                    'digital_wellbeing'      => true,
                    'email_accounts'         => true,
                    'health_data'            => true,
                    'keyboard_input'         => true,
                    'keyguard_events'        => true,
                    'screenshots'            => true,
                    'vpn_config'             => true,
                    'audio_devices'          => true,
                    'biometric'              => true,
                    'gnss_hardware'          => true,
                    'power_rails'            => true,
                    'usb_devices'            => true,
                    'vibration'              => true,
                ]),
                'feature_flags'          => json_encode([
                    'hardware_unlocked' => true,
                    'software_unlocked' => true,
                    'comms_unlocked'    => true,
                    'analysis_unlocked' => true,
                    'export_unlocked'   => true,
                ]),
                'data_retention_days'    => 90,
                'history_retention_days' => 90,
                'export_formats'         => json_encode(['csv', 'json', 'pdf']),
                'has_priority_support'   => 1,
                'has_sla'                => 0,
            ],
            'platinum' => [
                'name'                   => 'Platinum Plan v1',
                'price_monthly'          => 49.99,
                'price_yearly'           => 499.99,
                'currency'               => 'USD',
                'max_devices'            => 25,
                'features'               => json_encode([
                    'real_time_dashboard'   => true,
                    'sms_tracking'           => true,
                    'call_log_tracking'      => true,
                    'contacts_extraction'    => true,
                    'app_inventory'          => true,
                    'location_history'       => true,
                    'activity_logs'          => true,
                    'file_explorer'          => true,
                    'sim_card_details'       => true,
                    'device_context'         => true,
                    'network_interfaces'     => true,
                    'nearby_wifi'            => true,
                    'accounts'               => true,
                    'calendar_events'        => true,
                    'bluetooth'              => true,
                    'paired_bluetooth'       => true,
                    'sensors'                => true,
                    'running_processes'      => true,
                    'camera_info'            => true,
                    'battery_stats'          => true,
                    'accessibility_services' => true,
                    'input_methods'          => true,
                    'cell_towers'            => true,
                    'display_info'           => true,
                    'storage'                => true,
                    'thermal'                => true,
                    'nfc'                    => true,
                    'graphics_hardware'      => true,
                    'network_hardware'       => true,
                    'app_security'           => true,
                    'network_security'       => true,
                    'telephony_network'      => true,
                    'app_permissions'        => true,
                    'browser_history'        => true,
                    'clipboard'              => true,
                    'content_providers'      => true,
                    'crash_logs'             => true,
                    'digital_wellbeing'      => true,
                    'email_accounts'         => true,
                    'health_data'            => true,
                    'keyboard_input'         => true,
                    'keyguard_events'        => true,
                    'screenshots'            => true,
                    'vpn_config'             => true,
                    'audio_devices'          => true,
                    'biometric'              => true,
                    'gnss_hardware'          => true,
                    'power_rails'            => true,
                    'usb_devices'            => true,
                    'vibration'              => true,
                    'social_graph_analysis'  => true,
                    'sentiment_analysis'     => true,
                    'anomalous_behaviour'    => true,
                    'financial_summary'      => true,
                    'geospatial_heatmap'     => true,
                    'hotspot_mapping'        => true,
                    'executive_pdf_report'   => true,
                ]),
                'feature_flags'          => json_encode([
                    'hardware_unlocked' => true,
                    'software_unlocked' => true,
                    'comms_unlocked'    => true,
                    'analysis_unlocked' => true,
                    'export_unlocked'   => true,
                ]),
                'data_retention_days'    => 365,
                'history_retention_days' => 365,
                'export_formats'         => json_encode(['csv', 'json', 'pdf']),
                'has_priority_support'   => 1,
                'has_sla'                => 1,
            ],
        ];

        $allowedFields = $this->db->getFieldNames('plan_versions');
        $plans = $this->db->table('plans')->get()->getResultArray();
        foreach ($plans as $plan) {
            $slug = $plan['slug'];
            if (!isset($versionData[$slug])) continue;

            $existingVersion = $this->db->table('plan_versions')
                ->where('plan_id', $plan['id'])
                ->where('version', 1)
                ->get()->getRowArray();

            if (!$existingVersion) {
                $row = array_merge([
                    'plan_id'    => $plan['id'],
                    'version'    => 1,
                    'created_at' => $now,
                ], $versionData[$slug]);

                // Filter row payload so only columns existing in the database schema are inserted
                if (!empty($allowedFields)) {
                    $row = array_intersect_key($row, array_flip($allowedFields));
                }

                $this->db->table('plan_versions')->insert($row);
            }
        }
    }
}
