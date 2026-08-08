<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDigitalWellbeingApps extends Migration
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
            'wellbeing_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
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
            'package_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'app_timer_minutes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'app_timer_spent_minutes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'daily_usage_minutes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'daily_limit_minutes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('wellbeing_id', false, false, 'wellbeing_id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('package_name', false, false, 'package_name');

        $this->forge->createTable('tbl_digital_wellbeing_apps', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_digital_wellbeing_apps', true);
    }
}
