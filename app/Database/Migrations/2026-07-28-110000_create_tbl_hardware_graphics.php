<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblHardwareGraphics extends Migration
{
    public function up()
    {
        // Check if table already exists
        if ($this->db->tableExists('tbl_hardware_graphics')) {
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
            'gpu_renderer_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'media_codecs_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'input_devices_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'audit_timestamp' => [
                'type' => 'BIGINT',
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
        $this->forge->createTable('tbl_hardware_graphics');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_hardware_graphics');
    }
}