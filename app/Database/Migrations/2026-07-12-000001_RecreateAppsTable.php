<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RecreateAppsTable extends Migration
{
    public function up()
    {
        // Drop existing table if it exists (data loss is acceptable per user request)
        $this->forge->dropTable('tbl_apps', true);

        // Recreate table with updated unique constraint
        $this->forge->addField([
            'counter' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'package_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'app_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'version_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'version_code' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'first_install_time' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'last_update_time' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_system_app' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'target_sdk' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'min_sdk' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'permissions' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'permission_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'app_size' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
            ],
            'app_icon' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'device_model' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'android_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
            ],
            'app_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_seen' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('counter');
        // Composite unique key now includes owner_id
        $this->forge->addUniqueKey(['package_name', 'device_id', 'owner_id']);
        $this->forge->addKey('package_name');
        $this->forge->addKey('device_id');
        $this->forge->addKey('is_system_app');
        $this->forge->addKey('owner_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey('updated_at');
        $this->forge->addKey('app_name');
        $this->forge->addKey('version_name');
        $this->forge->addKey('first_install_time');
        $this->forge->addKey('last_update_time');
        $this->forge->addKey('extracted_at');
        $this->forge->addKey('app_category');
        $this->forge->addKey('last_seen');
        $this->forge->addKey(['device_id', 'is_system_app']);
        $this->forge->addKey(['owner_id', 'device_id']);
        $this->forge->addKey(['is_system_app', 'app_size']);
        $this->forge->addKey(['owner_id', 'package_name']);
        $this->forge->addKey(['owner_id', 'app_name']);

        $this->forge->createTable('tbl_apps');
    }

    public function down()
    {
        // Drop the recreated table
        $this->forge->dropTable('tbl_apps', true);
    }
}
?>
