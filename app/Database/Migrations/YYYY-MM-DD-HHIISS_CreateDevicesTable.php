<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDevicesTable extends Migration
{
    public function up()
    {
        // Enable foreign key checks
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        // Drop table if exists (for development)
        $this->forge->dropTable('tbl_Devices', true);

        $this->forge->addField([
            // === PRIMARY KEY ===
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],

            // === BASIC DEVICE INFO ===
            'device_model' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'device_brand' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'device_manufacturer' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'device_product' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'device_device' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'device_board' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'device_hardware' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],

            // === ANDROID OS INFO ===
            'android_version' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'android_sdk_int' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'android_codename' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'android_incremental' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'android_base_os' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'android_security_patch' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],

            // === BUILD INFO ===
            'build_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'build_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'build_tags' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
                'constraint' => 100,
                'null' => true,
            ],
            'build_host' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'build_display' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],

            // === DEVICE IDENTIFIERS ===
            'android_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],

            // === DISPLAY INFO ===
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

            // === CPU & MEMORY INFO ===
            'cpu_cores' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'cpu_abi' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'cpu_abis' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'memory_total_mb' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'memory_free_mb' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],

            // === STORAGE INFO ===
            'internal_storage_total_gb' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'internal_storage_free_gb' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'internal_storage_usable_gb' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'external_storage_total_gb' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'external_storage_free_gb' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],

            // === NETWORK & TELEPHONY INFO ===
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'sim_operator' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'network_operator' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'sim_country' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'network_country' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'imei' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'meid' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'device_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Telephony device ID (pre-Android O)',
            ],
            'sim_state' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],

            // === LOCALE & TIME INFO ===
            'language' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'country' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'timezone' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
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
                'constraint' => 100,
                'null' => true,
            ],

            // === BATTERY INFO ===
            'battery_charging' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 0,
            ],
            'battery_level' => [
                'type' => 'FLOAT',
                'null' => true,
            ],

            // === SENSORS INFO ===
            'sensor_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],

            // === NETWORK INFO (MAC) ===
            'mac_address' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],

            // === KERNEL INFO ===
            'kernel_info' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // === SECURITY INFO ===
            'is_emulator' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 0,
            ],
            'is_rooted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 0,
            ],

            // === APP INFO ===
            'app_package' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'app_version' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'app_version_code' => [
                'type' => 'INT',
                'constraint' => 11,
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

            // === EXTRACTION METADATA ===
            'extraction_timestamp' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => false,
            ],
            'extractor_version' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'default' => '1.0',
            ],

            // === RAW JSON DATA (Optional - stores complete JSON) ===
            'raw_device_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'Complete JSON device data',
            ],

            // === SERVER METADATA ===
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 1,
            ],
        ]);

        // Primary Key
        $this->forge->addKey('id', true);

        // Indexes for better performance
        $this->forge->addKey('android_id');
        $this->forge->addKey('device_model');
        $this->forge->addKey('android_version');
        $this->forge->addKey('extraction_timestamp');
        $this->forge->addKey('created_at');
        $this->forge->addKey('is_active');

        // Unique constraint to prevent duplicate device entries
        $this->forge->addUniqueKey(['android_id', 'device_model', 'android_version'], 'unique_device_fingerprint');

        // Create the table
        $this->forge->createTable('tbl_Devices', true);

        // Add table comment
        $this->db->query("ALTER TABLE tbl_Devices COMMENT = 'Stores comprehensive device information from Android devices'");

        // Re-enable foreign key checks
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        // Drop the table
        $this->forge->dropTable('tbl_Devices', true);
    }
}