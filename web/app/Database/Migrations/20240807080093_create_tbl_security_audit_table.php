<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSecurityAudit extends Migration
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
            'vpn_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'proxy_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'user_ca_certs_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'open_ports_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'audit_timestamp' => [
                'type'       => 'BIGINT',
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
            'system_ca_certs_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'vpn_config_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'device_admin_apps_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'dns_config_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        $this->forge->createTable('tbl_security_audit', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_security_audit', true);
    }
}
