<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblUsbDevices extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_usb_devices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `usb_device_id` int DEFAULT NULL,
  `vendor_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `device_class` int DEFAULT NULL,
  `device_subclass` int DEFAULT NULL,
  `device_protocol` int DEFAULT NULL,
  `manufacturer_name` varchar(255) DEFAULT NULL,
  `product_name` varchar(500) DEFAULT NULL,
  `serial_number` varchar(500) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `configuration_count` int DEFAULT NULL,
  `interface_count` int DEFAULT NULL,
  `endpoint_count` int DEFAULT NULL,
  `power_ma` int DEFAULT NULL,
  `speed` varchar(50) DEFAULT NULL,
  `is_charging` tinyint(1) DEFAULT \'0\',
  `is_debug_accessory` tinyint(1) DEFAULT \'0\',
  `is_audio_accessory` tinyint(1) DEFAULT \'0\',
  `is_midi` tinyint(1) DEFAULT \'0\',
  `is_adb` tinyint(1) DEFAULT \'0\',
  `connected_time` bigint DEFAULT NULL,
  `disconnected_time` bigint DEFAULT NULL,
  `total_bytes_transferred` bigint DEFAULT NULL,
  `has_permission` tinyint(1) DEFAULT \'0\',
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usb_device_per_device` (`owner_id`,`device_id`,`usb_device_id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_usb_devices`');
    }
}
