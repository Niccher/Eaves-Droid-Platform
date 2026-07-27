<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblBluetooth extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_bluetooth` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT \'0\',
  `adapter_name` varchar(100) DEFAULT NULL,
  `adapter_address` varchar(30) DEFAULT NULL,
  `paired_count` int DEFAULT \'0\',
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb3

CREATE TABLE `tbl_bluetooth_paired` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `bluetooth_id` int unsigned NOT NULL,
  `owner_id` int unsigned DEFAULT NULL,
  `bt_name` varchar(100) DEFAULT NULL,
  `bt_address` varchar(30) DEFAULT NULL,
  `bt_type` varchar(20) DEFAULT NULL,
  `bond_state` varchar(20) DEFAULT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bluetooth_id` (`bluetooth_id`),
  KEY `owner_id` (`owner_id`),
  KEY `bt_address` (`bt_address`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_bluetooth`');
    }
}
