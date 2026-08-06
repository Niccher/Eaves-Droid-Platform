<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGeoEvents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'device_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'zone_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'place_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'event_type' => [
                'type' => 'ENUM',
                'constraint' => ['enter', 'exit', 'dwell', 'travel', 'anomaly'],
            ],
            'event_time' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'latitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true,
            ],
            'longitude' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => true,
            ],
            'dwell_ms' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'dwell_minutes' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'distance_from_prev_km' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
            ],
'created_at' => [
            'type' => 'TIMESTAMP',
            'null' => false,
        ],
    ]);

    $this->forge->addKey('id', true);
    $this->forge->addKey(['user_id', 'device_id', 'event_time']);
    $this->forge->addKey(['zone_id', 'event_time']);
    $this->forge->addKey('event_type');
    $this->forge->createTable('geo_events');
    
    // Use raw SQL for timestamp defaults (after table creation)
    $this->db->query("ALTER TABLE `geo_events` 
        MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('geo_events');
    }
}