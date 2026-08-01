<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblAccounts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_accounts', [
            'account_label'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_syncable'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'sync_auto'                => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'sync_interval'            => ['type' => 'INT', 'default' => 0],
            'last_sync_time'           => ['type' => 'BIGINT', 'null' => true],
            'last_sync_result'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'last_sync_error'          => ['type' => 'TEXT', 'null' => true],
            'user_data'                => ['type' => 'TEXT', 'null' => true],
            'auth_token_type'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'features'                 => ['type' => 'TEXT', 'null' => true],
            'icon_base64'              => ['type' => 'LONGTEXT', 'null' => true],
            'small_icon_base64'        => ['type' => 'LONGTEXT', 'null' => true],
            'authenticator_description' => ['type' => 'TEXT', 'null' => true],
            'custom_auth_token'        => ['type' => 'TEXT', 'null' => true],
            'grant_kerberos_token'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_accounts', [
            'account_label', 'is_syncable', 'sync_auto', 'sync_interval', 'last_sync_time',
            'last_sync_result', 'last_sync_error', 'user_data', 'auth_token_type', 'features',
            'icon_base64', 'small_icon_base64', 'authenticator_description', 'custom_auth_token',
            'grant_kerberos_token'
        ]);
    }
}
