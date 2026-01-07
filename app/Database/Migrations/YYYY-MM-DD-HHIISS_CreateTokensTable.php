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
            ],
            'owner_id' => [
                'type'     => 'INT',
                'unsigned' => true,
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
                'type'    => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP',
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
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'scopes' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('counter', true);
        $this->forge->addUniqueKey('token');

        // Uncomment when users table is stable
        // $this->forge->addForeignKey(
        //     'owner_id',
        //     'users',
        //     'id',
        //     'CASCADE',
        //     'CASCADE'
        // );

        $this->forge->createTable('tbl_tokens', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_tokens', true);
    }
}
