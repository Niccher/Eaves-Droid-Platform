<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCreatedAtToDeviceProfile extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_device_profile', [
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'counter'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_device_profile', 'created_at');
    }
}