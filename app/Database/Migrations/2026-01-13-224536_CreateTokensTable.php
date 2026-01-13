<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'counter' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
            ],
            'token_type' => [
                'type'       => 'ENUM',
                'constraint' => ['pin', 'qr'],
                'null'       => false,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'initiator' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'default'    => 'active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'device_checksum' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => true,
            ],
            'android_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
            ],
            'device_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'last_used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_refreshable' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'scopes' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('counter');
        $this->forge->addUniqueKey('token');

        $this->forge->addKey('token_type');
        $this->forge->addKey('owner_id');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->addKey('expires_at');
        $this->forge->addKey('last_used_at');
        $this->forge->addKey('device_checksum');
        $this->forge->addKey('android_id');

        // Foreign key commented as per original
        // $this->forge->addForeignKey('owner_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('tbl_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_tokens');
    }
}