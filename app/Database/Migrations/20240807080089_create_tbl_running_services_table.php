<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblRunningServices extends Migration
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
            'running_process_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'pid' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'process' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'client_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'client_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'active_since' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'crash_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'flags' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'started' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'service_class' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'service_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('running_process_id', false, false, 'running_process_id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');

        $this->forge->createTable('tbl_running_services', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_running_services', true);
    }
}
