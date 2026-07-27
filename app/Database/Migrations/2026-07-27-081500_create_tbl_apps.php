<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblApps extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_apps` (
  `counter` int unsigned NOT NULL AUTO_INCREMENT,
  `package_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `app_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version_code` int DEFAULT NULL,
  `first_install_time` bigint unsigned DEFAULT NULL,
  `last_update_time` bigint unsigned DEFAULT NULL,
  `is_system_app` tinyint(1) NOT NULL DEFAULT \'0\',
  `target_sdk` int DEFAULT NULL,
  `min_sdk` int DEFAULT NULL,
  `permissions` text COLLATE utf8mb4_unicode_ci,
  `permission_count` int NOT NULL DEFAULT \'0\',
  `app_size` bigint unsigned NOT NULL DEFAULT \'0\',
  `app_icon` mediumtext COLLATE utf8mb4_unicode_ci,
  `device_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_model` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `android_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_id` int unsigned NOT NULL,
  `extracted_at` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT \'1\',
  `app_category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  PRIMARY KEY (`counter`),
  UNIQUE KEY `unique_package_device_owner` (`package_name`,`device_id`,`owner_id`),
  KEY `idx_package_name` (`package_name`),
  KEY `idx_device_id` (`device_id`),
  KEY `idx_is_system_app` (`is_system_app`),
  KEY `idx_owner_id` (`owner_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_updated_at` (`updated_at`),
  KEY `idx_app_name` (`app_name`),
  KEY `idx_version_name` (`version_name`),
  KEY `idx_first_install_time` (`first_install_time`),
  KEY `idx_last_update_time` (`last_update_time`),
  KEY `idx_extracted_at` (`extracted_at`),
  KEY `idx_app_category` (`app_category`),
  KEY `idx_last_seen` (`last_seen`),
  KEY `idx_device_id_is_system_app` (`device_id`,`is_system_app`),
  KEY `idx_owner_id_device_id` (`owner_id`,`device_id`),
  KEY `idx_is_system_app_app_size` (`is_system_app`,`app_size`),
  KEY `idx_owner_id_package_name` (`owner_id`,`package_name`),
  KEY `idx_owner_id_app_name` (`owner_id`,`app_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1357 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_apps`');
    }
}
