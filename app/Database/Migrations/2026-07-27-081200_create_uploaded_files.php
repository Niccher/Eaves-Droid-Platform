<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUploadedFiles extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `uploaded_files` (
  `file_id` int unsigned NOT NULL AUTO_INCREMENT,
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `file_size_bytes` bigint unsigned NOT NULL,
  `file_extension` varchar(10) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_category` varchar(50) NOT NULL,
  `token_used` varchar(255) NOT NULL,
  `token_owner_id` int unsigned DEFAULT NULL,
  `device_checksum` varchar(100) NOT NULL,
  `device_print_id` varchar(100) NOT NULL,
  `android_id` varchar(100) DEFAULT NULL,
  `upload_path` varchar(500) NOT NULL,
  `upload_status` enum(\'pending\',\'processing\',\'parsed\',\'failed\',\'archived\') NOT NULL DEFAULT \'pending\',
  `upload_error` text,
  `parsed_at` datetime DEFAULT NULL,
  `parsed_records` int unsigned NOT NULL DEFAULT \'0\',
  `parse_duration_ms` int unsigned DEFAULT NULL,
  `uploaded_at` datetime DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `upload_source` enum(\'manual\',\'auto_sync\',\'web_initiated\') DEFAULT \'auto_sync\',
  PRIMARY KEY (`file_id`),
  KEY `token_used` (`token_used`),
  KEY `token_owner_id` (`token_owner_id`),
  KEY `device_checksum` (`device_checksum`),
  KEY `file_category` (`file_category`),
  KEY `upload_status` (`upload_status`),
  KEY `uploaded_at` (`uploaded_at`),
  KEY `original_filename` (`original_filename`),
  KEY `file_extension` (`file_extension`),
  KEY `mime_type` (`mime_type`),
  KEY `android_id` (`android_id`),
  KEY `parsed_at` (`parsed_at`),
  KEY `processed_at` (`processed_at`)
) ENGINE=InnoDB AUTO_INCREMENT=603 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `uploaded_files`');
    }
}
