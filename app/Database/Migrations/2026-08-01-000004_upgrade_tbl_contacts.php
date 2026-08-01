<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblContacts extends Migration
{
    public function up()
    {
        $fields = [
            'contact_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'contact_frequency'],
            'phonetic_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'contact_hash'],
            'nickname' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'phonetic_name'],
            'website' => ['type' => 'TEXT', 'null' => true, 'after' => 'nickname'],
            'im_handles' => ['type' => 'TEXT', 'null' => true, 'after' => 'website'],
            'social_profiles' => ['type' => 'TEXT', 'null' => true, 'after' => 'im_handles'],
            'events' => ['type' => 'TEXT', 'null' => true, 'after' => 'social_profiles'],
            'relation' => ['type' => 'TEXT', 'null' => true, 'after' => 'events'],
            'sip_address' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'relation'],
            'custom_fields' => ['type' => 'TEXT', 'null' => true, 'after' => 'sip_address'],
            'group_membership' => ['type' => 'TEXT', 'null' => true, 'after' => 'custom_fields'],
            'contact_last_updated' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'group_membership'],
            'raw_contact_account_type' => ['type' => 'TEXT', 'null' => true, 'after' => 'contact_last_updated'],
            'raw_contact_account_name' => ['type' => 'TEXT', 'null' => true, 'after' => 'raw_contact_account_type'],
            'sync_status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'raw_contact_account_name'],
            'is_restricted' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'sync_status'],
            'photo_thumbnail_base64' => ['type' => 'LONGTEXT', 'null' => true, 'after' => 'is_restricted'],
            'photo_file_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'photo_thumbnail_base64'],
            'display_name_source' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'photo_file_id'],
            'phonetic_given_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'display_name_source'],
            'phonetic_family_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'phonetic_given_name'],
            'transcription' => ['type' => 'TEXT', 'null' => true, 'after' => 'phonetic_family_name'],
            'communication_quality_score' => ['type' => 'FLOAT', 'null' => true, 'after' => 'transcription'],
            'communication_quality_category' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'communication_quality_score'],
            'quality_score' => ['type' => 'FLOAT', 'null' => true, 'after' => 'communication_quality_category'],
        ];
        $this->forge->addColumn('tbl_contacts', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_contacts', [
            'contact_hash','phonetic_name','nickname','website','im_handles','social_profiles','events',
            'relation','sip_address','custom_fields','group_membership','contact_last_updated',
            'raw_contact_account_type','raw_contact_account_name','sync_status','is_restricted',
            'photo_thumbnail_base64','photo_file_id','display_name_source',
            'phonetic_given_name','phonetic_family_name','transcription',
            'communication_quality_score','communication_quality_category','quality_score'
        ]);
    }
}
