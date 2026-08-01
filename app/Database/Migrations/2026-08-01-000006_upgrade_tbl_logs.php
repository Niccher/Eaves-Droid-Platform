<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class UpgradeTblLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_logs', [
            'is_conference' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'formatted_duration'],
            'conference_participants' => ['type' => 'TEXT', 'null' => true, 'after' => 'is_conference'],
            'parent_call_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'conference_participants'],
            'is_voip' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'parent_call_id'],
            'voip_app_package' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'is_voip'],
            'encryption_status' => ['type' => 'INT', 'default' => 0, 'after' => 'voip_app_package'],
            'call_subject' => ['type' => 'TEXT', 'null' => true, 'after' => 'encryption_status'],
            'call_notes' => ['type' => 'TEXT', 'null' => true, 'after' => 'call_subject'],
            'is_screening' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'call_notes'],
            'screening_result' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'is_screening'],
            'call_companion_app' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'screening_result'],
            'presentation' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'call_companion_app'],
            'cnap_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'presentation'],
            'cnap_number' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'cnap_name'],
            'redirecting_number' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'cnap_number'],
            'connected_number' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'redirecting_number'],
            'dialing_number' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'connected_number'],
        ]);
    }
    public function down() {
        $this->forge->dropColumn('tbl_logs', ['is_conference','conference_participants','parent_call_id','is_voip','voip_app_package','encryption_status','call_subject','call_notes','is_screening','screening_result','call_companion_app','presentation','cnap_name','cnap_number','redirecting_number','connected_number','dialing_number']);
    }
}
