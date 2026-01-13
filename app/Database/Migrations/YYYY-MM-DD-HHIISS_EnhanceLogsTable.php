<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEnhancedLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            // Primary Key
            'counter' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // ---------- From Java: Call Log Data ----------
            'contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'From cursor.getString(nameIndex) or phone number',
            ],

            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'From cursor.getString(numberIndex)',
            ],

            'call_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'From getCallTypeString(callType): Incoming, Outgoing, Missed, etc.',
            ],

            'call_date' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'From cursor.getLong(dateIndex) - milliseconds',
            ],

            'duration_seconds' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'From cursor.getInt(durationIndex)',
            ],

            'formatted_duration' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'From formatDuration(duration) - e.g., "5m 30s"',
            ],

            // ---------- Device & Extraction Info ----------
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Device identifier where call log was extracted',
            ],

            'extracted_at' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'From result.put("extracted_at", System.currentTimeMillis())',
            ],

            // ---------- Ownership ----------
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'User ID - the owner of the data',
            ],

            // ---------- Additional Tracking ----------
            'is_synced' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Whether this record has been synced to central server',
            ],

            'sync_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Number of times synced',
            ],

            'last_sync' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'comment' => 'Last sync timestamp',
            ],

            // ---------- Timestamps ----------
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ],

            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'null'    => false,
            ],

            // ---------- Call Details (Optional Enhancements) ----------
            'call_direction' => [
                'type'       => 'ENUM',
                'constraint' => ['incoming', 'outgoing', 'missed'],
                'null'       => true,
                'comment'    => 'Simplified call direction',
            ],

            'call_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
                'null'       => false,
                'comment'    => 'Number of calls with same contact within time window',
            ],

            'timezone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Timezone when call was made',
            ],

            'geolocation' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Location data if available',
            ],
        ]);

        // Add these to your tbl_Logs migration:
        $this->forge->addColumn('tbl_Logs', [
            'is_deleted' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'after'      => 'is_synced',
            ],
            'deleted_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'after'   => 'is_deleted',
            ],
            'deletion_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'deleted_at',
            ],
            'sync_status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'deleted_pending', 'deleted_synced'],
                'default'    => 'active',
                'null'       => false,
                'after'      => 'deletion_reason',
            ],
        ]);

        // Primary Key
        $this->forge->addPrimaryKey('counter');

        // Add indexes for common queries
        $this->forge->addKey('phone_number');
        $this->forge->addKey('call_date');
        $this->forge->addKey('call_type');
        $this->forge->addKey('device_id');
        $this->forge->addKey('owner_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey('updated_at');
        $this->forge->addKey('is_synced');

        // Composite indexes for common query patterns
        $this->forge->addKey(['owner_id', 'call_date']);
        $this->forge->addKey(['device_id', 'call_date']);
        $this->forge->addKey(['phone_number', 'call_date']);
        $this->forge->addKey(['call_type', 'call_date']);

        // Create the table
        $this->forge->createTable('tbl_Logs', true);

        // Add full-text search index for contact names
        $this->db->query("CREATE FULLTEXT INDEX ft_logs_contact ON tbl_Logs(contact_name)");
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Logs', true);
    }
}