<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDeviceHealthChecks extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
            ],
            'network_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
            ],
            'wifi_ssid' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'sim_operator' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'signal_strength' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'battery_level' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'battery_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
            ],
            'battery_temp' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'screen_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 8,
            ],
            'keyguard_locked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'storage_free_percent' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'ram_free_mb' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'last_latitude' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'last_longitude' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'location_provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => true,
            ],
            'app_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => true,
            ],
            'uptime_seconds' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('device_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('tbl_device_health_checks');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_device_health_checks');
    }
}
