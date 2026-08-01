<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblKeyguardEvents extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_keyguard_events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `timestamp` bigint DEFAULT NULL,
  `method` varchar(100) DEFAULT NULL,
  `success` int DEFAULT NULL,
  `failed_attempts` int DEFAULT NULL,
  `remaining_attempts` int DEFAULT NULL,
  `lockout_until` bigint DEFAULT NULL,
  `strong_auth_required_reason` varchar(255) DEFAULT NULL,
  `biometric_error` varchar(255) DEFAULT NULL,
  `is_secure` tinyint(1) DEFAULT \'0\',
  `biometric_type` varchar(50) DEFAULT NULL,
  `biometric_available` tinyint(1) DEFAULT \'0\',
  `notifications_on_lockscreen` tinyint(1) DEFAULT \'0\',
  `sensitive_notifications_hidden` tinyint(1) DEFAULT \'0\',
  `lock_timeout_ms` bigint DEFAULT NULL,
  `lock_screen_widgets` varchar(255) DEFAULT NULL,
  `camera_shortcut` varchar(255) DEFAULT NULL,
  `assistant_shortcut` varchar(255) DEFAULT NULL,
  `storage_encryption_status` int DEFAULT NULL,
  `strong_auth_timeout_ms` bigint DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `timestamp` (`timestamp`),
  KEY `event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_keyguard_events`');
    }
}
