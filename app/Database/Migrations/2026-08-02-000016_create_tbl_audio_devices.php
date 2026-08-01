<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAudioDevices extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_audio_devices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `audio_device_id` int DEFAULT NULL,
  `device_type` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `product_name` varchar(500) DEFAULT NULL,
  `is_sink` tinyint(1) DEFAULT \'0\',
  `is_source` tinyint(1) DEFAULT \'0\',
  `sample_rates` text,
  `channel_masks` text,
  `channel_counts` text,
  `encoding` varchar(100) DEFAULT NULL,
  `format` varchar(100) DEFAULT NULL,
  `gain_min` int DEFAULT NULL,
  `gain_max` int DEFAULT NULL,
  `gain_step` int DEFAULT NULL,
  `latency_low_ms` bigint DEFAULT NULL,
  `latency_high_ms` bigint DEFAULT NULL,
  `supported_uid` varchar(255) DEFAULT NULL,
  `volume_handle` int DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_audio_device_per_device` (`owner_id`,`device_id`,`audio_device_id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `device_type` (`device_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');

        $this->db->query('CREATE TABLE `tbl_audio_volumes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `stream` varchar(50) DEFAULT NULL,
  `volume_min` int DEFAULT NULL,
  `volume_max` int DEFAULT NULL,
  `volume_current` int DEFAULT NULL,
  `is_muted` tinyint(1) DEFAULT \'0\',
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `stream` (`stream`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_audio_volumes`');
        $this->db->query('DROP TABLE IF EXISTS `tbl_audio_devices`');
    }
}
