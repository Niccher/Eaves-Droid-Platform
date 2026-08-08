<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGeoZones extends Migration
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
            'place_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['home', 'work', 'custom'],
                'null'       => false,
            ],
            'center_lat' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,8',
                'null'       => false,
            ],
            'center_lng' => [
                'type'       => 'DECIMAL',
                'constraint' => '11,8',
                'null'       => false,
            ],
            'radius_m' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 150,
            ],
            'enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'notify_on_enter' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'notify_on_exit' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'min_dwell_minutes' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 5,
            ],
            'created_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => false,
                'default'    => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => false,
                'default'    => 'CURRENT_TIMESTAMP',
                // ON UPDATE CURRENT_TIMESTAMP (add via raw query if required)
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('place_id', false, false, 'geo_zones_place_id_foreign');
        $this->forge->addKey(['user_id', 'device_id'], false, false, 'user_id_device_id');
        $this->forge->addForeignKey('place_id', 'geo_places', 'id', 'CASCADE', 'SET NULL', 'geo_zones_place_id_foreign');

        $this->forge->createTable('geo_zones', true);
    }

    public function down()
    {
        $this->forge->dropTable('geo_zones', true);
    }
}
