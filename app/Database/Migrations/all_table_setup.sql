-- ============================================================
-- Combined database schema from all migration files
-- Generated on: 2026-05-24
-- ============================================================

-- ------------------------------------------------------------
-- Table: contact_messages
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `attachment` VARCHAR(255) DEFAULT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: uploaded_files
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `uploaded_files` (
    `file_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `original_filename` VARCHAR(255) NOT NULL,
    `stored_filename` VARCHAR(255) NOT NULL,
    `file_size_bytes` BIGINT(20) UNSIGNED NOT NULL,
    `file_extension` VARCHAR(10) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_category` VARCHAR(50) NOT NULL,
    `token_used` VARCHAR(255) NOT NULL,
    `token_owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `device_checksum` VARCHAR(100) NOT NULL,
    `device_print_id` VARCHAR(100) NOT NULL,
    `android_id` VARCHAR(100) DEFAULT NULL,
    `upload_path` VARCHAR(500) NOT NULL,
    `upload_status` ENUM('pending','processing','parsed','failed','archived') DEFAULT 'pending',
    `upload_error` TEXT DEFAULT NULL,
    `parsed_at` DATETIME DEFAULT NULL,
    `parsed_records` INT(11) UNSIGNED DEFAULT 0,
    `parse_duration_ms` INT(11) UNSIGNED DEFAULT NULL,
    `uploaded_at` DATETIME DEFAULT NULL,
    `processed_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`file_id`),
    KEY `token_used` (`token_used`),
    KEY `token_owner_id` (`token_owner_id`),
    KEY `device_checksum` (`device_checksum`),
    KEY `file_category` (`file_category`),
    KEY `upload_status` (`upload_status`),
    KEY `uploaded_at` (`uploaded_at`),
    KEY `original_filename` (`original_filename`),
    KEY `file_extension` (`file_extension`),
    KEY `mime_type` (`mime_type`),
    KEY `android_id` (`android_id`),
    KEY `parsed_at` (`parsed_at`),
    KEY `processed_at` (`processed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: user_profiles
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_profiles` (
    `user_id` INT(11) UNSIGNED NOT NULL,
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `last_seen_at` DATETIME DEFAULT NULL,
    `last_ip` VARCHAR(45) DEFAULT NULL,
    `last_user_agent` VARCHAR(255) DEFAULT NULL,
    `unread_notifications` INT(11) UNSIGNED DEFAULT 0,
    `last_notification_at` DATETIME DEFAULT NULL,
    `notifications_enabled` TINYINT(1) DEFAULT 1,
    `email_notifications` TINYINT(1) DEFAULT 1,
    `push_notifications` TINYINT(1) DEFAULT 1,
    `language` VARCHAR(8) DEFAULT 'en',
    `timezone` VARCHAR(64) DEFAULT NULL,
    `theme` VARCHAR(16) DEFAULT 'system',
    `account_status` VARCHAR(16) DEFAULT 'active',
    `suspended_reason` VARCHAR(255) DEFAULT NULL,
    `last_deleted_data_at` DATETIME DEFAULT NULL,
    `last_exported_at` DATETIME DEFAULT NULL,
    `export_count` INT(11) UNSIGNED DEFAULT 0,
    `onboarding_completed` TINYINT(1) DEFAULT 0,
    `profile_completed` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    KEY `last_seen_at` (`last_seen_at`),
    KEY `last_ip` (`last_ip`),
    KEY `account_status` (`account_status`),
    KEY `language` (`language`),
    KEY `timezone` (`timezone`),
    KEY `created_at` (`created_at`),
    KEY `updated_at` (`updated_at`),
    KEY `notifications_enabled` (`notifications_enabled`),
    KEY `profile_completed` (`profile_completed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_tokens
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_tokens` (
    `counter` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `token` VARCHAR(128) NOT NULL,
    `token_type` ENUM('pin','qr') NOT NULL,
    `owner_id` INT(11) UNSIGNED NOT NULL,
    `initiator` VARCHAR(64) DEFAULT NULL,
    `status` VARCHAR(16) DEFAULT 'active',
    `created_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `device_checksum` VARCHAR(128) DEFAULT NULL,
    `android_id` VARCHAR(32) DEFAULT NULL,
    `device_name` VARCHAR(64) DEFAULT NULL,
    `last_used_at` DATETIME DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `is_refreshable` TINYINT(1) DEFAULT 0,
    `scopes` VARCHAR(128) DEFAULT NULL,
    PRIMARY KEY (`counter`),
    UNIQUE KEY `token` (`token`),
    KEY `token_type` (`token_type`),
    KEY `owner_id` (`owner_id`),
    KEY `status` (`status`),
    KEY `created_at` (`created_at`),
    KEY `expires_at` (`expires_at`),
    KEY `last_used_at` (`last_used_at`),
    KEY `device_checksum` (`device_checksum`),
    KEY `android_id` (`android_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_user_actions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_user_actions` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) UNSIGNED DEFAULT NULL,
    `session_id` VARCHAR(128) DEFAULT NULL,
    `action_category` ENUM('authentication','file','profile','admin','system','security') NOT NULL,
    `action_type` VARCHAR(100) NOT NULL,
    `action_severity` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'low',
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `device_type` ENUM('desktop','mobile','tablet','bot','unknown') DEFAULT NULL,
    `device_name` VARCHAR(100) DEFAULT NULL,
    `operating_system` VARCHAR(100) DEFAULT NULL,
    `browser` VARCHAR(100) DEFAULT NULL,
    `country_code` CHAR(2) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `request_url` VARCHAR(500) DEFAULT NULL,
    `request_method` VARCHAR(10) DEFAULT NULL,
    `response_code` SMALLINT(6) DEFAULT NULL,
    `execution_time_ms` INT(11) UNSIGNED DEFAULT NULL,
    `resource_id` VARCHAR(100) DEFAULT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `success` TINYINT(1) NOT NULL DEFAULT 1,
    `error_code` VARCHAR(50) DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `session_id` (`session_id`),
    KEY `action_category` (`action_category`),
    KEY `action_type` (`action_type`),
    KEY `action_severity` (`action_severity`),
    KEY `ip_address` (`ip_address`),
    KEY `resource_id` (`resource_id`),
    KEY `success` (`success`),
    KEY `created_at` (`created_at`),
    KEY `user_id_created_at` (`user_id`, `created_at`),
    KEY `action_category_action_type_created_at` (`action_category`, `action_type`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_devices
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_devices` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `device_model` VARCHAR(255) DEFAULT NULL,
    `device_brand` VARCHAR(100) DEFAULT NULL,
    `device_manufacturer` VARCHAR(100) DEFAULT NULL,
    `device_product` VARCHAR(100) DEFAULT NULL,
    `device_device` VARCHAR(100) DEFAULT NULL,
    `device_board` VARCHAR(100) DEFAULT NULL,
    `device_hardware` VARCHAR(100) DEFAULT NULL,
    `android_version` VARCHAR(50) DEFAULT NULL,
    `android_sdk_int` INT(11) DEFAULT NULL,
    `android_codename` VARCHAR(50) DEFAULT NULL,
    `android_incremental` VARCHAR(50) DEFAULT NULL,
    `android_base_os` VARCHAR(100) DEFAULT NULL,
    `android_security_patch` VARCHAR(50) DEFAULT NULL,
    `build_id` VARCHAR(100) DEFAULT NULL,
    `build_type` VARCHAR(50) DEFAULT NULL,
    `build_tags` VARCHAR(255) DEFAULT NULL,
    `build_fingerprint` TEXT DEFAULT NULL,
    `build_time` BIGINT(20) DEFAULT NULL,
    `build_user` VARCHAR(100) DEFAULT NULL,
    `build_host` VARCHAR(100) DEFAULT NULL,
    `build_display` VARCHAR(255) DEFAULT NULL,
    `android_id` VARCHAR(100) DEFAULT NULL,
    `display_width` INT(11) DEFAULT NULL,
    `display_height` INT(11) DEFAULT NULL,
    `display_density` FLOAT DEFAULT NULL,
    `display_density_dpi` INT(11) DEFAULT NULL,
    `display_scaled_density` FLOAT DEFAULT NULL,
    `display_xdpi` FLOAT DEFAULT NULL,
    `display_ydpi` FLOAT DEFAULT NULL,
    `cpu_cores` INT(11) DEFAULT NULL,
    `cpu_abi` VARCHAR(50) DEFAULT NULL,
    `cpu_abis` VARCHAR(255) DEFAULT NULL,
    `memory_total_mb` INT(11) DEFAULT NULL,
    `memory_free_mb` INT(11) DEFAULT NULL,
    `internal_storage_total_gb` INT(11) DEFAULT NULL,
    `internal_storage_free_gb` INT(11) DEFAULT NULL,
    `internal_storage_usable_gb` INT(11) DEFAULT NULL,
    `external_storage_total_gb` INT(11) DEFAULT NULL,
    `external_storage_free_gb` INT(11) DEFAULT NULL,
    `phone_number` VARCHAR(50) DEFAULT NULL,
    `sim_operator` VARCHAR(100) DEFAULT NULL,
    `network_operator` VARCHAR(100) DEFAULT NULL,
    `sim_country` VARCHAR(10) DEFAULT NULL,
    `network_country` VARCHAR(10) DEFAULT NULL,
    `imei` VARCHAR(50) DEFAULT NULL,
    `meid` VARCHAR(50) DEFAULT NULL,
    `device_id` VARCHAR(50) DEFAULT NULL,
    `sim_state` VARCHAR(50) DEFAULT NULL,
    `language` VARCHAR(10) DEFAULT NULL,
    `country` VARCHAR(10) DEFAULT NULL,
    `timezone` VARCHAR(100) DEFAULT NULL,
    `timezone_offset` INT(11) DEFAULT NULL,
    `current_time` BIGINT(20) DEFAULT NULL,
    `current_time_formatted` VARCHAR(100) DEFAULT NULL,
    `battery_charging` TINYINT(1) DEFAULT 0,
    `battery_level` FLOAT DEFAULT NULL,
    `sensor_count` INT(11) DEFAULT NULL,
    `mac_address` VARCHAR(50) DEFAULT NULL,
    `kernel_info` TEXT DEFAULT NULL,
    `is_emulator` TINYINT(1) DEFAULT 0,
    `is_rooted` TINYINT(1) DEFAULT 0,
    `app_package` VARCHAR(255) DEFAULT NULL,
    `app_version` VARCHAR(50) DEFAULT NULL,
    `app_version_code` INT(11) DEFAULT NULL,
    `app_first_install` BIGINT(20) DEFAULT NULL,
    `app_last_update` BIGINT(20) DEFAULT NULL,
    `extraction_timestamp` BIGINT(20) NOT NULL,
    `extractor_version` VARCHAR(20) DEFAULT '1.0',
    `raw_device_json` LONGTEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_device_fingerprint` (`android_id`, `device_model`, `android_version`),
    KEY `android_id` (`android_id`),
    KEY `device_model` (`device_model`),
    KEY `android_version` (`android_version`),
    KEY `extraction_timestamp` (`extraction_timestamp`),
    KEY `created_at` (`created_at`),
    KEY `is_active` (`is_active`),
    KEY `device_brand` (`device_brand`),
    KEY `android_sdk_int` (`android_sdk_int`),
    KEY `phone_number` (`phone_number`),
    KEY `sim_operator` (`sim_operator`),
    KEY `imei` (`imei`),
    KEY `is_rooted` (`is_rooted`),
    KEY `app_package` (`app_package`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_device_profile
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_device_profile` (
    `counter` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `device_model` VARCHAR(255) DEFAULT NULL,
    `device_brand` VARCHAR(255) DEFAULT NULL,
    `device_manufacturer` VARCHAR(255) DEFAULT NULL,
    `device_product` VARCHAR(255) DEFAULT NULL,
    `device_device` VARCHAR(255) DEFAULT NULL,
    `device_board` VARCHAR(255) DEFAULT NULL,
    `device_hardware` VARCHAR(255) DEFAULT NULL,
    `android_version` VARCHAR(50) DEFAULT NULL,
    `android_sdk_int` INT(11) DEFAULT NULL,
    `android_codename` VARCHAR(50) DEFAULT NULL,
    `android_incremental` VARCHAR(255) DEFAULT NULL,
    `android_base_os` VARCHAR(255) DEFAULT NULL,
    `android_security_patch` VARCHAR(50) DEFAULT NULL,
    `build_id` VARCHAR(255) DEFAULT NULL,
    `build_type` VARCHAR(50) DEFAULT NULL,
    `build_tags` VARCHAR(255) DEFAULT NULL,
    `build_fingerprint` TEXT DEFAULT NULL,
    `build_time` BIGINT(20) DEFAULT NULL,
    `build_user` VARCHAR(255) DEFAULT NULL,
    `build_host` VARCHAR(255) DEFAULT NULL,
    `build_display` TEXT DEFAULT NULL,
    `android_id` VARCHAR(255) DEFAULT NULL,
    `display_width` INT(11) DEFAULT NULL,
    `display_height` INT(11) DEFAULT NULL,
    `display_density` FLOAT DEFAULT NULL,
    `display_density_dpi` INT(11) DEFAULT NULL,
    `display_scaled_density` FLOAT DEFAULT NULL,
    `display_xdpi` FLOAT DEFAULT NULL,
    `display_ydpi` FLOAT DEFAULT NULL,
    `cpu_cores` INT(11) DEFAULT NULL,
    `cpu_abi` VARCHAR(255) DEFAULT NULL,
    `cpu_abis` TEXT DEFAULT NULL,
    `memory_total_mb` BIGINT(20) DEFAULT NULL,
    `memory_free_mb` BIGINT(20) DEFAULT NULL,
    `internal_storage_total_gb` BIGINT(20) DEFAULT NULL,
    `internal_storage_free_gb` BIGINT(20) DEFAULT NULL,
    `internal_storage_usable_gb` BIGINT(20) DEFAULT NULL,
    `external_storage_total_gb` BIGINT(20) DEFAULT NULL,
    `external_storage_free_gb` BIGINT(20) DEFAULT NULL,
    `phone_number` VARCHAR(50) DEFAULT NULL,
    `sim_operator` VARCHAR(255) DEFAULT NULL,
    `network_operator` VARCHAR(255) DEFAULT NULL,
    `sim_country` VARCHAR(50) DEFAULT NULL,
    `network_country` VARCHAR(50) DEFAULT NULL,
    `imei` VARCHAR(255) DEFAULT NULL,
    `meid` VARCHAR(255) DEFAULT NULL,
    `device_checksum` VARCHAR(100) DEFAULT NULL,
    `fcm_token` TEXT DEFAULT NULL,
    `device_id` VARCHAR(255) DEFAULT NULL,
    `sim_state` VARCHAR(50) DEFAULT NULL,
    `language` VARCHAR(50) DEFAULT NULL,
    `country` VARCHAR(50) DEFAULT NULL,
    `timezone` VARCHAR(255) DEFAULT NULL,
    `timezone_offset` INT(11) DEFAULT NULL,
    `current_time` BIGINT(20) DEFAULT NULL,
    `current_time_formatted` VARCHAR(255) DEFAULT NULL,
    `battery_charging` TINYINT(1) DEFAULT NULL,
    `battery_level` FLOAT DEFAULT NULL,
    `sensor_count` INT(11) DEFAULT NULL,
    `mac_address` VARCHAR(50) DEFAULT NULL,
    `kernel_info` TEXT DEFAULT NULL,
    `is_emulator` TINYINT(1) DEFAULT NULL,
    `is_rooted` TINYINT(1) DEFAULT NULL,
    `app_package` VARCHAR(255) DEFAULT NULL,
    `app_version` VARCHAR(50) DEFAULT NULL,
    `app_version_code` BIGINT(20) DEFAULT NULL,
    `app_first_install` BIGINT(20) DEFAULT NULL,
    `app_last_update` BIGINT(20) DEFAULT NULL,
    `extraction_timestamp` BIGINT(20) DEFAULT NULL,
    `extractor_version` VARCHAR(50) DEFAULT NULL,
    `device_ip_address` VARCHAR(45) DEFAULT NULL,
    PRIMARY KEY (`counter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_apps
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_apps` (
    `counter` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `package_name` VARCHAR(255) NOT NULL,
    `app_name` VARCHAR(255) NOT NULL,
    `version_name` VARCHAR(100) DEFAULT NULL,
    `version_code` INT(11) DEFAULT NULL,
    `first_install_time` BIGINT(20) UNSIGNED DEFAULT NULL,
    `last_update_time` BIGINT(20) UNSIGNED DEFAULT NULL,
    `is_system_app` TINYINT(1) NOT NULL DEFAULT 0,
    `target_sdk` INT(11) DEFAULT NULL,
    `min_sdk` INT(11) DEFAULT NULL,
    `permissions` TEXT DEFAULT NULL,
    `permission_count` INT(11) NOT NULL DEFAULT 0,
    `app_size` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
    `app_icon` MEDIUMTEXT DEFAULT NULL,
    `device_id` VARCHAR(100) NOT NULL,
    `device_model` VARCHAR(100) DEFAULT NULL,
    `android_version` VARCHAR(50) DEFAULT NULL,
    `owner_id` INT(11) UNSIGNED NOT NULL,
    `extracted_at` BIGINT(20) UNSIGNED DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `app_category` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `last_seen` DATETIME DEFAULT NULL,
    PRIMARY KEY (`counter`),
    UNIQUE KEY `package_name_device_id` (`package_name`, `device_id`),
    KEY `package_name` (`package_name`),
    KEY `device_id` (`device_id`),
    KEY `is_system_app` (`is_system_app`),
    KEY `owner_id` (`owner_id`),
    KEY `created_at` (`created_at`),
    KEY `updated_at` (`updated_at`),
    KEY `app_name` (`app_name`),
    KEY `version_name` (`version_name`),
    KEY `first_install_time` (`first_install_time`),
    KEY `last_update_time` (`last_update_time`),
    KEY `extracted_at` (`extracted_at`),
    KEY `app_category` (`app_category`),
    KEY `last_seen` (`last_seen`),
    KEY `device_id_is_system_app` (`device_id`, `is_system_app`),
    KEY `owner_id_device_id` (`owner_id`, `device_id`),
    KEY `is_system_app_app_size` (`is_system_app`, `app_size`),
    KEY `owner_id_package_name` (`owner_id`, `package_name`),
    KEY `owner_id_app_name` (`owner_id`, `app_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_contacts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_contacts` (
    `counter` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `contact_id` VARCHAR(100) DEFAULT NULL,
    `display_name` VARCHAR(255) NOT NULL,
    `phone_numbers` TEXT DEFAULT NULL,
    `phone_count` INT(11) NOT NULL DEFAULT 0,
    `emails` TEXT DEFAULT NULL,
    `email_count` INT(11) NOT NULL DEFAULT 0,
    `photo_uri` VARCHAR(500) DEFAULT NULL,
    `companies` TEXT DEFAULT NULL,
    `addresses` TEXT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `is_favorite` TINYINT(1) NOT NULL DEFAULT 0,
    `last_contacted` BIGINT(20) UNSIGNED DEFAULT NULL,
    `contact_frequency` INT(11) NOT NULL DEFAULT 0,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `extracted_at` BIGINT(20) UNSIGNED DEFAULT NULL,
    `owner_id` INT(11) UNSIGNED NOT NULL,
    `is_synced` TINYINT(1) NOT NULL DEFAULT 0,
    `sync_count` INT(11) NOT NULL DEFAULT 0,
    `last_sync` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`counter`),
    UNIQUE KEY `contact_id_device_id_owner_id` (`contact_id`, `device_id`, `owner_id`),
    KEY `display_name` (`display_name`),
    KEY `contact_id` (`contact_id`),
    KEY `device_id` (`device_id`),
    KEY `owner_id` (`owner_id`),
    KEY `is_favorite` (`is_favorite`),
    KEY `is_active` (`is_active`),
    KEY `created_at` (`created_at`),
    KEY `owner_id_device_id` (`owner_id`, `device_id`),
    KEY `owner_id_is_favorite` (`owner_id`, `is_favorite`),
    KEY `owner_id_display_name` (`owner_id`, `display_name`),
    KEY `owner_id_contact_frequency` (`owner_id`, `contact_frequency`),
    KEY `phone_count` (`phone_count`),
    KEY `email_count` (`email_count`),
    KEY `last_contacted` (`last_contacted`),
    KEY `extracted_at` (`extracted_at`),
    KEY `is_synced` (`is_synced`),
    KEY `last_sync` (`last_sync`),
    KEY `updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_logs
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_logs` (
    `counter` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `contact_name` VARCHAR(255) DEFAULT NULL,
    `phone_number` VARCHAR(50) DEFAULT NULL,
    `call_type` VARCHAR(50) NOT NULL,
    `call_date` BIGINT(20) UNSIGNED NOT NULL,
    `duration_seconds` INT(11) NOT NULL DEFAULT 0,
    `formatted_duration` VARCHAR(50) DEFAULT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `extracted_at` BIGINT(20) UNSIGNED DEFAULT NULL,
    `owner_id` INT(11) UNSIGNED NOT NULL,
    `is_synced` TINYINT(1) NOT NULL DEFAULT 0,
    `sync_count` INT(11) NOT NULL DEFAULT 0,
    `last_sync` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `call_direction` ENUM('incoming','outgoing','missed') DEFAULT NULL,
    `call_count` INT(11) NOT NULL DEFAULT 1,
    `timezone` VARCHAR(50) DEFAULT NULL,
    `geolocation` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`counter`),
    KEY `phone_number` (`phone_number`),
    KEY `call_date` (`call_date`),
    KEY `call_type` (`call_type`),
    KEY `device_id` (`device_id`),
    KEY `owner_id` (`owner_id`),
    KEY `created_at` (`created_at`),
    KEY `updated_at` (`updated_at`),
    KEY `is_synced` (`is_synced`),
    KEY `owner_id_call_date` (`owner_id`, `call_date`),
    KEY `device_id_call_date` (`device_id`, `call_date`),
    KEY `phone_number_call_date` (`phone_number`, `call_date`),
    KEY `call_type_call_date` (`call_type`, `call_date`),
    KEY `contact_name` (`contact_name`),
    KEY `duration_seconds` (`duration_seconds`),
    KEY `extracted_at` (`extracted_at`),
    KEY `sync_count` (`sync_count`),
    KEY `last_sync` (`last_sync`),
    KEY `call_direction` (`call_direction`),
    KEY `call_count` (`call_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_sms
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_sms` (
    `counter` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `android_sms_id` BIGINT(20) NOT NULL,
    `thread_id` BIGINT(20) NOT NULL,
    `address` VARCHAR(50) NOT NULL,
    `formatted_address` VARCHAR(100) DEFAULT NULL,
    `body` LONGTEXT NOT NULL,
    `body_length` INT(11) NOT NULL DEFAULT 0,
    `sms_date` BIGINT(20) UNSIGNED NOT NULL,
    `sms_date_sent` BIGINT(20) UNSIGNED DEFAULT NULL,
    `sms_type` VARCHAR(20) NOT NULL,
    `type_code` INT(5) NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `is_seen` TINYINT(1) NOT NULL DEFAULT 0,
    `status_code` INT(11) NOT NULL DEFAULT 0,
    `error_code` INT(11) NOT NULL DEFAULT 0,
    `protocol` INT(11) NOT NULL,
    `protocol_type` VARCHAR(20) NOT NULL,
    `service_center` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(255) DEFAULT NULL,
    `is_locked` TINYINT(1) NOT NULL DEFAULT 0,
    `creator` VARCHAR(255) DEFAULT NULL,
    `owner_id` INT(11) UNSIGNED NOT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `extracted_at` BIGINT(20) UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`counter`),
    UNIQUE KEY `android_sms_id_device_id_owner_id` (`android_sms_id`, `device_id`, `owner_id`),
    KEY `address` (`address`),
    KEY `sms_date` (`sms_date`),
    KEY `owner_id` (`owner_id`),
    KEY `thread_id` (`thread_id`),
    KEY `sms_type` (`sms_type`),
    KEY `is_read` (`is_read`),
    KEY `is_seen` (`is_seen`),
    KEY `status_code` (`status_code`),
    KEY `error_code` (`error_code`),
    KEY `protocol_type` (`protocol_type`),
    KEY `extracted_at` (`extracted_at`),
    KEY `created_at` (`created_at`),
    KEY `updated_at` (`updated_at`),
    KEY `owner_id_sms_date` (`owner_id`, `sms_date`),
    KEY `device_id_sms_date` (`device_id`, `sms_date`),
    KEY `owner_id_address` (`owner_id`, `address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_location
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_location` (
    `counter` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED NOT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `latitude` DECIMAL(10,8) DEFAULT NULL COMMENT 'GPS latitude coordinate',
    `longitude` DECIMAL(11,8) DEFAULT NULL COMMENT 'GPS longitude coordinate',
    `accuracy` FLOAT(10) DEFAULT NULL COMMENT 'Location accuracy in meters',
    `altitude` FLOAT(10) DEFAULT NULL COMMENT 'Altitude in meters above sea level',
    `bearing` FLOAT(10) DEFAULT NULL COMMENT 'Bearing in degrees',
    `speed` FLOAT(10) DEFAULT NULL COMMENT 'Speed in meters/second',
    `provider` VARCHAR(50) DEFAULT NULL COMMENT 'Location provider (gps, network, etc.)',
    `location_time` BIGINT(20) UNSIGNED DEFAULT NULL COMMENT 'Timestamp when location was recorded (Unix milliseconds)',
    `status` VARCHAR(50) DEFAULT 'no_location_found' COMMENT 'success, no_location_found, error',
    `extracted_at` BIGINT(20) UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`counter`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `extracted_at` (`extracted_at`),
    KEY `location_time` (`location_time`),
    KEY `owner_id_extracted_at` (`owner_id`, `extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores device location data extracted from Android';

-- ------------------------------------------------------------
-- Table: tbl_activity
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_activity` (
    `counter` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED NOT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `status` VARCHAR(100) DEFAULT 'feature_not_fully_implemented' COMMENT 'Status of activity recognition',
    `activity_type` VARCHAR(100) DEFAULT NULL COMMENT 'Detected activity type (walking, running, driving, etc.)',
    `confidence` INT(3) UNSIGNED DEFAULT 0 COMMENT 'Confidence level (0-100)',
    `info` TEXT DEFAULT NULL COMMENT 'Additional information about activity',
    `is_interactive` TINYINT(1) DEFAULT 0 COMMENT '1 = device interactive, 0 = not interactive',
    `battery_level` INT(3) UNSIGNED DEFAULT NULL COMMENT 'Battery percentage (0-100)',
    `charging_status` VARCHAR(50) DEFAULT NULL COMMENT 'charging, discharging, full, unknown',
    `network_type` VARCHAR(50) DEFAULT NULL COMMENT 'wifi, mobile, ethernet, unknown',
    `screen_on` TINYINT(1) DEFAULT 0 COMMENT '1 = screen on, 0 = screen off',
    `extracted_at` BIGINT(20) UNSIGNED DEFAULT NULL,
    `activity_time` BIGINT(20) UNSIGNED DEFAULT NULL COMMENT 'Timestamp when activity was detected (Unix milliseconds)',
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`counter`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `extracted_at` (`extracted_at`),
    KEY `activity_type` (`activity_type`),
    KEY `activity_time` (`activity_time`),
    KEY `owner_id_extracted_at` (`owner_id`, `extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores device activity and sensor data extracted from Android';

-- ------------------------------------------------------------
-- Table: tbl_captured_media
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_captured_media` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED NOT NULL,
    `device_id` VARCHAR(100) NOT NULL,
    `media_type` ENUM('audio','image') NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `stored_filename` VARCHAR(255) NOT NULL,
    `file_size` BIGINT(20) UNSIGNED NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_record_id` INT(11) UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `media_type` (`media_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_notifications
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_notifications` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `notification_id` INT(11) DEFAULT NULL,
    `package_name` VARCHAR(255) DEFAULT NULL,
    `app_name` VARCHAR(255) DEFAULT NULL,
    `title` VARCHAR(500) DEFAULT NULL,
    `text` TEXT DEFAULT NULL,
    `sender` VARCHAR(255) DEFAULT NULL,
    `sub_text` VARCHAR(500) DEFAULT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `visibility` VARCHAR(50) DEFAULT NULL,
    `is_screen_notification` TINYINT(1) NOT NULL DEFAULT 0,
    `notification_timestamp` BIGINT(20) DEFAULT NULL,
    `action` VARCHAR(20) DEFAULT NULL COMMENT 'POSTED or REMOVED',
    `extracted_at` BIGINT(20) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_notification_entry` (`owner_id`, `device_id`, `notification_id`, `notification_timestamp`, `action`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `package_name` (`package_name`),
    KEY `action` (`action`),
    KEY `notification_timestamp` (`notification_timestamp`),
    KEY `extracted_at` (`extracted_at`),
    KEY `sender` (`sender`),
    KEY `is_screen_notification` (`is_screen_notification`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_app_usage
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_app_usage` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `package_name` VARCHAR(255) NOT NULL,
    `app_name` VARCHAR(255) DEFAULT NULL,
    `foreground_time_ms` BIGINT(20) DEFAULT 0,
    `foreground_time_hours` FLOAT DEFAULT 0,
    `foreground_time_minutes` INT(11) DEFAULT 0,
    `times_opened` INT(11) DEFAULT 0,
    `time_taken_formatted` VARCHAR(50) DEFAULT NULL,
    `last_time_used` BIGINT(20) DEFAULT NULL,
    `is_system_app` TINYINT(1) DEFAULT 0,
    `extracted_at` BIGINT(20) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_app_usage_snapshot` (`owner_id`, `device_id`, `package_name`, `extracted_at`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `package_name` (`package_name`),
    KEY `last_time_used` (`last_time_used`),
    KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_app_usage_sessions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_app_usage_sessions` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `app_usage_id` INT(11) UNSIGNED NOT NULL,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `event_type` VARCHAR(30) DEFAULT NULL,
    `timestamp` BIGINT(20) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `app_usage_id` (`app_usage_id`),
    KEY `owner_id` (`owner_id`),
    KEY `event_type` (`event_type`),
    KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_calendar_events
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_calendar_events` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `event_id` VARCHAR(50) DEFAULT NULL,
    `title` VARCHAR(500) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `location` VARCHAR(500) DEFAULT NULL,
    `start_time` BIGINT(20) DEFAULT NULL,
    `end_time` BIGINT(20) DEFAULT NULL,
    `all_day` TINYINT(1) DEFAULT 0,
    `organizer` VARCHAR(255) DEFAULT NULL,
    `extracted_at` BIGINT(20) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_calendar_event_per_device` (`owner_id`, `device_id`, `event_id`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `event_id` (`event_id`),
    KEY `start_time` (`start_time`),
    KEY `organizer` (`organizer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_accounts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_accounts` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `account_name` VARCHAR(255) DEFAULT NULL,
    `account_type` VARCHAR(100) DEFAULT NULL,
    `summary_json` TEXT DEFAULT NULL,
    `total_count` INT(11) DEFAULT 0,
    `extracted_at` BIGINT(20) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_account_per_device` (`owner_id`, `device_id`, `account_name`, `account_type`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `account_type` (`account_type`),
    KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_network_info
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_network_info` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `is_connected` TINYINT(1) DEFAULT 0,
    `connection_type` VARCHAR(30) DEFAULT NULL,
    `is_roaming` TINYINT(1) DEFAULT 0,
    `network_operator_name` VARCHAR(100) DEFAULT NULL,
    `network_country_iso` VARCHAR(10) DEFAULT NULL,
    `sim_operator_name` VARCHAR(100) DEFAULT NULL,
    `sim_country_iso` VARCHAR(10) DEFAULT NULL,
    `sim_state` VARCHAR(30) DEFAULT NULL,
    `phone_type` VARCHAR(20) DEFAULT NULL,
    `device_imei` VARCHAR(30) DEFAULT NULL,
    `sim_serial` VARCHAR(30) DEFAULT NULL,
    `subscriber_id` VARCHAR(30) DEFAULT NULL,
    `wifi_ssid` VARCHAR(100) DEFAULT NULL,
    `wifi_bssid` VARCHAR(30) DEFAULT NULL,
    `wifi_link_speed` INT(11) DEFAULT NULL,
    `wifi_frequency` INT(11) DEFAULT NULL,
    `wifi_rssi` INT(11) DEFAULT NULL,
    `wifi_mac_address` VARCHAR(30) DEFAULT NULL,
    `wifi_ip_address` VARCHAR(50) DEFAULT NULL,
    `extracted_at` BIGINT(20) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `device_imei` (`device_imei`),
    KEY `connection_type` (`connection_type`),
    KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_nearby_wifi
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_nearby_wifi` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `network_info_id` INT(11) UNSIGNED NOT NULL,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `ssid` VARCHAR(100) DEFAULT NULL,
    `bssid` VARCHAR(30) DEFAULT NULL,
    `capabilities` VARCHAR(255) DEFAULT NULL,
    `level` INT(11) DEFAULT NULL,
    `frequency` INT(11) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `network_info_id` (`network_info_id`),
    KEY `owner_id` (`owner_id`),
    KEY `bssid` (`bssid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_device_context
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_device_context` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `battery_level_percent` FLOAT DEFAULT NULL,
    `battery_is_charging` TINYINT(1) DEFAULT 0,
    `battery_plugged_usb` TINYINT(1) DEFAULT 0,
    `battery_plugged_ac` TINYINT(1) DEFAULT 0,
    `battery_temperature_celsius` FLOAT DEFAULT NULL,
    `battery_voltage_mv` INT(11) DEFAULT NULL,
    `battery_health` VARCHAR(50) DEFAULT NULL,
    `clipboard_text` TEXT DEFAULT NULL,
    `locale_country` VARCHAR(10) DEFAULT NULL,
    `locale_display_country` VARCHAR(100) DEFAULT NULL,
    `locale_language` VARCHAR(10) DEFAULT NULL,
    `locale_display_language` VARCHAR(100) DEFAULT NULL,
    `locale_timezone` VARCHAR(100) DEFAULT NULL,
    `locale_timezone_offset_ms` BIGINT(20) DEFAULT NULL,
    `extracted_at` BIGINT(20) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `extracted_at` (`extracted_at`),
    KEY `locale_timezone` (`locale_timezone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_bluetooth
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_bluetooth` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `is_enabled` TINYINT(1) DEFAULT 0,
    `adapter_name` VARCHAR(100) DEFAULT NULL,
    `adapter_address` VARCHAR(30) DEFAULT NULL,
    `paired_count` INT(11) DEFAULT 0,
    `extracted_at` BIGINT(20) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_bluetooth_paired
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_bluetooth_paired` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `bluetooth_id` INT(11) UNSIGNED NOT NULL,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `bt_name` VARCHAR(100) DEFAULT NULL,
    `bt_address` VARCHAR(30) DEFAULT NULL,
    `bt_type` VARCHAR(20) DEFAULT NULL,
    `bond_state` VARCHAR(20) DEFAULT NULL,
    `alias` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `bluetooth_id` (`bluetooth_id`),
    KEY `owner_id` (`owner_id`),
    KEY `bt_address` (`bt_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_sensor_profile
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_sensor_profile` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `sensor_name` VARCHAR(255) DEFAULT NULL,
    `vendor` VARCHAR(100) DEFAULT NULL,
    `type_id` INT(11) DEFAULT NULL,
    `type_string` VARCHAR(100) DEFAULT NULL,
    `version` INT(11) DEFAULT NULL,
    `maximum_range` FLOAT DEFAULT NULL,
    `resolution` FLOAT DEFAULT NULL,
    `power_ma` FLOAT DEFAULT NULL,
    `extracted_at` BIGINT(20) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_sensor_per_snapshot` (`owner_id`, `device_id`, `type_id`, `extracted_at`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `type_id` (`type_id`),
    KEY `type_string` (`type_string`),
    KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_device_files
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_device_files` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `path` TEXT NOT NULL,
    `is_directory` TINYINT(1) DEFAULT 0,
    `size_bytes` BIGINT(20) UNSIGNED DEFAULT 0,
    `last_modified` BIGINT(20) UNSIGNED DEFAULT NULL,
    `extension` VARCHAR(20) DEFAULT NULL,
    `formatted_size` VARCHAR(50) DEFAULT NULL,
    `formatted_date` DATETIME DEFAULT NULL,
    `category` VARCHAR(50) DEFAULT NULL,
    `owner_id` INT(11) UNSIGNED NOT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `extracted_at` BIGINT(20) UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_Tokentest
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_Tokentest` (
    `ID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `token_submitted` VARCHAR(100) NOT NULL,
    `token_senttime` VARCHAR(20) NOT NULL,
    `token_received` VARCHAR(20) NOT NULL,
    `token_ip` VARCHAR(20) NOT NULL,
    `token_format` VARCHAR(20) NOT NULL,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tbl_security_audit
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_security_audit` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id` INT(11) UNSIGNED DEFAULT NULL,
    `device_id` VARCHAR(100) DEFAULT NULL,
    `vpn_active` TINYINT(1) DEFAULT 0,
    `proxy_active` TINYINT(1) DEFAULT 0,
    `user_ca_certs_json` TEXT DEFAULT NULL COMMENT 'JSON array of user-installed CA certificate details',
    `open_ports_json` TEXT DEFAULT NULL COMMENT 'JSON array of open TCP/UDP ports detected on device',
    `audit_timestamp` BIGINT(20) DEFAULT NULL COMMENT 'Unix ms timestamp from the device when audit was taken',
    `extracted_at` BIGINT(20) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `owner_id` (`owner_id`),
    KEY `device_id` (`device_id`),
    KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Security audit snapshots: VPN/proxy status, open ports, CA certs';

-- ============================================================
-- End of schema
-- ============================================================
