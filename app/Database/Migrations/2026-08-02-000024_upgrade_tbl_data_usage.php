<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblDataUsageFields extends Migration
{
    public function up()
    {
        $fields = [
            'level'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'network_type'],
            'uid'           => ['type' => 'INT', 'null' => true, 'after' => 'level'],
            'package_name'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'uid'],
            'wifi_rx'       => ['type' => 'BIGINT', 'null' => true, 'after' => 'bucket_end'],
            'wifi_tx'       => ['type' => 'BIGINT', 'null' => true, 'after' => 'wifi_rx'],
            'mobile_rx'     => ['type' => 'BIGINT', 'null' => true, 'after' => 'wifi_tx'],
            'mobile_tx'     => ['type' => 'BIGINT', 'null' => true, 'after' => 'mobile_rx'],
        ];
        $this->forge->addColumn('tbl_data_usage', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_data_usage', ['level', 'uid', 'package_name', 'wifi_rx', 'wifi_tx', 'mobile_rx', 'mobile_tx']);
    }
}
