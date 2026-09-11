<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $users = auth()->getProvider();
        
        $email = 'demo@eavesdroid.com';
        $user = $users->findByCredentials(['email' => $email]);

        if (!$user) {
            $user = new User([
                'username' => 'DemoUser',
                'email'    => $email,
                'password' => 'DemoPassword123!',
            ]);
            $users->save($user);
            $user = $users->findById($users->getInsertID());
            
            // Activate and add to default group if necessary
            if (method_exists($user, 'activate')) {
                $user->activate();
            }
            if (method_exists($user, 'addGroup')) {
                $user->addGroup('user');
            }
        }
        
        $ownerId = $user->id;
        $deviceId = 'demo-device-uuid-12345';
        $db = \Config\Database::connect();

        // 1. Mock Device Profile
        if ($db->tableExists('tbl_device_profiles')) {
            $db->table('tbl_device_profiles')->where('owner_id', $ownerId)->delete();
            $db->table('tbl_device_profiles')->insert([
                'owner_id' => $ownerId,
                'device_id' => $deviceId,
                'device_name' => 'Demo Pixel 7',
                'device_model' => 'Pixel 7',
                'android_version' => '13',
                'battery_level' => 85,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // 2. Mock SMS
        if ($db->tableExists('tbl_extracted_sms')) {
            $db->table('tbl_extracted_sms')->where('owner_id', $ownerId)->delete();
            $batchSms = [];
            for ($i = 0; $i < 20; $i++) {
                $batchSms[] = [
                    'owner_id' => $ownerId,
                    'device_id' => $deviceId,
                    'address' => '+1' . rand(1000000000, 9999999999),
                    'body' => 'This is a simulated demo SMS message # ' . $i,
                    'type' => rand(1, 2), // 1 = received, 2 = sent
                    'date' => (time() - rand(0, 86400 * 7)) * 1000,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
            $db->table('tbl_extracted_sms')->insertBatch($batchSms);
        }

        // 3. Mock Call Logs
        if ($db->tableExists('tbl_extracted_call_logs')) {
            $db->table('tbl_extracted_call_logs')->where('owner_id', $ownerId)->delete();
            $batchCalls = [];
            for ($i = 0; $i < 15; $i++) {
                $batchCalls[] = [
                    'owner_id' => $ownerId,
                    'device_id' => $deviceId,
                    'number' => '+1' . rand(1000000000, 9999999999),
                    'name' => 'Demo Contact ' . rand(1, 10),
                    'type' => rand(1, 3), // 1=Incoming, 2=Outgoing, 3=Missed
                    'duration' => rand(10, 600),
                    'date' => (time() - rand(0, 86400 * 7)) * 1000,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
            $db->table('tbl_extracted_call_logs')->insertBatch($batchCalls);
        }

        // 4. Mock GPS Locations
        if ($db->tableExists('tbl_extracted_locations')) {
            $db->table('tbl_extracted_locations')->where('owner_id', $ownerId)->delete();
            $batchLocs = [];
            $baseLat = 40.7128;
            $baseLng = -74.0060;
            for ($i = 0; $i < 30; $i++) {
                $batchLocs[] = [
                    'owner_id' => $ownerId,
                    'device_id' => $deviceId,
                    'latitude' => $baseLat + (rand(-100, 100) / 10000),
                    'longitude' => $baseLng + (rand(-100, 100) / 10000),
                    'accuracy' => rand(5, 20),
                    'timestamp' => (time() - ($i * 3600)) * 1000,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
            $db->table('tbl_extracted_locations')->insertBatch($batchLocs);
        }

        // 5. Mock Contacts
        if ($db->tableExists('tbl_extracted_contacts')) {
            $db->table('tbl_extracted_contacts')->where('owner_id', $ownerId)->delete();
            $batchContacts = [];
            $names = ['John Doe', 'Jane Smith', 'Alice Johnson', 'Bob Brown', 'Charlie Davis'];
            foreach ($names as $idx => $name) {
                $batchContacts[] = [
                    'owner_id' => $ownerId,
                    'device_id' => $deviceId,
                    'contact_id' => 'contact_' . $idx,
                    'display_name' => $name,
                    'phone_numbers' => json_encode([
                        ['number' => '+1' . rand(1000000000, 9999999999), 'type' => 'Mobile', 'normalized_number' => '+1' . rand(1000000000, 9999999999)]
                    ]),
                    'phone_count' => 1,
                    'emails' => json_encode([
                        ['address' => strtolower(str_replace(' ', '.', $name)) . '@example.com', 'type' => 'Home']
                    ]),
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
            $db->table('tbl_extracted_contacts')->insertBatch($batchContacts);
        }

        // 6. Mock Installed Apps
        if ($db->tableExists('tbl_extracted_installed_apps')) {
            $db->table('tbl_extracted_installed_apps')->where('owner_id', $ownerId)->delete();
            $apps = [
                ['com.whatsapp', 'WhatsApp', '2.23.1.76'],
                ['com.facebook.katana', 'Facebook', '401.0.0.24.77'],
                ['com.instagram.android', 'Instagram', '270.0.0.23.82'],
                ['com.android.chrome', 'Chrome', '110.0.5481.153'],
                ['com.google.android.youtube', 'YouTube', '18.05.35']
            ];
            $batchApps = [];
            foreach ($apps as $app) {
                $batchApps[] = [
                    'owner_id' => $ownerId,
                    'device_id' => $deviceId,
                    'package_name' => $app[0],
                    'app_name' => $app[1],
                    'version_name' => $app[2],
                    'version_code' => rand(10000, 99999),
                    'first_install_time' => (time() - 86400 * 30) * 1000,
                    'last_update_time' => (time() - 86400 * 2) * 1000,
                    'is_system_app' => ($app[1] == 'Chrome' || $app[1] == 'YouTube') ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
            $db->table('tbl_extracted_installed_apps')->insertBatch($batchApps);
        }

        // 7. Mock Storage Stats
        if ($db->tableExists('tbl_telemetry_storage_stats')) {
            $db->table('tbl_telemetry_storage_stats')->where('owner_id', $ownerId)->delete();
            $db->table('tbl_telemetry_storage_stats')->insert([
                'owner_id' => $ownerId,
                'device_id' => $deviceId,
                'volume_path' => '/storage/emulated/0',
                'description' => 'Internal Storage',
                'is_removable' => 0,
                'total_bytes' => 128 * 1073741824, // 128GB
                'free_bytes' => 45 * 1073741824,  // 45GB
                'used_bytes' => 83 * 1073741824,  // 83GB
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // 8. Mock Risk Score (Current)
        if ($db->tableExists('tbl_device_risk_scores')) {
            $db->table('tbl_device_risk_scores')->where('user_id', $ownerId)->delete(); // Note: table uses user_id
            $db->table('tbl_device_risk_scores')->insert([
                'user_id' => $ownerId,
                'device_id' => $deviceId,
                'score' => 42,
                'category_breakdown' => json_encode(['network' => 20, 'apps' => 15, 'system' => 7]),
                'severity_counts' => json_encode(['high' => 0, 'medium' => 2, 'low' => 5]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // 9. Mock Risk Score History (Trend)
        if ($db->tableExists('tbl_device_risk_scores_history')) {
            $db->table('tbl_device_risk_scores_history')->where('user_id', $ownerId)->delete();
            $batchHistory = [];
            for ($i = 6; $i >= 0; $i--) {
                $batchHistory[] = [
                    'user_id' => $ownerId,
                    'device_id' => $deviceId,
                    'score' => rand(30, 60),
                    'severity_counts' => json_encode(['high' => 0, 'medium' => rand(1, 4), 'low' => rand(3, 8)]),
                    'created_at' => date('Y-m-d H:i:s', time() - ($i * 86400)),
                ];
            }
            $db->table('tbl_device_risk_scores_history')->insertBatch($batchHistory);
        }

        echo "Demo user and comprehensive mock data seeded successfully!\n";
    }
}
