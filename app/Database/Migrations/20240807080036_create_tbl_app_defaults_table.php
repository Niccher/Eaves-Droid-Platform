<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAppDefaults extends Migration
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
            'config_json' => [
                'type'       => 'JSON',
                'null'       => false,
            ],
            'version' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 1,
            ],
            'created_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
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
        $this->forge->addKey('created_by', false, false, 'created_by');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE', 'tbl_app_defaults_ibfk_1');

        $this->forge->createTable('tbl_app_defaults', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_app_defaults', true);
    }
}
