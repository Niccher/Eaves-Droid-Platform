<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSupportMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'null'           => false,
                'auto_increment' => true,
            ],
            'client_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'sender_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'message' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'attachment' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'read_at' => [
                'type'       => 'DATETIME',
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
        $this->forge->addForeignKey('client_id', 'users', 'id', 'CASCADE', 'CASCADE', 'support_messages_client_id_foreign');
        $this->forge->addForeignKey('sender_id', 'users', 'id', 'CASCADE', 'CASCADE', 'support_messages_sender_id_foreign');
        $this->forge->addKey(['client_id', 'created_at'], false, false, 'client_id_created_at_idx');

        $this->forge->createTable('support_messages', true);
    }

    public function down()
    {
        $this->forge->dropTable('support_messages', true);
    }
}
