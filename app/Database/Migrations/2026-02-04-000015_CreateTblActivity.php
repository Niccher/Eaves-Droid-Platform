<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblActivity extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'counter' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
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
                'constraint' => 3,
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
                'default'    => 0,
                'null'       => true,
                'comment'    => '1 = device interactive, 0 = not interactive',
            ],
            'battery_level' => [
                'type'       => 'INT',
                'constraint' => 3,
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
                'default'    => 0,
                'null'       => true,
                'comment'    => '1 = screen on, 0 = screen off',
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'activity_time' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Timestamp when activity was detected (Unix milliseconds)',
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

        // Primary Key
        $this->forge->addPrimaryKey('counter');

        // Indexes for better query performance
        $this->forge->addKey('owner_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('extracted_at');
        $this->forge->addKey('activity_type');
        $this->forge->addKey('activity_time');
        $this->forge->addKey(['owner_id', 'extracted_at']);

        // Create the table
        $this->forge->createTable('tbl_activity', true);

        // Add comment to table
        $this->db->query("ALTER TABLE tbl_activity COMMENT 'Stores device activity and sensor data extracted from Android'");
    }

    public function down()
    {
        $this->forge->dropTable('tbl_activity', true);
    }
}