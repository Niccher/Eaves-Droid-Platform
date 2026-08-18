<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWidevineToDeviceProfiles extends Migration
{
    public function up()
    {
        $fields = [
            'widevine_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'android_id'
            ]
        ];
        
        $this->forge->addColumn('tbl_device_profiles', $fields);
        
        // Add index on widevine_id
        $this->db->query("ALTER TABLE tbl_device_profiles ADD INDEX idx_widevine_id (widevine_id)");
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_device_profiles', 'widevine_id');
    }
}
