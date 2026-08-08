<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthRememberTokens extends Migration
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
            'selector' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'hashedValidator' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'expires' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('selector', 'selector');
        $this->forge->addKey('user_id', false, false, 'auth_remember_tokens_user_id_foreign');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'RESTRICT', 'auth_remember_tokens_user_id_foreign');

        $this->forge->createTable('auth_remember_tokens', true);
    }

    public function down()
    {
        $this->forge->dropTable('auth_remember_tokens', true);
    }
}
