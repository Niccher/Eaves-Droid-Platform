<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUploadQueue extends Migration
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
            'stored_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'original_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'file_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'file_size_bytes' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'file_record_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'device_checksum' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'default'    => '',
            ],
            'device_print_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'default'    => '',
            ],
            'token_used' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'default'    => '',
            ],
            'upload_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => false,
                'default'    => '',
            ],
            'upload_source' => [
                'type'       => 'ENUM',
                'constraint' => ['manual', 'auto_sync', 'web_initiated'],
                'null'       => true,
                'default'    => 'auto_sync',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'processing', 'completed', 'failed'],
                'null'       => false,
                'default'    => 'pending',
            ],
            'attempts' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'error_message' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'queued_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => 'CURRENT_TIMESTAMP',
            ],
            'processing_started_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'completed_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => 'CURRENT_TIMESTAMP',
                // ON UPDATE CURRENT_TIMESTAMP (add via raw query if required)
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('status', false, false, 'status');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('file_category', false, false, 'file_category');
        $this->forge->addKey('queued_at', false, false, 'queued_at');

        $this->forge->createTable('upload_queue', true);
    }

    public function down()
    {
        $this->forge->dropTable('upload_queue', true);
    }
}
