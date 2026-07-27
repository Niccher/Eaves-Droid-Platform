<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAlgorithmTrackingColumns extends Migration
{
    public function up()
    {
        $db = db_connect();

        try {
            $db->query("ALTER TABLE ml_jobs ADD COLUMN algorithm_logs JSON DEFAULT NULL AFTER error_message");
        } catch (\Throwable $e) {
            // Column already exists
        }

        try {
            $db->query("ALTER TABLE ml_results ADD COLUMN duration_ms INT UNSIGNED DEFAULT NULL AFTER score");
        } catch (\Throwable $e) {
            // Column already exists
        }
    }

    public function down()
    {
        $db = db_connect();

        try {
            $db->query("ALTER TABLE ml_jobs DROP COLUMN algorithm_logs");
        } catch (\Throwable $e) {
        }

        try {
            $db->query("ALTER TABLE ml_results DROP COLUMN duration_ms");
        } catch (\Throwable $e) {
        }
    }
}
