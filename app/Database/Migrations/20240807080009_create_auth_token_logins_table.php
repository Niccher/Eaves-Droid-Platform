<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthTokenLogins extends Migration
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
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'id_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'identifier' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'date' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'success' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['id_type', 'identifier'], false, false, 'id_type_identifier');
        $this->forge->addKey('user_id', false, false, 'user_id');

        $this->forge->createTable('auth_token_logins', true);
    }

    public function down()
    {
        $this->forge->dropTable('auth_token_logins', true);
    }
}
