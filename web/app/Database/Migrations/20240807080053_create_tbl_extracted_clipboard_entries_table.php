<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblExtractedClipboardEntries extends Migration
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
            'clip_data_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'clip_text' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'clip_html' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'clip_intent_action' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'clip_intent_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'clip_uri' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'item_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'primary_clip_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
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
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'is_sensitive' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
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
        $this->forge->addKey('clip_data_type', false, false, 'clip_data_type');

        $this->forge->createTable('tbl_extracted_clipboard_entries', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_extracted_clipboard_entries', true);
    }
}
