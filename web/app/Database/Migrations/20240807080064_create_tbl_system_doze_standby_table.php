<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSystemDozeStandby extends Migration
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
            'is_in_doze' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_in_light_doze' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_in_deep_doze' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'power_save_mode' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'battery_saver_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'battery_saver_since' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'next_maintenance_window' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'last_standby_transition' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'adaptive_battery_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'adaptive_battery_learning' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'device_standby_bucket' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
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

        $this->forge->createTable('tbl_system_doze_standby', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_system_doze_standby', true);
    }
}
