<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblNetworkInfo extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_network_info` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `is_connected` tinyint(1) DEFAULT \'0\',
  `connection_type` varchar(30) DEFAULT NULL,
  `is_roaming` tinyint(1) DEFAULT \'0\',
  `network_operator_name` varchar(100) DEFAULT NULL,
  `network_country_iso` varchar(10) DEFAULT NULL,
  `sim_operator_name` varchar(100) DEFAULT NULL,
  `sim_country_iso` varchar(10) DEFAULT NULL,
  `sim_state` varchar(30) DEFAULT NULL,
  `phone_type` varchar(20) DEFAULT NULL,
  `device_imei` varchar(30) DEFAULT NULL,
  `sim_serial` varchar(30) DEFAULT NULL,
  `subscriber_id` varchar(30) DEFAULT NULL,
  `wifi_ssid` varchar(100) DEFAULT NULL,
  `wifi_bssid` varchar(30) DEFAULT NULL,
  `wifi_link_speed` int DEFAULT NULL,
  `wifi_frequency` int DEFAULT NULL,
  `wifi_rssi` int DEFAULT NULL,
  `wifi_mac_address` varchar(30) DEFAULT NULL,
  `wifi_ip_address` varchar(50) DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `device_imei` (`device_imei`),
  KEY `connection_type` (`connection_type`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb3

CREATE TABLE `tbl_nearby_wifi` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `network_info_id` int unsigned NOT NULL,
  `owner_id` int unsigned DEFAULT NULL,
  `ssid` varchar(100) DEFAULT NULL,
  `bssid` varchar(30) DEFAULT NULL,
  `capabilities` varchar(255) DEFAULT NULL,
  `level` int DEFAULT NULL,
  `frequency` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `network_info_id` (`network_info_id`),
  KEY `owner_id` (`owner_id`),
  KEY `bssid` (`bssid`)
) ENGINE=InnoDB AUTO_INCREMENT=538 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_network_info`');
    }
}
