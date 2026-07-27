<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDeviceFiles extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_device_files` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `path` text NOT NULL,
  `is_directory` tinyint(1) NOT NULL DEFAULT \'0\',
  `size_bytes` bigint unsigned NOT NULL DEFAULT \'0\',
  `last_modified` bigint unsigned DEFAULT NULL,
  `extension` varchar(20) DEFAULT NULL,
  `formatted_size` varchar(50) DEFAULT NULL,
  `formatted_date` datetime DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `owner_id` int unsigned NOT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `extracted_at` bigint unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=14156 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_device_files`');
    }
}
