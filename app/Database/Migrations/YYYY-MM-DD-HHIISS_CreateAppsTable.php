<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAppsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            // Primary Key
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // ---------- From Java: Basic App Info ----------
            'package_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'comment'    => 'From packageInfo.packageName',
            ],

            'app_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'comment'    => 'From packageManager.getApplicationLabel(appInfo).toString()',
            ],

            'version_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'From packageInfo.versionName',
            ],

            'version_code' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'From packageInfo.versionCode',
            ],

            // ---------- From Java: Install/Update Times ----------
            'first_install_time' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'From packageInfo.firstInstallTime (API 28+) - milliseconds',
            ],

            'last_update_time' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'From packageInfo.lastUpdateTime (API 28+) - milliseconds',
            ],

            // ---------- From Java: System vs User App ----------
            'is_system_app' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'From (appInfo.flags & ApplicationInfo.FLAG_SYSTEM) != 0',
            ],

            // ---------- From Java: SDK Info ----------
            'target_sdk' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'From appInfo.targetSdkVersion',
            ],

            'min_sdk' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'From appInfo.minSdkVersion',
            ],

            // ---------- From Java: Permissions ----------
            'permissions' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array from packageInfo.requestedPermissions',
            ],

            'permission_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'From permissions.length()',
            ],

            // ---------- From Java: App Size ----------
            'app_size' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'From new File(appInfo.sourceDir).length() - bytes',
            ],

            // ---------- From Java: App Icon ----------
            'app_icon' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
                'comment' => 'Base64 encoded icon from appInfo.loadIcon(packageManager)',
            ],

            // ---------- From Java: Device Info ----------
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'From Settings.Secure.ANDROID_ID',
            ],

            'device_model' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'From Build.MODEL',
            ],

            'android_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'From Build.VERSION.RELEASE',
            ],

            // ---------- From Java: Metadata ----------
            'meta_owner' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'From getUserId(context)',
            ],

            'last_sync' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'From System.currentTimeMillis() - milliseconds',
            ],

            // ---------- Additional Tracking ----------
            'extracted_at' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'From result.put("extracted_at", System.currentTimeMillis())',
            ],

            // ---------- Your Original Metadata Fields ----------
            'meta_inserted' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'default'    => 'kotlin',
                'null'       => false,
                'comment'    => 'Source of insertion',
            ],

            'meta_opened' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Number of times opened',
            ],

            'meta_viewed' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Number of times viewed',
            ],

            'meta_print' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
                'null'       => false,
                'comment'    => 'Version/print count',
            ],

            // ---------- Status & Management ----------
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'comment'    => '1 = active, 0 = inactive',
            ],

            'app_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Category: game, social, utility, etc.',
            ],

            // ---------- Timestamps ----------
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ],

            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'null'    => false,
            ],

            'last_seen' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'comment' => 'Last time this app was seen on device',
            ],
        ]);

        // Add these to your tbl_Apps migration:
        $this->forge->addColumn('tbl_Apps', [
            'is_deleted' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'after'      => 'is_active', // Add after existing column
            ],
            'deleted_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'after'   => 'is_deleted',
            ],
            'deletion_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'deleted_at',
            ],
            'sync_status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'deleted_pending', 'deleted_synced'],
                'default'    => 'active',
                'null'       => false,
                'after'      => 'deletion_reason',
            ],
        ]);

        // Primary Key
        $this->forge->addPrimaryKey('id');

        // Unique constraint: Same app on same device should not duplicate
        $this->forge->addUniqueKey(['package_name', 'device_id']);

        // Create the table
        $this->forge->createTable('tbl_apps', true);

        // Add indexes for common queries using Forge method
        $this->forge->addKey('package_name');
        $this->forge->addKey('device_id');
        $this->forge->addKey('is_system_app');
        $this->forge->addKey('meta_owner');
        $this->forge->addKey('created_at');
        $this->forge->addKey('updated_at');
        $this->forge->addKey('last_sync');

        // Composite indexes for common query patterns
        $this->forge->addKey(['device_id', 'is_system_app']);
        $this->forge->addKey(['meta_owner', 'device_id']);
        $this->forge->addKey(['is_system_app', 'app_size']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_apps', true);
    }
}