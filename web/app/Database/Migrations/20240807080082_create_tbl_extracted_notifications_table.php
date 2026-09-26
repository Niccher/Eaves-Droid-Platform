<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblExtractedNotifications extends Migration
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
            'notification_id' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'package_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'app_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'text' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'sender' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sub_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'visibility' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'is_screen_notification' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'notification_timestamp' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'POSTED or REMOVED',
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'channel_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'channel_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'channel_importance' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'group_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sort_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'is_ongoing' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_local_only' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'color' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'badge_icon' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'large_icon_base64' => [
                'type'       => 'LONGTEXT',
                'null'       => true,
            ],
            'actions' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'remote_input_history' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'people' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'shortcut_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'locus_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'bubble_metadata' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'settings_text' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'timeout_after' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'flags' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'suppressed_visual_effects' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'notification_id', 'notification_timestamp', 'action'], 'uq_notification_entry');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('package_name', false, false, 'package_name');
        $this->forge->addKey('action', false, false, 'action');
        $this->forge->addKey('notification_timestamp', false, false, 'notification_timestamp');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');
        $this->forge->addKey('sender', false, false, 'sender');
        $this->forge->addKey('is_screen_notification', false, false, 'is_screen_notification');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        $this->forge->createTable('tbl_extracted_notifications', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_extracted_notifications', true);
    }
}
