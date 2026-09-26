<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSystemAccessibilityServices extends Migration
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
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'service_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'package_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'feedback_type' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'capabilities' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'flags' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'notification_timeout' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'settings_activity_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'can_retrieve_window_content' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');

        $this->forge->createTable('tbl_system_accessibility_services', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_system_accessibility_services', true);
    }
}
