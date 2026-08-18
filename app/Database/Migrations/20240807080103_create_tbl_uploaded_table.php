<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblUploaded extends Migration
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
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'file_realname' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'file_size' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'file_extension' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'file_text' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'file_viewed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'file_downloaded' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('token', false, false, 'token');
        $this->forge->addKey('created_at', false, false, 'created_at');
        $this->forge->addKey('file_name', false, false, 'file_name');

        $this->forge->createTable('tbl_uploaded', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_uploaded', true);
    }
}
