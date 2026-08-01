<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblKeyboardInput extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_keyboard_input` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `ime_package` varchar(255) DEFAULT NULL,
  `ime_id` varchar(500) DEFAULT NULL,
  `ime_label` varchar(500) DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT \'0\',
  `is_default` tinyint(1) DEFAULT \'0\',
  `is_system_ime` tinyint(1) DEFAULT \'0\',
  `is_auxiliary` tinyint(1) DEFAULT \'0\',
  `supports_switching_to_next_input_method` tinyint(1) DEFAULT \'0\',
  `subtypes` text,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_keyboard_input_per_device` (`owner_id`,`device_id`,`ime_id`(255)),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `ime_package` (`ime_package`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_keyboard_input`');
    }
}
