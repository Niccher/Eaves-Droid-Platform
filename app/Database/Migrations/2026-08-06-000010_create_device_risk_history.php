<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeviceRiskHistory extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'device_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'score' => [
                'type' => 'TINYINT',
                'unsigned' => true,
                'default' => 0,
            ],
            'severity_counts' => [
                'type' => 'JSON',
            ],
            'top_findings' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'computed_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'device_id', 'computed_at']);
        $this->forge->createTable('device_risk_history');

        $this->db->query("ALTER TABLE `device_risk_history` 
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('device_risk_history');
    }
}