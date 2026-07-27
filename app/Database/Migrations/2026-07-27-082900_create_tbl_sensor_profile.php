<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSensorProfile extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_sensor_profile` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `sensor_name` varchar(255) DEFAULT NULL,
  `vendor` varchar(100) DEFAULT NULL,
  `type_id` int DEFAULT NULL,
  `type_string` varchar(100) DEFAULT NULL,
  `version` int DEFAULT NULL,
  `maximum_range` float DEFAULT NULL,
  `resolution` float DEFAULT NULL,
  `power_ma` float DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sensor_per_snapshot` (`owner_id`,`device_id`,`type_id`,`extracted_at`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `type_id` (`type_id`),
  KEY `type_string` (`type_string`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=963 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_sensor_profile`');
    }
}
