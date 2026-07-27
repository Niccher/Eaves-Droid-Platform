<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblContacts extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_contacts` (
  `counter` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` varchar(100) DEFAULT NULL,
  `display_name` varchar(255) NOT NULL,
  `phone_numbers` text,
  `phone_count` int NOT NULL DEFAULT \'0\',
  `emails` text,
  `email_count` int NOT NULL DEFAULT \'0\',
  `photo_uri` varchar(500) DEFAULT NULL,
  `companies` text,
  `addresses` text,
  `notes` text,
  `is_favorite` tinyint(1) NOT NULL DEFAULT \'0\',
  `last_contacted` bigint unsigned DEFAULT NULL,
  `contact_frequency` int NOT NULL DEFAULT \'0\',
  `device_id` varchar(100) DEFAULT NULL,
  `extracted_at` bigint unsigned DEFAULT NULL,
  `owner_id` int unsigned NOT NULL,
  `is_synced` tinyint(1) NOT NULL DEFAULT \'0\',
  `sync_count` int NOT NULL DEFAULT \'0\',
  `last_sync` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT \'1\',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`counter`),
  UNIQUE KEY `contact_id_device_id_owner_id` (`contact_id`,`device_id`,`owner_id`),
  KEY `display_name` (`display_name`),
  KEY `contact_id` (`contact_id`),
  KEY `device_id` (`device_id`),
  KEY `owner_id` (`owner_id`),
  KEY `is_favorite` (`is_favorite`),
  KEY `is_active` (`is_active`),
  KEY `created_at` (`created_at`),
  KEY `owner_id_device_id` (`owner_id`,`device_id`),
  KEY `owner_id_is_favorite` (`owner_id`,`is_favorite`),
  KEY `owner_id_display_name` (`owner_id`,`display_name`),
  KEY `owner_id_contact_frequency` (`owner_id`,`contact_frequency`),
  KEY `phone_count` (`phone_count`),
  KEY `email_count` (`email_count`),
  KEY `last_contacted` (`last_contacted`),
  KEY `extracted_at` (`extracted_at`),
  KEY `is_synced` (`is_synced`),
  KEY `last_sync` (`last_sync`),
  KEY `updated_at` (`updated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2257 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_contacts`');
    }
}
