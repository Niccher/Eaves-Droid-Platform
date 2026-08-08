<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserProfiles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'profile_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'bio' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'last_seen_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
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
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'last_notification_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'notifications_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'email_notifications' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'push_notifications' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'language' => [
                'type'       => 'VARCHAR',
                'constraint' => 8,
                'null'       => false,
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
                'null'       => false,
                'default'    => 'system',
            ],
            'account_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => false,
                'default'    => 'active',
            ],
            'suspended_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'last_deleted_data_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'last_exported_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'export_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'onboarding_completed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'profile_completed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('user_id');
        $this->forge->addKey('last_seen_at', false, false, 'last_seen_at');
        $this->forge->addKey('last_ip', false, false, 'last_ip');
        $this->forge->addKey('account_status', false, false, 'account_status');
        $this->forge->addKey('language', false, false, 'language');
        $this->forge->addKey('timezone', false, false, 'timezone');
        $this->forge->addKey('created_at', false, false, 'created_at');
        $this->forge->addKey('updated_at', false, false, 'updated_at');
        $this->forge->addKey('notifications_enabled', false, false, 'notifications_enabled');
        $this->forge->addKey('profile_completed', false, false, 'profile_completed');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'user_profiles_user_id_foreign');

        $this->forge->createTable('user_profiles', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_profiles', true);
    }
}
