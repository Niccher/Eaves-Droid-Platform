<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMlResults extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `ml_results` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `category` varchar(50) NOT NULL,
  `algorithm` varchar(100) NOT NULL,
  `algorithm_id` varchar(50) NOT NULL,
  `severity` enum(\'High\',\'Medium\',\'Low\') NOT NULL,
  `anomaly` text NOT NULL,
  `score` decimal(10,4) NOT NULL DEFAULT \'0.0000\',
  `duration_ms` int unsigned DEFAULT NULL,
  `event_timestamp` datetime DEFAULT NULL,
  `details` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_job_severity` (`job_id`,`severity`),
  CONSTRAINT `fk_ml_results_job` FOREIGN KEY (`job_id`) REFERENCES `ml_jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2424 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `ml_results`');
    }
}
