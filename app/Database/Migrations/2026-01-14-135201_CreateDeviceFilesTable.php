<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeviceFilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'path' => [
                'type'       => 'TEXT', // Paths can be long
            ],
            'is_directory' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'size_bytes' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'last_modified' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'extension' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            'formatted_size' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'formatted_date' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('owner_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('category');
        $this->forge->createTable('tbl_device_files');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_device_files');
    }
}
