<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class UpgradeTblBluetoothPaired extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_bluetooth_paired', [
            'device_class' => ['type' => 'INT', 'null' => true, 'after' => 'alias'],
            'device_class_major' => ['type' => 'INT', 'null' => true, 'after' => 'device_class'],
            'device_class_minor' => ['type' => 'INT', 'null' => true, 'after' => 'device_class_major'],
            'rssi' => ['type' => 'INT', 'default' => 0, 'after' => 'device_class_minor'],
            'tx_power' => ['type' => 'INT', 'null' => true, 'after' => 'rssi'],
            'appearance' => ['type' => 'INT', 'null' => true, 'after' => 'tx_power'],
            'uuids' => ['type' => 'TEXT', 'null' => true, 'after' => 'appearance'],
            'manufacturer_data' => ['type' => 'TEXT', 'null' => true, 'after' => 'uuids'],
            'service_data' => ['type' => 'TEXT', 'null' => true, 'after' => 'manufacturer_data'],
            'address_type' => ['type' => 'INT', 'null' => true, 'after' => 'service_data'],
            'bonding_attempt' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'address_type'],
            'is_le' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'bonding_attempt'],
            'le_address' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'is_le'],
            'le_address_type' => ['type' => 'INT', 'null' => true, 'after' => 'le_address'],
            'connection_state' => ['type' => 'INT', 'default' => 0, 'after' => 'le_address_type'],
            'connection_interval_ms' => ['type' => 'INT', 'null' => true, 'after' => 'connection_state'],
            'connection_latency' => ['type' => 'INT', 'null' => true, 'after' => 'connection_interval_ms'],
            'supervision_timeout_ms' => ['type' => 'INT', 'null' => true, 'after' => 'connection_latency'],
            'mtu' => ['type' => 'INT', 'null' => true, 'after' => 'supervision_timeout_ms'],
            'bt_phy' => ['type' => 'INT', 'null' => true, 'after' => 'mtu'],
            'phy_tx' => ['type' => 'INT', 'null' => true, 'after' => 'bt_phy'],
            'phy_rx' => ['type' => 'INT', 'null' => true, 'after' => 'phy_tx'],
            'data_length' => ['type' => 'INT', 'null' => true, 'after' => 'phy_rx'],
            'att_mtu' => ['type' => 'INT', 'null' => true, 'after' => 'data_length'],
            'bond_order' => ['type' => 'INT', 'default' => 0, 'after' => 'att_mtu'],
            'last_seen_time' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'bond_order'],
            'last_connected_time' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'last_seen_time'],
            'connection_count' => ['type' => 'INT', 'default' => 0, 'after' => 'last_connected_time'],
            'total_bytes_sent' => ['type' => 'BIGINT', 'default' => 0, 'after' => 'connection_count'],
            'total_bytes_received' => ['type' => 'BIGINT', 'default' => 0, 'after' => 'total_bytes_sent'],
        ]);
    }
    public function down() {
        $this->forge->dropColumn('tbl_bluetooth_paired', ['device_class','device_class_major','device_class_minor','rssi','tx_power','appearance','uuids','manufacturer_data','service_data','address_type','bonding_attempt','is_le','le_address','le_address_type','connection_state','connection_interval_ms','connection_latency','supervision_timeout_ms','mtu','bt_phy','phy_tx','phy_rx','data_length','att_mtu','bond_order','last_seen_time','last_connected_time','connection_count','total_bytes_sent','total_bytes_received']);
    }
}
