<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanDefinitionsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        $features = [
            // ── FCM Commands ──
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_fetch_contacts',
                'label'         => 'Fetch Contacts',
                'description'   => 'Extract device phonebook contacts',
                'icon'          => 'fas fa-address-book',
                'color_class'   => 'card-success',
                'bg_class'      => 'bg-success',
                'required_tier' => 'free',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_cmd_beep',
                'label'         => 'Test Beep',
                'description'   => 'Trigger audible test alert beep on device',
                'icon'          => 'fas fa-volume-up',
                'color_class'   => 'card-success',
                'bg_class'      => 'bg-success',
                'required_tier' => 'free',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_cmd_health',
                'label'         => 'Check Device Health',
                'description'   => 'Fetch real-time hardware diagnostics (battery, screen, network)',
                'icon'          => 'fas fa-heartbeat',
                'color_class'   => 'card-success',
                'bg_class'      => 'bg-success',
                'required_tier' => 'free',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_cmd_reset_app',
                'label'         => 'Reset App Command',
                'description'   => 'Allow remote application reset to defaults',
                'icon'          => 'fas fa-sync-alt',
                'color_class'   => 'card-success',
                'bg_class'      => 'bg-success',
                'required_tier' => 'free',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_fetch_apps',
                'label'         => 'Fetch Installed Apps',
                'description'   => 'Extract list of installed applications',
                'icon'          => 'fas fa-th-list',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_fetch_calls',
                'label'         => 'Fetch Call Logs',
                'description'   => 'Extract call log database',
                'icon'          => 'fas fa-phone',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_fetch_sms',
                'label'         => 'Fetch SMS Logs',
                'description'   => 'Extract text message database',
                'icon'          => 'fas fa-sms',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_fetch_location',
                'label'         => 'Fetch Location & Activity',
                'description'   => 'Track GPS coordinates and user motion activity',
                'icon'          => 'fas fa-map-marker-alt',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_fetch_usage',
                'label'         => 'Fetch App Usage & Notifications',
                'description'   => 'Extract package screen time and notification streams',
                'icon'          => 'fas fa-chart-line',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_cmd_deactivate',
                'label'         => 'Deactivate Command',
                'description'   => 'Allow remote lock and stealth dummy deactivation',
                'icon'          => 'fas fa-lock',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_cmd_camera',
                'label'         => 'Remote Camera Capture',
                'description'   => 'Command remote camera snapshot',
                'icon'          => 'fas fa-camera',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_cmd_audio',
                'label'         => 'Remote Audio Record',
                'description'   => 'Command remote microphone audio clip recording',
                'icon'          => 'fas fa-microphone',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_fetch_files',
                'label'         => 'Fetch Files',
                'description'   => 'Browse and extract device filesystem files',
                'icon'          => 'fas fa-file-download',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_fetch_soft_misc',
                'label'         => 'Fetch Misc Software Details',
                'description'   => 'Extract accounts, calendar, and clipboard data',
                'icon'          => 'fas fa-paste',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_fetch_hard_misc',
                'label'         => 'Fetch Misc Hardware Details',
                'description'   => 'Extract bluetooth devices, sensors, and thermal metrics',
                'icon'          => 'fas fa-microchip',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_fetch_all',
                'label'         => 'Sync All Data',
                'description'   => 'Execute complete device extraction backup sync',
                'icon'          => 'fas fa-sync',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_cmd_logout',
                'label'         => 'Logout User Command',
                'description'   => 'Allow remote user log out and session clear',
                'icon'          => 'fas fa-sign-out-alt',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_cmd_uninstall_preserve',
                'label'         => 'Uninstall & Preserve Command',
                'description'   => 'Allow remote uninstall but keep backup data',
                'icon'          => 'fas fa-trash-alt',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_cmd_uninstall_wipe',
                'label'         => 'Uninstall & Wipe Command',
                'description'   => 'Allow remote uninstall with complete data wipe',
                'icon'          => 'fas fa-eraser',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'fcm',
                'slug'          => 'fcm_file_management',
                'label'         => 'FCM: File Management',
                'description'   => 'Download or permanently delete a specific file directly from the device',
                'icon'          => 'fas fa-folder-open',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],

            // ── ML Algorithms ──
            [
                'category_type' => 'ml',
                'slug'          => 'sms_bert',
                'label'         => 'SMS Phishing Heuristic',
                'description'   => 'Heuristic keyword scan of SMS message bodies for phishing indicators',
                'icon'          => 'fas fa-shield-alt',
                'color_class'   => 'card-success',
                'bg_class'      => 'bg-success',
                'required_tier' => 'free',
            ],
            [
                'category_type' => 'ml',
                'slug'          => 'files_entropy',
                'label'         => 'File Metadata Scanner',
                'description'   => 'Flags files whose metadata suggests encrypted payloads or hidden executables',
                'icon'          => 'fas fa-file-medical-alt',
                'color_class'   => 'card-success',
                'bg_class'      => 'bg-success',
                'required_tier' => 'free',
            ],
            [
                'category_type' => 'ml',
                'slug'          => 'contacts_graph',
                'label'         => 'Contact Graph Outlier Model',
                'description'   => 'Flags orphaned and low-connectivity nodes in contacts network',
                'icon'          => 'fas fa-project-diagram',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'ml',
                'slug'          => 'calls_isolation',
                'label'         => 'Call Logs Isolation Forest',
                'description'   => 'Applies Isolation Forest to multidimensional call attributes',
                'icon'          => 'fas fa-tree',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'ml',
                'slug'          => 'apps_autoencoder',
                'label'         => 'App Manifest Anomaly Scanner',
                'description'   => 'Uses PCA reconstruction error to find apps with abnormal configurations',
                'icon'          => 'fas fa-code-branch',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'ml',
                'slug'          => 'act_lstm',
                'label'         => 'Activity Sequence Predictor',
                'description'   => 'MLP on app-usage timestamps to model normal activity rhythms',
                'icon'          => 'fas fa-clock',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'ml',
                'slug'          => 'dev_oneclass',
                'label'         => 'One-Class SVM Profiler',
                'description'   => 'Models normal operational bounds of CPU, RAM, battery, active radios',
                'icon'          => 'fas fa-brain',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],
        ];

        foreach ($features as $f) {
            $exists = $db->table('tbl_feature_tiers')
                ->where('slug', $f['slug'])
                ->get()
                ->getRowArray();

            if (!$exists) {
                $db->table('tbl_feature_tiers')->insert($f);
            } else {
                $db->table('tbl_feature_tiers')
                    ->where('slug', $f['slug'])
                    ->update($f);
            }
        }
    }
}
