<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblStorage extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_storage` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `volume_path` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_removable` tinyint(1) DEFAULT 0,
  `state` varchar(50) DEFAULT NULL,
  `total_bytes` bigint DEFAULT NULL,
  `available_bytes` bigint DEFAULT NULL,
  `free_bytes` bigint DEFAULT NULL,
  `used_bytes` bigint DEFAULT NULL,
  `total_formatted` varchar(50) DEFAULT NULL,
  `available_formatted` varchar(50) DEFAULT NULL,
  `used_formatted` varchar(50) DEFAULT NULL,
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_storage`');
    }
}