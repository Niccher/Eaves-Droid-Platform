<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblTelemetryCellTowers extends Migration
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
            'tower_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'cid' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'lac' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'mcc' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'mnc' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'pci' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'nci' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'tac' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'nrarfcn' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'bandwidth' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'psc' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'system_id' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'rssi' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'rsrp' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'rsrq' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'rssnr' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'cqi' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'asu_level' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'csi_rsrp' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'csi_rsrq' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'csi_sinr' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'is_registered' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'network_operator' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'network_operator_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'phone_type' => [
                'type'       => 'TINYINT',
                'null'       => true,
            ],
            'sim_state' => [
                'type'       => 'TINYINT',
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

        $this->forge->createTable('tbl_telemetry_cell_towers', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_telemetry_cell_towers', true);
    }
}
