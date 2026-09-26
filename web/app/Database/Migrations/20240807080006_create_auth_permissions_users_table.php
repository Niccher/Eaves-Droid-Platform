<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthPermissionsUsers extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'permission' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id', false, false, 'auth_permissions_users_user_id_foreign');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'RESTRICT', 'auth_permissions_users_user_id_foreign');

        $this->forge->createTable('auth_permissions_users', true);
    }

    public function down()
    {
        $this->forge->dropTable('auth_permissions_users', true);
    }
}
