<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblNearbyWifi extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'network_info_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'ssid' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'bssid' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'capabilities' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'level' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'frequency' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('network_info_id', false, false, 'network_info_id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('bssid', false, false, 'bssid');

        $this->forge->createTable('tbl_nearby_wifi', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_nearby_wifi', true);
    }
}
