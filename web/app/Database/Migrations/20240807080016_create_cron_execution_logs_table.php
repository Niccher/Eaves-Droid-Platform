<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCronExecutionLogs extends Migration
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
            'job_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'command' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'arguments' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'started_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'finished_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['running', 'success', 'failed'],
                'null'       => false,
                'default'    => 'running',
            ],
            'output' => [
                'type'       => 'LONGTEXT',
                'null'       => true,
            ],
            'duration_ms' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('job_id', false, false, 'job_id');
        $this->forge->addKey('started_at', false, false, 'started_at');
        $this->forge->addKey('status', false, false, 'status');

        $this->forge->createTable('cron_execution_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('cron_execution_logs', true);
    }
}
