<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblBlocklist extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_blocklist` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned NOT NULL,
  `category` enum(\'sms\',\'call\',\'notification\',\'app_usage\',\'location\') NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `owner_id_category_identifier` (`owner_id`,`category`,`identifier`),
  KEY `owner_id` (`owner_id`),
  KEY `category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_blocklist`');
    }
}
