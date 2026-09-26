<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblTelemetryCameras extends Migration
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
            'camera_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'lens_facing' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'sensor_orientation' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'pixel_array_width' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'pixel_array_height' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'physical_width_mm' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'physical_height_mm' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'available_focal_lengths' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'flash_available' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'available_effects' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'available_scene_modes' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'available_video_stabilization' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'available_ae_modes' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'available_af_modes' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'max_jpeg_width' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'max_jpeg_height' => [
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
            'pixel_array_size' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'active_array_size' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'pixel_size_um' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'max_analog_sensitivity' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'max_digital_zoom' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'optical_zoom_range' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'focal_lengths' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'apertures' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'filter_densities' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'flash_info' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'available_capabilities' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'available_request_keys' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'available_result_keys' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'available_characteristics_keys' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'physical_camera_ids' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'logical_multi_camera' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'high_resolution_stream_config' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'min_frame_duration' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'bokeh_capabilities' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'heic_support' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'hevc_support' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'av1_support' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            '10bit_output' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'hdr_capabilities' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'dynamic_range_profiles' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'night_mode_support' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'macro_mode_support' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'under_display_camera' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');

        $this->forge->createTable('tbl_telemetry_cameras', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_telemetry_cameras', true);
    }
}
