<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblUsageStats24h extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_usage_stats_24h` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `running_processes_id` int unsigned NOT NULL,
  `owner_id` int unsigned DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `total_time_foreground` bigint DEFAULT NULL,
  `last_time_used` bigint DEFAULT NULL,
  `last_time_service_used` bigint DEFAULT NULL,
  `last_time_visible` bigint DEFAULT NULL,
  `app_launch_count` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `running_processes_id` (`running_processes_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_usage_stats_24h`');
    }
}
