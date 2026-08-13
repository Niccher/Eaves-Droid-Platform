<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFetchedAtToTblActivity extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_activity', [
            'fetched_at' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Timestamp when the device captured this reading (Unix milliseconds)',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_activity', 'fetched_at');
    }
}
