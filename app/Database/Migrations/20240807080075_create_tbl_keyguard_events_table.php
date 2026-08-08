<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblKeyguardEvents extends Migration
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
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'timestamp' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'method' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'success' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'failed_attempts' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'remaining_attempts' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'lockout_until' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'strong_auth_required_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'biometric_error' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_secure' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'biometric_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'biometric_available' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'notifications_on_lockscreen' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'sensitive_notifications_hidden' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'lock_timeout_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'lock_screen_widgets' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'camera_shortcut' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'assistant_shortcut' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'storage_encryption_status' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'strong_auth_timeout_ms' => [
                'type'       => 'BIGINT',
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
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('timestamp', false, false, 'timestamp');
        $this->forge->addKey('event_type', false, false, 'event_type');

        $this->forge->createTable('tbl_keyguard_events', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_keyguard_events', true);
    }
}
