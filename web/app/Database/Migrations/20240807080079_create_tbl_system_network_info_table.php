<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSystemNetworkInfo extends Migration
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
            'is_connected' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'connection_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'is_roaming' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'network_operator_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'network_country_iso' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'sim_operator_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sim_country_iso' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'sim_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'phone_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'device_imei' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'sim_serial' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'subscriber_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'wifi_ssid' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'wifi_bssid' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'wifi_link_speed' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'wifi_frequency' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'wifi_rssi' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'wifi_mac_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'wifi_ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
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
            'wifi_link_speed_mbps' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'wifi_frequency_mhz' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'wifi_channel' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'wifi_channel_width' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'wifi_noise' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'wifi_snr' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'wifi_standard' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'wifi_phy_mode' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'wifi_tx_rate' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'wifi_rx_rate' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'wifi_retry_rate' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'wifi_lost_packet_rate' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'cell_identity' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'data_network_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'is_5g_nsa' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_5g_sa' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'nr_ssb_frequency' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'nr_scs' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'nr_band' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('device_imei', false, false, 'device_imei');
        $this->forge->addKey('connection_type', false, false, 'connection_type');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        $this->forge->createTable('tbl_system_network_info', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_system_network_info', true);
    }
}
