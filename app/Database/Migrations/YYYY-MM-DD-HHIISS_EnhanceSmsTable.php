<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEnhancedSmsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            // Primary Key
            'SMS_ID' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // ---------- Basic SMS Info ----------
            'sms_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'inbox, sent, draft, outbox, queued, failed',
            ],

            'sms_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Phone number of sender/receiver',
            ],

            'contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Contact name from address book',
            ],

            'formatted_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Formatted phone number for display',
            ],

            'sms_thread_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Conversation thread ID',
            ],

            // ---------- Timestamps ----------
            'sms_time' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Message timestamp in milliseconds',
            ],

            'date_sent' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'When message was sent (for sent messages)',
            ],

            'date_received' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'When message was received (for inbox messages)',
            ],

            'timestamp_difference' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Difference between sent and received times',
            ],

            // ---------- Message Status ----------
            'sms_seen' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'Original seen status',
            ],

            'is_read' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
                'null'       => false,
                'comment'    => 'Whether message has been read',
            ],

            'is_unread' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
            ],

            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'complete',
                'null'       => false,
                'comment'    => 'Delivery status: complete, pending, failed',
            ],

            'status_code' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],

            'error_code' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Error code if delivery failed',
            ],

            'is_failed' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
            ],

            // ---------- Message IDs ----------
            'sms__id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Original SMS ID field',
            ],

            'android_sms_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Android SMS database ID',
            ],

            // ---------- Message Content ----------
            'sms_body' => [
                'type' => 'LONGTEXT',
                'null' => false,
                'comment' => 'Original message body',
            ],

            'body_encoded' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'Base64 encoded message body for secure transfer',
            ],

            'body_length' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Character count of message body',
            ],

            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'Subject for MMS messages',
            ],

            // ---------- Message Analysis ----------
            'has_attachment' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
            ],

            'is_media_message' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Whether message contains media',
            ],

            'has_emoji' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
            ],

            'is_mms' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Whether this is an MMS message',
            ],

            // ---------- Technical Details ----------
            'protocol' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Protocol type code',
            ],

            'protocol_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'SMS',
                'null'       => false,
                'comment'    => 'SMS, MMS, WAP_PUSH',
            ],

            'service_center' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'SMSC service center address',
            ],

            'reply_path_present' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
            ],

            'creator' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Message creator (for group messages)',
            ],

            // ---------- User Actions ----------
            'is_locked' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Whether message is locked/protected',
            ],

            'is_starred' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Whether message is starred/favorited',
            ],

            // ---------- Your Metadata Fields ----------
            'meta_owner' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'User ID who owns this SMS data',
            ],

            'meta_Print' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'comment'    => 'Device fingerprint identifier',
            ],

            'meta_uploaded' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'When SMS was uploaded to server',
            ],

            'meta_seen' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'Seen status on server',
            ],

            // ---------- Device Info ----------
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Device identifier where SMS was extracted',
            ],

            'extracted_at' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'When SMS was extracted from device',
            ],

            // ---------- Sync Tracking ----------
            'is_synced' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Whether synced to central server',
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
        ]);

        // Add these to your tbl_Sms migration:
        $this->forge->addColumn('tbl_Sms', [
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
        $this->forge->addPrimaryKey('SMS_ID');

        // Add indexes for common queries
        $this->forge->addKey('sms_thread_id');
        $this->forge->addKey('sms_number');
        $this->forge->addKey('sms_type');
        $this->forge->addKey('sms_time');
        $this->forge->addKey('meta_owner');
        $this->forge->addKey('meta_Print');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->addKey('updated_at');
        $this->forge->addKey('is_synced');

        // Composite indexes for common query patterns
        $this->forge->addKey(['meta_owner', 'sms_thread_id', 'sms_time']);
        $this->forge->addKey(['meta_owner', 'sms_number', 'sms_time']);
        $this->forge->addKey(['meta_owner', 'sms_type', 'sms_time']);
        $this->forge->addKey(['device_id', 'sms_time']);

        // Create the table
        $this->forge->createTable('tbl_Sms', true);

        // Add full-text search index
        $this->db->query("
            CREATE FULLTEXT INDEX ft_sms_search 
            ON tbl_Sms(sms_body, contact_name, subject)
        ");
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Sms', true);
    }
}