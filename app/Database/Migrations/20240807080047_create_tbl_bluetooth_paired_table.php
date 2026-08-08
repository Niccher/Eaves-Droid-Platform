<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblBluetoothPaired extends Migration
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
            'bluetooth_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'bt_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'bt_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'bt_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'bond_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'alias' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'device_class' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'device_class_major' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'device_class_minor' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'rssi' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'tx_power' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'appearance' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'uuids' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'manufacturer_data' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'service_data' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'address_type' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'bonding_attempt' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_le' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'le_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'le_address_type' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'connection_state' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'connection_interval_ms' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'connection_latency' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'supervision_timeout_ms' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'mtu' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'bt_phy' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'phy_tx' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'phy_rx' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'data_length' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'att_mtu' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'bond_order' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'last_seen_time' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'last_connected_time' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'connection_count' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'total_bytes_sent' => [
                'type'       => 'BIGINT',
                'null'       => true,
                'default'    => 0,
            ],
            'total_bytes_received' => [
                'type'       => 'BIGINT',
                'null'       => true,
                'default'    => 0,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('bluetooth_id', false, false, 'bluetooth_id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('bt_address', false, false, 'bt_address');

        $this->forge->createTable('tbl_bluetooth_paired', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_bluetooth_paired', true);
    }
}
