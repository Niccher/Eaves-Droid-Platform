<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAdminReports extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_admin_reports` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_scope` varchar(255) NOT NULL DEFAULT \'all\',
  `user_id` int unsigned DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `data_types` json DEFAULT NULL,
  `data_types_labels` text,
  `format` varchar(10) NOT NULL DEFAULT \'html\',
  `record_count` int unsigned NOT NULL DEFAULT \'0\',
  `file_path` varchar(500) NOT NULL,
  `file_size` int unsigned DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_admin_reports`');
    }
}
