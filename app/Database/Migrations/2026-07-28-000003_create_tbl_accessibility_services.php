<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAccessibilityServices extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_accessibility_services` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `service_id` varchar(255) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `description` text,
  `capabilities` text,
  `flags` int DEFAULT NULL,
  `notification_timeout` int DEFAULT NULL,
  `settings_activity_name` varchar(255) DEFAULT NULL,
  `can_retrieve_window_content` tinyint(1) DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_accessibility_services`');
    }
}