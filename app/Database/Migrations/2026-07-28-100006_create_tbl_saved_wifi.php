<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSavedWifi extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_saved_wifi` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `ssid` varchar(255) DEFAULT NULL,
  `bssid` varchar(50) DEFAULT NULL,
  `network_id` int DEFAULT NULL,
  `priority` int DEFAULT NULL,
  `status` int DEFAULT NULL,
  `is_hidden` tinyint(1) DEFAULT 0,
  `security` varchar(50) DEFAULT NULL,
  `protocols_json` json DEFAULT NULL,
  `auth_algorithms_json` json DEFAULT NULL,
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_saved_wifi`');
    }
}