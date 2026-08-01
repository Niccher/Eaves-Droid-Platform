<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblPowerRails extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_power_rails` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `rail_name` varchar(255) DEFAULT NULL,
  `rail_type` varchar(100) DEFAULT NULL,
  `voltage_mv` bigint DEFAULT NULL,
  `voltage_min_mv` bigint DEFAULT NULL,
  `voltage_max_mv` bigint DEFAULT NULL,
  `current_ma` bigint DEFAULT NULL,
  `current_max_ma` bigint DEFAULT NULL,
  `power_mw` bigint DEFAULT NULL,
  `temperature_c` double DEFAULT NULL,
  `capacity_percent` int DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `health` varchar(100) DEFAULT NULL,
  `technology` varchar(100) DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT \'0\',
  `regulator_type` varchar(100) DEFAULT NULL,
  `mode` varchar(100) DEFAULT NULL,
  `efficiency_percent` int DEFAULT NULL,
  `remote_sense` tinyint(1) DEFAULT \'0\',
  `soft_start_us` bigint DEFAULT NULL,
  `ramp_delay_us` bigint DEFAULT NULL,
  `constraints` text,
  `num_consumers` int DEFAULT NULL,
  `consumer_names` text,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_power_rail_per_device` (`owner_id`,`device_id`,`rail_name`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `rail_type` (`rail_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_power_rails`');
    }
}
