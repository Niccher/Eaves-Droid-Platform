<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastRunNextRunToCronJobs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cron_jobs', [
            'last_run' => ['type' => 'DATETIME', 'null' => true, 'after' => 'enabled'],
            'next_run' => ['type' => 'DATETIME', 'null' => true, 'after' => 'last_run'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('cron_jobs', 'last_run');
        $this->forge->dropColumn('cron_jobs', 'next_run');
    }
}
