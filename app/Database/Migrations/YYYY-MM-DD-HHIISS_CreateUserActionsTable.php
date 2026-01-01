<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserActionsTable extends Migration
{
    public function up()
    {
        // Main user_actions table structure
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '128',
                'null'       => true,
            ],
            'action_category' => [
                'type'       => 'ENUM',
                'constraint' => ['authentication', 'file', 'profile', 'admin', 'system', 'security'],
                'null'       => false,
            ],
            'action_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
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
                'constraint' => '45',
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
                'constraint' => '100',
                'null'       => true,
            ],
            'operating_system' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'browser' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'country_code' => [
                'type'       => 'CHAR',
                'constraint' => '2',
                'null'       => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'request_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'       => true,
            ],
            'request_method' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
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
                'constraint' => '100',
                'null'       => true,
                'comment'    => 'e.g., file_id, post_id, etc.',
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
                'type'       => 'BOOLEAN',
                'default'    => true,
                'null'       => false,
            ],
            'error_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ],
        ]);

        // Primary Key
        $this->forge->addPrimaryKey('id');

        // Foreign Key (make sure your users table exists first)
        $this->forge->addForeignKey('user_id', 'tbl_users', 'id', 'CASCADE', 'SET NULL');

        // Create the table
        $this->forge->createTable('tbl_user_actions', true);

        // ========== ADD INDEXES ==========

        // Note: We'll add indexes in a separate method after table creation
        // because some indexes require raw SQL

        // Create indexes using the database connection
        $db = \Config\Database::connect();

        // 1. Composite index for user_id and created_at
        $db->query("CREATE INDEX idx_composite ON tbl_user_actions(user_id, created_at)");

        // 2. Index for action_category
        $db->query("CREATE INDEX idx_action_category ON tbl_user_actions(action_category)");

        // 3. Index for action_severity
        $db->query("CREATE INDEX idx_severity ON tbl_user_actions(action_severity)");

        // 4. Index for resource_id
        $db->query("CREATE INDEX idx_resource ON tbl_user_actions(resource_id)");

        // 5. Functional index on DATE(created_at) - MySQL 8+ only
        // For MySQL 5.7 or lower, we'll use a generated column approach
        $db->query("ALTER TABLE tbl_user_actions 
                    ADD COLUMN created_date DATE GENERATED ALWAYS AS (DATE(created_at)) STORED");

        $db->query("CREATE INDEX idx_created_date ON tbl_user_actions(created_date)");

        // 6. Additional useful indexes (optional but recommended)
        $db->query("CREATE INDEX idx_ip_address ON tbl_user_actions(ip_address)");
        $db->query("CREATE INDEX idx_action_type ON tbl_user_actions(action_type)");
        $db->query("CREATE INDEX idx_success ON tbl_user_actions(success)");
        $db->query("CREATE INDEX idx_created_at ON tbl_user_actions(created_at DESC)");

        // 7. Composite index for common queries
        $db->query("CREATE INDEX idx_user_category ON tbl_user_actions(user_id, action_category, created_at DESC)");
        $db->query("CREATE INDEX idx_category_type ON tbl_user_actions(action_category, action_type, created_at DESC)");
    }

    public function down()
    {
        // Drop foreign key first
        $this->forge->dropForeignKey('tbl_user_actions', 'tbl_user_actions_user_id_foreign');

        // Drop the table
        $this->forge->dropTable('tbl_user_actions', true);
    }
}