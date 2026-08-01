<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblCrashLogs extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_crash_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `process_name` varchar(255) DEFAULT NULL,
  `pid` int DEFAULT NULL,
  `uid` int DEFAULT NULL,
  `crash_time` bigint DEFAULT NULL,
  `crash_type` varchar(50) DEFAULT NULL,
  `exception_class` varchar(255) DEFAULT NULL,
  `exception_message` text,
  `stack_trace` text,
  `build_fingerprint` varchar(500) DEFAULT NULL,
  `android_version` varchar(50) DEFAULT NULL,
  `device_model` varchar(255) DEFAULT NULL,
  `is_system_app` tinyint(1) DEFAULT \'0\',
  `is_silent` tinyint(1) DEFAULT \'0\',
  `is_user_perceived` tinyint(1) DEFAULT \'0\',
  `logcat_tail` text,
  `dropbox_tag` varchar(255) DEFAULT NULL,
  `dropbox_data` text,
  `tombstone_path` varchar(500) DEFAULT NULL,
  `minidump_path` varchar(500) DEFAULT NULL,
  `last_crash_time` bigint DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crash_log_per_device` (`owner_id`,`device_id`,`pid`,`crash_time`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `crash_time` (`crash_time`),
  KEY `package_name` (`package_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_crash_logs`');
    }
}
