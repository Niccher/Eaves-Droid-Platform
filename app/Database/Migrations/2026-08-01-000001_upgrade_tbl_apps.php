<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblApps extends Migration
{
    public function up()
    {
        $fields = [
            'installer_package_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'last_update_time'
            ],
            'signatures_sha256' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'installer_package_name'
            ],
            'cert_expiry' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'signatures_sha256'
            ],
            'is_instant_app' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'is_system_app'
            ],
            'is_archived' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'is_instant_app'
            ],
            'is_suspended' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'is_archived'
            ],
            'hidden' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'is_suspended'
            ],
            'disabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'hidden'
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'disabled'
            ],
            'granted_runtime_permissions' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'permission_count'
            ],
            'denied_permissions' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'granted_runtime_permissions'
            ],
            'requested_permissions_flags' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'denied_permissions'
            ],
            'app_ops_mode' => [
                'type' => 'INT',
                'default' => -1,
                'after' => 'requested_permissions_flags'
            ],
            'activities' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'app_ops_mode'
            ],
            'services' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'activities'
            ],
            'receivers' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'services'
            ],
            'providers' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'receivers'
            ],
            'native_libs' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'providers'
            ],
            'abi' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'native_libs'
            ],
            'uses_libraries' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'abi'
            ],
            'data_dir_size' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'app_size'
            ],
            'cache_dir_size' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'data_dir_size'
            ],
            'external_files_size' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'cache_dir_size'
            ],
            'shared_prefs_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'external_files_size'
            ],
            'databases_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'shared_prefs_count'
            ],
            'last_used_time' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'databases_count'
            ],
            'first_launch_time' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'last_used_time'
            ],
            'launch_count_30d' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'first_launch_time'
            ],
            'total_foreground_time_30d' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'launch_count_30d'
            ],
        ];

        $this->forge->addColumn('tbl_apps', $fields);
    }

    public function down()
    {
        $columns = [
            'installer_package_name', 'signatures_sha256', 'cert_expiry',
            'is_instant_app', 'is_archived', 'is_suspended', 'hidden', 'disabled', 'category',
            'granted_runtime_permissions', 'denied_permissions', 'requested_permissions_flags', 'app_ops_mode',
            'activities', 'services', 'receivers', 'providers', 'native_libs', 'abi', 'uses_libraries',
            'data_dir_size', 'cache_dir_size', 'external_files_size', 'shared_prefs_count', 'databases_count',
            'last_used_time', 'first_launch_time', 'launch_count_30d', 'total_foreground_time_30d'
        ];
        $this->forge->dropColumn('tbl_apps', $columns);
    }
}
