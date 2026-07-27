<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblCapturedMedia extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_captured_media` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned NOT NULL,
  `device_id` varchar(100) NOT NULL,
  `media_type` enum(\'audio\',\'image\') NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `file_size` bigint unsigned NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_record_id` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `media_type` (`media_type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_captured_media`');
    }
}
