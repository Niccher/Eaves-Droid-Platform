<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblTelemetryWifiNetworksSaved extends Migration
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
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'ssid' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'bssid' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'network_id' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'priority' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'is_hidden' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'security' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'protocols_json' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'auth_algorithms_json' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');

        $this->forge->createTable('tbl_telemetry_wifi_networks', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_telemetry_wifi_networks', true);
    }
}
