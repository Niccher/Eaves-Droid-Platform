<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOwnerIdToDeviceProfile extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_device_profile', [
            'owner_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'device_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_device_profile', 'owner_id');
    }
}
