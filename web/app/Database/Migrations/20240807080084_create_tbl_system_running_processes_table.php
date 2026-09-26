<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSystemRunningProcesses extends Migration
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
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'meminfo_json' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'cpuinfo_json' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'stat_json' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'version' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'uptime_json' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'net_interfaces_json' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'net_connections_json' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');

        $this->forge->createTable('tbl_system_running_processes', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_system_running_processes', true);
    }
}
