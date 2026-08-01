<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblNetworkInfo extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_network_info', [
            'wifi_link_speed_mbps'  => ['type' => 'INT', 'null' => true],
            'wifi_frequency_mhz'    => ['type' => 'INT', 'null' => true],
            'wifi_channel'          => ['type' => 'INT', 'null' => true],
            'wifi_channel_width'    => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'wifi_noise'            => ['type' => 'INT', 'null' => true],
            'wifi_snr'              => ['type' => 'FLOAT', 'null' => true],
            'wifi_standard'         => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'wifi_phy_mode'         => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'wifi_tx_rate'          => ['type' => 'INT', 'null' => true],
            'wifi_rx_rate'          => ['type' => 'INT', 'null' => true],
            'wifi_retry_rate'       => ['type' => 'FLOAT', 'null' => true],
            'wifi_lost_packet_rate' => ['type' => 'FLOAT', 'null' => true],
            'cell_identity'         => ['type' => 'TEXT', 'null' => true],
            'data_network_type'     => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'is_5g_nsa'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_5g_sa'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'nr_ssb_frequency'      => ['type' => 'INT', 'null' => true],
            'nr_scs'                => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'nr_band'               => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_network_info', [
            'wifi_link_speed_mbps', 'wifi_frequency_mhz', 'wifi_channel', 'wifi_channel_width',
            'wifi_noise', 'wifi_snr', 'wifi_standard', 'wifi_phy_mode', 'wifi_tx_rate',
            'wifi_rx_rate', 'wifi_retry_rate', 'wifi_lost_packet_rate', 'cell_identity',
            'data_network_type', 'is_5g_nsa', 'is_5g_sa', 'nr_ssb_frequency', 'nr_scs', 'nr_band'
        ]);
    }
}
