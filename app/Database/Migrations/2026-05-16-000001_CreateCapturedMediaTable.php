<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCapturedMediaTable extends Migration
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
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'media_type' => [
                'type'       => 'ENUM',
                'constraint' => ['audio', 'image'],
            ],
            'original_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'stored_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'file_size' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'file_record_id' => [
                'type'       => 'INT',
                'constraint' => 11,
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
        $this->forge->addKey('media_type');
        $this->forge->createTable('tbl_captured_media');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_captured_media');
    }
}
