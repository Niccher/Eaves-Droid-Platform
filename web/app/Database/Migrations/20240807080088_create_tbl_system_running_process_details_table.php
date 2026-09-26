<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSystemRunningProcessDetails extends Migration
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
            'running_processes_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'pid' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'process_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'uid' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'importance' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'importance_reason_code' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'pkg_list_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'lru' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'oom_score_adj' => [
                'type'       => 'SMALLINT',
                'null'       => true,
            ],
            'oom_score' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'threads_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'memory_rss_kb' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'memory_pss_kb' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'memory_shared_kb' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'cpu_time_ms' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'cpu_percent' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'open_fds' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'connection_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'network_bytes_sent' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'network_bytes_recv' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'wake_lock_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'alarm_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'service_start_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'service_bind_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_foreground_service' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'notification_channel_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'started_by_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'dependency_packages' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'seinfo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'appprocess_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'zombie' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('running_processes_id', false, false, 'running_processes_id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');

        $this->forge->createTable('tbl_system_running_process_details', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_system_running_process_details', true);
    }
}
