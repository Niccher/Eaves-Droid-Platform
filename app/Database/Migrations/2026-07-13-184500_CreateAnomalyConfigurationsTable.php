<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnomalyConfigurationsTable extends Migration
{
    public function up()
    {
        // 1. Table for running configuration batches
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'device_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'null'           => true,
            ],
            'engine' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'php',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'pending', // pending, running, completed, failed
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('anomaly_runs', true);

        // 2. Table for selected algorithms per run
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'run_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'algorithm_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('run_id', 'anomaly_runs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('anomaly_run_algorithms', true);
    }

    public function down()
    {
        $this->forge->dropTable('anomaly_run_algorithms', true);
        $this->forge->dropTable('anomaly_runs', true);
    }
}
