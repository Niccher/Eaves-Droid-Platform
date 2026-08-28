<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastActiveToUsersTable extends Migration
{
    public function up()
    {
        $fields = [
            'last_active_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'last_active_at');
    }
}
