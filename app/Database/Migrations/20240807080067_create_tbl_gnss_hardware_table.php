<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblGnssHardware extends Migration
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
            'gnss_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'constellations_supported' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'antenna_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'frequencies_supported' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'max_satellites_tracked' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'max_satellites_used' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'agps_supported' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'agps_modes' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'dead_reckoning_supported' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'raw_measurements_supported' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'correction_data_supported' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'navigation_messages_supported' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'antenna_info' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'measurement_capabilities' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'status_supported' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'time_offset_ns' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'leap_second' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'utc_time_accuracy_ns' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'gps_provider_available' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'gnss_hardware_model_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'gnss_year_of_hardware' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'gnss_batch_size' => [
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
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');

        $this->forge->createTable('tbl_gnss_hardware', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_gnss_hardware', true);
    }
}
