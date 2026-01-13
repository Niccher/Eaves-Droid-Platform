<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserActionsTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => true,
            ],
            'action_category' => [
                'type'       => 'ENUM',
                'constraint' => ['authentication', 'file', 'profile', 'admin', 'system', 'security'],
                'null'       => false,
            ],
            'action_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'action_severity' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'medium', 'high', 'critical'],
                'default'    => 'low',
                'null'       => false,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => false,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'device_type' => [
                'type'       => 'ENUM',
                'constraint' => ['desktop', 'mobile', 'tablet', 'bot', 'unknown'],
                'null'       => true,
            ],
            'device_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'operating_system' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'browser' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'country_code' => [
                'type'       => 'CHAR',
                'constraint' => 2,
                'null'       => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'request_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'request_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'response_code' => [
                'type'       => 'SMALLINT',
                'constraint' => 6,
                'null'       => true,
            ],
            'execution_time_ms' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'resource_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'old_values' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'new_values' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'success' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
            ],
            'error_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');

        $this->forge->addKey('user_id');
        $this->forge->addKey('session_id');
        $this->forge->addKey('action_category');
        $this->forge->addKey('action_type');
        $this->forge->addKey('action_severity');
        $this->forge->addKey('ip_address');
        $this->forge->addKey('resource_id');
        $this->forge->addKey('success');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->addKey(['action_category', 'action_type', 'created_at']);

        $this->forge->createTable('tbl_user_actions');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_user_actions');
    }
}