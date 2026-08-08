<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblApps extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'counter' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
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
                'null'       => true,
            ],
            'first_install_time' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'last_update_time' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'installer_package_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'signatures_sha256' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'cert_expiry' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_system_app' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'is_instant_app' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_archived' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_suspended' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'hidden' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'disabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'target_sdk' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'min_sdk' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'permissions' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'permission_count' => [
                'type'       => 'INT',
                'null'       => false,
                'default'    => 0,
            ],
            'granted_runtime_permissions' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'denied_permissions' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'requested_permissions_flags' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'app_ops_mode' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => -1,
            ],
            'activities' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'services' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'receivers' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'providers' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'native_libs' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'abi' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'uses_libraries' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'app_size' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'data_dir_size' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'cache_dir_size' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'external_files_size' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'shared_prefs_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'databases_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'last_used_time' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'first_launch_time' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'launch_count_30d' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'total_foreground_time_30d' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'app_icon' => [
                'type'       => 'MEDIUMTEXT',
                'null'       => true,
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
                'unsigned'   => true,
                'null'       => false,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'app_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
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
            'last_seen' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('counter');
        $this->forge->addUniqueKey(['package_name', 'device_id', 'owner_id'], 'unique_package_device_owner');
        $this->forge->addKey('package_name', false, false, 'idx_package_name');
        $this->forge->addKey('device_id', false, false, 'idx_device_id');
        $this->forge->addKey('is_system_app', false, false, 'idx_is_system_app');
        $this->forge->addKey('owner_id', false, false, 'idx_owner_id');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');
        $this->forge->addKey('updated_at', false, false, 'idx_updated_at');
        $this->forge->addKey('app_name', false, false, 'idx_app_name');
        $this->forge->addKey('version_name', false, false, 'idx_version_name');
        $this->forge->addKey('first_install_time', false, false, 'idx_first_install_time');
        $this->forge->addKey('last_update_time', false, false, 'idx_last_update_time');
        $this->forge->addKey('extracted_at', false, false, 'idx_extracted_at');
        $this->forge->addKey('app_category', false, false, 'idx_app_category');
        $this->forge->addKey('last_seen', false, false, 'idx_last_seen');
        $this->forge->addKey(['device_id', 'is_system_app'], false, false, 'idx_device_id_is_system_app');
        $this->forge->addKey(['owner_id', 'device_id'], false, false, 'idx_owner_id_device_id');
        $this->forge->addKey(['is_system_app', 'app_size'], false, false, 'idx_is_system_app_app_size');
        $this->forge->addKey(['owner_id', 'package_name'], false, false, 'idx_owner_id_package_name');
        $this->forge->addKey(['owner_id', 'app_name'], false, false, 'idx_owner_id_app_name');

        $this->forge->createTable('tbl_apps', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_apps', true);
    }
}
