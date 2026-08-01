<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblHealthData extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_health_data` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `value` bigint DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `start_time` bigint DEFAULT NULL,
  `end_time` bigint DEFAULT NULL,
  `data_source` varchar(255) DEFAULT NULL,
  `data_source_type` varchar(50) DEFAULT NULL,
  `data_source_name` varchar(255) DEFAULT NULL,
  `data_source_package` varchar(255) DEFAULT NULL,
  `step_count` bigint DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `session_name` varchar(255) DEFAULT NULL,
  `session_type` varchar(255) DEFAULT NULL,
  `session_description` text,
  `distance_meters` bigint DEFAULT NULL,
  `calories_kcal` bigint DEFAULT NULL,
  `sleep_stage` varchar(50) DEFAULT NULL,
  `sleep_efficiency` double DEFAULT NULL,
  `workout_type` varchar(255) DEFAULT NULL,
  `workout_duration_seconds` bigint DEFAULT NULL,
  `max_heart_rate` int DEFAULT NULL,
  `avg_heart_rate` int DEFAULT NULL,
  `heart_rate_bpm` int DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `data_type` (`data_type`),
  KEY `end_time` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_health_data`');
    }
}
