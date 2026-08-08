<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDigitalWellbeing extends Migration
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
            'focus_mode_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'focus_mode_apps' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'bedtime_mode_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'bedtime_schedule' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'bedtime_grayscale' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'bedtime_dnd' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'unlock_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'notification_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'wind_down_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'wind_down_schedule' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'total_daily_usage_minutes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'social_minutes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'productivity_minutes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'entertainment_minutes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'other_minutes' => [
                'type'       => 'BIGINT',
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

        $this->forge->createTable('tbl_digital_wellbeing', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_digital_wellbeing', true);
    }
}
