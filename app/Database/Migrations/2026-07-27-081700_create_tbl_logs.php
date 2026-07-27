<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblLogs extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_logs` (
  `counter` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `call_type` varchar(50) NOT NULL,
  `call_date` bigint unsigned NOT NULL,
  `duration_seconds` int NOT NULL DEFAULT \'0\',
  `formatted_duration` varchar(50) DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `extracted_at` bigint unsigned DEFAULT NULL,
  `owner_id` int unsigned NOT NULL,
  `is_synced` tinyint(1) NOT NULL DEFAULT \'0\',
  `sync_count` int NOT NULL DEFAULT \'0\',
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `call_direction` enum(\'incoming\',\'outgoing\',\'missed\') DEFAULT NULL,
  `call_count` int NOT NULL DEFAULT \'1\',
  `timezone` varchar(50) DEFAULT NULL,
  `geolocation` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`counter`),
  KEY `phone_number` (`phone_number`),
  KEY `call_date` (`call_date`),
  KEY `call_type` (`call_type`),
  KEY `device_id` (`device_id`),
  KEY `owner_id` (`owner_id`),
  KEY `created_at` (`created_at`),
  KEY `updated_at` (`updated_at`),
  KEY `is_synced` (`is_synced`),
  KEY `owner_id_call_date` (`owner_id`,`call_date`),
  KEY `device_id_call_date` (`device_id`,`call_date`),
  KEY `phone_number_call_date` (`phone_number`,`call_date`),
  KEY `call_type_call_date` (`call_type`,`call_date`),
  KEY `contact_name` (`contact_name`),
  KEY `duration_seconds` (`duration_seconds`),
  KEY `extracted_at` (`extracted_at`),
  KEY `sync_count` (`sync_count`),
  KEY `last_sync` (`last_sync`),
  KEY `call_direction` (`call_direction`),
  KEY `call_count` (`call_count`)
) ENGINE=InnoDB AUTO_INCREMENT=1947 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_logs`');
    }
}
