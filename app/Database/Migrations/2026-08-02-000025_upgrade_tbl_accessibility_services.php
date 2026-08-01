<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblAccessibilityServicesFields extends Migration
{
    public function up()
    {
        $fields = [
            'feedback_type' => ['type' => 'INT', 'default' => 0, 'after' => 'description'],
        ];
        $this->forge->addColumn('tbl_accessibility_services', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_accessibility_services', ['feedback_type']);
    }
}
