<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblBrowserHistory extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_browser_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `browser_package` varchar(255) DEFAULT NULL,
  `url` text,
  `title` varchar(500) DEFAULT NULL,
  `visit_count` bigint DEFAULT NULL,
  `last_visit_time` bigint DEFAULT NULL,
  `typed_count` int DEFAULT NULL,
  `favicon_base64` longtext,
  `is_bookmark` tinyint(1) DEFAULT \'0\',
  `bookmark_folder` varchar(255) DEFAULT NULL,
  `transition_type` varchar(100) DEFAULT NULL,
  `referrer_url` text,
  `visit_duration_ms` bigint DEFAULT NULL,
  `search_terms` varchar(255) DEFAULT NULL,
  `is_incognito` tinyint(1) DEFAULT \'0\',
  `domain` varchar(255) DEFAULT NULL,
  `scheme` varchar(50) DEFAULT NULL,
  `path_depth` int DEFAULT NULL,
  `query_params` text,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_browser_history_per_device` (`owner_id`,`device_id`,`url`(255)),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `last_visit_time` (`last_visit_time`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_browser_history`');
    }
}
