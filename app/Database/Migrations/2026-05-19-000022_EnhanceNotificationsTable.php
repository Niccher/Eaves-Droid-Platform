<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnhanceNotificationsTable extends Migration
{
    public function up()
    {
        $fields = [
            'sender' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'text',
            ],
            'sub_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'sender',
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'sub_text',
            ],
            'visibility' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'category',
            ],
            'is_screen_notification' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'visibility',
            ],
        ];

        $this->forge->addColumn('tbl_notifications', $fields);
        $this->forge->addKey('sender');
        $this->forge->addKey('is_screen_notification');
        $this->forge->processIndexes('tbl_notifications');
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_notifications', [
            'sender',
            'sub_text',
            'category',
            'visibility',
            'is_screen_notification',
        ]);
    }
}
