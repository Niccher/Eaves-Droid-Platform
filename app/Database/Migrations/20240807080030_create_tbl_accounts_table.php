<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAccounts extends Migration
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
            'account_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'account_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'summary_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'total_count' => [
                'type'       => 'INT',
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
            'account_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_syncable' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'sync_auto' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'sync_interval' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => 0,
            ],
            'last_sync_time' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'last_sync_result' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'last_sync_error' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'user_data' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'auth_token_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'features' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'icon_base64' => [
                'type'       => 'LONGTEXT',
                'null'       => true,
            ],
            'small_icon_base64' => [
                'type'       => 'LONGTEXT',
                'null'       => true,
            ],
            'authenticator_description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'custom_auth_token' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'grant_kerberos_token' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'authenticator_types' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['owner_id', 'device_id', 'account_name', 'account_type'], 'uq_account_per_device');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('account_type', false, false, 'account_type');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        $this->forge->createTable('tbl_accounts', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_accounts', true);
    }
}
