<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblUsageStats24h extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'running_processes_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'package_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'total_time_foreground' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'last_time_used' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'last_time_service_used' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'last_time_visible' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'app_launch_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('running_processes_id', false, false, 'running_processes_id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');

        $this->forge->createTable('tbl_usage_stats_24h', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_usage_stats_24h', true);
    }
}
