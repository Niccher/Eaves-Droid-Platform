<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblTokens extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_tokens` (
  `counter` int unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(128) NOT NULL,
  `token_type` enum(\'pin\',\'qr\') NOT NULL,
  `owner_id` int unsigned NOT NULL,
  `initiator` varchar(64) DEFAULT NULL,
  `status` varchar(16) NOT NULL DEFAULT \'active\',
  `created_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `device_checksum` varchar(128) DEFAULT NULL,
  `android_id` varchar(32) DEFAULT NULL,
  `device_name` varchar(64) DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `is_refreshable` tinyint(1) NOT NULL DEFAULT \'0\',
  `scopes` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`counter`),
  UNIQUE KEY `token` (`token`),
  KEY `token_type` (`token_type`),
  KEY `owner_id` (`owner_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  KEY `expires_at` (`expires_at`),
  KEY `last_used_at` (`last_used_at`),
  KEY `device_checksum` (`device_checksum`),
  KEY `android_id` (`android_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_tokens`');
    }
}
