<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSensorProfile extends Migration
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
            'sensor_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'vendor' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'type_id' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'type_string' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'version' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'maximum_range' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'resolution' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'power_ma' => [
                'type'       => 'FLOAT',
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
            'sensor_string_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'min_delay_us' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'max_delay_us' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'fifo_reserved_event_count' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'fifo_max_event_count' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'is_wakeup' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_dynamic' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_additional_info' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'reporting_mode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'required_permission' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'permission_display_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'flags' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'direct_channel_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'direct_report_rates' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'additional_info' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'calibration_params' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'mounting_matrix' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'drivetime_us' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'event_time_ns' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'sensor_max_range' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'type_id', 'extracted_at'], 'uq_sensor_per_snapshot');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('type_id', false, false, 'type_id');
        $this->forge->addKey('type_string', false, false, 'type_string');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        $this->forge->createTable('tbl_sensor_profile', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_sensor_profile', true);
    }
}
