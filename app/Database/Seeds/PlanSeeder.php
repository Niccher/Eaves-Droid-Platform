<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // PlansController
        $plans = [
            [
                'slug'        => 'free',
                'name'        => 'Free',
                'description' => 'For getting started with one device',
                'is_active'   => 1,
                'sort_order'  => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'slug'        => 'gold',
                'name'        => 'Gold',
                'description' => 'For monitoring a small circle of devices',
                'is_active'   => 1,
                'sort_order'  => 2,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'slug'        => 'platinum',
                'name'        => 'Platinum',
                'description' => 'Full intelligence for you and your whole family',
                'is_active'   => 1,
                'sort_order'  => 3,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        foreach ($plans as $plan) {
            // Check if plan already exists
            $existing = $this->db->table('plans')
                ->where('slug', $plan['slug'])
                ->get()->getRowArray();

            if (!$existing) {
                $this->db->table('plans')->insert($plan);
                $planId = $this->db->insertID();
            } else {
                $planId = $existing['id'];
            }

            // Check if version 1 already exists
            $existingVersion = $this->db->table('plan_versions')
                ->where('plan_id', $planId)
                ->where('version', 1)
                ->get()->getRowArray();

            if (!$existingVersion) {
                $this->createVersion1($planId, $plan['slug'], $now);
            }
        }
    }

    private function createVersion1(int $planId, string $slug, string $now): void
    {
        $versions = [
            'free' => [
                'price_monthly_cents' => 0,
                'price_yearly_cents'  => 0,
                'max_devices'         => 1,
                'history_days'        => 10,
                'features'            => json_encode([
                    'risk_score'           => false,
                    'geofencing'           => false,
                    'push_notifications'   => false,
                    'forensic_export'      => true,
                    'wellbeing_summary_days' => 0,
                    'smart_timeline'       => true,
                    'correlation'          => false,
                    'care_plan'            => false,
                    
                    // FCM Data Fetch Actions (Free DefaultsController)
                    'fcm_fetch_contacts' => true,
                    'fcm_cmd_beep'       => true,
                    'fcm_cmd_health'     => true,
                    'fcm_fetch_apps'     => false,
                    'fcm_fetch_calls'    => false,
                    'fcm_fetch_sms'      => false,
                    'fcm_fetch_location' => false,
                    'fcm_fetch_usage'    => false,
                    'fcm_cmd_camera'     => false,
                    'fcm_cmd_audio'      => false,
                    'fcm_fetch_files'    => false,
                    'fcm_fetch_soft_misc' => false,
                    'fcm_fetch_hard_misc' => false,
                    'fcm_fetch_all'      => false,
                    
                    // FCM Device Management Actions (Free DefaultsController)
                    'fcm_cmd_reset_app'          => true,
                    'fcm_cmd_deactivate'         => false,
                    'fcm_cmd_logout'             => false,
                    'fcm_cmd_uninstall_preserve' => false,
                    'fcm_cmd_uninstall_wipe'     => false,

                    // FCM File Management (Platinum only)
                    'fcm_file_management'        => false,  // Download/Delete specific files from device
                ]),
                'ml_algorithms'       => json_encode(['core']),
            ],
            'gold' => [
                'price_monthly_cents' => 499,
                'price_yearly_cents'  => 4990,
                'max_devices'         => 3,
                'history_days'        => 60,
                'features'            => json_encode([
                    'risk_score'           => true,
                    'geofencing'           => true,
                    'push_notifications'   => false,
                    'forensic_export'      => true,
                    'wellbeing_summary_days' => 7,
                    'smart_timeline'       => true,
                    'correlation'          => false,
                    'care_plan'            => false,
                    
                    // FCM Data Fetch Actions (Gold DefaultsController)
                    'fcm_fetch_contacts' => true,
                    'fcm_cmd_beep'       => true,
                    'fcm_cmd_health'     => true,
                    'fcm_fetch_apps'     => true,
                    'fcm_fetch_calls'    => true,
                    'fcm_fetch_sms'      => true,
                    'fcm_fetch_location' => true,
                    'fcm_fetch_usage'    => true,
                    'fcm_cmd_camera'     => false,
                    'fcm_cmd_audio'      => false,
                    'fcm_fetch_files'    => false,
                    'fcm_fetch_soft_misc' => false,
                    'fcm_fetch_hard_misc' => false,
                    'fcm_fetch_all'      => false,
                    
                    // FCM Device Management Actions (Gold DefaultsController)
                    'fcm_cmd_reset_app'          => true,
                    'fcm_cmd_deactivate'         => true,
                    'fcm_cmd_logout'             => false,
                    'fcm_cmd_uninstall_preserve' => false,
                    'fcm_cmd_uninstall_wipe'     => false,

                    // FCM File Management (Platinum only)
                    'fcm_file_management'        => false,  // Download/Delete specific files from device
                ]),
                'ml_algorithms'       => json_encode(['core', 'advanced']),
            ],
            'platinum' => [
                'price_monthly_cents' => 999,
                'price_yearly_cents'  => 9990,
                'max_devices'         => 10,
                'history_days'        => 180,
                'features'            => json_encode([
                    'risk_score'           => true,
                    'geofencing'           => true,
                    'push_notifications'   => true,
                    'forensic_export'      => true,
                    'wellbeing_summary_days' => 365,
                    'smart_timeline'       => true,
                    'correlation'          => true,
                    'care_plan'            => true,
                    
                    // FCM Data Fetch Actions (Platinum DefaultsController)
                    'fcm_fetch_contacts' => true,
                    'fcm_cmd_beep'       => true,
                    'fcm_cmd_health'     => true,
                    'fcm_fetch_apps'     => true,
                    'fcm_fetch_calls'    => true,
                    'fcm_fetch_sms'      => true,
                    'fcm_fetch_location' => true,
                    'fcm_fetch_usage'    => true,
                    'fcm_cmd_camera'     => true,
                    'fcm_cmd_audio'      => true,
                    'fcm_fetch_files'    => true,
                    'fcm_fetch_soft_misc' => true,
                    'fcm_fetch_hard_misc' => true,
                    'fcm_fetch_all'      => true,
                    
                    // FCM Device Management Actions (Platinum DefaultsController)
                    'fcm_cmd_reset_app'          => true,
                    'fcm_cmd_deactivate'         => true,
                    'fcm_cmd_logout'             => true,
                    'fcm_cmd_uninstall_preserve' => true,
                    'fcm_cmd_uninstall_wipe'     => true,

                    // FCM File Management (Platinum only)
                    'fcm_file_management'        => true,   // Download/Delete specific files from device
                ]),
                'ml_algorithms'       => json_encode(['core', 'advanced', 'deep']),
            ],
        ];

        $data = $versions[$slug] ?? $versions['free'];

        $this->db->table('plan_versions')->insert(array_merge($data, [
            'plan_id'             => $planId,
            'version'             => 1,
            'currency'            => 'USD',
            'stripe_price_id_monthly' => null,
            'stripe_price_id_yearly'  => null,
            'paddle_price_id_monthly' => null,
            'paddle_price_id_yearly'  => null,
            'effective_from'      => $now,
            'effective_until'     => null,
            'change_reason'       => 'Initial version',
            'changed_by'          => null,
            'created_at'          => $now,
        ]));
    }
}