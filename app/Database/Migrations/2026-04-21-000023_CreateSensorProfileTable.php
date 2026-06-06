<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSensorProfileTable extends Migration
{
    public function up()
    {
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
                'constraint' => 11,
                'null'       => true,
            ],
            'type_string' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'version' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'maximum_range' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'resolution' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'power_ma' => [
                'type' => 'FLOAT',
                'null' => true,
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
        $this->forge->addKey('type_id');
        $this->forge->addKey('type_string');
        $this->forge->addKey('extracted_at');
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'type_id', 'extracted_at'], 'uq_sensor_per_snapshot');

        $this->forge->createTable('tbl_sensor_profile');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_sensor_profile');
    }
}
