<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGeoPlaces extends Migration
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
            'label' => [
                'type'       => 'ENUM',
                'constraint' => ['home', 'work', 'poi'],
                'null'       => false,
            ],
            'centroid_lat' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,8',
                'null'       => false,
            ],
            'centroid_lng' => [
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
            'visit_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'first_visit' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'last_visit' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'typical_arrival' => [
                'type'       => 'TIME',
                'null'       => true,
            ],
            'typical_departure' => [
                'type'       => 'TIME',
                'null'       => true,
            ],
            'confidence' => [
                'type'       => 'FLOAT',
                'null'       => false,
                'default'    => 0,
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
        $this->forge->addKey(['user_id', 'device_id'], false, false, 'user_id_device_id');
        $this->forge->addKey('label', false, false, 'label');

        $this->forge->createTable('geo_places', true);
    }

    public function down()
    {
        $this->forge->dropTable('geo_places', true);
    }
}
