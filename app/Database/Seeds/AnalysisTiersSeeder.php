<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnalysisTiersSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        $analysisFeatures = [
            // 🟢 Free Tier Analysis Pages
            [
                'category_type' => 'software',
                'slug'          => 'storage_analysis',
                'label'         => 'Storage Forensics',
                'description'   => 'Storage capacity, content aging, and disk distribution forensics',
                'icon'          => 'fas fa-database',
                'color_class'   => 'card-info',
                'bg_class'      => 'bg-info',
                'required_tier' => 'free',
            ],
            [
                'category_type' => 'software',
                'slug'          => 'apps_analysis',
                'label'         => 'App Portfolio',
                'description'   => 'Installed applications inventory and exfiltration risks',
                'icon'          => 'fas fa-mobile-alt',
                'color_class'   => 'card-info',
                'bg_class'      => 'bg-info',
                'required_tier' => 'free',
            ],
            [
                'category_type' => 'software',
                'slug'          => 'lifestyle_analysis',
                'label'         => 'Lifestyle Profiling',
                'description'   => 'App usage screentime and daily activity breakdowns',
                'icon'          => 'fas fa-user-clock',
                'color_class'   => 'card-info',
                'bg_class'      => 'bg-info',
                'required_tier' => 'free',
            ],

            // 🥇 Gold Tier Analysis Pages
            [
                'category_type' => 'software',
                'slug'          => 'social_analysis',
                'label'         => 'Social Graph',
                'description'   => 'Top call/SMS contacts and interpersonal communication frequency',
                'icon'          => 'fas fa-project-diagram',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'software',
                'slug'          => 'privacy_analysis',
                'label'         => 'Privacy Audit',
                'description'   => 'Sideloaded APKs, unvetted permissions, and threat detection',
                'icon'          => 'fas fa-user-shield',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'software',
                'slug'          => 'subscriptions_analysis',
                'label'         => 'Subscription Tracker',
                'description'   => 'Recurring monthly bill forecasting and subscription due dates',
                'icon'          => 'fas fa-calendar-check',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],
            [
                'category_type' => 'software',
                'slug'          => 'sentiment_analysis',
                'label'         => 'Sentiment Profiler',
                'description'   => 'NLP conversation tone tracking and relationship health scoring',
                'icon'          => 'fas fa-smile-beam',
                'color_class'   => 'card-warning',
                'bg_class'      => 'bg-warning',
                'required_tier' => 'gold',
            ],

            // 💎 Platinum Tier Analysis Pages
            [
                'category_type' => 'software',
                'slug'          => 'finance_analysis',
                'label'         => 'Financial Forensics',
                'description'   => 'Mobile wallet outflows, M-PESA & bank settlement analysis',
                'icon'          => 'fas fa-money-bill-wave',
                'color_class'   => 'card-primary',
                'bg_class'      => 'bg-primary',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'software',
                'slug'          => 'location_analysis',
                'label'         => 'Geospatial Location',
                'description'   => 'Real-time GPS telemetry and historical location tracking',
                'icon'          => 'fas fa-map-marked-alt',
                'color_class'   => 'card-primary',
                'bg_class'      => 'bg-primary',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'software',
                'slug'          => 'hotspots_analysis',
                'label'         => 'Movement Hotspots',
                'description'   => 'Geospatial clustering and frequent visitation hotspots',
                'icon'          => 'fas fa-fire',
                'color_class'   => 'card-primary',
                'bg_class'      => 'bg-primary',
                'required_tier' => 'platinum',
            ],
            [
                'category_type' => 'software',
                'slug'          => 'report_export',
                'label'         => 'Forensic PDF Export',
                'description'   => 'Comprehensive executive forensic intelligence PDF report export',
                'icon'          => 'fas fa-file-pdf',
                'color_class'   => 'card-primary',
                'bg_class'      => 'bg-primary',
                'required_tier' => 'platinum',
            ],
        ];

        foreach ($analysisFeatures as $f) {
            $exists = $db->table('tbl_feature_tiers')
                ->where('slug', $f['slug'])
                ->get()
                ->getRowArray();

            if (!$exists) {
                $db->table('tbl_feature_tiers')->insert($f);
            } else {
                $db->table('tbl_feature_tiers')
                    ->where('slug', $f['slug'])
                    ->update([
                        'required_tier' => $f['required_tier'],
                        'label'         => $f['label'],
                        'description'   => $f['description']
                    ]);
            }
        }
    }
}
