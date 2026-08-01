<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblContentProviders extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_content_providers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `authority` varchar(255) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `name` varchar(500) DEFAULT NULL,
  `read_permission` varchar(255) DEFAULT NULL,
  `write_permission` varchar(255) DEFAULT NULL,
  `grant_uri_permissions` tinyint(1) DEFAULT \'0\',
  `is_exported` tinyint(1) DEFAULT \'0\',
  `is_syncable` tinyint(1) DEFAULT \'0\',
  `is_multiprocess` tinyint(1) DEFAULT \'0\',
  `init_order` int DEFAULT NULL,
  `authorities` varchar(500) DEFAULT NULL,
  `flags` int DEFAULT NULL,
  `path_permissions` text,
  `types` text,
  `stream_types` text,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_content_provider_per_device` (`owner_id`,`device_id`,`authority`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `package_name` (`package_name`),
  KEY `authority` (`authority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_content_providers`');
    }
}
