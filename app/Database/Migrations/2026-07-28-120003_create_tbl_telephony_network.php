<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblTelephonyNetwork extends Migration
{
    public function up()
    {
        // Check if table already exists
        if ($this->db->tableExists('tbl_telephony_network')) {
            return;
        }
        
        $this->forge->addField([
            'id' => [
                'type' => 'INT UNSIGNED',
                'auto_increment' => true,
            ],
            'owner_id' => [
                'type' => 'INT UNSIGNED',
                'null' => true,
            ],
            'device_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'ims_volte_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'data_roaming_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'extracted_at' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('owner_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('extracted_at');
        $this->forge->createTable('tbl_telephony_network');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_telephony_network');
    }
}
