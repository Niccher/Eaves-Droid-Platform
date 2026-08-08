<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblLocation extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'counter' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
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
                'null'       => true,
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
                'comment'    => 'Bearing in degrees',
            ],
            'speed' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'comment'    => 'Speed in meters/second',
            ],
            'provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Location provider (gps, network, etc.)',
            ],
            'location_time' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Timestamp when location was recorded (Unix milliseconds)',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => 'no_location_found',
                'comment'    => 'success, no_location_found, error',
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
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
            'geofence_transitions' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'place_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'place_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'place_types' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'place_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'place_confidence' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'default'    => 0,
            ],
            'place_likelihood' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'default'    => 0,
            ],
            'is_home' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_work' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_saved_place' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'visit_duration_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'arrival_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'departure_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'transport_mode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'transport_confidence' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'default'    => 0,
            ],
            'route_polyline' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'waypoints' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'speed_kmh' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'vertical_accuracy' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'floor_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'building_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'indoor_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'satellite_count' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'hdop' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'default'    => 0,
            ],
            'vdop' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'default'    => 0,
            ],
            'pdop' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'default'    => 0,
            ],
            'gnss_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'nmea_sentence' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('counter');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');
        $this->forge->addKey('location_time', false, false, 'location_time');
        $this->forge->addKey(['owner_id', 'extracted_at'], false, false, 'owner_id_extracted_at');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        $this->forge->createTable('tbl_location', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_location', true);
    }
}
