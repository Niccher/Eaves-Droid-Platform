<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblReceive extends Migration
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
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'data_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'source' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => 'device',
            ],
            'record_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'received_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('data_type', false, false, 'data_type');
        $this->forge->addKey('source', false, false, 'source');
        $this->forge->addKey('received_at', false, false, 'received_at');

        $this->forge->createTable('tbl_receive', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_receive', true);
    }
}
