<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SystemChangelogsSeeder extends Seeder
{
    public function run()
    {
        $version = $this->db->table('system_versions')
            ->where('version', 'v2.5.1')
            ->get()->getRowArray();

        if (!$version) return;

        $versionId = $version['id'];

        $changelogs = [
            [
                'version_id'  => $versionId,
                'category'    => 'feature',
                'title'       => 'Consolidated Hardware & Software Telemetry',
                'description' => 'Merged single-table features and optimized database migrations.',
                'component'   => 'webapp',
                'created_at'  => '2026-08-28 12:00:00',
            ],
            [
                'version_id'  => $versionId,
                'category'    => 'security',
                'title'       => 'Role & Tier Security Auditing',
                'description' => 'Enforced plan tier restrictions and isolated permissions.',
                'component'   => 'platform',
                'created_at'  => '2026-08-28 12:00:00',
            ],
        ];

        foreach ($changelogs as $c) {
            $existing = $this->db->table('system_changelogs')
                ->where('version_id', $c['version_id'])
                ->where('title', $c['title'])
                ->get()->getRowArray();

            if (!$existing) {
                $this->db->table('system_changelogs')->insert($c);
            }
        }
    }
}
