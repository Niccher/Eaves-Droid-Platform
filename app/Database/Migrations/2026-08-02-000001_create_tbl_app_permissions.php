<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAppPermissions extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_app_permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `permission_name` varchar(255) DEFAULT NULL,
  `is_requested` tinyint(1) DEFAULT \'0\',
  `is_granted` tinyint(1) DEFAULT \'0\',
  `is_runtime` tinyint(1) DEFAULT \'0\',
  `is_system_fixed` tinyint(1) DEFAULT \'0\',
  `is_revoked` tinyint(1) DEFAULT \'0\',
  `grant_time` bigint DEFAULT NULL,
  `last_used_time` bigint DEFAULT NULL,
  `flags` int DEFAULT NULL,
  `is_one_time` tinyint(1) DEFAULT \'0\',
  `is_auto_revoke_whitelisted` tinyint(1) DEFAULT \'0\',
  `is_hard_restricted` tinyint(1) DEFAULT \'0\',
  `is_soft_restricted` tinyint(1) DEFAULT \'0\',
  `user_set` tinyint(1) DEFAULT \'0\',
  `fixed_policy` tinyint(1) DEFAULT \'0\',
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_app_perm_per_device` (`owner_id`,`device_id`,`package_name`,`permission_name`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `package_name` (`package_name`),
  KEY `permission_name` (`permission_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_app_permissions`');
    }
}
