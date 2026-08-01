<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblSensorProfile extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_sensor_profile', [
            'sensor_string_type'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'min_delay_us'               => ['type' => 'INT', 'null' => true],
            'max_delay_us'               => ['type' => 'INT', 'null' => true],
            'fifo_reserved_event_count'  => ['type' => 'INT', 'default' => 0],
            'fifo_max_event_count'       => ['type' => 'INT', 'default' => 0],
            'is_wakeup'                  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_dynamic'                 => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_additional_info'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'reporting_mode'             => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'required_permission'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'permission_display_name'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'flags'                      => ['type' => 'INT', 'default' => 0],
            'direct_channel_type'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'direct_report_rates'        => ['type' => 'TEXT', 'null' => true],
            'additional_info'            => ['type' => 'TEXT', 'null' => true],
            'calibration_params'         => ['type' => 'TEXT', 'null' => true],
            'mounting_matrix'            => ['type' => 'TEXT', 'null' => true],
            'drivetime_us'               => ['type' => 'BIGINT', 'null' => true],
            'event_time_ns'              => ['type' => 'BIGINT', 'null' => true],
            'sensor_max_range'           => ['type' => 'FLOAT', 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_sensor_profile', [
            'sensor_string_type', 'min_delay_us', 'max_delay_us', 'fifo_reserved_event_count',
            'fifo_max_event_count', 'is_wakeup', 'is_dynamic', 'is_additional_info',
            'reporting_mode', 'required_permission', 'permission_display_name', 'flags',
            'direct_channel_type', 'direct_report_rates', 'additional_info', 'calibration_params',
            'mounting_matrix', 'drivetime_us', 'event_time_ns', 'sensor_max_range'
        ]);
    }
}
