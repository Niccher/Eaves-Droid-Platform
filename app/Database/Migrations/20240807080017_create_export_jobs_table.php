<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExportJobs extends Migration
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
            'job_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => false,
                'default'    => 'forensics',
            ],
            'requester_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'target_user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'params' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'queued',
            ],
            'result_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'result_size' => [
                'type'       => 'BIGINT',
                'null'       => false,
                'default'    => 0,
            ],
            'error_message' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'attempts' => [
                'type'       => 'INT',
                'null'       => false,
                'default'    => 0,
            ],
            'queued_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'processing_started_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'completed_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['status', 'queued_at'], false, false, 'status_queued_at');
        $this->forge->addKey('requester_id', false, false, 'requester_id');

        $this->forge->createTable('export_jobs', true);
    }

    public function down()
    {
        $this->forge->dropTable('export_jobs', true);
    }
}
