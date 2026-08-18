<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDeviceRiskHistory extends Migration
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
            'severity_counts' => [
                'type'       => 'JSON',
                'null'       => false,
            ],
            'top_findings' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'computed_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'created_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => false,
                'default'    => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'device_id', 'computed_at'], false, false, 'user_id_device_id_computed_at');

        $this->forge->createTable('tbl_device_risk_history', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_device_risk_history', true);
    }
}
