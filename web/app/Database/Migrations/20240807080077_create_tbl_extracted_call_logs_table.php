<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblExtractedCallLogs extends Migration
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
            'contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'call_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'type_code' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'call_date' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'duration_seconds' => [
                'type'       => 'INT',
                'null'       => false,
                'default'    => 0,
            ],
            'formatted_duration' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'is_conference' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'conference_participants' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'parent_call_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_voip' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'voip_app_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'encryption_status' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'call_subject' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'call_notes' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'is_screening' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'screening_result' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'call_companion_app' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'presentation' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'cnap_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'cnap_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'redirecting_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'connected_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'dialing_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
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
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'call_direction' => [
                'type'       => 'ENUM',
                'constraint' => ['incoming', 'outgoing', 'missed'],
                'null'       => true,
            ],
            'call_count' => [
                'type'       => 'INT',
                'null'       => false,
                'default'    => 1,
            ],
            'timezone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'geolocation' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('counter');
        $this->forge->addUniqueKey(['phone_number', 'call_date', 'duration_seconds', 'call_type', 'device_id', 'owner_id'], 'unique_call_natural_key');
        $this->forge->addKey('phone_number', false, false, 'phone_number');
        $this->forge->addKey('call_date', false, false, 'call_date');
        $this->forge->addKey('call_type', false, false, 'call_type');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('created_at', false, false, 'created_at');
        $this->forge->addKey('updated_at', false, false, 'updated_at');
        $this->forge->addKey('is_synced', false, false, 'is_synced');
        $this->forge->addKey(['owner_id', 'call_date'], false, false, 'owner_id_call_date');
        $this->forge->addKey(['device_id', 'call_date'], false, false, 'device_id_call_date');
        $this->forge->addKey(['phone_number', 'call_date'], false, false, 'phone_number_call_date');
        $this->forge->addKey(['call_type', 'call_date'], false, false, 'call_type_call_date');
        $this->forge->addKey('contact_name', false, false, 'contact_name');
        $this->forge->addKey('duration_seconds', false, false, 'duration_seconds');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');
        $this->forge->addKey('sync_count', false, false, 'sync_count');
        $this->forge->addKey('last_sync', false, false, 'last_sync');
        $this->forge->addKey('call_direction', false, false, 'call_direction');
        $this->forge->addKey('call_count', false, false, 'call_count');

        $this->forge->createTable('tbl_extracted_call_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_extracted_call_logs', true);
    }
}
