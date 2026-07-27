<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblUserActions extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_user_actions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `action_category` enum(\'authentication\',\'file\',\'profile\',\'admin\',\'system\',\'security\',\'upload\') NOT NULL DEFAULT \'system\',
  `action_type` varchar(100) NOT NULL,
  `action_severity` enum(\'low\',\'medium\',\'high\',\'critical\') NOT NULL DEFAULT \'low\',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `device_type` enum(\'desktop\',\'mobile\',\'tablet\',\'bot\',\'unknown\') DEFAULT NULL,
  `device_name` varchar(100) DEFAULT NULL,
  `operating_system` varchar(100) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `country_code` char(2) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `request_url` varchar(500) DEFAULT NULL,
  `request_method` varchar(10) DEFAULT NULL,
  `response_code` smallint DEFAULT NULL,
  `execution_time_ms` int unsigned DEFAULT NULL,
  `resource_id` varchar(100) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT \'1\',
  `error_code` varchar(50) DEFAULT NULL,
  `error_message` text,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`),
  KEY `action_category` (`action_category`),
  KEY `action_type` (`action_type`),
  KEY `action_severity` (`action_severity`),
  KEY `ip_address` (`ip_address`),
  KEY `resource_id` (`resource_id`),
  KEY `success` (`success`),
  KEY `created_at` (`created_at`),
  KEY `user_id_created_at` (`user_id`,`created_at`),
  KEY `action_category_action_type_created_at` (`action_category`,`action_type`,`created_at`),
  CONSTRAINT `tbl_user_actions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=941 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_user_actions`');
    }
}
