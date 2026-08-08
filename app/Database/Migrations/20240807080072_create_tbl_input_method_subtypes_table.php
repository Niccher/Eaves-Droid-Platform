<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblInputMethodSubtypes extends Migration
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
            'input_method_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'locale' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'mode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'is_ascii_capable' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_auxiliary' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'overrides_implicitly_enabled_subtype' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('input_method_id', false, false, 'input_method_id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');

        $this->forge->createTable('tbl_input_method_subtypes', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_input_method_subtypes', true);
    }
}
