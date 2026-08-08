<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMlResults extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'sms | contacts | call_logs | locations | apps | files | activity | device_info',
            ],
            'algorithm' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Display name: LSTM Sequence Pattern Predictor',
            ],
            'algorithm_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Machine name: act_lstm',
            ],
            'severity' => [
                'type'       => 'ENUM',
                'constraint' => ['High', 'Medium', 'Low'],
                'null'       => false,
            ],
            'anomaly' => [
                'type'       => 'TEXT',
                'null'       => false,
                'comment'    => 'Human-readable description of the finding',
            ],
            'score' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,4',
                'null'       => false,
                'default'    => 0.0000,
            ],
            'duration_ms' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'event_timestamp' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'When the anomalous event occurred (from source data)',
            ],
            'details' => [
                'type'       => 'JSON',
                'null'       => true,
                'comment'    => 'Arbitrary metadata: feature vectors, z-scores, etc.',
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
                'default'    => 'CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('job_id', false, false, 'idx_job');
        $this->forge->addKey('user_id', false, false, 'idx_user');
        $this->forge->addKey(['job_id', 'severity'], false, false, 'idx_job_severity');
        $this->forge->addForeignKey('job_id', 'ml_jobs', 'id', 'CASCADE', 'RESTRICT', 'fk_ml_results_job');

        $this->forge->createTable('ml_results', true);
    }

    public function down()
    {
        $this->forge->dropTable('ml_results', true);
    }
}
