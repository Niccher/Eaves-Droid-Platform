<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeviceContextTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            // Battery
            'battery_level_percent' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'battery_is_charging' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ],
            'battery_plugged_usb' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ],
            'battery_plugged_ac' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ],
            'battery_temperature_celsius' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'battery_voltage_mv' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'battery_health' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            // Clipboard
            'clipboard_text' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Locale
            'locale_country' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'locale_display_country' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'locale_language' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'locale_display_language' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'locale_timezone' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'locale_timezone_offset_ms' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
            ],

            'extracted_at' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('extracted_at');
        $this->forge->addKey('locale_timezone');

        $this->forge->createTable('tbl_device_context');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_device_context');
    }
}
