<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSecurityAudit extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_security_audit` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `vpn_active` tinyint(1) NOT NULL DEFAULT \'0\',
  `proxy_active` tinyint(1) NOT NULL DEFAULT \'0\',
  `user_ca_certs_json` text,
  `open_ports_json` text,
  `audit_timestamp` bigint DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `extracted_at` (`extracted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_security_audit`');
    }
}
