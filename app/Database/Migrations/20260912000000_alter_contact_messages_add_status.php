<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterContactMessagesAddStatus extends Migration
{
    public function up()
    {
        $fields = [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'in_progress', 'resolved'],
                'default'    => 'pending',
                'null'       => false,
            ],
        ];
        $this->forge->addColumn('contact_messages', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('contact_messages', 'status');
    }
}
