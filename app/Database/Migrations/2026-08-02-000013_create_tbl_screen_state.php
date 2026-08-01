<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblScreenState extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_screen_state` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `timestamp` bigint DEFAULT NULL,
  `battery_level` int DEFAULT NULL,
  `unlock_method` varchar(100) DEFAULT NULL,
  `unlock_success` int DEFAULT NULL,
  `failed_attempts` int DEFAULT NULL,
  `strong_auth_required` tinyint(1) DEFAULT \'0\',
  `screen_brightness` int DEFAULT NULL,
  `auto_brightness` tinyint(1) DEFAULT \'0\',
  `doze_state` varchar(50) DEFAULT NULL,
  `keyguard_state` varchar(50) DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `timestamp` (`timestamp`),
  KEY `event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_screen_state`');
    }
}
