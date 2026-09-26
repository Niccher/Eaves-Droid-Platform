<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblEmailLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'email_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'to_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'template' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'body' => [
                'type' => 'LONGTEXT',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('to_email');
        $this->forge->addKey('sent_at');
        $this->forge->createTable('tbl_email_logs');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_email_logs');
    }
}
