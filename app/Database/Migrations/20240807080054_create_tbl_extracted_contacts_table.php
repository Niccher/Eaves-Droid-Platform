<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblExtractedContacts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'counter' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'contact_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'display_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'phone_numbers' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'phone_count' => [
                'type'       => 'INT',
                'null'       => false,
                'default'    => 0,
            ],
            'emails' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'email_count' => [
                'type'       => 'INT',
                'null'       => false,
                'default'    => 0,
            ],
            'photo_uri' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'companies' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'addresses' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'notes' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'is_favorite' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'last_contacted' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'contact_frequency' => [
                'type'       => 'INT',
                'null'       => false,
                'default'    => 0,
            ],
            'contact_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'phonetic_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nickname' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'website' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'im_handles' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'social_profiles' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'events' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'relation' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'sip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'custom_fields' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'group_membership' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'contact_last_updated' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'raw_contact_account_type' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'raw_contact_account_name' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'sync_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'is_restricted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'photo_thumbnail_base64' => [
                'type'       => 'LONGTEXT',
                'null'       => true,
            ],
            'photo_file_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'display_name_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'phonetic_given_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'phonetic_family_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'transcription' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'communication_quality_score' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'communication_quality_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'quality_score' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'is_synced' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'sync_count' => [
                'type'       => 'INT',
                'null'       => false,
                'default'    => 0,
            ],
            'last_sync' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('counter');
        $this->forge->addUniqueKey(['contact_id', 'device_id', 'owner_id'], 'contact_id_device_id_owner_id');
        $this->forge->addKey('display_name', false, false, 'display_name');
        $this->forge->addKey('contact_id', false, false, 'contact_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('is_favorite', false, false, 'is_favorite');
        $this->forge->addKey('is_active', false, false, 'is_active');
        $this->forge->addKey('created_at', false, false, 'created_at');
        $this->forge->addKey(['owner_id', 'device_id'], false, false, 'owner_id_device_id');
        $this->forge->addKey(['owner_id', 'is_favorite'], false, false, 'owner_id_is_favorite');
        $this->forge->addKey(['owner_id', 'display_name'], false, false, 'owner_id_display_name');
        $this->forge->addKey(['owner_id', 'contact_frequency'], false, false, 'owner_id_contact_frequency');
        $this->forge->addKey('phone_count', false, false, 'phone_count');
        $this->forge->addKey('email_count', false, false, 'email_count');
        $this->forge->addKey('last_contacted', false, false, 'last_contacted');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');
        $this->forge->addKey('is_synced', false, false, 'is_synced');
        $this->forge->addKey('last_sync', false, false, 'last_sync');
        $this->forge->addKey('updated_at', false, false, 'updated_at');

        $this->forge->createTable('tbl_extracted_contacts', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_extracted_contacts', true);
    }
}
