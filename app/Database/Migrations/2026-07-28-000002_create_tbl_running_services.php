<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblRunningServices extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_running_services` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `running_process_id` int unsigned NOT NULL,
  `owner_id` int unsigned DEFAULT NULL,
  `pid` int DEFAULT NULL,
  `process` varchar(255) DEFAULT NULL,
  `client_package` varchar(255) DEFAULT NULL,
  `client_label` varchar(255) DEFAULT NULL,
  `active_since` bigint DEFAULT NULL,
  `crash_count` int DEFAULT NULL,
  `flags` int DEFAULT NULL,
  `started` tinyint(1) DEFAULT NULL,
  `service_class` varchar(255) DEFAULT NULL,
  `service_package` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `running_process_id` (`running_process_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_running_services`');
    }
}