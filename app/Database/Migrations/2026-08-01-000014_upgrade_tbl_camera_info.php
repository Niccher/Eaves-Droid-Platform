<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblCameraInfo extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_camera_info', [
            'pixel_array_size'               => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'active_array_size'              => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'pixel_size_um'                  => ['type' => 'FLOAT', 'null' => true],
            'max_analog_sensitivity'         => ['type' => 'INT', 'null' => true],
            'max_digital_zoom'               => ['type' => 'FLOAT', 'null' => true],
            'optical_zoom_range'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'focal_lengths'                  => ['type' => 'TEXT', 'null' => true],
            'apertures'                      => ['type' => 'TEXT', 'null' => true],
            'filter_densities'               => ['type' => 'TEXT', 'null' => true],
            'flash_info'                     => ['type' => 'TEXT', 'null' => true],
            'available_capabilities'         => ['type' => 'TEXT', 'null' => true],
            'available_request_keys'         => ['type' => 'TEXT', 'null' => true],
            'available_result_keys'          => ['type' => 'TEXT', 'null' => true],
            'available_characteristics_keys' => ['type' => 'TEXT', 'null' => true],
            'physical_camera_ids'            => ['type' => 'TEXT', 'null' => true],
            'logical_multi_camera'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'high_resolution_stream_config'  => ['type' => 'TEXT', 'null' => true],
            'min_frame_duration'             => ['type' => 'BIGINT', 'null' => true],
            'bokeh_capabilities'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'heic_support'                   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'hevc_support'                   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'av1_support'                    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            '10bit_output'                   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'hdr_capabilities'               => ['type' => 'TEXT', 'null' => true],
            'dynamic_range_profiles'         => ['type' => 'TEXT', 'null' => true],
            'night_mode_support'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'macro_mode_support'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'under_display_camera'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_camera_info', [
            'pixel_array_size', 'active_array_size', 'pixel_size_um', 'max_analog_sensitivity',
            'max_digital_zoom', 'optical_zoom_range', 'focal_lengths', 'apertures',
            'filter_densities', 'flash_info', 'available_capabilities', 'available_request_keys',
            'available_result_keys', 'available_characteristics_keys', 'physical_camera_ids',
            'logical_multi_camera', 'high_resolution_stream_config', 'min_frame_duration',
            'bokeh_capabilities', 'heic_support', 'hevc_support', 'av1_support', '10bit_output',
            'hdr_capabilities', 'dynamic_range_profiles', 'night_mode_support',
            'macro_mode_support', 'under_display_camera'
        ]);
    }
}
