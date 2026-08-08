<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDeviceProfile extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'counter' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'device_model' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'device_brand' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'device_manufacturer' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'device_product' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'device_device' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'device_board' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'device_hardware' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'android_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'android_sdk_int' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'android_codename' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'android_incremental' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'android_base_os' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'android_security_patch' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'build_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'build_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'build_tags' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'build_fingerprint' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'build_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'build_user' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'build_host' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'build_display' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'android_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'display_width' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'display_height' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'display_density' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'display_density_dpi' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'display_scaled_density' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'display_xdpi' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'display_ydpi' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'cpu_cores' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'cpu_abi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'cpu_abis' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'memory_total_mb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'memory_free_mb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'internal_storage_total_gb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'internal_storage_free_gb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'internal_storage_usable_gb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'external_storage_total_gb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'external_storage_free_gb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sim_operator' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'network_operator' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sim_country' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'network_country' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'imei' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'meid' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'device_checksum' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'fcm_token' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'sim_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'language' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'timezone' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'timezone_offset' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'current_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'current_time_formatted' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'battery_charging' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'battery_level' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'sensor_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'mac_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'kernel_info' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'is_emulator' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'is_rooted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'app_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'app_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'app_version_code' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'app_first_install' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'app_last_update' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'extraction_timestamp' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'extractor_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'device_ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'screen_size_inches' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'display_refresh_rate' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'display_mode_width' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'display_mode_height' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'display_mode_refresh' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'cpu_detail' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'memory_available_mb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'max_heap_mb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'internal_storage_used_gb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'external_storage_used_gb' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'timezone_display_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'boot_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'battery_temperature_c' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'battery_voltage_mv' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'battery_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'has_accelerometer' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'has_gyroscope' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'has_magnetometer' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'has_proximity' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'has_light' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'kernel_version' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'is_debuggable' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'is_test_build' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'app_signature_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'app_target_sdk' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'app_installer' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'system_feature_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'system_features' => [
                'type'       => 'LONGTEXT',
                'null'       => true,
            ],
            'bootloader' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'radio_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'build_serial' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'java_vm_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'java_vm_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'os_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'user_language' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'user_region' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'uptime_seconds' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'uptime_boot' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'system_load' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'ui_mode' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'font_scale' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'screen_layout' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'smallest_width_dp' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'keyboard' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'touchscreen' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'navigation' => [
                'type'       => 'INT',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('counter');

        $this->forge->createTable('tbl_device_profile', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_device_profile', true);
    }
}
