<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblAccountsAuthenticatorTypes extends Migration
{
    public function up()
    {
        $fields = [
            'authenticator_types' => ['type' => 'TEXT', 'null' => true, 'after' => 'grant_kerberos_token'],
        ];
        $this->forge->addColumn('tbl_accounts', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_accounts', ['authenticator_types']);
    }
}
