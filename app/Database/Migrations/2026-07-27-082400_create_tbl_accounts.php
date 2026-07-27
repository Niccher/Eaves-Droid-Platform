<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAccounts extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_accounts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `account_type` varchar(100) DEFAULT NULL,
  `summary_json` text,
  `total_count` int DEFAULT \'0\',
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_account_per_device` (`owner_id`,`device_id`,`account_name`,`account_type`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `account_type` (`account_type`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_accounts`');
    }
}
