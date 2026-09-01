<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SystemVersionsSeeder extends Seeder
{
    public function run()
    {
        $versions = [
            [
                'version'      => 'v2.5.1',
                'build_number' => 251,
                'release_name' => 'Forensic Telemetry Architecture Optimization & Hardware Analytics',
                'release_type' => 'patch',
                'is_current'   => 1,
                'released_at'  => '2026-08-28 12:00:00',
            ],
            [
                'version'      => 'v2.5.0',
                'build_number' => 250,
                'release_name' => 'Intelligence & Forensic Telemetry Release',
                'release_type' => 'minor',
                'is_current'   => 0,
                'released_at'  => '2026-08-20 00:50:00',
            ],
        ];

        foreach ($versions as $v) {
            $existing = $this->db->table('system_versions')
                ->where('version', $v['version'])
                ->get()->getRowArray();

            if (!$existing) {
                $this->db->table('system_versions')->insert($v);
            }
        }
    }
}
