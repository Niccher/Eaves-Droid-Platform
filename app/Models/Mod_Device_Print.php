<?php

namespace App\Models;

use CodeIgniter\Model;

class DeviceModel extends Model
{
    protected $table = 'tbl_Devices';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        // Basic Device Info
        'device_model', 'device_brand', 'device_manufacturer',
        'device_product', 'device_device', 'device_board', 'device_hardware',

        // Android OS Info
        'android_version', 'android_sdk_int', 'android_codename',
        'android_incremental', 'android_base_os', 'android_security_patch',

        // Build Info
        'build_id', 'build_type', 'build_tags', 'build_fingerprint',
        'build_time', 'build_user', 'build_host', 'build_display',

        // Device Identifiers
        'android_id',

        // Display Info
        'display_width', 'display_height', 'display_density',
        'display_density_dpi', 'display_scaled_density',
        'display_xdpi', 'display_ydpi',

        // CPU & Memory
        'cpu_cores', 'cpu_abi', 'cpu_abis',
        'memory_total_mb', 'memory_free_mb',

        // Storage
        'internal_storage_total_gb', 'internal_storage_free_gb',
        'internal_storage_usable_gb', 'external_storage_total_gb',
        'external_storage_free_gb',

        // Network & Telephony
        'phone_number', 'sim_operator', 'network_operator',
        'sim_country', 'network_country', 'imei', 'meid',
        'device_id', 'sim_state',

        // Locale & Time
        'language', 'country', 'timezone', 'timezone_offset',
        'current_time', 'current_time_formatted',

        // Battery
        'battery_charging', 'battery_level',

        // Sensors
        'sensor_count',

        // Network
        'mac_address',

        // System
        'kernel_info',

        // Security
        'is_emulator', 'is_rooted',

        // App Info
        'app_package', 'app_version', 'app_version_code',
        'app_first_install', 'app_last_update',

        // Metadata
        'extraction_timestamp', 'extractor_version',
        'raw_device_json',

        // Server fields
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'android_id' => 'required',
        'device_model' => 'required',
        'extraction_timestamp' => 'required|numeric',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Find device by Android ID
     */
    public function findByAndroidId($androidId)
    {
        return $this->where('android_id', $androidId)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * Insert or update device data
     */
    public function saveDeviceData($deviceData)
    {
        // Check if device already exists
        $existing = $this->findByAndroidId($deviceData['android_id'] ?? '');

        if ($existing) {
            // Update existing record
            return $this->update($existing['id'], $deviceData);
        } else {
            // Insert new record
            return $this->insert($deviceData);
        }
    }
}