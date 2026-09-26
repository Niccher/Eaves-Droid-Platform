<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblLiveLocationTrackingTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
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
                'null'       => false,
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,8',
                'null'       => true,
                'comment'    => 'GPS latitude coordinate',
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '11,8',
                'null'       => true,
                'comment'    => 'GPS longitude coordinate',
            ],
            'accuracy' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'comment'    => 'Location accuracy in meters',
            ],
            'altitude' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'comment'    => 'Altitude in meters above sea level',
            ],
            'bearing' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'comment'    => 'Heading bearing in degrees',
            ],
            'speed' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'comment'    => 'Speed in m/s',
            ],
            'provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'fused, gps, or network',
            ],
            'activity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => 'STILL',
                'comment'    => 'Detected activity e.g. IN_VEHICLE, WALKING, STILL',
            ],
            'activity_confidence' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'battery_level' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'charge_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'network_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'is_interactive' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => 'Screen interactive (1) or off (0)',
            ],
            'raw_payload' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'recorded_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['owner_id', 'device_id']);
        $this->forge->addKey('recorded_at');
        $this->forge->createTable('tbl_live_location_tracking', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_live_location_tracking', true);
    }
}
