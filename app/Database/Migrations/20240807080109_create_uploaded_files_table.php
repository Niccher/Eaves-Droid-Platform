<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUploadedFiles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'file_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'original_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'stored_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'file_size_bytes' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'file_extension' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'file_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'token_used' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'token_owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'device_checksum' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'device_print_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'android_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'upload_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => false,
            ],
            'upload_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'processing', 'parsed', 'failed', 'archived'],
                'null'       => false,
                'default'    => 'pending',
            ],
            'upload_error' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'parsed_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'parsed_records' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'parse_duration_ms' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'uploaded_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'processed_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'upload_source' => [
                'type'       => 'ENUM',
                'constraint' => ['manual', 'auto_sync', 'web_initiated'],
                'null'       => true,
                'default'    => 'auto_sync',
            ],
        ]);

        $this->forge->addPrimaryKey('file_id');
        $this->forge->addKey('token_used', false, false, 'token_used');
        $this->forge->addKey('token_owner_id', false, false, 'token_owner_id');
        $this->forge->addKey('device_checksum', false, false, 'device_checksum');
        $this->forge->addKey('file_category', false, false, 'file_category');
        $this->forge->addKey('upload_status', false, false, 'upload_status');
        $this->forge->addKey('uploaded_at', false, false, 'uploaded_at');
        $this->forge->addKey('original_filename', false, false, 'original_filename');
        $this->forge->addKey('file_extension', false, false, 'file_extension');
        $this->forge->addKey('mime_type', false, false, 'mime_type');
        $this->forge->addKey('android_id', false, false, 'android_id');
        $this->forge->addKey('parsed_at', false, false, 'parsed_at');
        $this->forge->addKey('processed_at', false, false, 'processed_at');

        $this->forge->createTable('uploaded_files', true);
    }

    public function down()
    {
        $this->forge->dropTable('uploaded_files', true);
    }
}
