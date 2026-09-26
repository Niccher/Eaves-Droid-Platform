<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblExtractedCalendarEvents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'event_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'start_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'end_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'duration' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'all_day' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'original_all_day' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'original_instance_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'organizer' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'timezone' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
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
            'uid' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'rrule' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'rdate' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'exdate' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'exrule' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'access_level' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'calendar_id' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'calendar_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'calendar_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'calendar_access_level' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'owner_account' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'event_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'visibility' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'transparency' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'availability' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'has_alarm' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'has_attendee_data' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'has_extended_properties' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'can_invite_others' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'last_synced' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'event_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'is_obsolete' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'self_attendee_status' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'last_date' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'original_sync_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'custom_app_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'custom_app_uri' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'deleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'dirty' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'attendees_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'reminders_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'extended_properties_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'event_id'], 'uq_calendar_event_per_device');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('event_id', false, false, 'event_id');
        $this->forge->addKey('start_time', false, false, 'start_time');
        $this->forge->addKey('organizer', false, false, 'organizer');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        $this->forge->createTable('tbl_extracted_calendar_events', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_extracted_calendar_events', true);
    }
}
