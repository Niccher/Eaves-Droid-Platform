<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeviceProfileTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'counter' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'device_model' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'device_brand' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'device_manufacturer' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'device_product' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'device_device' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'device_board' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'device_hardware' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'android_version' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'android_sdk_int' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'android_codename' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'android_incremental' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'android_base_os' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'android_security_patch' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'build_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'build_type' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'build_tags' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'build_fingerprint' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'build_time' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'build_user' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'build_host' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'build_display' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'android_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'display_width' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'display_height' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'display_density' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'display_density_dpi' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'display_scaled_density' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'display_xdpi' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'display_ydpi' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'cpu_cores' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'cpu_abi' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'cpu_abis' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'memory_total_mb' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'memory_free_mb' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'internal_storage_total_gb' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'internal_storage_free_gb' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'internal_storage_usable_gb' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'external_storage_total_gb' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'external_storage_free_gb' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'sim_operator' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'network_operator' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'sim_country' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'network_country' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'imei' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'meid' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'device_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'sim_state' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'language' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'country' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'timezone' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'timezone_offset' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'current_time' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'current_time_formatted' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'battery_charging' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'battery_level' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'sensor_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'mac_address' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'kernel_info' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_emulator' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'is_rooted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'app_package' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'app_version' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'app_version_code' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'app_first_install' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'app_last_update' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'extraction_timestamp' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'extractor_version' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'device_ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('counter', true);
        $this->forge->createTable('tbl_device_profile');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_device_profile');
    }
}
