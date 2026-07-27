<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblNotifications extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_notifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `notification_id` int DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `app_name` varchar(255) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `text` text,
  `sender` varchar(255) DEFAULT NULL,
  `sub_text` varchar(500) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `visibility` varchar(50) DEFAULT NULL,
  `is_screen_notification` tinyint(1) NOT NULL DEFAULT \'0\',
  `notification_timestamp` bigint DEFAULT NULL,
  `action` varchar(20) DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_notification_entry` (`owner_id`,`device_id`,`notification_id`,`notification_timestamp`,`action`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `package_name` (`package_name`),
  KEY `action` (`action`),
  KEY `notification_timestamp` (`notification_timestamp`),
  KEY `extracted_at` (`extracted_at`),
  KEY `sender` (`sender`),
  KEY `is_screen_notification` (`is_screen_notification`)
) ENGINE=InnoDB AUTO_INCREMENT=23517 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_notifications`');
    }
}
