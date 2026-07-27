<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblRunningProcessDetails extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_running_process_details` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `running_processes_id` int unsigned NOT NULL,
  `owner_id` int unsigned DEFAULT NULL,
  `pid` int DEFAULT NULL,
  `process_name` varchar(255) DEFAULT NULL,
  `uid` int DEFAULT NULL,
  `importance` int DEFAULT NULL,
  `importance_reason_code` int DEFAULT NULL,
  `pkg_list_json` text,
  `lru` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `running_processes_id` (`running_processes_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_running_process_details`');
    }
}
