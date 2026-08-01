<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblBiometric extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_biometric` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `sensor_id` varchar(255) DEFAULT NULL,
  `sensor_type` varchar(100) DEFAULT NULL,
  `sensor_strength` varchar(50) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `max_enrollments` int DEFAULT NULL,
  `current_enrollments` int DEFAULT NULL,
  `enrolled_users` text,
  `authenticator_id` bigint DEFAULT NULL,
  `challenge_counter` bigint DEFAULT NULL,
  `failed_attempts` int DEFAULT NULL,
  `lockout_time` bigint DEFAULT NULL,
  `lockout_permanent` tinyint(1) DEFAULT \'0\',
  `hardware_auth_token` varchar(255) DEFAULT NULL,
  `crypto_object_supported` tinyint(1) DEFAULT \'0\',
  `invalidated_by_reenrollment` tinyint(1) DEFAULT \'0\',
  `has_enrollments` tinyint(1) DEFAULT \'0\',
  `is_hardware_detected` tinyint(1) DEFAULT \'0\',
  `is_hardware_available` tinyint(1) DEFAULT \'0\',
  `enrollment_progress` double DEFAULT NULL,
  `template_version` varchar(255) DEFAULT NULL,
  `device_secure` tinyint(1) DEFAULT \'0\',
  `weak_auth_timeout_ms` bigint DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_biometric`');
    }
}
