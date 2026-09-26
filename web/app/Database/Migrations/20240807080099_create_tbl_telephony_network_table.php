<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblTelephonyNetwork extends Migration
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
            'ims_volte_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'data_roaming_json' => [
                'type'       => 'TEXT',
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
            'ims_registration_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'ims_registration_tech' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'volte_provisioned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'vowifi_provisioned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'vowifi_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'rtcsupported' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'utsupported' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'mmttel_supported' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'wfc_mode_pref' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'wfc_roaming_mode_pref' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'volte_roaming_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'video_call_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'vt_quality' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'ims_capabilities' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'provisioned_ims_apns' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'emergency_numbers' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'emergency_categories' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'mwi_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'voice_message_count' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'call_forwarding_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'call_waiting_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'clip_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'clir_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'colp_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'ussd_service_available' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');

        $this->forge->createTable('tbl_telephony_network', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_telephony_network', true);
    }
}
