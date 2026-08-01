<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class UpgradeTblSms extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_sms', [
            'is_mms' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'protocol_type'],
            'mms_subject' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'is_mms'],
            'mms_attachments' => ['type' => 'TEXT', 'null' => true, 'after' => 'mms_subject'],
            'delivery_report' => ['type' => 'INT', 'default' => 0, 'after' => 'mms_attachments'],
            'reply_path_present' => ['type' => 'INT', 'default' => 0, 'after' => 'delivery_report'],
            'is_spam' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'reply_path_present'],
            'is_archived' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'is_spam'],
            'is_scheduled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'is_archived'],
            'schedule_time' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'is_scheduled'],
            'sub_id' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'schedule_time'],
            'carrier_id' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'sub_id'],
            'message_bundle_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'carrier_id'],
            'group_recipients' => ['type' => 'INT', 'default' => 0, 'after' => 'message_bundle_id'],
            'is_group' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'group_recipients'],
            'rich_communication_service_data' => ['type' => 'TEXT', 'null' => true, 'after' => 'is_group'],
            'verified_sender' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'rich_communication_service_data'],
        ]);
    }
    public function down()
    {
        $this->forge->dropColumn('tbl_sms', ['is_mms','mms_subject','mms_attachments','delivery_report','reply_path_present','is_spam','is_archived','is_scheduled','schedule_time','sub_id','carrier_id','message_bundle_id','group_recipients','is_group','rich_communication_service_data','verified_sender']);
    }
}
