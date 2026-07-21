<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExtendedDeviceProfileColumns extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_device_profile', [
            // Display
            'screen_size_inches' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'display_refresh_rate' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'display_mode_width' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'display_mode_height' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'display_mode_refresh' => [
                'type' => 'FLOAT',
                'null' => true,
            ],

            // CPU / Memory
            'cpu_detail' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'memory_available_mb' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'max_heap_mb' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],

            // Storage
            'internal_storage_used_gb' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'external_storage_used_gb' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],

            // Locale / Time
            'timezone_display_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'boot_time' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],

            // Battery
            'battery_temperature_c' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'battery_voltage_mv' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'battery_source' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],

            // Sensors
            'has_accelerometer' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'has_gyroscope' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'has_magnetometer' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'has_proximity' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'has_light' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],

            // Kernel / Security
            'kernel_version' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_debuggable' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'is_test_build' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],

            // App metadata
            'app_signature_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'app_target_sdk' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'app_installer' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],

            // System features
            'system_feature_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'system_features' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            // Firmware
            'bootloader' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'radio_version' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'build_serial' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],

            // System properties
            'java_vm_version' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'java_vm_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'os_name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'user_language' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'user_region' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],

            // Runtime
            'uptime_seconds' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'uptime_boot' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'system_load' => [
                'type' => 'FLOAT',
                'null' => true,
            ],

            // Configuration
            'ui_mode' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'font_scale' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'screen_layout' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'smallest_width_dp' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'keyboard' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'touchscreen' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'navigation' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_device_profile', [
            'screen_size_inches',
            'display_refresh_rate',
            'display_mode_width',
            'display_mode_height',
            'display_mode_refresh',
            'cpu_detail',
            'memory_available_mb',
            'max_heap_mb',
            'internal_storage_used_gb',
            'external_storage_used_gb',
            'timezone_display_name',
            'boot_time',
            'battery_temperature_c',
            'battery_voltage_mv',
            'battery_source',
            'has_accelerometer',
            'has_gyroscope',
            'has_magnetometer',
            'has_proximity',
            'has_light',
            'kernel_version',
            'is_debuggable',
            'is_test_build',
            'app_signature_count',
            'app_target_sdk',
            'app_installer',
            'system_feature_count',
            'system_features',
            'bootloader',
            'radio_version',
            'build_serial',
            'java_vm_version',
            'java_vm_name',
            'os_name',
            'user_language',
            'user_region',
            'uptime_seconds',
            'uptime_boot',
            'system_load',
            'ui_mode',
            'font_scale',
            'screen_layout',
            'smallest_width_dp',
            'keyboard',
            'touchscreen',
            'navigation',
        ]);
    }
}
