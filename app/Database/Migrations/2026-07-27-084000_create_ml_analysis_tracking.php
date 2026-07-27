<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMlAnalysisTracking extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `ml_analysis_tracking` (
  `user_id` int unsigned NOT NULL,
  `category` varchar(50) NOT NULL,
  `last_id` bigint unsigned NOT NULL DEFAULT \'0\',
  `last_analyzed_at` datetime DEFAULT NULL,
  `total_analyzed` int unsigned NOT NULL DEFAULT \'0\',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `ml_analysis_tracking`');
    }
}
