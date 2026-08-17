<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDeviceRiskScores extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'score' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'category_breakdown' => [
                'type'       => 'JSON',
                'null'       => false,
            ],
            'severity_counts' => [
                'type'       => 'JSON',
                'null'       => false,
            ],
            'top_findings' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'window_days' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 30,
            ],
            'computed_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'created_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => false,
                'default'    => 'CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['user_id', 'device_id'], 'user_id_device_id');

        $this->forge->createTable('tbl_device_risk_scores', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_device_risk_scores', true);
    }
}
