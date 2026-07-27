<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAppDefaults extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_app_defaults` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `config_json` json NOT NULL,
  `version` int unsigned NOT NULL DEFAULT \'1\',
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `tbl_app_defaults_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_app_defaults`');
    }
}
