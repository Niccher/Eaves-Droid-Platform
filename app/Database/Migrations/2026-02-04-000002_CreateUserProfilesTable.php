<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserProfilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
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
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'last_notification_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notifications_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'email_notifications' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'push_notifications' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'onboarding_completed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'profile_completed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('user_id');
        $this->forge->addForeignKey(
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addKey('last_seen_at');
        $this->forge->addKey('last_ip');
        $this->forge->addKey('account_status');
        $this->forge->addKey('language');
        $this->forge->addKey('timezone');
        $this->forge->addKey('created_at');
        $this->forge->addKey('updated_at');
        $this->forge->addKey('notifications_enabled');
        $this->forge->addKey('profile_completed');

        $this->forge->createTable('user_profiles');
    }

    public function down()
    {
        $this->forge->dropTable('user_profiles');
    }
}