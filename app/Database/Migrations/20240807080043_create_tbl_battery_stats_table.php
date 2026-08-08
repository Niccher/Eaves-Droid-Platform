<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblBatteryStats extends Migration
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
            'level_percent' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'is_charging' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'health' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'temperature_celsius' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'voltage_mv' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'plugged_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'technology' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'capacity_percent' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'charge_counter_uah' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'current_now_ua' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'energy_counter_uwh' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'status_int' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'health_int' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'temperature_deci_c' => [
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

        $this->forge->createTable('tbl_battery_stats', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_battery_stats', true);
    }
}
