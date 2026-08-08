<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblUsbDevices extends Migration
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
            'usb_device_id' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'vendor_id' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'product_id' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'device_class' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'device_subclass' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'device_protocol' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'manufacturer_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'product_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'serial_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'version' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'configuration_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'interface_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'endpoint_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'power_ma' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'speed' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'is_charging' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_debug_accessory' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_audio_accessory' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_midi' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_adb' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'connected_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'disconnected_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'total_bytes_transferred' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'has_permission' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
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
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'usb_device_id'], 'uq_usb_device_per_device');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('vendor_id', false, false, 'vendor_id');

        $this->forge->createTable('tbl_usb_devices', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_usb_devices', true);
    }
}
