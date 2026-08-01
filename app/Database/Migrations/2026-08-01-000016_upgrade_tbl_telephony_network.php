<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblTelephonyNetwork extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_telephony_network', [
            'ims_registration_state'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'ims_registration_tech'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'volte_provisioned'       => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'vowifi_provisioned'      => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'vowifi_enabled'          => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'rtcsupported'            => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'utsupported'             => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'mmttel_supported'        => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'wfc_mode_pref'           => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'wfc_roaming_mode_pref'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'volte_roaming_enabled'   => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'video_call_enabled'      => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'vt_quality'              => ['type' => 'INT', 'null' => true],
            'ims_capabilities'        => ['type' => 'TEXT', 'null' => true],
            'provisioned_ims_apns'    => ['type' => 'TEXT', 'null' => true],
            'emergency_numbers'       => ['type' => 'TEXT', 'null' => true],
            'emergency_categories'    => ['type' => 'TEXT', 'null' => true],
            'mwi_status'              => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'voice_message_count'     => ['type' => 'INT', 'null' => true],
            'call_forwarding_status'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'call_waiting_enabled'    => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'clip_enabled'            => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'clir_enabled'            => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'colp_enabled'            => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'ussd_service_available'  => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_telephony_network', [
            'ims_registration_state', 'ims_registration_tech', 'volte_provisioned',
            'vowifi_provisioned', 'vowifi_enabled', 'rtcsupported', 'utsupported',
            'mmttel_supported', 'wfc_mode_pref', 'wfc_roaming_mode_pref',
            'volte_roaming_enabled', 'video_call_enabled', 'vt_quality',
            'ims_capabilities', 'provisioned_ims_apns', 'emergency_numbers',
            'emergency_categories', 'mwi_status', 'voice_message_count',
            'call_forwarding_status', 'call_waiting_enabled', 'clip_enabled',
            'clir_enabled', 'colp_enabled', 'ussd_service_available'
        ]);
    }
}
