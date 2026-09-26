<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSystemAppUsageSessions extends Migration
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
            'app_usage_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'timestamp' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'session_start_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'session_end_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'session_duration_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('app_usage_id', false, false, 'app_usage_id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('event_type', false, false, 'event_type');
        $this->forge->addKey('timestamp', false, false, 'timestamp');

        $this->forge->createTable('tbl_system_app_usage_sessions', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_system_app_usage_sessions', true);
    }
}
