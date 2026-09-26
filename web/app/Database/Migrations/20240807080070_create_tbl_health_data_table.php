<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblHealthData extends Migration
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
            'data_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'value' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'start_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'end_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'data_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'data_source_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'data_source_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'data_source_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'step_count' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'session_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'session_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'session_description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'distance_meters' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'calories_kcal' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'sleep_stage' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sleep_efficiency' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'workout_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'workout_duration_seconds' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'max_heart_rate' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'avg_heart_rate' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'heart_rate_bpm' => [
                'type'       => 'INT',
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
        $this->forge->addKey('data_type', false, false, 'data_type');
        $this->forge->addKey('end_time', false, false, 'end_time');

        $this->forge->createTable('tbl_health_data', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_health_data', true);
    }
}
