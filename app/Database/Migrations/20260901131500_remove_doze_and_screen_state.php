<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveDozeAndScreenState extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Delete feature tier records for doze_standby and screen_state
        $db->table('tbl_feature_tiers')
           ->whereIn('slug', ['doze_standby', 'screen_state'])
           ->delete();

        // 2. Move remaining analysis features (location_analysis, report_export, pdf_export) to category_type 'analysis'
        $db->table('tbl_feature_tiers')
           ->whereIn('slug', ['location_analysis', 'report_export', 'pdf_export'])
           ->update(['category_type' => 'analysis']);
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $db->table('tbl_feature_tiers')
           ->whereIn('slug', ['location_analysis', 'report_export', 'pdf_export'])
           ->update(['category_type' => 'software']);
    }
}
