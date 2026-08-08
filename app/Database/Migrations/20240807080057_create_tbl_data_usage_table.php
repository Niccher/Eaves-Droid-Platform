<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDataUsage extends Migration
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
            'network_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'level' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'uid' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'package_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sub_id' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'is_wifi' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'rx_bytes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'tx_bytes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'total_bytes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'rx_formatted' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'tx_formatted' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'bucket_start' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'bucket_end' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'wifi_rx' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'wifi_tx' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'mobile_rx' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'mobile_tx' => [
                'type'       => 'BIGINT',
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

        $this->forge->createTable('tbl_data_usage', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_data_usage', true);
    }
}
