<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblVpnConfig extends Migration
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
                'null'       => true,
                'default'    => 0,
            ],
            'vpn_interface' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'vpn_dns_servers' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'vpn_routes' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'vpn_mtu' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'vpn_protocol' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'vpn_is_always_on' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'vpn_is_lockdown' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'vpn_package' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'vpn_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'vpn_apps' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'vpn_server' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'vpn_port' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'vpn_auth_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'vpn_ca_cert_sha256' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'vpn_client_cert_sha256' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'vpn_dns_search_domains' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'vpn_excluded_apps' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'vpn_included_apps' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'vpn_block_non_vpn' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
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
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');

        $this->forge->createTable('tbl_vpn_config', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_vpn_config', true);
    }
}
