<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixSoftwareSuiteCleanup extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Move high-level analysis feature slugs from category_type 'software' to 'analysis'
        $analysisSlugs = [
            'storage_analysis',
            'apps_analysis',
            'lifestyle_analysis',
            'social_analysis',
            'privacy_analysis',
            'subscriptions_analysis',
            'sentiment_analysis',
            'finance_analysis',
            'geospatial_analysis',
            'hotspots_analysis',
            'pdf_export',
        ];

        $db->table('tbl_feature_tiers')
           ->whereIn('slug', $analysisSlugs)
           ->update(['category_type' => 'analysis']);

        // 2. Ensure composite index exists on tbl_data_usage for fast grouped queries
        $fields = $db->getFieldNames('tbl_data_usage');
        if (in_array('owner_id', $fields, true) && in_array('extracted_at', $fields, true)) {
            // Check if index exists
            $keys = $db->getIndexData('tbl_data_usage');
            $hasIndex = false;
            foreach ($keys as $key) {
                if (in_array('owner_id', $key->fields, true) && in_array('extracted_at', $key->fields, true)) {
                    $hasIndex = true;
                    break;
                }
            }
            if (!$hasIndex) {
                $this->db->query("CREATE INDEX idx_data_usage_owner_extracted ON tbl_data_usage (owner_id, extracted_at)");
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $analysisSlugs = [
            'storage_analysis',
            'apps_analysis',
            'lifestyle_analysis',
            'social_analysis',
            'privacy_analysis',
            'subscriptions_analysis',
            'sentiment_analysis',
            'finance_analysis',
            'geospatial_analysis',
            'hotspots_analysis',
            'pdf_export',
        ];

        $db->table('tbl_feature_tiers')
           ->whereIn('slug', $analysisSlugs)
           ->update(['category_type' => 'software']);
    }
}
