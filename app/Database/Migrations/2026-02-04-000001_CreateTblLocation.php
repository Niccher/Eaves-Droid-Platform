<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblLocation extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'counter' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
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
                'constraint' => 10,
                'null'       => true,
                'comment'    => 'Location accuracy in meters',
            ],
            'altitude' => [
                'type'       => 'FLOAT',
                'constraint' => 10,
                'null'       => true,
                'comment'    => 'Altitude in meters above sea level',
            ],
            'bearing' => [
                'type'       => 'FLOAT',
                'constraint' => 10,
                'null'       => true,
                'comment'    => 'Bearing in degrees',
            ],
            'speed' => [
                'type'       => 'FLOAT',
                'constraint' => 10,
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
                'constraint' => 20,
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
                'constraint' => 20,
                'unsigned'   => true,
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

        // Primary Key
        $this->forge->addPrimaryKey('counter');

        // Indexes for better query performance
        $this->forge->addKey('owner_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('extracted_at');
        $this->forge->addKey('location_time');
        $this->forge->addKey(['owner_id', 'extracted_at']);

        // Create the table
        $this->forge->createTable('tbl_location', true);

        // Add comment to table
        $this->db->query("ALTER TABLE tbl_location COMMENT 'Stores device location data extracted from Android'");
    }

    public function down()
    {
        $this->forge->dropTable('tbl_location', true);
    }
}