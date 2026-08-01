<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDozeStandby extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_doze_standby` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `is_in_doze` tinyint(1) DEFAULT \'0\',
  `is_in_light_doze` tinyint(1) DEFAULT \'0\',
  `is_in_deep_doze` tinyint(1) DEFAULT \'0\',
  `power_save_mode` tinyint(1) DEFAULT \'0\',
  `battery_saver_enabled` tinyint(1) DEFAULT \'0\',
  `battery_saver_since` bigint DEFAULT NULL,
  `next_maintenance_window` bigint DEFAULT NULL,
  `last_standby_transition` bigint DEFAULT NULL,
  `adaptive_battery_enabled` tinyint(1) DEFAULT \'0\',
  `adaptive_battery_learning` tinyint(1) DEFAULT \'0\',
  `device_standby_bucket` varchar(50) DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');

        $this->db->query('CREATE TABLE `tbl_doze_standby_apps` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `doze_id` int unsigned DEFAULT NULL,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `whitelisted` tinyint(1) DEFAULT \'0\',
  `whitelist_reason` varchar(255) DEFAULT NULL,
  `last_standby_transition` bigint DEFAULT NULL,
  `restricted_reasons` text,
  `standby_bucket` varchar(50) DEFAULT NULL,
  `is_app_standby` tinyint(1) DEFAULT \'0\',
  `standby_bucket_reason` varchar(255) DEFAULT NULL,
  `restriction_level` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `doze_id` (`doze_id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `package_name` (`package_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_doze_standby_apps`');
        $this->db->query('DROP TABLE IF EXISTS `tbl_doze_standby`');
    }
}
