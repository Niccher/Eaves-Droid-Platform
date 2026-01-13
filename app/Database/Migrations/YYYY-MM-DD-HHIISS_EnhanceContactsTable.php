<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEnhancedContactsTable extends Migration
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

            // Core contact data from extractor
            'contact_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Android ContactsContract.Contacts._ID',
            ],
            'display_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'comment'    => 'Primary display name',
            ],
            'phone_numbers' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON array of phone numbers',
            ],
            'phone_count' => [
                'type'       => 'INT',
                'default'    => 0,
                'null'       => false,
            ],
            'emails' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON array of email addresses',
            ],
            'email_count' => [
                'type'       => 'INT',
                'default'    => 0,
                'null'       => false,
            ],
            'photo_uri' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'Contact photo content URI',
            ],
            'companies' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON array of company names',
            ],
            'addresses' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON array of formatted address strings',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Contact management fields from Android
            'is_favorite' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => '1 = starred/favorite',
            ],
            'last_contacted' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Timestamp (ms) of last interaction',
            ],
            'contact_frequency' => [
                'type'       => 'INT',
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Times contacted (Android TIMES_CONTACTED)',
            ],

            // Device & extraction info
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Extraction timestamp from Android (ms)',
            ],

            // Ownership & sync
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'User ID owning this data',
            ],
            'is_synced' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'sync_count' => [
                'type'    => 'INT',
                'default' => 0,
                'null'    => false,
            ],
            'last_sync' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
            ],

            // Automatic timestamps
            'created_at' => [
                'type'       => 'TIMESTAMP',
                'default'    => 'CURRENT_TIMESTAMP',
                'null'       => false,
            ],
            'updated_at' => [
                'type'       => 'TIMESTAMP',
                'default'    => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'null'       => false,
            ],
        ]);

        // Primary key
        $this->forge->addPrimaryKey('counter');

        // Unique: prevent duplicates for same Android contact on same device for same user
        $this->forge->addUniqueKey(['contact_id', 'device_id', 'owner_id']);

        // Indexes for performance
        $this->forge->addKey('display_name');
        $this->forge->addKey('contact_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('owner_id');
        $this->forge->addKey('is_favorite');
        $this->forge->addKey('is_active');
        $this->forge->addKey('created_at');

        // Composite indexes
        $this->forge->addKey(['owner_id', 'device_id']);
        $this->forge->addKey(['owner_id', 'is_favorite']);
        $this->forge->addKey(['owner_id', 'display_name']);
        $this->forge->addKey(['owner_id', 'contact_frequency']);

        // Create table
        $this->forge->createTable('tbl_contacts', true);

        // Full-text search on name and notes
        $this->db->query("
            CREATE FULLTEXT INDEX ft_contacts_search
            ON tbl_contacts(display_name, notes)
        ");
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Contacts', true);
    }
}