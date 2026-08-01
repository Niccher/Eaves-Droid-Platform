<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblCalendarEventsFields extends Migration
{
    public function up()
    {
        $fields = [
            'duration'                => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'end_time'],
            'original_all_day'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'all_day'],
            'original_instance_time'  => ['type' => 'BIGINT', 'null' => true, 'after' => 'original_all_day'],
            'timezone'                => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'organizer'],
            'uid'                     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'rrule'                   => ['type' => 'TEXT', 'null' => true],
            'rdate'                   => ['type' => 'TEXT', 'null' => true],
            'exdate'                  => ['type' => 'TEXT', 'null' => true],
            'exrule'                  => ['type' => 'TEXT', 'null' => true],
            'access_level'            => ['type' => 'INT', 'default' => 0],
            'calendar_id'             => ['type' => 'BIGINT', 'null' => true],
            'calendar_name'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'calendar_color'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'calendar_access_level'   => ['type' => 'INT', 'default' => 0],
            'owner_account'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'event_status'            => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'visibility'              => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'transparency'            => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'availability'            => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'has_alarm'               => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'has_attendee_data'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'has_extended_properties' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'can_invite_others'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'last_synced'             => ['type' => 'BIGINT', 'null' => true],
            'event_color'             => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'is_obsolete'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'self_attendee_status'    => ['type' => 'INT', 'default' => 0],
            'last_date'               => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'original_sync_id'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'custom_app_package'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'custom_app_uri'          => ['type' => 'TEXT', 'null' => true],
            'deleted'                 => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'dirty'                   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'attendees_json'          => ['type' => 'TEXT', 'null' => true],
            'reminders_json'          => ['type' => 'TEXT', 'null' => true],
            'extended_properties_json'=> ['type' => 'TEXT', 'null' => true],
        ];
        $this->forge->addColumn('tbl_calendar_events', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_calendar_events', [
            'duration', 'original_all_day', 'original_instance_time', 'timezone', 'uid', 'rrule',
            'rdate', 'exdate', 'exrule', 'access_level', 'calendar_id', 'calendar_name',
            'calendar_color', 'calendar_access_level', 'owner_account', 'event_status', 'visibility',
            'transparency', 'availability', 'has_alarm', 'has_attendee_data', 'has_extended_properties',
            'can_invite_others', 'last_synced', 'event_color', 'is_obsolete', 'self_attendee_status',
            'last_date', 'original_sync_id', 'custom_app_package', 'custom_app_uri', 'deleted', 'dirty',
            'attendees_json', 'reminders_json', 'extended_properties_json'
        ]);
    }
}
