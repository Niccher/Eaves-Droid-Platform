<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblCalendarEvents extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_calendar_events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `event_id` varchar(50) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `description` text,
  `location` varchar(500) DEFAULT NULL,
  `start_time` bigint DEFAULT NULL,
  `end_time` bigint DEFAULT NULL,
  `all_day` tinyint(1) DEFAULT \'0\',
  `organizer` varchar(255) DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_calendar_event_per_device` (`owner_id`,`device_id`,`event_id`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `event_id` (`event_id`),
  KEY `start_time` (`start_time`),
  KEY `organizer` (`organizer`)
) ENGINE=InnoDB AUTO_INCREMENT=604 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_calendar_events`');
    }
}
