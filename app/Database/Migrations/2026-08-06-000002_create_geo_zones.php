<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGeoZones extends Migration
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
            'place_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['home', 'work', 'custom'],
            ],
            'center_lat' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
            ],
            'center_lng' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
            ],
            'radius_m' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 150,
            ],
            'enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'notify_on_enter' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'notify_on_exit' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
'min_dwell_minutes' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 5,
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
    $this->forge->addForeignKey('place_id', 'geo_places', 'id', 'SET NULL', 'CASCADE');
    $this->forge->createTable('geo_zones');
    
    // Use raw SQL for timestamp defaults (after table creation)
    $this->db->query("ALTER TABLE `geo_zones` 
        MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('geo_zones');
    }
}