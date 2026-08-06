<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeviceRisk extends Migration
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
            'category_breakdown' => [
                'type' => 'JSON',
            ],
            'severity_counts' => [
                'type' => 'JSON',
            ],
            'top_findings' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'window_days' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 30,
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
    $this->forge->addKey(['user_id', 'device_id'], '', true); // unique
    $this->forge->createTable('device_risk');
    
    // Use raw SQL for timestamp defaults (after table creation)
    $this->db->query("ALTER TABLE `device_risk` 
        MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('device_risk');
    }
}