<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblScreenshots extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_screenshots` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `file_path` text,
  `file_name` varchar(500) DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `width` int DEFAULT NULL,
  `height` int DEFAULT NULL,
  `timestamp` bigint DEFAULT NULL,
  `source_package` varchar(255) DEFAULT NULL,
  `is_screen_record` tinyint(1) DEFAULT \'0\',
  `duration_ms` bigint DEFAULT NULL,
  `video_path` text,
  `video_size` bigint DEFAULT NULL,
  `video_width` int DEFAULT NULL,
  `video_height` int DEFAULT NULL,
  `video_duration_ms` bigint DEFAULT NULL,
  `video_frame_rate` int DEFAULT NULL,
  `video_bitrate` bigint DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT \'0\',
  `edit_timestamp` bigint DEFAULT NULL,
  `edit_app_package` varchar(255) DEFAULT NULL,
  `contains_pii` tinyint(1) DEFAULT \'0\',
  `pii_types` text,
  `detection_confidence` double DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `timestamp` (`timestamp`),
  KEY `file_name` (`file_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_screenshots`');
    }
}
