<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMlJobs extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `ml_jobs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `engine` varchar(20) NOT NULL DEFAULT \'python\',
  `algorithms` json NOT NULL,
  `scope` enum(\'full\',\'incremental\') NOT NULL DEFAULT \'full\',
  `incremental_since` datetime DEFAULT NULL,
  `status` enum(\'pending\',\'running\',\'completed\',\'failed\') NOT NULL DEFAULT \'pending\',
  `progress_pct` tinyint unsigned DEFAULT \'0\',
  `total_algorithms` tinyint unsigned DEFAULT \'0\',
  `completed_algorithms` tinyint unsigned DEFAULT \'0\',
  `current_algorithm` varchar(100) DEFAULT NULL,
  `error_message` text,
  `algorithm_logs` json DEFAULT NULL,
  `results_count` int unsigned NOT NULL DEFAULT \'0\',
  `error_msg` text,
  `timing_ms` int unsigned NOT NULL DEFAULT \'0\',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_status` (`user_id`,`status`),
  KEY `idx_status_created` (`status`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `ml_jobs`');
    }
}
