<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGeoPlaces extends Migration
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
            'label' => [
                'type' => 'ENUM',
                'constraint' => ['home', 'work', 'poi'],
            ],
            'centroid_lat' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
            ],
            'centroid_lng' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
            ],
            'radius_m' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 150,
            ],
            'visit_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
            ],
            'first_visit' => [
                'type' => 'DATETIME',
            ],
            'last_visit' => [
                'type' => 'DATETIME',
            ],
            'typical_arrival' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'typical_departure' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'confidence' => [
                'type' => 'FLOAT',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'device_id']);
        $this->forge->addKey('label');
        $this->forge->createTable('geo_places');
        
        // Use raw SQL for timestamp defaults (after table creation)
        $this->db->query("ALTER TABLE `geo_places` 
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('geo_places');
    }
}