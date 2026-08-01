<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblVpnConfig extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_vpn_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `vpn_active` tinyint(1) DEFAULT \'0\',
  `vpn_interface` varchar(255) DEFAULT NULL,
  `vpn_dns_servers` text,
  `vpn_routes` text,
  `vpn_mtu` bigint DEFAULT NULL,
  `vpn_protocol` varchar(255) DEFAULT NULL,
  `vpn_is_always_on` tinyint(1) DEFAULT \'0\',
  `vpn_is_lockdown` tinyint(1) DEFAULT \'0\',
  `vpn_package` varchar(255) DEFAULT NULL,
  `vpn_label` varchar(255) DEFAULT NULL,
  `vpn_apps` text,
  `vpn_server` varchar(255) DEFAULT NULL,
  `vpn_port` int DEFAULT NULL,
  `vpn_auth_type` varchar(255) DEFAULT NULL,
  `vpn_ca_cert_sha256` varchar(255) DEFAULT NULL,
  `vpn_client_cert_sha256` varchar(255) DEFAULT NULL,
  `vpn_dns_search_domains` text,
  `vpn_excluded_apps` text,
  `vpn_included_apps` text,
  `vpn_block_non_vpn` tinyint(1) DEFAULT \'0\',
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
        $this->db->query('DROP TABLE IF EXISTS `tbl_vpn_config`');
    }
}
