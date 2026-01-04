<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUploadedFiles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'file_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'original_filename' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'stored_filename' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_size_bytes' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'file_extension' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'mime_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'file_category' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'token_used' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'token_owner_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'device_checksum' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'device_print_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'android_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'upload_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
            ],
            'upload_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'processing', 'parsed', 'failed', 'archived'],
                'default' => 'pending',
            ],
            'upload_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'parsed_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'parsed_records' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'parse_duration_ms' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'uploaded_at' => [
                'type' => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'processed_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addKey('file_id', true);
        $this->forge->addKey('token_used');
        $this->forge->addKey('token_owner_id');
        $this->forge->addKey('device_checksum');
        $this->forge->addKey('file_category');
        $this->forge->addKey('upload_status');
        $this->forge->addKey('uploaded_at');

        // Add foreign key if you have users table
        // $this->forge->addForeignKey('token_owner_id', 'users', 'user_id', 'CASCADE', 'SET NULL');

        $this->forge->createTable('uploaded_files');
    }

    public function down()
    {
        $this->forge->dropTable('uploaded_files');
    }
}