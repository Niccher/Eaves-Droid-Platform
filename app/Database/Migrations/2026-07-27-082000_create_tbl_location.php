<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblLocation extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_location` (
  `counter` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned NOT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `accuracy` float DEFAULT NULL,
  `altitude` float DEFAULT NULL,
  `bearing` float DEFAULT NULL,
  `speed` float DEFAULT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `location_time` bigint unsigned DEFAULT NULL,
  `status` varchar(50) DEFAULT \'no_location_found\',
  `extracted_at` bigint unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`counter`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`),
  KEY `location_time` (`location_time`),
  KEY `owner_id_extracted_at` (`owner_id`,`extracted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1263 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_location`');
    }
}
