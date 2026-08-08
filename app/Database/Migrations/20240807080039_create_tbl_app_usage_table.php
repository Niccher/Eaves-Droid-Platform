<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAppUsage extends Migration
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
                'null'       => false,
            ],
            'app_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'foreground_time_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
                'default'    => 0,
            ],
            'foreground_time_hours' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'default'    => 0,
            ],
            'background_time_ms' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'interactive_time_ms' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'screen_on_time_ms' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'keyguard_shown_time_ms' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'session_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'session_durations_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'first_launch_of_day' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'last_launch_of_day' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'longest_session_ms' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'shortest_session_ms' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'avg_session_ms' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'distinct_days_used' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'usage_by_hour_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'usage_by_dow_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'notification_seen_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'notification_clicked_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'app_standby_bucket' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'app_standby_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'foreground_time_minutes' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'times_opened' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'time_taken_formatted' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'last_time_used' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'is_system_app' => [
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
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'package_name', 'extracted_at'], 'uq_app_usage_snapshot');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('package_name', false, false, 'package_name');
        $this->forge->addKey('last_time_used', false, false, 'last_time_used');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        $this->forge->createTable('tbl_app_usage', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_app_usage', true);
    }
}
