<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblRunningProcessDetails extends Migration
{
    public function up()
    {
        $fields = [
            'oom_score_adj' => [
                'type' => 'SMALLINT',
                'null' => true,
                'after' => 'lru'
            ],
            'oom_score' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'oom_score_adj'
            ],
            'threads_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'oom_score'
            ],
            'memory_rss_kb' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'threads_count'
            ],
            'memory_pss_kb' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'memory_rss_kb'
            ],
            'memory_shared_kb' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'memory_pss_kb'
            ],
            'cpu_time_ms' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'memory_shared_kb'
            ],
            'cpu_percent' => [
                'type' => 'FLOAT',
                'null' => true,
                'after' => 'cpu_time_ms'
            ],
            'open_fds' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'cpu_percent'
            ],
            'connection_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'open_fds'
            ],
            'network_bytes_sent' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'connection_count'
            ],
            'network_bytes_recv' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'network_bytes_sent'
            ],
            'wake_lock_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'network_bytes_recv'
            ],
            'alarm_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'wake_lock_count'
            ],
            'service_start_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'alarm_count'
            ],
            'service_bind_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'service_start_count'
            ],
            'is_foreground_service' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'after' => 'service_bind_count'
            ],
            'notification_channel_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'is_foreground_service'
            ],
            'started_by_package' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'notification_channel_id'
            ],
            'dependency_packages' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'started_by_package'
            ],
            'seinfo' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'dependency_packages'
            ],
            'appprocess_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'seinfo'
            ],
            'zombie' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'appprocess_name'
            ],
        ];

        $this->forge->addColumn('tbl_running_process_details', $fields);
    }

    public function down()
    {
        $columns = [
            'oom_score_adj', 'oom_score', 'threads_count',
            'memory_rss_kb', 'memory_pss_kb', 'memory_shared_kb',
            'cpu_time_ms', 'cpu_percent', 'open_fds', 'connection_count',
            'network_bytes_sent', 'network_bytes_recv',
            'wake_lock_count', 'alarm_count', 'service_start_count', 'service_bind_count',
            'is_foreground_service', 'notification_channel_id', 'started_by_package',
            'dependency_packages', 'seinfo', 'appprocess_name', 'zombie'
        ];
        $this->forge->dropColumn('tbl_running_process_details', $columns);
    }
}