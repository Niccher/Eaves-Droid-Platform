<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUploadQueue extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `upload_queue` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `stored_filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_category` varchar(50) NOT NULL,
  `file_size_bytes` bigint unsigned NOT NULL DEFAULT \'0\',
  `file_record_id` int unsigned DEFAULT NULL,
  `owner_id` int unsigned NOT NULL,
  `device_checksum` varchar(100) NOT NULL DEFAULT \'\',
  `device_print_id` varchar(100) NOT NULL DEFAULT \'\',
  `token_used` varchar(255) NOT NULL DEFAULT \'\',
  `upload_path` varchar(500) NOT NULL DEFAULT \'\',
  `upload_source` enum(\'manual\',\'auto_sync\',\'web_initiated\') DEFAULT \'auto_sync\',
  `status` enum(\'pending\',\'processing\',\'completed\',\'failed\') NOT NULL DEFAULT \'pending\',
  `attempts` tinyint unsigned NOT NULL DEFAULT \'0\',
  `error_message` text,
  `queued_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `processing_started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `owner_id` (`owner_id`),
  KEY `file_category` (`file_category`),
  KEY `queued_at` (`queued_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `upload_queue`');
    }
}
