<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblNfc extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_nfc` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `nfc_available` tinyint(1) DEFAULT NULL,
  `nfc_enabled` tinyint(1) DEFAULT NULL,
  `nfc_supported` tinyint(1) DEFAULT NULL,
  `nfc_secure_nfc` tinyint(1) DEFAULT NULL,
  `nfc_secure_supported` tinyint(1) DEFAULT NULL,
  `features_json` json DEFAULT NULL,
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_nfc`');
    }
}