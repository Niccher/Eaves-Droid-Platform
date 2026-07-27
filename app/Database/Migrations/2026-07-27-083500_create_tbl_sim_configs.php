<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSimConfigs extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_sim_configs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sim_serial` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscriber_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sim_operator_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sim_country_iso` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sim_state` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_sim_changed` tinyint(1) DEFAULT \'0\',
  `captured_at` bigint DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `sim_serial` (`sim_serial`),
  KEY `captured_at` (`captured_at`),
  CONSTRAINT `fk_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_sim_configs`');
    }
}
