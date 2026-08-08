<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblGeoEvents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'zone_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'place_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'event_type' => [
                'type'       => 'ENUM',
                'constraint' => ['enter', 'exit', 'dwell', 'travel', 'anomaly'],
                'null'       => false,
            ],
            'event_time' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,8',
                'null'       => true,
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '11,8',
                'null'       => true,
            ],
            'dwell_ms' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'dwell_minutes' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'distance_from_prev_km' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'metadata' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => false,
                'default'    => 'CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'device_id', 'event_time'], false, false, 'user_id_device_id_event_time');
        $this->forge->addKey(['zone_id', 'event_time'], false, false, 'zone_id_event_time');
        $this->forge->addKey('event_type', false, false, 'event_type');

        $this->forge->createTable('tbl_geo_events', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_geo_events', true);
    }
}
