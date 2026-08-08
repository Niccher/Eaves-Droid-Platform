<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMlAnalysisTracking extends Migration
{
    public function up()
    {
        $this->forge->addField([
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
            'last_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'last_analyzed_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'total_analyzed' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
                'default'    => 'CURRENT_TIMESTAMP',
                // ON UPDATE CURRENT_TIMESTAMP (add via raw query if required)
            ],
        ]);

        $this->forge->addPrimaryKey(['user_id', 'category']);

        $this->forge->createTable('ml_analysis_tracking', true);
    }

    public function down()
    {
        $this->forge->dropTable('ml_analysis_tracking', true);
    }
}
