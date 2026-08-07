<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds DB-level uniqueness for call logs and device files and purges the
 * historical duplicates that predate the existing per-row ingest checks.
 *
 * Duplicates are "not proof of large data": they are the same logical record
 * captured more than once. We keep the FIRST inserted row (MIN(counter / id))
 * for each natural key.
 */
class DeduplicateCallLogsAndFiles extends Migration
{
    public function up()
    {
        $db = $this->db;

        // ---------------------------------------------------------------
        // 1) tbl_logs — natural key: number + time + duration + type + device + owner
        // ---------------------------------------------------------------
        $this->dropIfExistsIndex('tbl_logs', 'unique_call_natural_key');

        $db->query('
            DELETE l1 FROM tbl_logs l1
            INNER JOIN tbl_logs l2
                ON  l1.phone_number     = l2.phone_number
                AND l1.call_date        = l2.call_date
                AND l1.duration_seconds = l2.duration_seconds
                AND l1.call_type        = l2.call_type
                AND l1.device_id        = l2.device_id
                AND l1.owner_id         = l2.owner_id
                AND l1.counter          > l2.counter
        ');

        $this->applyIndex('tbl_logs', ['phone_number', 'call_date', 'duration_seconds', 'call_type', 'device_id', 'owner_id'], 'unique_call_natural_key', true);

        // ---------------------------------------------------------------
        // 2) tbl_device_files — natural key: path + device + owner.
        //    path is TEXT, so we add a stable sha1 hash column to index on.
        // ---------------------------------------------------------------
        if (!$this->columnExists('tbl_device_files', 'path_hash')) {
            $this->forge->addColumn('tbl_device_files', [
                'path_hash' => ['type' => 'CHAR', 'constraint' => 40, 'null' => false, 'default' => ''],
            ]);
        }

        $db->query('UPDATE tbl_device_files SET path_hash = SHA1(path) WHERE path_hash = \'\' OR path_hash IS NULL');

        $this->dropIfExistsIndex('tbl_device_files', 'unique_file_path');

        $db->query('
            DELETE f1 FROM tbl_device_files f1
            INNER JOIN tbl_device_files f2
                ON  f1.path_hash    = f2.path_hash
                AND f1.device_id    = f2.device_id
                AND f1.owner_id     = f2.owner_id
                AND f1.id           > f2.id
        ');

        $this->applyIndex('tbl_device_files', ['path_hash', 'device_id', 'owner_id'], 'unique_file_path', true);
    }

    public function down()
    {
        $this->dropIfExistsIndex('tbl_logs', 'unique_call_natural_key');
        $this->dropIfExistsIndex('tbl_device_files', 'unique_file_path');

        if ($this->columnExists('tbl_device_files', 'path_hash')) {
            $this->forge->dropColumn('tbl_device_files', 'path_hash');
        }
    }

    /**
     * Apply a (unique) index, ignoring duplicate-key errors if it already exists.
     */
    private function applyIndex(string $table, array $cols, string $name, bool $unique = false): void
    {
        try {
            $sql = 'ALTER TABLE ' . $table . ' ADD ' . ($unique ? 'UNIQUE' : '') . ' KEY ' . $name . ' (' . implode(', ', $cols) . ')';
            $this->db->query($sql);
        } catch (\Exception $e) {
            // Ignore if the index already exists (idempotency).
            log_message('debug', 'applyIndex skipped for ' . $name . ': ' . $e->getMessage());
        }
    }

    private function dropIfExistsIndex(string $table, string $name): void
    {
        if (!$this->indexExists($table, $name)) {
            return;
        }
        $this->db->query('ALTER TABLE ' . $table . ' DROP INDEX ' . $name);
    }

    private function indexExists(string $table, string $name): bool
    {
        $res = $this->db->query("SHOW INDEX FROM {$table} WHERE Key_name = '{$name}'")->getResultArray();
        return !empty($res);
    }

    private function columnExists(string $table, string $col): bool
    {
        $res = $this->db->query("SHOW COLUMNS FROM {$table} LIKE '{$col}'")->getRow();
        return $res !== null;
    }
}
