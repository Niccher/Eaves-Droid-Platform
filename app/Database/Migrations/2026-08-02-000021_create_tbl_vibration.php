<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblVibration extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_vibration` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `has_vibrator` tinyint(1) DEFAULT \'0\',
  `supports_amplitude_control` tinyint(1) DEFAULT \'0\',
  `supports_frequency_control` tinyint(1) DEFAULT \'0\',
  `actuator_id` varchar(255) DEFAULT NULL,
  `max_amplitude` int DEFAULT NULL,
  `resonant_frequency_hz` double DEFAULT NULL,
  `q_factor` double DEFAULT NULL,
  `actuator_type` varchar(50) DEFAULT NULL,
  `primitives` text,
  `frequency_range_hz` text,
  `composite_primitives` text,
  `supports_external_control` tinyint(1) DEFAULT \'0\',
  `braking_supported` tinyint(1) DEFAULT \'0\',
  `envelope_supported` tinyint(1) DEFAULT \'0\',
  `pwm_supported` tinyint(1) DEFAULT \'0\',
  `waveform_supported` tinyint(1) DEFAULT \'0\',
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_vibration`');
    }
}
