<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblLocation extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_location', [
            'geofence_transitions' => ['type' => 'TEXT', 'null' => true],
            'place_id'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'place_name'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'place_types'          => ['type' => 'TEXT', 'null' => true],
            'place_address'        => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'place_confidence'     => ['type' => 'FLOAT', 'default' => 0],
            'place_likelihood'     => ['type' => 'FLOAT', 'default' => 0],
            'is_home'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_work'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_saved_place'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'visit_duration_ms'    => ['type' => 'BIGINT', 'null' => true],
            'arrival_time'         => ['type' => 'BIGINT', 'null' => true],
            'departure_time'       => ['type' => 'BIGINT', 'null' => true],
            'transport_mode'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'transport_confidence' => ['type' => 'FLOAT', 'default' => 0],
            'route_polyline'       => ['type' => 'TEXT', 'null' => true],
            'waypoints'            => ['type' => 'TEXT', 'null' => true],
            'speed_kmh'            => ['type' => 'FLOAT', 'null' => true],
            'vertical_accuracy'    => ['type' => 'FLOAT', 'null' => true],
            'floor_level'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'building_id'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'indoor_level'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'satellite_count'      => ['type' => 'INT', 'default' => 0],
            'hdop'                 => ['type' => 'FLOAT', 'default' => 0],
            'vdop'                 => ['type' => 'FLOAT', 'default' => 0],
            'pdop'                 => ['type' => 'FLOAT', 'default' => 0],
            'gnss_status'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'nmea_sentence'        => ['type' => 'TEXT', 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_location', [
            'geofence_transitions', 'place_id', 'place_name', 'place_types', 'place_address',
            'place_confidence', 'place_likelihood', 'is_home', 'is_work', 'is_saved_place',
            'visit_duration_ms', 'arrival_time', 'departure_time', 'transport_mode',
            'transport_confidence', 'route_polyline', 'waypoints', 'speed_kmh',
            'vertical_accuracy', 'floor_level', 'building_id', 'indoor_level', 'satellite_count',
            'hdop', 'vdop', 'pdop', 'gnss_status', 'nmea_sentence'
        ]);
    }
}
