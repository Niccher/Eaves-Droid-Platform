<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDataUsage extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_data_usage` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `network_type` varchar(50) DEFAULT NULL,
  `sub_id` int DEFAULT NULL,
  `is_wifi` tinyint(1) DEFAULT 0,
  `rx_bytes` bigint DEFAULT NULL,
  `tx_bytes` bigint DEFAULT NULL,
  `total_bytes` bigint DEFAULT NULL,
  `rx_formatted` varchar(50) DEFAULT NULL,
  `tx_formatted` varchar(50) DEFAULT NULL,
  `bucket_start` bigint DEFAULT NULL,
  `bucket_end` bigint DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_data_usage`');
    }
}