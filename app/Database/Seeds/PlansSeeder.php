<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

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
            $existing = $this->db->table('plans')
                ->where('slug', $plan['slug'])
                ->get()->getRowArray();

            if (!$existing) {
                $this->db->table('plans')->insert($plan);
            }
        }
    }
}
