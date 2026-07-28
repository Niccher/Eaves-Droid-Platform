<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblCellTowers extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_cell_towers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `tower_type` varchar(20) DEFAULT NULL,
  `cid` varchar(50) DEFAULT NULL,
  `lac` varchar(50) DEFAULT NULL,
  `mcc` varchar(10) DEFAULT NULL,
  `mnc` varchar(10) DEFAULT NULL,
  `pci` int DEFAULT NULL,
  `nci` varchar(50) DEFAULT NULL,
  `tac` varchar(50) DEFAULT NULL,
  `nrarfcn` int DEFAULT NULL,
  `bandwidth` int DEFAULT NULL,
  `psc` int DEFAULT NULL,
  `system_id` int DEFAULT NULL,
  `rssi` int DEFAULT NULL,
  `rsrp` int DEFAULT NULL,
  `rsrq` int DEFAULT NULL,
  `rssnr` int DEFAULT NULL,
  `cqi` int DEFAULT NULL,
  `asu_level` int DEFAULT NULL,
  `csi_rsrp` int DEFAULT NULL,
  `csi_rsrq` int DEFAULT NULL,
  `csi_sinr` int DEFAULT NULL,
  `is_registered` tinyint(1) DEFAULT 0,
  `network_operator` varchar(255) DEFAULT NULL,
  `network_operator_name` varchar(255) DEFAULT NULL,
  `phone_type` tinyint DEFAULT NULL,
  `sim_state` tinyint DEFAULT NULL,
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_cell_towers`');
    }
}