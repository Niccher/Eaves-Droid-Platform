<?php

namespace App\Services;

use Config\Database;

class RetentionService
{
    public const CATEGORIES_MAP = [
        'apps'                  => ['tbl_extracted_installed_apps'],
        'calls'                 => ['tbl_extracted_call_logs'],
        'sms'                   => ['tbl_extracted_sms'],
        'contacts'              => ['tbl_extracted_contacts'],
        'files'                 => ['tbl_extracted_device_files', 'tbl_extracted_media_files'],
        'location_activities'   => ['tbl_extracted_locations', 'tbl_extracted_activities'],
        'misc_hardware_software'=> ['tbl_device_hardware_contexts', 'tbl_system_network_info', 'tbl_telemetry_bluetooth_devices', 'tbl_telemetry_sensors', 'tbl_sim_configs', 'tbl_security_audit'],
        'app_usage'             => ['tbl_system_app_usage'],
        'app_notifications'     => ['tbl_extracted_notifications'],
    ];

    public function getSettings(): array
    {
        $saved = [];
        $rows = Database::connect()->table('settings')
            ->where('class', 'retention')
            ->get()
            ->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }
        return $saved;
    }

    public function saveSettings(array $post): int
    {
        $db = Database::connect();
        $updated = 0;

        foreach ($post as $key => $value) {
            // Only persist retention-related keys (days + enabled toggles).
            if (!preg_match('/^retention_[a-z_]+_(days|enabled)$/', (string) $key)) {
                continue;
            }

            $existing = $db->table('settings')
                ->where('class', 'retention')
                ->where('key', $key)
                ->get()
                ->getRow();

            $now = date('Y-m-d H:i:s');
            if ($existing) {
                $db->table('settings')
                    ->where('id', $existing->id)
                    ->update(['value' => (string) $value, 'updated_at' => $now]);
            } else {
                $db->table('settings')->insert([
                    'class' => 'retention',
                    'key' => $key,
                    'value' => (string) $value,
                    'type' => 'string',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $updated++;
        }

        return $updated;
    }

    /**
     * Permanently deletes data older than the configured retention period.
     * If $categories is empty, only categories with auto-purge enabled are purged.
     *
     * @param string[] $categories
     * @return array<string, array{deleted:int, skipped?:bool}>
     */
    public function purge(array $categories = []): array
    {
        $db = Database::connect();
        $saved = $this->getSettings();
        $results = [];

        if (empty($categories)) {
            $categories = [];
            foreach (self::CATEGORIES_MAP as $cat => $tables) {
                if ((bool) ($saved["retention_{$cat}_enabled"] ?? true)) {
                    $categories[] = $cat;
                }
            }
        }

        foreach ($categories as $cat) {
            if (!isset(self::CATEGORIES_MAP[$cat])) {
                continue;
            }

            $tables = (array) self::CATEGORIES_MAP[$cat];
            $retentionDays = (int) ($saved["retention_{$cat}_days"] ?? 730);

            if ($retentionDays <= 0) {
                $results[$cat] = ['deleted' => 0, 'skipped' => true];
                continue;
            }

            $cutoff = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));
            $totalDeleted = 0;

            foreach ($tables as $table) {
                if ($db->tableExists($table)) {
                    $deleted = $db->table($table)
                        ->where('created_at <', $cutoff)
                        ->delete();
                    $totalDeleted += $deleted;
                }
            }

            $results[$cat] = ['deleted' => $totalDeleted];
        }

        return $results;
    }
}
