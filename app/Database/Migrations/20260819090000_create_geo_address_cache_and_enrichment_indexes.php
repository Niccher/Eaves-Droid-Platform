<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGeoAddressCacheAndEnrichmentIndexes extends Migration
{
    public function up()
    {
        // 1. Create Reverse Geocoding Address Cache Table
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'lat_rounded' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
            ],
            'lng_rounded' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
            ],
            'display_name' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'suburb' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'road' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey(['lat_rounded', 'lng_rounded'], false, false, 'idx_coords');
        $this->forge->createTable('tbl_geo_address_cache', true);

        // 2. Add performance indexes safely if tables exist
        $db = \Config\Database::connect();
        
        // tbl_telemetry_cell_towers index
        if ($db->tableExists('tbl_telemetry_cell_towers')) {
            $db->query("CREATE INDEX IF NOT EXISTS idx_cell_owner ON tbl_telemetry_cell_towers (owner_id)");
        }

        // tbl_geo_events index
        if ($db->tableExists('tbl_geo_events')) {
            $db->query("CREATE INDEX IF NOT EXISTS idx_geo_events_owner ON tbl_geo_events (owner_id)");
        }

        // tbl_telemetry_bluetooth_devices_paired index
        if ($db->tableExists('tbl_telemetry_bluetooth_devices_paired')) {
            $db->query("CREATE INDEX IF NOT EXISTS idx_bt_paired_owner ON tbl_telemetry_bluetooth_devices_paired (owner_id)");
        }
    }

    public function down()
    {
        $this->forge->dropTable('tbl_geo_address_cache', true);
    }
}
