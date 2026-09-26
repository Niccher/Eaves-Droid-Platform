<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CronJobsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $jobs = [
            [
                'command'        => 'retention:purge',
                'custom_command' => null,
                'schedule'       => '0 2 * * *',
                'description'    => 'Automatically purges expired fleet data according to retention policies.',
                'arguments'      => json_encode([]),
                'enabled'        => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'command'        => 'logs:clear',
                'custom_command' => null,
                'schedule'       => '*/30 * * * *',
                'description'    => 'System maintenance and log cleanup running every 30 minutes.',
                'arguments'      => json_encode([]),
                'enabled'        => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'command'        => 'db:backup',
                'custom_command' => null,
                'schedule'       => '0 0 * * *',
                'description'    => 'Takes full database backup every midnight.',
                'arguments'      => json_encode([]),
                'enabled'        => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'command'        => 'queue:work',
                'custom_command' => null,
                'schedule'       => '0 */2 * * *',
                'description'    => 'Processes upload queue jobs and clears stale/failed items every 2 hours.',
                'arguments'      => json_encode([]),
                'enabled'        => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'command'        => 'export:process',
                'custom_command' => null,
                'schedule'       => '* * * * *',
                'description'    => 'Processes queued forensic export jobs every minute.',
                'arguments'      => json_encode([]),
                'enabled'        => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ];

        foreach ($jobs as $job) {
            $exists = $db->table('cron_jobs')
                ->where('command', $job['command'])
                ->countAllResults();

            if ($exists === 0) {
                $db->table('cron_jobs')->insert($job);
            }
        }
    }
}
