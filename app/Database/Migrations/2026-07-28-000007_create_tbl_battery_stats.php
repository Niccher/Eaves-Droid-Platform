<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblBatteryStats extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_battery_stats` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `level_percent` float DEFAULT NULL,
  `is_charging` tinyint(1) DEFAULT NULL,
  `status` int DEFAULT NULL,
  `health` varchar(20) DEFAULT NULL,
  `temperature_celsius` float DEFAULT NULL,
  `voltage_mv` int DEFAULT NULL,
  `plugged_type` varchar(20) DEFAULT NULL,
  `technology` varchar(50) DEFAULT NULL,
  `capacity_percent` int DEFAULT NULL,
  `charge_counter_uah` bigint DEFAULT NULL,
  `current_now_ua` bigint DEFAULT NULL,
  `energy_counter_uwh` bigint DEFAULT NULL,
  `status_int` int DEFAULT NULL,
  `health_int` int DEFAULT NULL,
  `temperature_deci_c` int DEFAULT NULL,
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_battery_stats`');
    }
}