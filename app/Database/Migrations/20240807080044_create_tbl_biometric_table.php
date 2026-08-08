<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblBiometric extends Migration
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
            'sensor_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sensor_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sensor_strength' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'vendor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'version' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'max_enrollments' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'current_enrollments' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'enrolled_users' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'authenticator_id' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'challenge_counter' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'failed_attempts' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'lockout_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'lockout_permanent' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'hardware_auth_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'crypto_object_supported' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'invalidated_by_reenrollment' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'has_enrollments' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_hardware_detected' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_hardware_available' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'enrollment_progress' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'template_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'device_secure' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'weak_auth_timeout_ms' => [
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
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');

        $this->forge->createTable('tbl_biometric', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_biometric', true);
    }
}
