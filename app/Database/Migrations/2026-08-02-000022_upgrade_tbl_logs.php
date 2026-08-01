<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblLogsTypeCode extends Migration
{
    public function up()
    {
        $fields = [
            'type_code' => ['type' => 'INT', 'default' => 0, 'after' => 'call_type'],
        ];
        $this->forge->addColumn('tbl_logs', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_logs', ['type_code']);
    }
}