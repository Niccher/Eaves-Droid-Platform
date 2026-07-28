<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAlarms extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_alarms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `alarm_type` varchar(20) DEFAULT NULL,
  `job_id` int DEFAULT NULL,
  `service_class` varchar(255) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `is_periodic` tinyint(1) DEFAULT 0,
  `interval_millis` bigint DEFAULT NULL,
  `min_flex_millis` bigint DEFAULT NULL,
  `requires_charging` tinyint(1) DEFAULT 0,
  `requires_idle` tinyint(1) DEFAULT 0,
  `network_type` varchar(20) DEFAULT NULL,
  `persisted` tinyint(1) DEFAULT 0,
  `initial_delay_millis` bigint DEFAULT NULL,
  `minimum_latency_millis` bigint DEFAULT NULL,
  `important_foreground` tinyint(1) DEFAULT 0,
  `trigger_time` bigint DEFAULT NULL,
  `trigger_time_formatted` varchar(50) DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_alarms`');
    }
}