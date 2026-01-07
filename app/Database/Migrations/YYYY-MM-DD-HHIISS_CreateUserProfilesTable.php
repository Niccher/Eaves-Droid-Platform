<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserProfiles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],

            'profile_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'bio' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'last_seen_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'last_user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'unread_notifications' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'last_notification_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notifications_enabled' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'email_notifications' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'push_notifications' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],

            'language' => [
                'type'       => 'VARCHAR',
                'constraint' => 8,
                'default'    => 'en',
            ],
            'timezone' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'theme' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'default'    => 'system',
            ],

            'account_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'default'    => 'active',
            ],
            'suspended_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'last_deleted_data_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_exported_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'export_count' => [
                'type'    => 'INT',
                'default' => 0,
            ],

            'onboarding_completed' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'profile_completed' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],

            'created_at' => [
                'type'    => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP',
                'on_update' => 'CURRENT_TIMESTAMP',
            ],
        ]);

        // Primary Key
        $this->forge->addKey('user_id', true);

        // Foreign Key → Shield users table
        $this->forge->addForeignKey(
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('user_profiles', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_profiles', true);
    }
}
