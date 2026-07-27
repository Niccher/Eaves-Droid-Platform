<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblCameraInfo extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_camera_info` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `camera_id` varchar(50) DEFAULT NULL,
  `lens_facing` varchar(20) DEFAULT NULL,
  `sensor_orientation` int DEFAULT NULL,
  `pixel_array_width` int DEFAULT NULL,
  `pixel_array_height` int DEFAULT NULL,
  `physical_width_mm` float DEFAULT NULL,
  `physical_height_mm` float DEFAULT NULL,
  `available_focal_lengths` json DEFAULT NULL,
  `flash_available` tinyint(1) DEFAULT NULL,
  `available_effects` json DEFAULT NULL,
  `available_scene_modes` json DEFAULT NULL,
  `available_video_stabilization` json DEFAULT NULL,
  `available_ae_modes` json DEFAULT NULL,
  `available_af_modes` json DEFAULT NULL,
  `max_jpeg_width` int DEFAULT NULL,
  `max_jpeg_height` int DEFAULT NULL,
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_camera_info`');
    }
}