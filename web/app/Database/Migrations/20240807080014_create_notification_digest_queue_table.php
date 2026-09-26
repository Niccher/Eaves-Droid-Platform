<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationDigestQueue extends Migration
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
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'default'    => 'system',
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'body' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'normal', 'high', 'critical'],
                'null'       => false,
                'default'    => 'normal',
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'sent_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id', false, false, 'user_id');
        $this->forge->addKey('category', false, false, 'category');
        $this->forge->addKey('sent_at', false, false, 'sent_at');
        $this->forge->addKey('created_at', false, false, 'created_at');

        $this->forge->createTable('notification_digest_queue', true);
    }

    public function down()
    {
        $this->forge->dropTable('notification_digest_queue', true);
    }
}
