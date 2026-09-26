<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblTelemetryPowerRails extends Migration
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
            'rail_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'rail_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'voltage_mv' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'voltage_min_mv' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'voltage_max_mv' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'current_ma' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'current_max_ma' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'power_mw' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'temperature_c' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'capacity_percent' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'health' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'technology' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'is_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'regulator_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'mode' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'efficiency_percent' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'remote_sense' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'soft_start_us' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'ramp_delay_us' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'constraints' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'num_consumers' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'consumer_names' => [
                'type'       => 'TEXT',
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
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'rail_name'], 'uq_power_rail_per_device');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('rail_type', false, false, 'rail_type');

        $this->forge->createTable('tbl_telemetry_power_rails', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_telemetry_power_rails', true);
    }
}
