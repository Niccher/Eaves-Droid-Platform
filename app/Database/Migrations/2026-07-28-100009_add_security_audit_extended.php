<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSecurityAuditExtended extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_security_audit', [
            'system_ca_certs_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'vpn_config_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'device_admin_apps_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'dns_config_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_security_audit', [
            'system_ca_certs_json',
            'vpn_config_json',
            'device_admin_apps_json',
            'dns_config_json',
        ]);
    }
}