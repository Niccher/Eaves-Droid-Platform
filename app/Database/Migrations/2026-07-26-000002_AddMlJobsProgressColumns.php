<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMlJobsProgressColumns extends Migration
{
    public function up()
    {
        $db = db_connect();

        $columns = [
            'progress_pct'         => "ADD COLUMN progress_pct TINYINT(3) UNSIGNED DEFAULT 0 AFTER status",
            'total_algorithms'     => "ADD COLUMN total_algorithms TINYINT(3) UNSIGNED DEFAULT 0 AFTER progress_pct",
            'completed_algorithms' => "ADD COLUMN completed_algorithms TINYINT(3) UNSIGNED DEFAULT 0 AFTER total_algorithms",
            'current_algorithm'    => "ADD COLUMN current_algorithm VARCHAR(100) DEFAULT NULL AFTER completed_algorithms",
            'error_message'        => "ADD COLUMN error_message TEXT DEFAULT NULL AFTER current_algorithm",
            'started_at'           => "ADD COLUMN started_at DATETIME DEFAULT NULL AFTER error_message",
        ];

        // Check which columns already exist
        $existing = [];
        try {
            $result = $db->query("SHOW COLUMNS FROM ml_jobs")->getResultArray();
            foreach ($result as $row) {
                $existing[] = $row['Field'];
            }
        } catch (\Throwable $e) {
            // Table might not exist yet
        }

        foreach ($columns as $colName => $alterSql) {
            if (!in_array($colName, $existing)) {
                try {
                    $db->query("ALTER TABLE ml_jobs {$alterSql}");
                } catch (\Throwable $e) {
                    // Column may already exist via concurrent migration
                }
            }
        }
    }

    public function down()
    {
        $db = db_connect();
        $columns = [
            'progress_pct', 'total_algorithms', 'completed_algorithms',
            'current_algorithm', 'error_message', 'started_at',
        ];
        foreach ($columns as $col) {
            try {
                $db->query("ALTER TABLE ml_jobs DROP COLUMN {$col}");
            } catch (\Throwable $e) {
                // Column might not exist
            }
        }
    }
}
