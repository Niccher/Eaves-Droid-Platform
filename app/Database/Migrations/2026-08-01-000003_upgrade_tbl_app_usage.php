<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblAppUsage extends Migration
{
    public function up()
    {
        $fields = [
            'background_time_ms' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'foreground_time_hours'
            ],
            'interactive_time_ms' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'background_time_ms'
            ],
            'screen_on_time_ms' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'interactive_time_ms'
            ],
            'keyguard_shown_time_ms' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'screen_on_time_ms'
            ],
            'session_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'keyguard_shown_time_ms'
            ],
            'session_durations_json' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'session_count'
            ],
            'first_launch_of_day' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'session_durations_json'
            ],
            'last_launch_of_day' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'first_launch_of_day'
            ],
            'longest_session_ms' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'last_launch_of_day'
            ],
            'shortest_session_ms' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'longest_session_ms'
            ],
            'avg_session_ms' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'shortest_session_ms'
            ],
            'distinct_days_used' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'avg_session_ms'
            ],
            'usage_by_hour_json' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'distinct_days_used'
            ],
            'usage_by_dow_json' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'usage_by_hour_json'
            ],
            'notification_seen_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'usage_by_dow_json'
            ],
            'notification_clicked_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'notification_seen_count'
            ],
            'app_standby_bucket' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'notification_clicked_count'
            ],
            'app_standby_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'app_standby_bucket'
            ],
        ];

        $this->forge->addColumn('tbl_app_usage', $fields);

        $this->forge->addColumn('tbl_app_usage_sessions', [
            'session_start_ms'    => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'session_end_ms'      => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'session_duration_ms' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $columns = [
            'background_time_ms', 'interactive_time_ms', 'screen_on_time_ms',
            'keyguard_shown_time_ms', 'session_count', 'session_durations_json',
            'first_launch_of_day', 'last_launch_of_day', 'longest_session_ms',
            'shortest_session_ms', 'avg_session_ms', 'distinct_days_used',
            'usage_by_hour_json', 'usage_by_dow_json',
            'notification_seen_count', 'notification_clicked_count',
            'app_standby_bucket', 'app_standby_reason'
        ];
        $this->forge->dropColumn('tbl_app_usage', $columns);

        $this->forge->dropColumn('tbl_app_usage_sessions', [
            'session_start_ms', 'session_end_ms', 'session_duration_ms'
        ]);
    }
}