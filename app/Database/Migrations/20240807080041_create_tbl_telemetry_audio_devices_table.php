<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblTelemetryAudioDevices extends Migration
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
            'audio_device_id' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'device_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'product_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'is_sink' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_source' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'sample_rates' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'channel_masks' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'channel_counts' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'encoding' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'format' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'gain_min' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'gain_max' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'gain_step' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'latency_low_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'latency_high_ms' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'supported_uid' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'volume_handle' => [
                'type'       => 'INT',
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
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'audio_device_id'], 'uq_audio_device_per_device');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('device_type', false, false, 'device_type');

        $this->forge->createTable('tbl_telemetry_audio_devices', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_telemetry_audio_devices', true);
    }
}
