<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserProfiles extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `user_profiles` (
  `user_id` int unsigned NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text,
  `last_seen_at` datetime DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `last_user_agent` varchar(255) DEFAULT NULL,
  `unread_notifications` int unsigned NOT NULL DEFAULT \'0\',
  `last_notification_at` datetime DEFAULT NULL,
  `notifications_enabled` tinyint(1) NOT NULL DEFAULT \'1\',
  `email_notifications` tinyint(1) NOT NULL DEFAULT \'1\',
  `push_notifications` tinyint(1) NOT NULL DEFAULT \'1\',
  `language` varchar(8) NOT NULL DEFAULT \'en\',
  `timezone` varchar(64) DEFAULT NULL,
  `theme` varchar(16) NOT NULL DEFAULT \'system\',
  `account_status` varchar(16) NOT NULL DEFAULT \'active\',
  `suspended_reason` varchar(255) DEFAULT NULL,
  `last_deleted_data_at` datetime DEFAULT NULL,
  `last_exported_at` datetime DEFAULT NULL,
  `export_count` int unsigned NOT NULL DEFAULT \'0\',
  `onboarding_completed` tinyint(1) NOT NULL DEFAULT \'0\',
  `profile_completed` tinyint(1) NOT NULL DEFAULT \'0\',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `last_seen_at` (`last_seen_at`),
  KEY `last_ip` (`last_ip`),
  KEY `account_status` (`account_status`),
  KEY `language` (`language`),
  KEY `timezone` (`timezone`),
  KEY `created_at` (`created_at`),
  KEY `updated_at` (`updated_at`),
  KEY `notifications_enabled` (`notifications_enabled`),
  KEY `profile_completed` (`profile_completed`),
  CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `user_profiles`');
    }
}
