<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDeviceFiles extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'path' => [
                'type'       => 'TEXT',
                'null'       => false,
            ],
            'is_directory' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'size_bytes' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'last_modified' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'extension' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'formatted_size' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'formatted_date' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
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
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'magic_bytes' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'entropy' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'is_encrypted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'hash_sha256' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'hash_md5' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
            ],
            'exif_data' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'media_duration' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'media_resolution' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'media_bitrate' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'media_codec' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'document_page_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'document_author' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'document_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'document_subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'document_keywords' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'archive_contents_list' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'archive_encrypted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'apk_package_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'apk_version_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'apk_min_sdk' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'apk_target_sdk' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'apk_permissions' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'apk_signatures' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'certificate_info' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'is_hidden' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_system_file' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'selinux_context' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'extended_attributes' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'hard_link_count' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'inode_number' => [
                'type'       => 'BIGINT',
                'null'       => true,
                'default'    => 0,
            ],
            'mount_point' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'path_hash' => [
                'type'       => 'CHAR',
                'constraint' => 40,
                'null'       => false,
                'default'    => '',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['path_hash', 'device_id', 'owner_id'], 'unique_file_path');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('category', false, false, 'category');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        $this->forge->createTable('tbl_device_files', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_device_files', true);
    }
}
