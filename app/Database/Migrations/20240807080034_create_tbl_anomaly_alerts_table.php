<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAnomalyAlerts extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'alert_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'severity' => [
                'type'       => 'ENUM',
                'constraint' => ['High', 'Medium', 'Low'],
                'null'       => false,
                'default'    => 'Low',
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id', false, false, 'user_id');
        $this->forge->addKey('category', false, false, 'category');
        $this->forge->addKey('severity', false, false, 'severity');
        $this->forge->addKey('is_read', false, false, 'is_read');
        $this->forge->addKey('created_at', false, false, 'created_at');

        $this->forge->createTable('tbl_anomaly_alerts', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_anomaly_alerts', true);
    }
}
