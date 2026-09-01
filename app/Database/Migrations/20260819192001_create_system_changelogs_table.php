<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemChangelogsTable extends Migration
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
            'version_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'category' => [
                'type'       => 'ENUM',
                'constraint' => ['feature', 'capability', 'refactor', 'fix', 'security'],
                'default'    => 'feature',
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'component' => [
                'type'       => 'ENUM',
                'constraint' => ['platform', 'webapp', 'android', 'ml_engine'],
                'default'    => 'platform',
            ],
            'created_at datetime default current_timestamp',
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('version_id', false, false, 'idx_changelog_version_id');
        $this->forge->addForeignKey('version_id', 'system_versions', 'id', 'CASCADE', 'CASCADE', 'fk_changelog_version');
        $this->forge->createTable('system_changelogs', true);
    }

    public function down()
    {
        $this->forge->dropTable('system_changelogs', true);
    }
}
