<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateSystemVersionToV250 extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Ensure db_versions table exists
        if (!$db->tableExists('db_versions')) {
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
                'is_current' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'applied_at datetime default current_timestamp',
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->createTable('db_versions', true);
        }

        // 2. Set previous versions as not current in system_versions & db_versions
        if ($db->tableExists('system_versions')) {
            $db->table('system_versions')->update(['is_current' => 0]);
        }
        if ($db->tableExists('db_versions')) {
            $db->table('db_versions')->update(['is_current' => 0]);
        }

        // 3. Insert v2.5.0 into system_versions if table exists
        if ($db->tableExists('system_versions')) {
            $db->table('system_versions')->ignore(true)->insert([
                'version'      => '2.5.0',
                'build_number' => 20500,
                'release_name' => 'Analysis Suites Feature-Gating & Clean File Management',
                'release_type' => 'minor',
                'is_current'   => 1,
                'released_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        // 4. Insert v2.5.0 into db_versions
        if ($db->tableExists('db_versions')) {
            $db->table('db_versions')->ignore(true)->insert([
                'version'      => '2.5.0',
                'build_number' => 20500,
                'release_name' => 'Analysis Suites Feature-Gating & Clean File Management',
                'is_current'   => 1,
                'applied_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('system_versions')) {
            $db->table('system_versions')->where('version', '2.5.0')->delete();
        }
        if ($db->tableExists('db_versions')) {
            $db->table('db_versions')->where('version', '2.5.0')->delete();
        }
    }
}
