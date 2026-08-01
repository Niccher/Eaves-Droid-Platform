<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblDeviceFiles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_device_files', [
            'mime_type'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'magic_bytes'           => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'entropy'               => ['type' => 'FLOAT', 'null' => true],
            'is_encrypted'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'hash_sha256'           => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'hash_md5'              => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
            'exif_data'             => ['type' => 'TEXT', 'null' => true],
            'media_duration'        => ['type' => 'BIGINT', 'null' => true],
            'media_resolution'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'media_bitrate'         => ['type' => 'BIGINT', 'null' => true],
            'media_codec'           => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'document_page_count'   => ['type' => 'INT', 'null' => true],
            'document_author'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'document_title'        => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'document_subject'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'document_keywords'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'archive_contents_list' => ['type' => 'TEXT', 'null' => true],
            'archive_encrypted'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'apk_package_name'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'apk_version_code'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'apk_min_sdk'           => ['type' => 'INT', 'null' => true],
            'apk_target_sdk'        => ['type' => 'INT', 'null' => true],
            'apk_permissions'       => ['type' => 'TEXT', 'null' => true],
            'apk_signatures'        => ['type' => 'TEXT', 'null' => true],
            'certificate_info'      => ['type' => 'TEXT', 'null' => true],
            'is_hidden'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_system_file'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'selinux_context'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'extended_attributes'   => ['type' => 'TEXT', 'null' => true],
            'hard_link_count'       => ['type' => 'INT', 'default' => 0],
            'inode_number'          => ['type' => 'BIGINT', 'default' => 0],
            'mount_point'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_device_files', [
            'mime_type', 'magic_bytes', 'entropy', 'is_encrypted', 'hash_sha256', 'hash_md5',
            'exif_data', 'media_duration', 'media_resolution', 'media_bitrate', 'media_codec',
            'document_page_count', 'document_author', 'document_title', 'document_subject',
            'document_keywords', 'archive_contents_list', 'archive_encrypted',
            'apk_package_name', 'apk_version_code', 'apk_min_sdk', 'apk_target_sdk',
            'apk_permissions', 'apk_signatures', 'certificate_info', 'is_hidden', 'is_system_file',
            'selinux_context', 'extended_attributes', 'hard_link_count', 'inode_number', 'mount_point'
        ]);
    }
}
