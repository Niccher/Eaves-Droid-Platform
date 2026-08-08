<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblActivity extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'counter' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => 'feature_not_fully_implemented',
                'comment'    => 'Status of activity recognition',
            ],
            'activity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Detected activity type (walking, running, driving, etc.)',
            ],
            'confidence' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
                'comment'    => 'Confidence level (0-100)',
            ],
            'info' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Additional information about activity',
            ],
            'is_interactive' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
                'comment'    => '1 = device interactive, 0 = not interactive',
            ],
            'battery_level' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Battery percentage (0-100)',
            ],
            'charging_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'charging, discharging, full, unknown',
            ],
            'network_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'wifi, mobile, ethernet, unknown',
            ],
            'screen_on' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
                'comment'    => '1 = screen on, 0 = screen off',
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'activity_time' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Timestamp when activity was detected (Unix milliseconds)',
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

        $this->forge->addPrimaryKey('counter');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');
        $this->forge->addKey('activity_type', false, false, 'activity_type');
        $this->forge->addKey('activity_time', false, false, 'activity_time');
        $this->forge->addKey(['owner_id', 'extracted_at'], false, false, 'owner_id_extracted_at');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        $this->forge->createTable('tbl_activity', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_activity', true);
    }
}
