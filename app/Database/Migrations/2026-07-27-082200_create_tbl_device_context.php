<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDeviceContext extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_device_context` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `battery_level_percent` float DEFAULT NULL,
  `battery_is_charging` tinyint(1) DEFAULT \'0\',
  `battery_plugged_usb` tinyint(1) DEFAULT \'0\',
  `battery_plugged_ac` tinyint(1) DEFAULT \'0\',
  `battery_temperature_celsius` float DEFAULT NULL,
  `battery_voltage_mv` int DEFAULT NULL,
  `battery_health` varchar(50) DEFAULT NULL,
  `clipboard_text` text,
  `locale_country` varchar(10) DEFAULT NULL,
  `locale_display_country` varchar(100) DEFAULT NULL,
  `locale_language` varchar(10) DEFAULT NULL,
  `locale_display_language` varchar(100) DEFAULT NULL,
  `locale_timezone` varchar(100) DEFAULT NULL,
  `locale_timezone_offset_ms` bigint DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`),
  KEY `locale_timezone` (`locale_timezone`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_device_context`');
    }
}
