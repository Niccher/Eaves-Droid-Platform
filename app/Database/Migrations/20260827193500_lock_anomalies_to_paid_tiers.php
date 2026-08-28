<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LockAnomaliesToPaidTiers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Ensure that the database plan_versions features configuration is updated
        $plans = $db->table('plans')->get()->getResultArray();
        foreach ($plans as $p) {
            $slug = strtolower($p['slug'] ?? '');
            $pid = (int)($p['id'] ?? 0);
            
            $versions = $db->table('plan_versions')->where('plan_id', $pid)->get()->getResultArray();
            foreach ($versions as $v) {
                $features = !empty($v['features']) ? json_decode($v['features'], true) : [];
                
                if ($slug === 'free') {
                    $features['risk_score'] = false;
                } else if ($slug === 'gold' || $slug === 'platinum') {
                    $features['risk_score'] = true;
                }
                
                $db->table('plan_versions')
                    ->where('id', $v['id'])
                    ->update(['features' => json_encode($features)]);
            }
        }
    }

    public function down()
    {
        // No down migration required
    }
}
