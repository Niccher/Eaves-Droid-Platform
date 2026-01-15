<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTokenTestTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'ID' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true, // Normally ID is unsigned, but user spec didn't say. Best practice.
                'auto_increment' => true,
            ],
            'token_submitted' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'token_senttime' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'token_received' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'token_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'token_format' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
        ]);
        $this->forge->addKey('ID', true);
        $this->forge->createTable('tbl_Tokentest');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Tokentest');
    }
}
