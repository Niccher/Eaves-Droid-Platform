<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSms extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_sms` (
  `counter` int unsigned NOT NULL AUTO_INCREMENT,
  `android_sms_id` bigint NOT NULL,
  `thread_id` bigint NOT NULL,
  `address` varchar(50) NOT NULL,
  `formatted_address` varchar(100) DEFAULT NULL,
  `body` longtext NOT NULL,
  `body_length` int NOT NULL DEFAULT \'0\',
  `sms_date` bigint unsigned NOT NULL,
  `sms_date_sent` bigint unsigned DEFAULT NULL,
  `sms_type` varchar(20) NOT NULL,
  `type_code` int NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT \'0\',
  `is_seen` tinyint(1) NOT NULL DEFAULT \'0\',
  `status_code` int NOT NULL DEFAULT \'0\',
  `error_code` int NOT NULL DEFAULT \'0\',
  `protocol` int NOT NULL,
  `protocol_type` varchar(20) NOT NULL,
  `service_center` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT \'0\',
  `creator` varchar(255) DEFAULT NULL,
  `owner_id` int unsigned NOT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `extracted_at` bigint unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`counter`),
  UNIQUE KEY `android_sms_id_device_id_owner_id` (`android_sms_id`,`device_id`,`owner_id`),
  KEY `address` (`address`),
  KEY `sms_date` (`sms_date`),
  KEY `owner_id` (`owner_id`),
  KEY `thread_id` (`thread_id`),
  KEY `sms_type` (`sms_type`),
  KEY `is_read` (`is_read`),
  KEY `is_seen` (`is_seen`),
  KEY `status_code` (`status_code`),
  KEY `error_code` (`error_code`),
  KEY `protocol_type` (`protocol_type`),
  KEY `extracted_at` (`extracted_at`),
  KEY `created_at` (`created_at`),
  KEY `updated_at` (`updated_at`),
  KEY `owner_id_sms_date` (`owner_id`,`sms_date`),
  KEY `device_id_sms_date` (`device_id`,`sms_date`),
  KEY `owner_id_address` (`owner_id`,`address`)
) ENGINE=InnoDB AUTO_INCREMENT=3474 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_sms`');
    }
}
