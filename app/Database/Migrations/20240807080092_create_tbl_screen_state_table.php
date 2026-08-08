<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblScreenState extends Migration
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
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'timestamp' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'battery_level' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'unlock_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'unlock_success' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'failed_attempts' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'strong_auth_required' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'screen_brightness' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'auto_brightness' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'doze_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'keyguard_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('timestamp', false, false, 'timestamp');
        $this->forge->addKey('event_type', false, false, 'event_type');

        $this->forge->createTable('tbl_screen_state', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_screen_state', true);
    }
}
