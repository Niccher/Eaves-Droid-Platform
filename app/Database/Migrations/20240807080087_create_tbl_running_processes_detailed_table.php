<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblRunningProcessesDetailed extends Migration
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
            'pid' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'ppid' => [
                'type'       => 'INT',
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
            'state' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'tid' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'nice' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'threads' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'vsize_kb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'vsize_peak_kb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'rss_kb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'pss_kb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'uss_kb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'swap_kb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'cpu_time_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'cpu_time_user_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'cpu_time_system_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'start_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'elapsed_time_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'processor' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'cmdline' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'gid' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'groups' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'priority' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'fd_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'socket_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'wake_lock_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'oom_score' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'oom_score_adj' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'cgroup' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'selinux_context' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'capabilities_eff' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'capabilities_prm' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'capabilities_inh' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'capabilities_bnd' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'capabilities_amb' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'seccomp_mode' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'env_vars' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'signal_mask' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'signal_pending' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'signal_blocked' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'signal_ignored' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'signal_caught' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'wake_channels' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'timer_slack_ns' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'namespace' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'open_files' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'memory_maps' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'stack_trace' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'cputime_clock_id' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'cpu_percent' => [
                'type'       => 'DOUBLE',
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
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'pid'], 'uq_running_process_detailed_per_device');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('pid', false, false, 'pid');
        $this->forge->addKey('name`(255', false, false, 'name');

        $this->forge->createTable('tbl_running_processes_detailed', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_running_processes_detailed', true);
    }
}
