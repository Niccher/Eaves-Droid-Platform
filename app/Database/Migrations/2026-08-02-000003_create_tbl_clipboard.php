<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblClipboard extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_clipboard` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `clip_data_type` varchar(50) DEFAULT NULL,
  `clip_text` text,
  `clip_html` text,
  `clip_intent_action` varchar(255) DEFAULT NULL,
  `clip_intent_package` varchar(255) DEFAULT NULL,
  `clip_uri` text,
  `item_count` int DEFAULT NULL,
  `primary_clip_description` varchar(500) DEFAULT NULL,
  `timestamp` bigint DEFAULT NULL,
  `source_package` varchar(255) DEFAULT NULL,
  `label` varchar(500) DEFAULT NULL,
  `is_sensitive` tinyint(1) DEFAULT \'0\',
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `timestamp` (`timestamp`),
  KEY `clip_data_type` (`clip_data_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_clipboard`');
    }
}
