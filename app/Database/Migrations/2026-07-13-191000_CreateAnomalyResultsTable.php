<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnomalyResultsTable extends Migration
{
    public function up()
    {
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
                'constraint' => '50', // e.g. SMS, Contacts, Locations
            ],
            'anomaly_text' => [
                'type' => 'TEXT',
            ],
            'severity' => [
                'type'       => 'VARCHAR',
                'constraint' => '20', // High, Medium, Low
            ],
            'algorithm_used' => [
                'type'       => 'VARCHAR',
                'constraint' => '100', // e.g. BERT Semantic Phishing Classifier
            ],
            'detected_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('run_id', 'anomaly_runs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('anomaly_results', true);
    }

    public function down()
    {
        $this->forge->dropTable('anomaly_results', true);
    }
}
