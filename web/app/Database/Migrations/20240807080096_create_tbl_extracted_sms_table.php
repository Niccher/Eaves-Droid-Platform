<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblExtractedSms extends Migration
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
            'android_sms_id' => [
                'type'       => 'BIGINT',
                'null'       => false,
            ],
            'thread_id' => [
                'type'       => 'BIGINT',
                'null'       => false,
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'formatted_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'body' => [
                'type'       => 'LONGTEXT',
                'null'       => false,
            ],
            'body_length' => [
                'type'       => 'INT',
                'null'       => false,
                'default'    => 0,
            ],
            'sms_date' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'sms_date_sent' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'sms_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'type_code' => [
                'type'       => 'INT',
                'null'       => false,
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'is_seen' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'status_code' => [
                'type'       => 'INT',
                'null'       => false,
                'default'    => 0,
            ],
            'error_code' => [
                'type'       => 'INT',
                'null'       => false,
                'default'    => 0,
            ],
            'protocol' => [
                'type'       => 'INT',
                'null'       => false,
            ],
            'protocol_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'is_mms' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'mms_subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'mms_attachments' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'delivery_report' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'reply_path_present' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'is_spam' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_archived' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'is_scheduled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'schedule_time' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'sub_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'carrier_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'message_bundle_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'group_recipients' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'is_group' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'rich_communication_service_data' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'verified_sender' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'service_center' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_locked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'creator' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
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
        $this->forge->addUniqueKey(['android_sms_id', 'device_id', 'owner_id'], 'android_sms_id_device_id_owner_id');
        $this->forge->addKey('address', false, false, 'address');
        $this->forge->addKey('sms_date', false, false, 'sms_date');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('thread_id', false, false, 'thread_id');
        $this->forge->addKey('sms_type', false, false, 'sms_type');
        $this->forge->addKey('is_read', false, false, 'is_read');
        $this->forge->addKey('is_seen', false, false, 'is_seen');
        $this->forge->addKey('status_code', false, false, 'status_code');
        $this->forge->addKey('error_code', false, false, 'error_code');
        $this->forge->addKey('protocol_type', false, false, 'protocol_type');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');
        $this->forge->addKey('created_at', false, false, 'created_at');
        $this->forge->addKey('updated_at', false, false, 'updated_at');
        $this->forge->addKey(['owner_id', 'sms_date'], false, false, 'owner_id_sms_date');
        $this->forge->addKey(['device_id', 'sms_date'], false, false, 'device_id_sms_date');
        $this->forge->addKey(['owner_id', 'address'], false, false, 'owner_id_address');

        $this->forge->createTable('tbl_extracted_sms', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_extracted_sms', true);
    }
}
