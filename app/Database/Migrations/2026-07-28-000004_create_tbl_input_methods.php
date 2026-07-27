<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblInputMethods extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_input_methods` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `ime_id` varchar(255) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `service_name` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `is_auxiliary` tinyint(1) DEFAULT 0,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_input_methods`');
    }
}