<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAppUsage extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_app_usage` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `package_name` varchar(255) NOT NULL,
  `app_name` varchar(255) DEFAULT NULL,
  `foreground_time_ms` bigint DEFAULT \'0\',
  `foreground_time_hours` float DEFAULT \'0\',
  `foreground_time_minutes` int DEFAULT \'0\',
  `times_opened` int DEFAULT \'0\',
  `time_taken_formatted` varchar(50) DEFAULT NULL,
  `last_time_used` bigint DEFAULT NULL,
  `is_system_app` tinyint(1) DEFAULT \'0\',
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_app_usage_snapshot` (`owner_id`,`device_id`,`package_name`,`extracted_at`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `package_name` (`package_name`),
  KEY `last_time_used` (`last_time_used`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4528 DEFAULT CHARSET=utf8mb3

CREATE TABLE `tbl_app_usage_sessions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `app_usage_id` int unsigned NOT NULL,
  `owner_id` int unsigned DEFAULT NULL,
  `event_type` varchar(30) DEFAULT NULL,
  `timestamp` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_usage_id` (`app_usage_id`),
  KEY `owner_id` (`owner_id`),
  KEY `event_type` (`event_type`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_app_usage`');
    }
}
