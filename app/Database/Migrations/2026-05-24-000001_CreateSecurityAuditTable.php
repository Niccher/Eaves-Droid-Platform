<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSecurityAuditTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
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
                'default'    => 0,
            ],
            'proxy_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'user_ca_certs_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'open_ports_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'audit_timestamp' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'extracted_at' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('owner_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('extracted_at');
        
        $this->forge->createTable('tbl_security_audit', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_security_audit', true);
    }
}
