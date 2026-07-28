<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblThermal extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_thermal` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `zone_name` varchar(100) DEFAULT NULL,
  `zone_type` varchar(50) DEFAULT NULL,
  `temp_raw` varchar(50) DEFAULT NULL,
  `temp_celsius` float DEFAULT NULL,
  `policy` varchar(255) DEFAULT NULL,
  `cpu_name` varchar(20) DEFAULT NULL,
  `core_limit_max` varchar(50) DEFAULT NULL,
  `package_limit_max` varchar(50) DEFAULT NULL,
  `throttle_count` varchar(50) DEFAULT NULL,
  `scaling_min_freq` varchar(50) DEFAULT NULL,
  `scaling_max_freq` varchar(50) DEFAULT NULL,
  `scaling_cur_freq` varchar(50) DEFAULT NULL,
  `scaling_governor` varchar(50) DEFAULT NULL,
  `data_type` varchar(20) DEFAULT NULL,
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_thermal`');
    }
}