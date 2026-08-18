<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDeviceConfigs extends Migration
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
            'device_profile_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'config_json' => [
                'type'       => 'JSON',
                'null'       => true,
                'comment'    => 'All app settings (sync, stealth, codes, URLs)',
            ],
            'permissions_json' => [
                'type'       => 'JSON',
                'null'       => true,
                'comment'    => 'Permission grant status with timestamps',
            ],
            'device_info_json' => [
                'type'       => 'JSON',
                'null'       => true,
                'comment'    => 'Current HW/SW state snapshot',
            ],
            'last_synced_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
                // ON UPDATE CURRENT_TIMESTAMP (add via raw query if required)
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('device_profile_id', 'device_profile_id');
        $this->forge->addKey('user_id', false, false, 'user_id');
        $this->forge->addForeignKey('device_profile_id', 'tbl_device_profiles', 'counter', 'CASCADE', 'RESTRICT', 'tbl_device_config_ibfk_1');

        $this->forge->createTable('tbl_device_configs', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_device_configs', true);
    }
}
