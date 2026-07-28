<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblDisplayInfo extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_display_info` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `width_px` int DEFAULT NULL,
  `height_px` int DEFAULT NULL,
  `density` float DEFAULT NULL,
  `density_dpi` int DEFAULT NULL,
  `xdpi` float DEFAULT NULL,
  `ydpi` float DEFAULT NULL,
  `scaled_density` float DEFAULT NULL,
  `real_width` int DEFAULT NULL,
  `real_height` int DEFAULT NULL,
  `usable_width` int DEFAULT NULL,
  `usable_height` int DEFAULT NULL,
  `rotation` tinyint DEFAULT NULL,
  `refresh_rate` float DEFAULT NULL,
  `mode_width` int DEFAULT NULL,
  `mode_height` int DEFAULT NULL,
  `mode_refresh_rate` float DEFAULT NULL,
  `displays_json` json DEFAULT NULL,
  `screen_layout` int DEFAULT NULL,
  `smallest_screen_width_dp` int DEFAULT NULL,
  `ui_mode` int DEFAULT NULL,
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_display_info`');
    }
}