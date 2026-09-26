<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class MlSettingsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('settings');

        $now = Time::now()->toDateTimeString();

        $defaultUrl = getenv('ml_python_url') ?: (getenv('ML_PYTHON_URL') ?: 'http://ml:9070');
        $defaultToken = getenv('ML_INTERNAL_TOKEN') ?: (getenv('PYTHON_INTERNAL_TOKEN') ?: 'default_secure_token_change_me_in_prod');

        $parts = parse_url($defaultUrl);
        $host = $parts['host'] ?? 'ml';
        $port = (string)($parts['port'] ?? '9070');
        $path = $parts['path'] ?? '/api/v1/analysis-jobs';

        $settings = [
            ['class' => 'ml', 'key' => 'ml_enabled', 'value' => '1', 'type' => 'boolean'],
            ['class' => 'ml', 'key' => 'ml_python_enabled', 'value' => '1', 'type' => 'boolean'],
            ['class' => 'ml', 'key' => 'ml_anomaly_enabled', 'value' => '1', 'type' => 'boolean'],
            ['class' => 'ml', 'key' => 'ml_schedule_interval', 'value' => '3600', 'type' => 'integer'],
            ['class' => 'ml', 'key' => 'ml_python_url', 'value' => rtrim($defaultUrl, '/'), 'type' => 'string'],
            ['class' => 'ml', 'key' => 'ml_python_host', 'value' => $host, 'type' => 'string'],
            ['class' => 'ml', 'key' => 'ml_python_port', 'value' => $port, 'type' => 'string'],
            ['class' => 'ml', 'key' => 'ml_python_endpoint', 'value' => $path ?: '/api/v1/analysis-jobs', 'type' => 'string'],
            ['class' => 'ml', 'key' => 'ml_python_token', 'value' => $defaultToken, 'type' => 'string'],
            ['class' => 'ml', 'key' => 'ml_phpml_kmeans_k', 'value' => '4', 'type' => 'integer'],
            ['class' => 'ml', 'key' => 'ml_phpml_dbscan_epsilon', 'value' => '0.5', 'type' => 'float'],
            ['class' => 'ml', 'key' => 'ml_phpml_dbscan_minpoints', 'value' => '3', 'type' => 'integer'],
            ['class' => 'ml', 'key' => 'ml_phpml_isolationforest_trees', 'value' => '100', 'type' => 'integer'],
            ['class' => 'ml', 'key' => 'ml_phpml_isolationforest_samples', 'value' => '256', 'type' => 'integer'],
        ];

        foreach ($settings as $setting) {
            $existing = $builder->where('class', $setting['class'])
                                ->where('key', $setting['key'])
                                ->get()
                                ->getRowArray();

            if (!$existing) {
                $setting['created_at'] = $now;
                $setting['updated_at'] = $now;
                $builder->insert($setting);
            }
        }
    }
}
