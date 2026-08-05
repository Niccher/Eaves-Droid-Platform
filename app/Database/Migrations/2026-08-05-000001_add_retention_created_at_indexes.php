<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRetentionCreatedAtIndexes extends Migration
{
    /**
     * Retention category tables that are scanned by retention:purge
     * (WHERE created_at < cutoff). Adds a created_at index where missing so
     * purges do not full-table-scan large datasets.
     */
    private const TABLES = [
        'tbl_sms',
        'tbl_logs',
        'tbl_contacts',
        'tbl_location',
        'tbl_activity',
        'tbl_apps',
        'tbl_device_files',
        'tbl_network_info',
        'tbl_device_context',
        'tbl_bluetooth',
        'tbl_sensor_profile',
        'tbl_security_audit',
        'tbl_notifications',
        'tbl_calendar_events',
        'tbl_app_usage',
        'tbl_captured_media',
        'tbl_sim_configs',
        'tbl_accounts',
    ];

    public function up()
    {
        foreach (self::TABLES as $table) {
            $index = $this->getCreatedAtIndex($table);
            if ($index === null) {
                $this->db->query("ALTER TABLE `{$table}` ADD INDEX `idx_created_at` (`created_at`)");
            }
        }
    }

    public function down()
    {
        foreach (self::TABLES as $table) {
            if ($this->getCreatedAtIndex($table) !== null) {
                $this->db->query("ALTER TABLE `{$table}` DROP INDEX `idx_created_at`");
            }
        }
    }

    /**
     * Returns the key name of an existing single-column index on `created_at`,
     * or null if none exists.
     */
    private function getCreatedAtIndex(string $table): ?string
    {
        $indexes = $this->db->query("SHOW INDEX FROM `{$table}`")->getResultArray();
        foreach ($indexes as $row) {
            if (($row['Column_name'] ?? '') === 'created_at') {
                return $row['Key_name'] ?? 'idx_created_at';
            }
        }
        return null;
    }
}
