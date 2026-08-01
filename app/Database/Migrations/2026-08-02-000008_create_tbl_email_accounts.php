<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblEmailAccounts extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_email_accounts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `account_email` varchar(255) DEFAULT NULL,
  `account_type` varchar(255) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `folder` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT \'0\',
  `last_sync_time` bigint DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email_account_per_device` (`owner_id`,`device_id`,`account_email`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `account_email` (`account_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_email_accounts`');
    }
}
