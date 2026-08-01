<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblGnssHardware extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_gnss_hardware` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `gnss_id` varchar(255) DEFAULT NULL,
  `constellations_supported` text,
  `antenna_type` varchar(255) DEFAULT NULL,
  `frequencies_supported` text,
  `max_satellites_tracked` int DEFAULT NULL,
  `max_satellites_used` int DEFAULT NULL,
  `agps_supported` tinyint(1) DEFAULT \'0\',
  `agps_modes` varchar(255) DEFAULT NULL,
  `dead_reckoning_supported` tinyint(1) DEFAULT \'0\',
  `raw_measurements_supported` tinyint(1) DEFAULT \'0\',
  `correction_data_supported` tinyint(1) DEFAULT \'0\',
  `navigation_messages_supported` tinyint(1) DEFAULT \'0\',
  `antenna_info` text,
  `measurement_capabilities` text,
  `status_supported` tinyint(1) DEFAULT \'0\',
  `time_offset_ns` bigint DEFAULT NULL,
  `leap_second` int DEFAULT NULL,
  `utc_time_accuracy_ns` bigint DEFAULT NULL,
  `gps_provider_available` tinyint(1) DEFAULT \'0\',
  `gnss_hardware_model_id` varchar(255) DEFAULT NULL,
  `gnss_year_of_hardware` int DEFAULT NULL,
  `gnss_batch_size` int DEFAULT NULL,
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_gnss_hardware`');
    }
}
