<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MergeSystemLocaleTier extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Delete standalone system_locale slug from feature tiers
        $db->table('tbl_feature_tiers')
           ->where('slug', 'system_locale')
           ->delete();

        // 2. Rename input_methods feature tier label to Input Methods & System Locale
        $db->table('tbl_feature_tiers')
           ->where('slug', 'input_methods')
           ->update(['label' => 'Input Methods & System Locale']);
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $db->table('tbl_feature_tiers')
           ->where('slug', 'input_methods')
           ->update(['label' => 'Input Methods']);
    }
}
