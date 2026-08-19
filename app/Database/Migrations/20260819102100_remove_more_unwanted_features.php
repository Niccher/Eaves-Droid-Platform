<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveMoreUnwantedFeatures extends Migration
{
    public function up()
    {
        $this->db->table('tbl_feature_tiers')->whereIn('slug', ['power_rails', 'thermal', 'browser_history'])->delete();
    }

    public function down()
    {
        $this->db->table('tbl_feature_tiers')->insertBatch([
            [
                'category_type' => 'hardware',
                'slug'          => 'power_rails',
                'label'         => 'Power Rails',
                'description'   => 'Voltage, current, power per rail',
                'icon'          => 'fas fa-bolt',
                'color_class'   => 'card-orange',
                'bg_class'      => 'bg-orange',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'hardware',
                'slug'          => 'thermal',
                'label'         => 'Thermal',
                'description'   => 'Temperature zones, throttling, governors',
                'icon'          => 'fas fa-thermometer-half',
                'color_class'   => 'card-danger',
                'bg_class'      => 'bg-danger',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'software',
                'slug'          => 'browser_history',
                'label'         => 'Browser History',
                'description'   => 'Browsing history, bookmarks, and searches',
                'icon'          => 'fas fa-globe',
                'color_class'   => 'card-orange',
                'bg_class'      => 'bg-orange',
                'required_tier' => 'platinum',
            ],
        ]);
    }
}
