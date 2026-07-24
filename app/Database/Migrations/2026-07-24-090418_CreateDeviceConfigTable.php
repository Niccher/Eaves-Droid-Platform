<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeviceConfigTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'device_profile_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'config_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'permissions_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'device_info_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'last_synced_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('device_profile_id');
        $this->forge->addForeignKey('device_profile_id', 'tbl_device_profile', 'counter', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_device_config');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_device_config');
    }
}
