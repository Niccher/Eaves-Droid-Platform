<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblTokentest extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'ID' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'token_submitted' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'token_senttime' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'token_received' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'token_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'token_format' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
        ]);

        $this->forge->addPrimaryKey('ID');

        $this->forge->createTable('tbl_tokentest', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_tokentest', true);
    }
}
