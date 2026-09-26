<?php

namespace App\Services;

use Config\Database;

class SystemResetService
{
    /**
     * System/config + account tables that are NEVER touched during a factory
     * reset. Every other table (device data, telemetry, uploads, ML, billing,
     * etc.) is permanently wiped. All user accounts are spared so users keep
     * their logins but start with a completely empty account.
     */
    private const PRESERVE_TABLES = [
        'migrations',
        'settings',
        'plans',
        'plan_versions',
        'cron_jobs',
        // User accounts are retained — only their data is deleted.
        'users',
        'auth_identities',
        'auth_groups_users',
        'auth_permissions_users',
        'auth_remember_tokens',
        'auth_logins',
        'auth_token_logins',
        'user_profiles',
    ];

    /**
     * Writable data directories whose contents are wiped during a factory
     * reset. Directory structure and sentinel files (.htaccess / index.html)
     * are preserved so the app keeps working afterwards.
     */
    private const WIPE_DIRECTORIES = [
        'uploads',
        'exports',
        'reports',
        'backups',
        'contact_me',
        'cache',
        'temp',
        'debugbar',
    ];

    /**
     * Performs a factory reset:
     *  - keeps ALL user accounts (logins, identities, profiles) intact
     *  - permanently wipes every piece of their data (device/telemetry/ML,
     *    uploads, reports, backups, billing/subscriptions, etc.)
     *
     * @return array<string, int> Stats for the audit trail / UI.
     */
    public function resetMode(string $mode = 'soft'): array
    {
        $db = Database::connect();
        $stats = ['tables_wiped' => 0, 'files_deleted' => 0];

        if ($mode === 'logs_only') {
            $logDirs = ['cache', 'temp', 'reports', 'exports', 'debugbar'];
            foreach ($logDirs as $dir) {
                $stats['files_deleted'] += $this->wipeDirectoryContents(WRITEPATH . $dir);
            }
            if ($db->tableExists('tbl_admin_reports')) {
                $db->table('tbl_admin_reports')->truncate();
                $stats['tables_wiped']++;
            }
            return $stats;
        }

        $db->query('SET FOREIGN_KEY_CHECKS = 0');
        try {
            $preserve = self::PRESERVE_TABLES;
            if ($mode === 'hard') {
                // Hard mode wipes non-admin users & pairings too, preserving only migrations, settings, plans, cron_jobs
                $preserve = ['migrations', 'settings', 'plans', 'plan_versions', 'cron_jobs'];
            }

            $wipeTables = array_values(array_diff($db->listTables(), $preserve));
            foreach ($wipeTables as $table) {
                try {
                    $db->query("TRUNCATE TABLE `{$table}`");
                } catch (\Throwable $e) {
                    $db->table($table)->truncate();
                }
                $stats['tables_wiped']++;
            }
        } finally {
            $db->query('SET FOREIGN_KEY_CHECKS = 1');
        }

        // Wipe writable data folders.
        foreach (self::WIPE_DIRECTORIES as $dir) {
            $stats['files_deleted'] += $this->wipeDirectoryContents(WRITEPATH . $dir);
        }

        return $stats;
    }

    public function reset(): array
    {
        return $this->resetMode('soft');
    }

    /**
     * Recursively deletes all files inside a directory while preserving the
     * directory structure and sentinel files (.htaccess / index.html / .gitkeep).
     */
    private function wipeDirectoryContents(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $deleted = 0;
        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $deleted += $this->wipeDirectoryContents($path);
                continue;
            }

            if (in_array($item, ['.htaccess', 'index.html', '.gitkeep'], true)) {
                continue;
            }

            if (@unlink($path)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
