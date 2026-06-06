<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAppUsageTable extends Migration
{
    public function up()
    {
        // ── Per-app usage summary ─────────────────────────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'package_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'app_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'foreground_time_ms' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
                'null'       => true,
            ],
            'foreground_time_hours' => [
                'type'    => 'FLOAT',
                'default' => 0,
                'null'    => true,
            ],
            'foreground_time_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => true,
            ],
            'times_opened' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => true,
            ],
            'time_taken_formatted' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'last_time_used' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
            ],
            'is_system_app' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ],

            'extracted_at' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('package_name');
        $this->forge->addKey('last_time_used');
        $this->forge->addKey('extracted_at');
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'package_name', 'extracted_at'], 'uq_app_usage_snapshot');

        $this->forge->createTable('tbl_app_usage');

        // ── App session events (foreground/background) ────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'app_usage_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
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
                'constraint' => 20,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('app_usage_id');
        $this->forge->addKey('owner_id');
        $this->forge->addKey('event_type');
        $this->forge->addKey('timestamp');

        $this->forge->createTable('tbl_app_usage_sessions');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_app_usage_sessions');
        $this->forge->dropTable('tbl_app_usage');
    }
}