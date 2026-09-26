<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblExtractedBrowserHistory extends Migration
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
            'browser_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'url' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'visit_count' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'last_visit_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'typed_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'favicon_base64' => [
                'type'       => 'LONGTEXT',
                'null'       => true,
            ],
            'is_bookmark' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'bookmark_folder' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'transition_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'referrer_url' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'visit_duration_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'search_terms' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_incognito' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'domain' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'scheme' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'path_depth' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'query_params' => [
                'type'       => 'TEXT',
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
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'url`(255'], 'uq_browser_history_per_device');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('last_visit_time', false, false, 'last_visit_time');
        $this->forge->addKey('domain', false, false, 'domain');

        $this->forge->createTable('tbl_extracted_browser_history', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_extracted_browser_history', true);
    }
}
