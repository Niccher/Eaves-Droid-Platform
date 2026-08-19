<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemVersionsAndChangelogsTables extends Migration
{
    public function up()
    {
        // 1. Create system_versions table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'version' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'unique'     => true,
            ],
            'build_number' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'release_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'release_type' => [
                'type'       => 'ENUM',
                'constraint' => ['major', 'minor', 'patch'],
                'default'    => 'minor',
            ],
            'is_current' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'released_at datetime default current_timestamp',
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('system_versions', true);

        // 2. Create system_changelogs table
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
        $this->forge->dropTable('system_versions', true);
    }
}
