<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSystemCrashLogs extends Migration
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
            'package_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'process_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'pid' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'uid' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'crash_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'crash_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'exception_class' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'exception_message' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'stack_trace' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'build_fingerprint' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'android_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'device_model' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_system_app' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_silent' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_user_perceived' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'logcat_tail' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'dropbox_tag' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'dropbox_data' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'tombstone_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'minidump_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'last_crash_time' => [
                'type'       => 'BIGINT',
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
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'pid', 'crash_time'], 'uq_crash_log_per_device');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('crash_time', false, false, 'crash_time');
        $this->forge->addKey('package_name', false, false, 'package_name');

        $this->forge->createTable('tbl_system_crash_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_system_crash_logs', true);
    }
}
