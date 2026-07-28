<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDefaultApps extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_default_apps` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `handler_type` varchar(50) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `app_name` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_default_apps`');
    }
}