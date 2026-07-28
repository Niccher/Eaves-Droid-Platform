<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSystemLocale extends Migration
{
    public function up()
    {
        // Check if table already exists
        if ($this->db->tableExists('tbl_system_locale')) {
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
            'locale_region_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'system_fonts_json' => [
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
        $this->forge->createTable('tbl_system_locale');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_system_locale');
    }
}
