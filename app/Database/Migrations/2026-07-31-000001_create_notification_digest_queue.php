<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationDigestQueue extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false],
            'category' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'system'],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'body' => ['type' => 'TEXT', 'null' => true],
            'priority' => ['type' => "enum('low','normal','high','critical')", 'default' => 'normal'],
            'is_read' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('category');
        $this->forge->addKey('sent_at');
        $this->forge->addKey('created_at');
        $this->forge->createTable('notification_digest_queue', true);
    }

    public function down()
    {
        $this->forge->dropTable('notification_digest_queue', true);
    }
}