<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblInputMethodSubtypes extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_input_method_subtypes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `input_method_id` int unsigned NOT NULL,
  `owner_id` int unsigned DEFAULT NULL,
  `locale` varchar(50) DEFAULT NULL,
  `mode` varchar(50) DEFAULT NULL,
  `name` int DEFAULT NULL,
  `is_ascii_capable` tinyint(1) DEFAULT 0,
  `is_auxiliary` tinyint(1) DEFAULT 0,
  `overrides_implicitly_enabled_subtype` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `input_method_id` (`input_method_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_input_method_subtypes`');
    }
}