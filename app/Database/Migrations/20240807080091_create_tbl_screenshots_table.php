<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblScreenshots extends Migration
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
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'file_path' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'file_size' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'width' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'height' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'timestamp' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'source_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_screen_record' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'duration_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'video_path' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'video_size' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'video_width' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'video_height' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'video_duration_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'video_frame_rate' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'video_bitrate' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'is_edited' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'edit_timestamp' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'edit_app_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'contains_pii' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'pii_types' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'detection_confidence' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
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
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('timestamp', false, false, 'timestamp');
        $this->forge->addKey('file_name', false, false, 'file_name');

        $this->forge->createTable('tbl_screenshots', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_screenshots', true);
    }
}
