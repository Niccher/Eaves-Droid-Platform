<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblActivity extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_activity` (
  `counter` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned NOT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT \'feature_not_fully_implemented\',
  `activity_type` varchar(100) DEFAULT NULL,
  `confidence` int unsigned DEFAULT \'0\',
  `info` text,
  `is_interactive` tinyint(1) DEFAULT \'0\',
  `battery_level` int unsigned DEFAULT NULL,
  `charging_status` varchar(50) DEFAULT NULL,
  `network_type` varchar(50) DEFAULT NULL,
  `screen_on` tinyint(1) DEFAULT \'0\',
  `extracted_at` bigint unsigned DEFAULT NULL,
  `activity_time` bigint unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`counter`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`),
  KEY `activity_type` (`activity_type`),
  KEY `activity_time` (`activity_time`),
  KEY `owner_id_extracted_at` (`owner_id`,`extracted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1366 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_activity`');
    }
}
