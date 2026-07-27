<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDeviceConfig extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_device_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `device_profile_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `config_json` json DEFAULT NULL,
  `permissions_json` json DEFAULT NULL,
  `device_info_json` json DEFAULT NULL,
  `last_synced_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_profile_id` (`device_profile_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_device_config_ibfk_1` FOREIGN KEY (`device_profile_id`) REFERENCES `tbl_device_profile` (`counter`) ON DELETE CASCADE,
  CONSTRAINT `tbl_device_config_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_device_config`');
    }
}
