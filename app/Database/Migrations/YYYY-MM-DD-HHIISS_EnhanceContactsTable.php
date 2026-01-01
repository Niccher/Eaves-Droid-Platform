<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEnhancedContactsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            // Primary Key
            'Contact_ID' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // ---------- From Java: Contact Data ----------
            'contact_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'From ContactsContract.Contacts._ID',
            ],

            'display_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'comment'    => 'From ContactsContract.Contacts.DISPLAY_NAME',
            ],

            // Keep original Name field for backward compatibility
            'Name' => [
                'type'       => 'VARCHAR',
                'constraint' => 250,
                'null'       => true,
                'comment'    => 'Original name field (kept for compatibility)',
            ],

            // Phone numbers as JSON array
            'phone_numbers' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of phone numbers from ContactsContract.CommonDataKinds.Phone.NUMBER',
            ],

            'phone_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'From phoneNumbers.length()',
            ],

            // Keep original Number field for backward compatibility
            'Number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Primary phone number (first from phone_numbers array)',
            ],

            // Additional contact information (can be extracted with enhanced Java code)
            'emails' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of email addresses',
            ],

            'email_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],

            'photo_uri' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'Contact photo URI',
            ],

            'company' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'job_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'addresses' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of addresses',
            ],

            // Keep original ID/Updated fields
            'android_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Original ID field - Android contact ID',
            ],

            'Updated' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
                'comment'    => 'Last update timestamp from device',
            ],

            // ---------- Device & Extraction Info ----------
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Device identifier where contacts were extracted',
            ],

            'extracted_at' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'From result.put("extracted_at", System.currentTimeMillis())',
            ],

            // ---------- Your Original Metadata Fields ----------
            'meta_Inserted' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'kotlin',
                'null'       => false,
                'comment'    => 'When inserted to the database',
            ],

            'meta_Opened' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Number of times opened/viewed',
            ],

            'meta_Viewed' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Number of times viewed',
            ],

            'meta_Owner' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'User ID - the owner of the data',
            ],

            'meta_Print' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
                'null'       => false,
                'comment'    => 'Unique number identifying the device fingerprint',
            ],

            // ---------- Contact Management ----------
            'is_favorite' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Whether contact is marked as favorite',
            ],

            'last_contacted' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Timestamp of last call/message to this contact',
            ],

            'contact_frequency' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'How many times contacted',
            ],

            // ---------- Sync & Status ----------
            'is_synced' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Whether this contact has been synced to server',
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

            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'comment'    => '1 = active, 0 = deleted/removed',
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

        // Add these to your tbl_Contacts migration:
        $this->forge->addColumn('tbl_Contacts', [
            'is_deleted' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'after'      => 'is_active',
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
        $this->forge->addPrimaryKey('Contact_ID');

        // Unique constraint: Same contact on same device for same owner
        $this->forge->addUniqueKey(['contact_id', 'device_id', 'meta_Owner']);

        // Add indexes for common queries
        $this->forge->addKey('display_name');
        $this->forge->addKey('contact_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('meta_Owner');
        $this->forge->addKey('meta_Print');
        $this->forge->addKey('is_favorite');
        $this->forge->addKey('is_active');
        $this->forge->addKey('created_at');
        $this->forge->addKey('updated_at');

        // Composite indexes for common query patterns
        $this->forge->addKey(['meta_Owner', 'device_id']);
        $this->forge->addKey(['meta_Owner', 'is_favorite']);
        $this->forge->addKey(['meta_Owner', 'display_name']);
        $this->forge->addKey(['meta_Owner', 'contact_frequency']);

        // Create the table
        $this->forge->createTable('tbl_Contacts', true);

        // Add full-text search index
        $this->db->query("
            CREATE FULLTEXT INDEX ft_contacts_search 
            ON tbl_Contacts(display_name, company, notes)
        ");
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Contacts', true);
    }
}