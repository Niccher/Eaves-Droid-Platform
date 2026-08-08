<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMlJobs extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'engine' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'python',
            ],
            'algorithms' => [
                'type'       => 'JSON',
                'null'       => false,
                'comment'    => '[\"sms_bert\",\"calls_isolation\",...]',
            ],
            'scope' => [
                'type'       => 'ENUM',
                'constraint' => ['full', 'incremental'],
                'null'       => false,
                'default'    => 'full',
            ],
            'incremental_since' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'Cutoff for incremental analysis',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'running', 'completed', 'failed'],
                'null'       => false,
                'default'    => 'pending',
            ],
            'progress_pct' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'total_algorithms' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'completed_algorithms' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => 0,
            ],
            'current_algorithm' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'error_message' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'algorithm_logs' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'results_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'error_msg' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'timing_ms' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
                'default'    => 'CURRENT_TIMESTAMP',
            ],
            'started_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'completed_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'status'], false, false, 'idx_user_status');
        $this->forge->addKey(['status', 'created_at'], false, false, 'idx_status_created');

        $this->forge->createTable('ml_jobs', true);
    }

    public function down()
    {
        $this->forge->dropTable('ml_jobs', true);
    }
}
