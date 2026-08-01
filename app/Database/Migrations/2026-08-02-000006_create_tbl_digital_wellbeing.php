<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDigitalWellbeing extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_digital_wellbeing` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `focus_mode_enabled` tinyint(1) DEFAULT \'0\',
  `focus_mode_apps` text,
  `bedtime_mode_enabled` tinyint(1) DEFAULT \'0\',
  `bedtime_schedule` varchar(255) DEFAULT NULL,
  `bedtime_grayscale` tinyint(1) DEFAULT \'0\',
  `bedtime_dnd` tinyint(1) DEFAULT \'0\',
  `unlock_count` int DEFAULT NULL,
  `notification_count` int DEFAULT NULL,
  `wind_down_enabled` tinyint(1) DEFAULT \'0\',
  `wind_down_schedule` varchar(255) DEFAULT NULL,
  `total_daily_usage_minutes` bigint DEFAULT NULL,
  `social_minutes` bigint DEFAULT NULL,
  `productivity_minutes` bigint DEFAULT NULL,
  `entertainment_minutes` bigint DEFAULT NULL,
  `other_minutes` bigint DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');

        $this->db->query('CREATE TABLE `tbl_digital_wellbeing_apps` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `wellbeing_id` int unsigned DEFAULT NULL,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `app_timer_minutes` bigint DEFAULT NULL,
  `app_timer_spent_minutes` bigint DEFAULT NULL,
  `daily_usage_minutes` bigint DEFAULT NULL,
  `daily_limit_minutes` bigint DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wellbeing_id` (`wellbeing_id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `package_name` (`package_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_digital_wellbeing_apps`');
        $this->db->query('DROP TABLE IF EXISTS `tbl_digital_wellbeing`');
    }
}
