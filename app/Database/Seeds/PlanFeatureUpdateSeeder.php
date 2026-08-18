<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

/**
 * Adds fcm_file_management feature:
 *   plan_id=3 (Platinum) → true
 *   plan_id=1 (Free)     → false
 *   plan_id=2 (Gold)     → false
 */
class PlanFeatureUpdateSeeder extends Seeder
{
    public function run()
    {
        // plan_id => should have file management
        $map = [1 => false, 2 => false, 3 => true];
        $rows = $this->db->table('plan_versions')->get()->getResultArray();
        if (empty($rows)) { echo "No rows.\n"; return; }
        foreach ($rows as $row) {
            $pid = (int)($row['plan_id'] ?? 0);
            if (!array_key_exists($pid, $map)) { echo "SKIP unknown plan_id={$pid}\n"; continue; }
            $features = !empty($row['features']) ? json_decode($row['features'], true) : [];
            $shouldHave = $map[$pid];
            if (($features['fcm_file_management'] ?? null) === $shouldHave) {
                echo "SKIP plan_id={$pid} already=" . ($shouldHave?'true':'false') . "\n";
                continue;
            }
            $features['fcm_file_management'] = $shouldHave;
            $this->db->table('plan_versions')->where('id', $row['id'])->update(['features' => json_encode($features)]);
            echo "OK plan_id={$pid} fcm_file_management=" . ($shouldHave?'true':'false') . "\n";
        }
        echo "Done.\n";
    }
}
